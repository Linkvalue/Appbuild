<?php

namespace LinkValue\Appbuild\ApplicationBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use LinkValue\Appbuild\ApplicationBundle\Entity\Application;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class BaseController extends Controller
{
    /**
     * Get all applications a user has access to.
     *
     * @return ArrayCollection|Application[]
     */
    protected function getUserApplications()
    {
        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            return new ArrayCollection($this->getDoctrine()->getRepository('AppbuildApplicationBundle:Application')->findAll());
        }

        return new ArrayCollection($this->getUser()->getApplications()->toArray());
    }
}
