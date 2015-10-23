<?php

namespace AppBuild\Bundle\ApplicationBundle\Form\Type;

use AppBuild\Bundle\ApplicationBundle\Entity\Application;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for Application entity.
 */
class ApplicationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'appbuild_application';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBuild\Bundle\ApplicationBundle\Entity\Application',
            'csrf_protection' => true,
            'allow_extra_fields' => false,
            'cascade_validation' => false,
            'intention' => null,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('label', 'text', array(
            'required' => true,
            'label' => 'admin.application.form.label',
        ));

        $availableSupports = array();
        foreach (Application::getAvailableSupports() as $support) {
            $availableSupports[$support] = sprintf('admin.application.supports.%s', $support);
        }
        $builder->add('support', 'choice', array(
            'required' => true,
            'label' => 'admin.application.form.support',
            'choices' => $availableSupports,
        ));
    }
}
