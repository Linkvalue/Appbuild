<?php

namespace AppBuild\Bundle\ApplicationBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class BaseController extends Controller
{
    /**
     * Get all applications a user has access to.
     *
     * @return ArrayCollection
     */
    protected function getUserApplications()
    {
        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            return new ArrayCollection($this->getDoctrine()->getRepository('AppBuildApplicationBundle:Application')->findAll());
        }

        return $this->getUser()->getApplications();
    }
}
