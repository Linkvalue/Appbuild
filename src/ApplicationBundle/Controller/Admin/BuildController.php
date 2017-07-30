<?php

namespace Majora\OTAStore\ApplicationBundle\Controller\Admin;

use Doctrine\Common\Collections\Criteria;
use Majora\OTAStore\ApplicationBundle\Controller\BaseController;
use Majora\OTAStore\ApplicationBundle\Entity\Application;
use Majora\OTAStore\ApplicationBundle\Entity\Build;
use Majora\OTAStore\ApplicationBundle\Form\Type\BuildType;
use Majora\OTAStore\Pagination\Page;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class BuildController.
 *
 * Manage application builds.
 */
class BuildController extends BaseController
{
    /**
     * List given application builds.
     *
     * @ParamConverter("application", options={"mapping": {"application_id": "id"}})
     *
     * @param Application $application
     * @param Request     $request
     *
     * @return Response
     */
    public function listAction(Application $application, Request $request)
    {
        if (
            (!($isAskingForEnabled = $request->query->getBoolean('enabled', true)) || !$application->isEnabled())
            && !$this->isGranted('ROLE_ADMIN')
        ) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->getUserApplications()->contains($application)) {
            throw $this->createAccessDeniedException();
        }

        $page = new Page(
            $request->query->getInt('page', Page::FIRST_PAGE_NUMBER),
            $application->getBuilds()->matching(
                $criteria = Criteria::create()->where(Criteria::expr()->eq('enabled', $isAskingForEnabled))
            )->count()
        );
        $page->setElements($application->getBuilds()->matching(
            $page->setupCriteria($criteria)->orderBy(['updatedAt' => Criteria::DESC])
        ));

