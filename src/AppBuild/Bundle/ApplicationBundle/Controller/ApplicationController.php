<?php

namespace AppBuild\Bundle\ApplicationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use AppBuild\Bundle\ApplicationBundle\Entity\Application;

class ApplicationController extends Controller
{
    /**
     * List current user Applications.
     *
     * @return Response
     */
    public function listAction()
    {
        $applications = $this->getUser()->getApplications();

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
            $this->container->get('appbuild.application.application.form_type'),
            $application = new Application(),
            array('intention' => 'creation')
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

        if (!$this->getUser()->getApplications()->contains($application)) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->container->get('form.factory')->create(
            $this->container->get('appbuild.application.application.form_type'),
            $application,
            array('intention' => 'edition')
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

        if (!$this->getUser()->getApplications()->contains($application)) {
            throw $this->createAccessDeniedException();
        }

        $em = $this->container->get('doctrine.orm.entity_manager');
        $em->remove($application);
        $em->flush();

        return new RedirectResponse($this->container->get('router')->generate('appbuild_admin_application_list'));
    }
}
