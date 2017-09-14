<?php

namespace LinkValue\Appbuild\UserBundle\Form\Type;

use LinkValue\Appbuild\UserBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    const TOKEN_CREATION = 'creation';
    const TOKEN_EDITION = 'edition';
    const TOKEN_MY_ACCOUNT = 'my-account';

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbuild_user';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'csrf_protection' => true,
            'allow_extra_fields' => false,
            'csrf_token_id' => null,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', Type\EmailType::class, ['error_bubbling' => true])
            ->add('firstname', Type\TextType::class, ['error_bubbling' => true])
            ->add('lastname', Type\TextType::class, ['error_bubbling' => true])
            ->add('password', Type\RepeatedType::class, [
                'error_bubbling' => true,
                'type' => Type\PasswordType::class,
                'mapped' => false,
            ])
        ;

        // Roles can't be set for "my-account"
        if ($options['csrf_token_id'] !== self::TOKEN_MY_ACCOUNT) {
            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
                $userRole = $event->getData()->getRole();
                $form = $event->getForm();

                $form->add('role', Type\ChoiceType::class, [
                    'error_bubbling' => true,
                    'choices' => [
                        'ROLE_USER',
                        'ROLE_ADMIN',
                        'ROLE_SUPER_ADMIN',
                    ],
                    'data' => $userRole,
                    'mapped' => false,
                ]);
            });
        }
    }
}
