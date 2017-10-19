<?php

namespace LinkValue\Appbuild\ApplicationBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for Build entity.
 */
class BuildType extends AbstractType
{
    const TOKEN_CREATION = 'creation';
    const TOKEN_EDITION = 'edition';

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'appbuild_build';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'LinkValue\Appbuild\ApplicationBundle\Entity\Build',
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
        $builder->add('version', TextType::class, ['error_bubbling' => true]);
        $builder->add('comment', TextareaType::class, ['error_bubbling' => true]);
        $builder->add('filename', TextType::class, [
            'error_bubbling' => true,
            'mapped' => false,
        ]);
    }
}
