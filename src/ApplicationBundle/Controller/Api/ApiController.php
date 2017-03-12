<?php

namespace Majora\OTAStore\ApplicationBundle\Controller\Api;

use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ApiController extends Controller
{
    /**
     * Get all applications a user has access to.
     *
     * @return ArrayCollection
     */
    protected function getUserApplications()
    {
        if ($this->isGranted('ROLE_SUPER_ADMIN')) {
            return new ArrayCollection($this->getDoctrine()->getRepository('MajoraOTAStoreApplicationBundle:Application')->findAll());
        }

        return $this->getUser()->getApplications();
    }
}
