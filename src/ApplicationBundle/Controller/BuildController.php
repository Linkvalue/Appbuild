<?php

namespace Majora\OTAStore\ApplicationBundle\Controller;

use Majora\OTAStore\ApplicationBundle\Entity\Application;
use Majora\OTAStore\ApplicationBundle\Entity\Build;
use Majora\OTAStore\ApplicationBundle\Form\Type\BuildType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
        if (!$this->getUserApplications()->contains($application)) {
            throw $this->createAccessDeniedException();
        }

        list($enabled, $disabled) = $application->getBuilds()->partition(function ($i, Build $build) {
            return $build->isEnabled();
        });

        return $this->render(
            'MajoraOTAStoreApplicationBundle:Build:list.html.twig',
            array(
                'application' => $application,
                'builds' => $request->get('enabled', true) ? $enabled : $disabled,
            )
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
            array('csrf_token_id' => BuildType::TOKEN_CREATION)
        );

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->container->get('doctrine.orm.entity_manager');
                $em->persist($build);
                $em->flush();

                $this->addFlash('success', $this->container->get('translator')->trans('build.create.flash.success'));

                return new RedirectResponse($this->container->get('router')->generate(
                    'majoraotastore_admin_build_list', array(
                        'application_id' => $application->getId(),
                    )
                ));
            }
        }

        return $this->render('MajoraOTAStoreApplicationBundle:Build:create.html.twig',
            array(
                'form' => $form->createView(),
                'application' => $application,
                'build' => $build,
            )
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
            array('csrf_token_id' => BuildType::TOKEN_EDITION)
        );

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->container->get('doctrine.orm.entity_manager');
                $em->persist($build);
                $em->flush();

                $this->addFlash('success', $this->container->get('translator')->trans('build.update.flash.success'));

                return new RedirectResponse($this->container->get('router')->generate(
                    'majoraotastore_admin_build_list', array(
                        'application_id' => $application->getId(),
                    )
                ));
            }
        }

        return $this->render(
            'MajoraOTAStoreApplicationBundle:Build:update.html.twig',
            array(
                'form' => $form->createView(),
                'application' => $application,
                'build' => $build,
            )
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
                array(
                    'application_id' => $application->getId(),
                )
            )
        );
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

        $router = $this->container->get('router');

        switch ($build->getApplication()->getSupport()) {

            case Application::SUPPORT_IOS:
                // Redirect to iOS specific protocol to download build manifest
                $response = new RedirectResponse(
                    sprintf(
                        'itms-services://?action=download-manifest&url=%s',
                        urlencode($router->generate(
                            'majoraotastore_admin_build_get_manifest',
                            array(
                                'application_id' => $application->getId(),
                                'id' => $build->getId(),
                            ),
                            true
                        ))
                    ),
                    302,
                    array(
                        'Content-Type' => 'text/html',
                    )
                );
                break;

            default:
                // Download build file
                if ($this->container->getParameter('stream_builds_content')) {
                    // By streaming content
                    $response = new RedirectResponse(
                        $router->generate(
                            'majoraotastore_admin_build_stream_file',
                            array(
                                'application_id' => $application->getId(),
                                'id' => $build->getId(),
                            )
                        )
                    );
                } else {
                    // Directly
                    $response = new RedirectResponse(
                        sprintf(
                            '/%s/%s/%s',
                            $this->container->getParameter('web_relative_builds_application_dir'),
                            $application->getSlug(),
                            $build->getFileNameWithExtension()
                        )
                    );
                }

                break;
        }

        return $response;
    }

    /**
     * Download given build manifest.
     *
     * Manifest must be accessible without logging in (see security.yml).
     * TODO: set a unique/oneshot token system to make it accessible only after being redirected from the "download" route.
     *
     * @ParamConverter("application", options={"mapping": {"application_id": "id"}})
     *
     * @param Application $application
     * @param Build       $build
     *
     * @return Response
     */
    public function getManifestAction(Application $application, Build $build)
    {
        if ($build->getApplication() != $application) {
            throw $this->createNotFoundException();
        }

        switch ($application->getSupport()) {

            case Application::SUPPORT_IOS:
                $response = $this->render(
                    sprintf('MajoraOTAStoreApplicationBundle:Manifest:%s/manifest.plist.twig', $application->getSupport()),
                    array(
                        'application' => $application,
                        'build' => $build,
                        'stream_builds_content' => $this->container->getParameter('stream_builds_content'),
                        'web_relative_build_path' => sprintf(
                            '%s/%s/%s',
                            $this->container->getParameter('web_relative_builds_application_dir'),
                            $application->getSlug(),
                            $build->getFileNameWithExtension()
                        ),
                    )
                );

                $response->headers->set('Content-Type', 'text/xml');
                $response->headers->set('Content-Disposition', 'attachment; filename="manifest.plist"');

                return $response;

            default:
                throw $this->createAccessDeniedException();
        }
    }

    /**
     * Download given build file by streaming its content.
     *
     * @ParamConverter("application", options={"mapping": {"application_id": "id"}})
     *
     * @param Application $application
     * @param Build       $build
     *
     * @return StreamedResponse
     *
     * @see "stream_builds_content" parameter
     */
    public function streamFileAction(Application $application, Build $build)
    {
        if ($build->getApplication() != $application) {
            throw $this->createNotFoundException();
        }

        $response = new StreamedResponse(function () use ($build) {
            readfile($build->getFilePath());
        });

        $response->headers->set('Content-Type', 'application/octet-stream');

        return $response;
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
                array(
                    'application_id' => $application->getId(),
                )
            )
        );
    }
}
