<?php

namespace Majora\OTAStore\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;

/**
 * UserRepository.
 */
class UserRepository extends EntityRepository
{
    /**
     * @return ArrayCollection|User[]
     */
    public function findAllEnabled()
    {
        return $this->findBy(['enabled' => true]);
    }

    /**
     * @return ArrayCollection|User[]
     */
    public function findAllDisabled()
    {
        return $this->findBy(['enabled' => false]);
    }
}
