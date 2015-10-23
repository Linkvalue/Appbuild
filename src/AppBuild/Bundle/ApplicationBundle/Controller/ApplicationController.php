<?php

namespace AppBuild\Bundle\ApplicationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use AppBuild\Bundle\ApplicationBundle\Entity\Application;

class ApplicationController extends Controller
{
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
            $this->container->get('build.application.form_type'),
            $application = new Application(),
            array('intention' => 'creation')
        );

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->container->get('doctrine.orm.entity_manager');
                $em->persist($application);
                $em->flush();

                return new RedirectResponse($this->container->get('router')->generate(
                    'app_admin_update', array(
                        'id' => $application->getId(),
                    )
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
        $form = $this->container->get('form.factory')->create(
            $this->container->get('build.application.form_type'),
            $application,
            array('intention' => 'edition')
        );

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->container->get('doctrine.orm.entity_manager')->flush();
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
        $this->container->get('file_helper')->unlinkFile($application->getFilePath());
        $this->container->get('doctrine.orm.entity_manager')->remove($application);

        return new RedirectResponse($this->container->get('router')->generate('app_admin_create'));
    }

    /**
     * Trigger app download process.
     *
     * @param Application $application
     *
     * @return Response
     */
    public function downloadAction(Application $application)
    {
        $response = new RedirectResponse(
            sprintf(
                'itms-services://?action=download-manifest&amp;url=%s',
                urlencode($this->get('router')->generate(
                    'app_admin_get_manifest',
                    array('id' => $application->getId()),
                    true
                ))
            ),
            302
        );

        $response->headers->set('Content-Type', 'text/html');

        return $response;
    }

    /**
     * Download given application manifest.
     *
     * @param Application $application
     *
     * @return Response
     */
    public function getManifestAction(Application $application)
    {
        $response = $this->render(
            sprintf('AppBundle:Manifest:%s/manifest.plist.twig', $application->getSupport()),
            array(
                'application' => $application,
            )
        );

        $response->headers->set('Content-Type', 'application/octect-stream');
        $response->headers->set('Content-Disposition', 'attachment; filename="manifest.plist"');

        return $response;
    }

    /**
     * Download given application built package.
     *
     * @param Application $application
     *
     * @return Response
     */
    public function getRawFileAction(Application $application)
    {
        $response = new StreamedResponse(function () use ($application) {
            readfile($application->getFilePath());
        });

        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition', sprintf(
            'attachment; filename="%s"',
            basename($application->getFilePath())
        ));

        return $response;
    }
}
