<?php

namespace Majora\OTAStore\ApplicationBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;

/**
 * ApplicationRepository.
 */
class ApplicationRepository extends EntityRepository
{
    /**
     * @return ArrayCollection|Application[]
     */
    public function findAllEnabled()
    {
        return $this->findBy(['enabled' => true]);
    }

    /**
     * @return ArrayCollection|Application[]
     */
    public function findAllDisabled()
    {
        return $this->findBy(['enabled' => false]);
    }
}
