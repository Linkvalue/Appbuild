<?php

namespace Majora\OTAStore\ApplicationBundle\Controller\Api;

use Majora\OTAStore\ApplicationBundle\Entity\Application;
use Majora\OTAStore\ApplicationBundle\Entity\Build;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class BuildController extends Controller
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
}
