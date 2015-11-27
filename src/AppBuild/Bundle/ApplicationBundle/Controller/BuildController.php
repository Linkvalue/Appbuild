<?php

namespace AppBuild\Bundle\ApplicationBundle\Controller;

use AppBuild\Bundle\ApplicationBundle\Entity\Application;
use AppBuild\Bundle\ApplicationBundle\Entity\Build;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Class BuildController.
 *
 * Manage application builds.
 */
class BuildController extends Controller
{
    /**
     * List given application builds.
     *
     * @ParamConverter("application", options={"mapping": {"application_id": "id"}})
     *
     * @param Application $application
     *
     * @return Response
     */
    public function listAction(Application $application)
    {
        // @todo $this->getUser()->getApplications()->contains($application)

        return $this->render(
            'AppBuildApplicationBundle:Build:list.html.twig',
            array(
                'application' => $application,
                'builds' => $application->getBuilds(),
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
        // @todo $this->getUser()->getApplications()->contains($application)

        $form = $this->container->get('form.factory')->create(
            $this->container->get('appbuild.application.build.form_type'),
            $build = (new Build())->setApplication($application),
            array('intention' => 'creation')
        );

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->container->get('doctrine.orm.entity_manager');
                $em->persist($build);
                $em->flush();

                $this->addFlash('success', 'Votre build a bien été créé');

                return new RedirectResponse($this->container->get('router')->generate(
                    'appbuild_admin_build_list', array(
                        'application_id' => $application->getId(),
                    )
                ));
            }
        }

        return $this->render('AppBuildApplicationBundle:Build:create.html.twig',
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
        // @todo $this->getUser()->getApplications()->contains($application)

        $form = $this->container->get('form.factory')->create(
            $this->container->get('appbuild.application.build.form_type'),
            $build,
            array('intention' => 'edition')
        );

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            if ($form->isValid()) {
                $em = $this->container->get('doctrine.orm.entity_manager');
                $em->persist($build);
                $em->flush();

                $this->addFlash('success', 'Votre build a bien été mise à jour');

                return new RedirectResponse($this->container->get('router')->generate(
                    'appbuild_admin_build_list', array(
                        'application_id' => $application->getId(),
                    )
                ));
            }
        }

        return $this->render(
            'AppBuildApplicationBundle:Build:update.html.twig',
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
        // @todo $this->getUser()->getApplications()->contains($application)

        $em = $this->container->get('doctrine.orm.entity_manager');
        $em->remove($build);
        $em->flush();

        // @todo unlink $build->getFilePath() file

        return new RedirectResponse(
            $this->container->get('router')->generate(
                'appbuild_admin_build_list',
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
        // @todo $this->getUser()->getApplications()->contains($application)

        switch ($build->getApplication()->getSupport()) {

            case Application::SUPPORT_IOS:
                // Redirect to iOS specific protocol to download build manifest
                $response = new RedirectResponse(
                    sprintf(
                        'itms-services://?action=download-manifest&amp;url=%s',
                        urlencode($this->get('router')->generate(
                            'appbuild_admin_build_get_manifest',
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
                // Download raw build file
                $response = new RedirectResponse(
                    $this->get('router')->generate(
                        'appbuild_admin_build_get_raw_file',
                        array(
                            'application_id' => $application->getId(),
                            'id' => $build->getId(),
                        )
                    )
                );
        }

        return $response;
    }

    /**
     * Download given build manifest.
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
        // @todo $this->getUser()->getApplications()->contains($application)

        $response = $this->render(
            sprintf('AppBuildApplicationBundle:Manifest:%s/manifest.plist.twig', $application->getSupport()),
            array(
                'application' => $application,
                'build' => $build,
            )
        );

        $response->headers->set('Content-Type', 'application/octect-stream');
        $response->headers->set('Content-Disposition', 'attachment; filename="manifest.plist"');

        return $response;
    }

    /**
     * Download given build raw file.
     *
     * @ParamConverter("application", options={"mapping": {"application_id": "id"}})
     *
     * @param Application $application
     * @param Build       $build
     *
     * @return StreamedResponse
     */
    public function getRawFileAction(Application $application, Build $build)
    {
        // @todo $this->getUser()->getApplications()->contains($application)

        $response = new StreamedResponse(function () use ($build) {
            readfile($build->getFilePath());
        });

        $response->headers->set('Content-Type', 'application/force-download');
        $response->headers->set('Content-Disposition',
            sprintf(
                'attachment; filename="%s"',
                basename($build->getFilePath())
            )
        );

        return $response;
    }
}
