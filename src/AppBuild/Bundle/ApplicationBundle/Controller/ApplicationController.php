<?php

namespace AppBuild\Bundle\ApplicationBundle\Controller;

use AppBuild\Bundle\ApplicationBundle\Entity\Application;
use AppBuild\Bundle\ApplicationBundle\Form\Type\ApplicationType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApplicationController extends BaseController
{
    /**
     * List current user Applications.
     *
     * @return Response
     */
    public function listAction()
    {
        $applications = $this->getUserApplications();

        return $this->render(
            'AppBuildApplicationBundle:Application:list.html.twig',
            array('applications' => $applications)
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
            array('csrf_token_id' => 'creation')
        );

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->container->get('doctrine.orm.entity_manager');
                $em->persist($application);
                $em->flush();

                $this->addFlash('success', $this->container->get('translator')->trans('application.create.flash.success'));

                return new RedirectResponse($this->container->get('router')->generate(
                    'appbuild_admin_application_list'
                ));
            }
        }

        return $this->render('AppBuildApplicationBundle:Application:create.html.twig',
            array(
                'form' => $form->createView(),
                'application' => $application,
                'currentUserId' => $this->getUser()->getId(),
            )
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
            array('csrf_token_id' => 'edition')
        );

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
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
            'AppBuildApplicationBundle:Application:update.html.twig',
            array(
                'form' => $form->createView(),
                'application' => $application,
                'currentUserId' => $this->getUser()->getId(),
            )
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
}
