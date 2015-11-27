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
            ->add('email', 'email')
            ->add('firstname', 'text')
            ->add('lastname', 'text')
            ->add('password', 'repeated', array(
                    'type' => 'password',
                    'first_options' => array('label' => 'Password'),
                    'second_options' => array('label' => 'Repeat Password'),
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
