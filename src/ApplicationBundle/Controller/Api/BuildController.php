<?php

namespace Majora\OTAStore\ApplicationBundle\Controller\Api;

use Majora\OTAStore\ApplicationBundle\Entity\Application;
use Majora\OTAStore\ApplicationBundle\Entity\Build;
use Majora\OTAStore\ApplicationBundle\Form\Type\BuildAPIType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class BuildController extends ApiController
{
    /**
     * Get enabled build by id.
     *
     * @param Build $build
     *
     * @return JsonResponse
     */
    public function getAction(Build $build)
    {
        if (!$build->getApplication()->isEnabled() || !$build->isEnabled()) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($this->container->get('appbuild.application.serializer')->serializeBuild($build));
    }

    /**
     * List enabled application builds.
     *
     * @ParamConverter("application", options={"mapping": {"application_id": "id"}})
     *
     * @param Application $application
     *
     * @return JsonResponse
     */
    public function listForApplicationAction(Application $application)
    {
        if (!$application->isEnabled()) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        $builds = [];
        $serializer = $this->container->get('appbuild.application.serializer');

        foreach ($application->getEnabledBuilds() as $build) {
            $builds[] = $serializer->serializeBuild($build);
        }

        return new JsonResponse($builds);
    }

    /**
     * Get enabled application latest build.
     *
     * @ParamConverter("application", options={"mapping": {"application_id": "id"}})
     *
     * @param Application $application
     *
     * @return JsonResponse
     */
    public function getLatestForApplicationAction(Application $application)
    {
        if (!$application->isEnabled() || !($latestBuild = $application->getLatestEnabledBuild())) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($this->container->get('appbuild.application.serializer')->serializeBuildForDownloading($latestBuild));
    }

    /**
     * Create a new build for the application.
     *
     * @ParamConverter("application", options={"mapping": {"application_id": "id"}})
     *
     * @param Application $application
     * @param Request     $request
     *
     * @return Response
     */
    public function createForApplicationAction(Application $application, Request $request)
    {
        $form = $this->container->get('form.factory')->create(
            BuildAPIType::class,
            $build = (new Build())->setApplication($application)->setEnabled(false)
        );
        $data = json_decode($request->getContent(), true);
        $form->submit($data);

        if (!$form->isValid()) {
            $errors = [];
            foreach ($form->getErrors() as $error) {
                $errors[] = $error->getMessage();
            }

            $data = [
                'type' => 'validation_error',
                'title' => 'There was a validation error',
                'errors' => $errors,
            ];

            return new JsonResponse($data, 400);
        }

        $em = $this->container->get('doctrine.orm.entity_manager');
        $em->persist($build);
        $em->flush();

        return new JsonResponse([
            'build_id' => $build->getId(),
            'upload_location' => $this->generateUrl(
                'majoraotastore_api_build_add_file',
                ['id' => $build->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
        ]);
    }

    /**
     * Upload file for a build.
     *
     * @param Build   $build   The build computed from url param
     * @param Request $request The request
     *
     * @return JsonResponse The response
     *
     * @throws \Exception An uncatched exception
     */
    public function uploadFileAction(Build $build, Request $request)
    {
        $application = $build->getApplication();
        $filename = $request->query->get('filename');

        if (!$filename) {
            $filename = $build->getSlug();
        }

        $uploadHelper = $this->container->get('appbuild.application.build_upload_helper');

        try {
            $tmpFile = $uploadHelper->createTempFile($request->getContent(), $filename);
            $uploadHelper->moveUploadedFile($tmpFile, $application, $filename);
        } catch (FileException $e) {
            return new JsonResponse(['errors' => ['Cannot upload this file.']], 500);
        }

        $build
            ->setFilePath($uploadHelper->getFilePath($application, $filename))
            ->setEnabled(true);

        $em = $this->container->get('doctrine.orm.entity_manager');
        $em->persist($build);
        $em->flush();

        $response = new JsonResponse();
        $response->setStatusCode(200);

        return $response;
    }
}
