<?php

namespace AppBuild\Bundle\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use AppBuild\Bundle\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
            ->setRoles(array('ROLE_SUPER_ADMIN'))
            ->setEmail('superadmin@superadmin.fr')
            ->setPassword($encoder->encodePassword('superadmin', $userSuperAdmin->getSalt()))
            ->setFirstName('SuperAdmin')
            ->setLastName('SUPERADMIN')
        ;
        $manager->persist($userSuperAdmin);

        // ADMIN
        $userAdmin = new User();
        $encoder = $encoderFactory->getEncoder($userAdmin);
        $userAdmin
            ->setRoles(array('ROLE_ADMIN'))
            ->setEmail('admin@admin.fr')
            ->setPassword($encoder->encodePassword('admin', $userAdmin->getSalt()))
            ->setFirstName('Admin')
            ->setLastName('ADMIN')
        ;
        $manager->persist($userAdmin);

        // USER
        $userUser = new User();
        $encoder = $encoderFactory->getEncoder($userUser);
        $userUser
            ->setRoles(array('ROLE_USER'))
            ->setEmail('user@user.fr')
            ->setPassword($encoder->encodePassword('user', $userUser->getSalt()))
            ->setFirstName('User')
            ->setLastName('USER')
        ;
        $manager->persist($userUser);

        $manager->flush();
    }
}
