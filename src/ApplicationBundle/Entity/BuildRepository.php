<?php

namespace Majora\OTAStore\ApplicationBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;

/**
 * BuildRepository.
 */
class BuildRepository extends EntityRepository
{
    /**
     * @return ArrayCollection|Build[]
     */
    public function findAllEnabled()
    {
        return $this->findBy(['enabled' => true]);
    }

    /**
     * @return ArrayCollection|Build[]
     */
    public function findAllDisabled()
    {
        return $this->findBy(['enabled' => false]);
    }
}
