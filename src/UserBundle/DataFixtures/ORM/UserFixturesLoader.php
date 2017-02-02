<?php

namespace Majora\OTAStore\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Majora\OTAStore\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Fixtures loader for UserBundle.
 */
class UserFixturesLoader implements FixtureInterface, ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $encoderFactory = $this->container->get('security.encoder_factory');

        // SUPER_ADMIN
        $userSuperAdmin = new User();
        $encoder = $encoderFactory->getEncoder($userSuperAdmin);
        $userSuperAdmin
            ->setRoles(['ROLE_SUPER_ADMIN'])
            ->setEmail('superadmin@superadmin.fr')
            ->setPassword($encoder->encodePassword('superadmin', null))
            ->setFirstname('SuperAdmin')
            ->setLastname('SUPERADMIN')
        ;
        $manager->persist($userSuperAdmin);

        // ADMIN
        $userAdmin = new User();
        $encoder = $encoderFactory->getEncoder($userAdmin);
        $userAdmin
            ->setRoles(['ROLE_ADMIN'])
            ->setEmail('admin@admin.fr')
            ->setPassword($encoder->encodePassword('admin', null))
            ->setFirstname('Admin')
            ->setLastname('ADMIN')
        ;
        $manager->persist($userAdmin);

        // USER
        $userUser = new User();
        $encoder = $encoderFactory->getEncoder($userUser);
        $userUser
            ->setRoles(['ROLE_USER'])
            ->setEmail('user@user.fr')
            ->setPassword($encoder->encodePassword('user', null))
            ->setFirstname('User')
            ->setLastname('USER')
        ;
        $manager->persist($userUser);

        $manager->flush();
    }
}
