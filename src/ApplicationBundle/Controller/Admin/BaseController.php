<?php

namespace Majora\OTAStore\ApplicationBundle\Controller\Admin;

use Doctrine\Common\Collections\ArrayCollection;
use Majora\OTAStore\ApplicationBundle\Entity\Application;
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
            return new ArrayCollection($this->getDoctrine()->getRepository('MajoraOTAStoreApplicationBundle:Application')->findAll());
        }

        return $this->getUser()->getApplications();
    }
}
