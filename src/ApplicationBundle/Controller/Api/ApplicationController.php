<?php

namespace LinkValue\Appbuild\ApplicationBundle\Controller\Api;

use LinkValue\Appbuild\ApplicationBundle\Controller\BaseController;
use LinkValue\Appbuild\ApplicationBundle\Entity\Application;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ApplicationController extends BaseController
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
        $enabledApplications = $this->getDoctrine()->getRepository('AppbuildApplicationBundle:Application')->findAllEnabled();

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
