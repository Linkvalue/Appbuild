<?php

namespace LinkValue\Appbuild\ApplicationBundle\Controller\Api;

use LinkValue\Appbuild\ApplicationBundle\Controller\BaseController;
use LinkValue\Appbuild\ApplicationBundle\Entity\Application;
use LinkValue\Appbuild\ApplicationBundle\Entity\Build;
use LinkValue\Appbuild\ApplicationBundle\Form\Type\BuildAPIType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Validator\ConstraintViolation;

class BuildController extends BaseController
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
     * Create a new disabled build for application.
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
            $build = (new Build())
                ->setApplication($application)
                ->setEnabled(false)
        );
        $data = json_decode($request->getContent(), JSON_OBJECT_AS_ARRAY);
        $form->submit($data);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $errors = [];
            foreach ($form->getErrors() as $error) {
                $errors[] = $error->getMessage();
            }

            return new JsonResponse(['errors' => $errors], 400);
        }

        $em = $this->container->get('doctrine.orm.entity_manager');
        $em->persist($build);
        $em->flush();

        return new JsonResponse([
            'build_id' => $build->getId(),
            'upload_location' => $this->generateUrl(
                'appbuild_api_build_add_file',
                ['id' => $build->getId()],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
        ]);
    }

    /**
     * Upload file for a build and enable it.
     *
     * @param Build   $build
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function uploadFileAction(Build $build, Request $request)
    {
        $application = $build->getApplication();
        $uploadHelper = $this->container->get('appbuild.application.build_upload_helper');

        $filename = sprintf(
            '%s.%s',
            $build->getSlug(),
            ($build->getApplication()->getSupport() === Application::SUPPORT_IOS) ? 'ipa' : 'apk'
        );

        try {
            $tmpFile = $uploadHelper->createTempFile($request->getContent(), $filename);
            $uploadHelper->moveUploadedFile($tmpFile, $application, $filename);
        } catch (\Exception $e) {
            return new JsonResponse(
                ['errors' => [$this->container->get('translator')->trans('admin.upload.message.upload_failure')]],
                500
            );
        }

        $build
            ->setFilePath($uploadHelper->getFilePath($application, $filename))
            ->setEnabled(true)
        ;

        $constraintViolationList = $this->container->get('validator')->validate($build);
        if ($constraintViolationList->count() > 0) {
            $errors = [];
            foreach ($constraintViolationList as $error) {
                /* @var ConstraintViolation $error */
                $errors[] = $error->getMessage();
            }

            return new JsonResponse(['errors' => $errors], 400);
        }

        $em = $this->container->get('doctrine.orm.entity_manager');
        $em->persist($build);
        $em->flush();

        return new JsonResponse();
    }
}
