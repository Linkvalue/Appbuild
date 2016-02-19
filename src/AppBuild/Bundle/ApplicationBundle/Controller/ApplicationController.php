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

                $this->addFlash('success', 'Votre application a bien été créée');

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
        // @todo $this->getUser()->getApplications()->contains($application)

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

                $this->addFlash('success', 'Votre application a bien été mise à jour');

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
        // @todo $this->getUser()->getApplications()->contains($application)

        $em = $this->container->get('doctrine.orm.entity_manager');
        $em->remove($application);
        $em->flush();

        return new RedirectResponse($this->container->get('router')->generate('appbuild_admin_application_list'));
    }
}
