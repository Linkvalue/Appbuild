<?php

namespace AppBuild\Bundle\ApplicationBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class BaseController extends Controller
{
    /**
     * @return ArrayCollection User Applications
     */
    protected function getUserApplications()
    {
        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            return $this->getDoctrine()->getRepository('AppBuildApplicationBundle:Application')->findAll();
        }

        return $this->getUser()->getApplications();
    }
}
