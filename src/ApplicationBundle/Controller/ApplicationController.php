<?php

namespace Majora\OTAStore\ApplicationBundle\Controller;

use Majora\OTAStore\ApplicationBundle\Entity\Application;
use Majora\OTAStore\ApplicationBundle\Form\Type\ApplicationType;
use Majora\OTAStore\ApplicationBundle\Entity\Build;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\Common\Collections\ArrayCollection;

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
        $aDate = array();
        $role = '';

        //Get role of user
        foreach ($this->getUser()->getRoles() as $tmp) {
            $role = $tmp;
        }

        if ($role == 'ROLE_USER') {
            foreach ($applications as $application) {
                $builds = $application->getBuilds();

                foreach ($builds as $build) {
                    $aDate[$build->getId()]['created'] = $build->getCreatedAt();
                    $aDate[$build->getId()]['id'] = $build->getId();
                    $aDate[$build->getId()]['build'] = $build;
                }

                rsort($aDate);

                if (isset($aDate[0])) {
                    $build = $aDate[0]['build'];
                    $application->setBuilds(new ArrayCollection(array($build)));
                }
            }
        }

        return $this->render('MajoraOTAStoreApplicationBundle:Application:list.html.twig', array('applications' => $applications));
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
                ApplicationType::class, $application = new Application(), array('csrf_token_id' => ApplicationType::TOKEN_CREATION)
        );

        if ($request->isMethod(Request::METHOD_POST)) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->container->get('doctrine.orm.entity_manager');
                $em->persist($application);
                $em->flush();

                $this->addFlash('success', $this->container->get('translator')->trans('application.create.flash.success'));

                return new RedirectResponse($this->container->get('router')->generate(
                                'majoraotastore_admin_application_list'
                ));
            }
        }

        return $this->render('MajoraOTAStoreApplicationBundle:Application:create.html.twig', array(
                    'form' => $form->createView(),
                    'application' => $application,
                    'currentUserId' => $this->getUser()->getId(),
                    'applicationSupportIOS' => Application::SUPPORT_IOS,
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
                ApplicationType::class, $application, array('csrf_token_id' => ApplicationType::TOKEN_EDITION)
        );

        if ($request->isMethod(Request::METHOD_POST)) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->container->get('doctrine.orm.entity_manager');
                $em->persist($application);
                $em->flush();

                $this->addFlash('success', $this->container->get('translator')->trans('application.update.flash.success'));

                return new RedirectResponse($this->container->get('router')->generate(
                                'majoraotastore_admin_application_list'
                ));
            }
        }

        return $this->render(
                        'MajoraOTAStoreApplicationBundle:Application:update.html.twig', array(
                    'form' => $form->createView(),
                    'application' => $application,
                    'currentUserId' => $this->getUser()->getId(),
                    'applicationSupportIOS' => Application::SUPPORT_IOS,
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

        return new RedirectResponse($this->container->get('router')->generate('majoraotastore_admin_application_list'));
    }
}
