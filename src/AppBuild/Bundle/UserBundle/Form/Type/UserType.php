<?php

namespace AppBuild\Bundle\UserBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
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
                )
                );
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
