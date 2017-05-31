<?php

namespace Majora\OTAStore\ApplicationBundle\Form\Type;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for Build entity.
 */
class BuildAPIType extends BuildType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver->setDefault('csrf_protection', false);
    }
}