        return $this->render(
            'MajoraOTAStoreApplicationBundle:Build:list.html.twig',
            [
                'application' => $application,
                'page' => $page,
            ]
        );
    }

    /**
     * Create build.
     *
     * @ParamConverter("application", options={"mapping": {"application_id": "id"}})
     *
     * @param Application $application
     * @param Request     $request
     *
     * @return Response
     */
    public function createAction(Application $application, Request $request)
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->getUserApplications()->contains($application)) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->container->get('form.factory')->create(
            BuildType::class,
            $build = (new Build())->setApplication($application),
            ['csrf_token_id' => BuildType::TOKEN_CREATION]
        );

        if ($request->isMethod(Request::METHOD_POST)) {
            // Set build filePath using request build filename
            $uploadHelper = $this->container->get('appbuild.application.build_upload_helper');
            $buildFormData = $request->request->get('majoraotastore_build');
            if (!empty($buildFormData['filename'])) {
                $build->setFilePath(
                    $uploadHelper->getFilePath($application, $buildFormData['filename'])
                );
            }
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $em = $this->container->get('doctrine.orm.entity_manager');
                $em->persist($build);
                $em->flush();

                $this->addFlash('success', $this->container->get('translator')->trans('build.create.flash.success'));

                return new RedirectResponse($this->container->get('router')->generate(
                    'majoraotastore_admin_build_list', [
                        'application_id' => $application->getId(),
                    ]
                ));
            }
        }

        return $this->render('MajoraOTAStoreApplicationBundle:Build:create.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * Update build.
     *
     * @ParamConverter("application", options={"mapping": {"application_id": "id"}})
     *
     * @param Application $application
     * @param Build       $build
     * @param Request     $request
     *
     * @return Response
     */
    public function updateAction(Application $application, Build $build, Request $request)
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        if ($build->getApplication() != $application) {
            throw $this->createNotFoundException();
        }

        if (!$this->getUserApplications()->contains($application)) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->container->get('form.factory')->create(
            BuildType::class,
            $build,
            ['csrf_token_id' => BuildType::TOKEN_EDITION]
        );

        if ($request->isMethod(Request::METHOD_POST)) {
            // Set build filePath using request build filename
            $uploadHelper = $this->container->get('appbuild.application.build_upload_helper');
            $buildFormData = $request->request->get('majoraotastore_build');
            if (!empty($buildFormData['filename'])) {
                $build->setFilePath(
                    $uploadHelper->getFilePath($application, $buildFormData['filename'])
                );
            }
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $em = $this->container->get('doctrine.orm.entity_manager');
                $em->persist($build);
                $em->flush();

                $this->addFlash('success', $this->container->get('translator')->trans('build.update.flash.success'));

                return new RedirectResponse($this->container->get('router')->generate(
                    'majoraotastore_admin_build_list', [
                        'application_id' => $application->getId(),
                    ]
                ));
            }
        }

        return $this->render(
            'MajoraOTAStoreApplicationBundle:Build:update.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * Delete build.
     *
     * @ParamConverter("application", options={"mapping": {"application_id": "id"}})
     *
     * @param Application $application
     * @param Build       $build
     *
     * @return Response
     */
    public function deleteAction(Application $application, Build $build)
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        if ($build->getApplication() != $application) {
            throw $this->createNotFoundException();
        }

        if (!$this->getUserApplications()->contains($application)) {
            throw $this->createAccessDeniedException();
        }

        $em = $this->container->get('doctrine.orm.entity_manager');
        $em->remove($build);
        $em->flush();

        // Remove build file from disk
        unlink($build->getFilePath());

        return new RedirectResponse(
            $this->container->get('router')->generate(
                'majoraotastore_admin_build_list',
                [
                    'application_id' => $application->getId(),
                ]
            )
        );
    }

    /**
     * Upload build file using AJAX.
     *
     * @ParamConverter("application", options={"mapping": {"application_id": "id"}})
     *
     * @param Application $application
     * @param Request     $request
     *
     * @return Response
     */
    public function uploadAjaxAction(Application $application, Request $request)
    {
        $translator = $this->container->get('translator');

        if (!$this->isGranted('ROLE_ADMIN') || !$this->getUserApplications()->contains($application)) {
            return new JsonResponse([
                'success' => false,
                'error' => $translator->trans('admin.upload.message.not_allowed'),
            ]);
        }

        if (!$request->isXmlHttpRequest() || !$request->isMethod(Request::METHOD_POST)) {
            return new JsonResponse([
                'success' => false,
                'error' => $translator->trans('admin.upload.message.unexpected_method'),
            ]);
        }

        $uploadedFile = $request->files->get('build_file');
        if (!$uploadedFile instanceof UploadedFile) {
            return new JsonResponse([
                'success' => false,
                'error' => $translator->trans('admin.upload.message.upload_failure'),
            ]);
        }

        $uploadHelper = $this->container->get('appbuild.application.build_upload_helper');
        $filename = $uploadHelper->generateFilename($uploadedFile);
        try {
            $uploadHelper->moveUploadedFile($uploadedFile, $application, $filename);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $translator->trans('admin.upload.message.move_failure'),
            ]);
        }

        return new JsonResponse([
            'success' => true,
            'filename' => $filename,
        ]);
    }

    /**
     * Trigger build download process.
     *
     * @ParamConverter("application", options={"mapping": {"application_id": "id"}})
     *
     * @param Application $application
     * @param Build       $build
     *
     * @return Response
     */
    public function downloadAction(Application $application, Build $build)
    {
        if ($build->getApplication() != $application) {
            throw $this->createNotFoundException();
        }

        if (!$this->getUserApplications()->contains($application)) {
            throw $this->createAccessDeniedException();
        }

        if (!is_file($build->getFilePath())) {
            $this->addFlash('alert', $this->container->get('translator')->trans('build.download.error.file_not_exists'));

            return new RedirectResponse($this->container->get('router')->generate(
                'majoraotastore_admin_build_list', [
                    'application_id' => $application->getId(),
                ]
            ));
        }

        $router = $this->container->get('router');
        $buildToken = $this->container->get('appbuild.application.build_token_manager')->generate($build);

        switch ($build->getApplication()->getSupport()) {
            case Application::SUPPORT_IOS:
                // Redirect to iOS specific protocol to download build manifest
                $response = new RedirectResponse(
                    sprintf(
                        'itms-services://?action=download-manifest&url=%s',
                        urlencode($router->generate(
                            'majoraotastore_admin_build_get_manifest',
                            [
                                'application_id' => $application->getId(),
                                'id' => $build->getId(),
                                'token' => $buildToken->getToken(),
                            ],
                            UrlGeneratorInterface::ABSOLUTE_URL
                        ))
                    ),
                    302,
                    [
                        'Content-Type' => 'text/html',
                    ]
                );
                break;

            default:
                // Download build file
                $response = new RedirectResponse(
                    $router->generate(
                        'majoraotastore_admin_build_get_file',
                        [
                            'application_id' => $application->getId(),
                            'id' => $build->getId(),
                            'token' => $buildToken->getToken(),
                        ]
                    )
                );
                break;
        }

        return $response;
    }

    /**
     * Download manifest for given build.
     *
     * Manifest must be accessible without logging in (see security.yml).
     *
     * @ParamConverter("application", options={"mapping": {"application_id": "id"}})
     *
     * @param Application $application
     * @param Build       $build
     * @param Request     $request
     *
     * @return Response
     */
    public function getManifestAction(Application $application, Build $build, Request $request)
    {
        if ($build->getApplication() != $application) {
            throw $this->createNotFoundException();
        }

        if (!$token = $request->query->get('token')) {
            throw $this->createAccessDeniedException();
        }

        switch ($application->getSupport()) {
            case Application::SUPPORT_IOS:
                $response = $this->render(
                    sprintf('MajoraOTAStoreApplicationBundle:Manifest:%s/manifest.plist.twig', $application->getSupport()),
                    [
                        'application' => $application,
                        'build' => $build,
                        'token' => $token,
                    ]
                );

                $response->headers->set('Content-Type', 'text/xml');
                $response->headers->set('Content-Disposition', 'attachment; filename="manifest.plist"');

                return $response;

            default:
                throw $this->createAccessDeniedException();
        }
    }

    /**
     * Download given build file.
     *
     * @ParamConverter("application", options={"mapping": {"application_id": "id"}})
     *
     * @param Application $application
     * @param Build       $build
     * @param Request     $request
     *
     * @return BinaryFileResponse
     */
    public function getFileAction(Application $application, Build $build, Request $request)
    {
        if ($build->getApplication() != $application) {
            throw $this->createNotFoundException();
        }

        if (!$token = $request->query->get('token')) {
            throw $this->createAccessDeniedException();
        }

        if (!$buildToken = $this->container->get('appbuild.application.build_token_manager')->getFirstNotExpired($build, $token)) {
            throw $this->createAccessDeniedException();
        }

        // Support X-Sendfile headers
        BinaryFileResponse::trustXSendfileTypeHeader();

        return new BinaryFileResponse(
            $build->getFilePath(),
            200,
            ['Content-Type' => 'application/octet-stream'],
            false,
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            false,
            false
        );
    }

    /**
     * Toggles the enabled property of the build.
     *
     * @ParamConverter("application", options={"mapping": {"application_id": "id"}})
     *
     * @param Application $application
     * @param Build       $build
     * @param Request     $request
     *
     * @return RedirectResponse
     */
    public function toggleEnableAction(Application $application, Build $build, Request $request)
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        if ($build->getApplication() != $application) {
            throw $this->createNotFoundException();
        }

        if (!$this->getUserApplications()->contains($application)) {
            throw $this->createAccessDeniedException();
        }

        $em = $this->getDoctrine()->getManager();

        $build->setEnabled(!$build->isEnabled());

        $em->persist($build);
        $em->flush();

        return new RedirectResponse($request->headers->get('referer') ?:
            $this->container->get('router')->generate(
                'majoraotastore_admin_build_list',
                [
                    'application_id' => $application->getId(),
                ]
            )
        );
    }
}
