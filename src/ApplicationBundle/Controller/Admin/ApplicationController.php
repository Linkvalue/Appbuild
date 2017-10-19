<?php

namespace LinkValue\Appbuild\ApplicationBundle\Controller\Admin;

use Doctrine\Common\Collections\Criteria;
use LinkValue\Appbuild\ApplicationBundle\Controller\BaseController;
use LinkValue\Appbuild\ApplicationBundle\Entity\Application;
use LinkValue\Appbuild\ApplicationBundle\Form\Type\ApplicationType;
use LinkValue\Appbuild\Pagination\Page;
use LinkValue\Appbuild\UserBundle\Entity\User;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplicationController extends BaseController
{
    /**
     * List current user Applications.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function listAction(Request $request)
    {
        if (!($isAskingForEnabled = $request->query->getBoolean('enabled', true)) && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $page = new Page(
            $request->query->getInt('page', Page::FIRST_PAGE_NUMBER),
            $this->getUserApplications()->matching(
                $criteria = Criteria::create()->where(Criteria::expr()->eq('enabled', $isAskingForEnabled))
            )->count()
        );
        $page->setElements($this->getUserApplications()->matching(
            $page->setupCriteria($criteria)->orderBy(['updatedAt' => Criteria::DESC])
        ));

        return $this->render(
            'AppbuildApplicationBundle:Application:list.html.twig',
            ['page' => $page]
        );
    }

    /**
     * Create application.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function createAction(Request $request)
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->container->get('form.factory')->create(
            ApplicationType::class,
            $application = new Application(),
            ['csrf_token_id' => ApplicationType::TOKEN_CREATION]
        );

        if ($request->isMethod(Request::METHOD_POST)) {
            $this->handleApplicationImagesFile($request, $application);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                // Force current user to be linked to the application (except if its a super admin because it's useless)
                /** @var User $currentUser */
                if (!$application->getUsers()->contains($currentUser = $this->getUser())
                    && $currentUser->getRole() !== 'ROLE_SUPER_ADMIN'
                ) {
                    $application->addUser($currentUser);
                }

                $em = $this->container->get('doctrine.orm.entity_manager');
                $em->persist($application);
                $em->flush();

                $this->addFlash('success', $this->container->get('translator')->trans('application.create.flash.success'));

                return new RedirectResponse($this->container->get('router')->generate(
                    'appbuild_admin_application_list'
                ));
            }
        }

        return $this->render('AppbuildApplicationBundle:Application:create.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * Update application.
     *
     * @param Application $application
     * @param Request     $request
     *
     * @return Response
     */
    public function updateAction(Application $application, Request $request)
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->getUserApplications()->contains($application)) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->container->get('form.factory')->create(
            ApplicationType::class,
            $application,
            ['csrf_token_id' => ApplicationType::TOKEN_EDITION]
        );

        if ($request->isMethod(Request::METHOD_POST)) {
            $this->handleApplicationImagesFile($request, $application);
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                // Force current user to be linked to the application (except if its a super admin because it's useless)
                /** @var User $currentUser */
                if (!$application->getUsers()->contains($currentUser = $this->getUser())
                    && $currentUser->getRole() !== 'ROLE_SUPER_ADMIN'
                ) {
                    $application->addUser($currentUser);
                }

                $em = $this->container->get('doctrine.orm.entity_manager');
                $em->persist($application);
                $em->flush();

                $this->addFlash('success', $this->container->get('translator')->trans('application.update.flash.success'));

                return new RedirectResponse($this->container->get('router')->generate(
                    'appbuild_admin_application_list'
                ));
            }
        }

        return $this->render(
            'AppbuildApplicationBundle:Application:update.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * Delete application.
     *
     * @param Application $application
     *
     * @return Response
     */
    public function deleteAction(Application $application)
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->getUserApplications()->contains($application)) {
            throw $this->createAccessDeniedException();
        }

        $em = $this->container->get('doctrine.orm.entity_manager');
        $em->remove($application);
        $em->flush();

        return new RedirectResponse($this->container->get('router')->generate('appbuild_admin_application_list'));
    }

    /**
     * Upload display image file using AJAX.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function uploadDisplayImageAjaxAction(Request $request)
    {
        return $this->uploadApplicationImageAjax($request, 'displayImageFile');
    }

    /**
     * Upload full size image file using AJAX.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function uploadFullSizeImageAjaxAction(Request $request)
    {
        return $this->uploadApplicationImageAjax($request, 'fullSizeImageFile');
    }

    /**
     * Get existing display image using AJAX (useful for edition form).
     *
     * @param Application $application
     *
     * @return JsonResponse
     */
    public function getExistingDisplayImageAjaxAction(Application $application)
    {
        return $this->getExistingApplicationImageAjax($application, 'displayImageFile');
    }

    /**
     * Get existing full size image using AJAX (useful for edition form).
     *
     * @param Application $application
     *
     * @return JsonResponse
     */
    public function getExistingFullSizeImageAjaxAction(Application $application)
    {
        return $this->getExistingApplicationImageAjax($application, 'fullSizeImageFile');
    }

    /**
     * @return JsonResponse
     */
    public function deleteImageAjaxAction()
    {
        // do nothing because deleting will happen on save
        return new JsonResponse([]);
    }

    /**
     * Toggles the enabled property of the application.
     *
     * @param Application $application
     * @param Request     $request
     *
     * @return RedirectResponse
     */
    public function toggleEnableAction(Application $application, Request $request)
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        if (!$this->getUserApplications()->contains($application)) {
            throw $this->createAccessDeniedException();
        }

        $em = $this->getDoctrine()->getManager();

        $application->setEnabled(!$application->isEnabled());

        $em->persist($application);
        $em->flush();

        return new RedirectResponse($request->headers->get('referer') ?:
            $this->container->get('router')->generate(
                'appbuild_admin_application_list'
            )
        );
    }

    /**
     * Upload application image file using AJAX.
     *
     * @param Request $request
     * @param string  $applicationImageType
     *
     * @return JsonResponse
     */
    private function uploadApplicationImageAjax(Request $request, $applicationImageType)
    {
        $translator = $this->container->get('translator');

        if (!$this->isGranted('ROLE_ADMIN')) {
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

        $uploadedFile = $request->files->get($applicationImageType);
        if (!$uploadedFile instanceof UploadedFile) {
            return new JsonResponse([
                'success' => false,
                'error' => $translator->trans('admin.upload.message.upload_failure'),
            ]);
        }

        $uploadHelper = $this->container->get('appbuild.application.application_image_upload_helper');
        $filename = $uploadHelper->generateFilename($uploadedFile);
        try {
            $uploadHelper->moveUploadedFile($uploadedFile, $filename);
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
     * Get existing application image using AJAX.
     *
     * @param Application $application
     * @param string      $applicationImageType
     *
     * @return JsonResponse
     */
    private function getExistingApplicationImageAjax(Application $application, $applicationImageType)
    {
        if (!$this->isGranted('ROLE_ADMIN') || !$this->getUserApplications()->contains($application)) {
            return new JsonResponse([]);
        }

        $applicationFilenameGetter = sprintf('get%sName', ucfirst($applicationImageType));
        $applicationFilepathGetter = sprintf('get%sPath', ucfirst($applicationImageType));
        if (
            !method_exists($application, $applicationFilenameGetter)
            || !method_exists($application, $applicationFilepathGetter)
            || !($filename = call_user_func([$application, $applicationFilenameGetter]))
            || !($filesize = @filesize(call_user_func([$application, $applicationFilepathGetter])))
        ) {
            return new JsonResponse([]);
        }

        return new JsonResponse([
            [
                'name' => $filename,
                'uuid' => $applicationImageType.$application->getId(),
                'size' => $filesize,
                'thumbnailUrl' => $this->container->get('assets.packages')->getUrl(sprintf(
                    '%s/%s',
                    $this->container->getParameter('application_images_webroot_relative_dir'),
                    $filename
                )),
            ],
        ]);
    }

    /**
     * @param Request     $request
     * @param Application $application
     */
    private function handleApplicationImagesFile(Request $request, Application $application)
    {
        $uploadHelper = $this->container->get('appbuild.application.application_image_upload_helper');
        $formData = $request->request->get('appbuild_application');

        $application->setDisplayImageFilePath(
            !empty($formData['displayImageFilename'])
                ? $uploadHelper->getFilePath($formData['displayImageFilename'])
                : ''
        );

        $application->setFullSizeImageFilePath(
            !empty($formData['fullSizeImageFilename'])
                ? $uploadHelper->getFilePath($formData['fullSizeImageFilename'])
                : ''
        );
    }
}
