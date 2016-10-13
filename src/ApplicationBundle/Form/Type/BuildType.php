<?php

namespace Majora\OTAStore\ApplicationBundle\Form\Type;

use Majora\OTAStore\ApplicationBundle\Entity\Build;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
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
        return 'majoraotastore_build';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Majora\OTAStore\ApplicationBundle\Entity\Build',
            'csrf_protection' => true,
            'allow_extra_fields' => false,
            'csrf_token_id' => null,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('version', TextType::class, array(
            'required' => true,
            'label' => 'build.form.version',
        ));
        $builder->add('comment', TextareaType::class, array(
            'required' => false,
            'label' => 'build.form.comment',
        ));
        $builder->add('filename', TextType::class, array(
            'required' => false,
            'label' => 'build.form.filename',
            'mapped' => false,
        ));
    }
}
