<?php

namespace AppBuild\Bundle\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', 'email', array(
                'label' => 'user.edit.email.label',
            ))
            ->add('firstname', 'text', array(
                'label' => 'user.edit.firstname.label',
            ))
            ->add('lastname', 'text', array(
                'label' => 'user.edit.lastname.label',
            ))
            ->add('password', 'repeated', array(
                'type' => 'password',
                'first_options' => array('label' => 'user.edit.password.label.first'),
                'second_options' => array('label' => 'user.edit.password.label.second'),
                'required' => $options['intention'] == 'creation',
                'mapped' => false,
            ))
        ;

        // Roles can't be set for "my-account" intention
        if ($options['intention'] !== 'my-account') {
            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $userRoles = $event->getData()->getRoles();
                $form = $event->getForm();

                $form->add('roles', 'choice', array(
                    'label' => 'user.edit.roles.label',
                    'choices' => array(
                        'user.edit.roles.user' => 'ROLE_USER',
                        'user.edit.roles.admin' => 'ROLE_ADMIN',
                        'user.edit.roles.super_admin' => 'ROLE_SUPER_ADMIN',
                    ),
                    'choices_as_values' => true,
                    'mapped' => false,
                    'data' => reset($userRoles),
                ));
            });
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBuild\Bundle\UserBundle\Entity\User',
        ));
    }

    public function getName()
    {
        return 'user';
    }
}
