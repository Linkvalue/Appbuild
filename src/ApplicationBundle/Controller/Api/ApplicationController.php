<?php

namespace Majora\OTAStore\ApplicationBundle\Controller\Api;

use Majora\OTAStore\ApplicationBundle\Entity\Application;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApplicationController extends Controller
{
    /**
     * List enabled applications.
     *
     * @return JsonResponse
     */
    public function listAction()
    {
        $applications = [];
        $serializer = $this->container->get('appbuild.application.serializer');
        $enabledApplications = $this->getDoctrine()->getRepository('MajoraOTAStoreApplicationBundle:Application')->findAllEnabled();

        foreach ($enabledApplications as $application) {
            $applications[] = $serializer->serializeApplication($application);
        }

        return new JsonResponse($applications);
    }

    /**
     * Get enabled application by id.
     *
     * @param Application $application
     *
     * @return JsonResponse
     */
    public function getAction(Application $application)
    {
        if (!$application->isEnabled()) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($this->container->get('appbuild.application.serializer')->serializeApplication($application));
    }
}
