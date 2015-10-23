<?php

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use AppBundle\Form\DataTransformer\BuiltApplicationTransformer;

/**
 * Form type for Application entity.
 */
class ApplicationType extends AbstractType
{
    /**
     * @var string
     */
    protected $buildApplicationDir;

    /**
     * construct.
     *
     * @param string $buildApplicationDir
     */
    public function __construct($buildApplicationDir = null)
    {
        $this->buildApplicationDir = $buildApplicationDir;
    }

    /**
     * @see FormInterface::getName()
     */
    public function getName()
    {
        return 'application';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBundle\Entity\Application',
            'csrf_protection' => true,
            'allow_extra_fields' => false,
            'cascade_validation' => false,
            'intention' => null,
        ));
    }

    /**
     * @see FormInterface::buildForm()
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name', 'text', array(
            'required' => true,
            'label' => 'admin.application.label.name',
        ));
        $builder->add('support', 'text', array(
            'required' => true,
            'label' => 'admin.application.label.support',
        ));
        $builder->add('version', 'text', array(
            'required' => true,
            'label' => 'admin.application.label.version',
        ));

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($builder) {
            $form = $event->getForm();

            if ($this->buildApplicationDir
                && $application = $event->getData()
            ) {
                $formType = $builder->create('filePath', 'file', array(
                    'required' => false,
                    'label' => 'admin.application.label.builder',
                    'auto_initialize' => false,
                ));
                $formType->addModelTransformer(new BuiltApplicationTransformer(
                    sprintf('%s/%s',
                        $this->buildApplicationDir,
                        $application->getSlug()
                    ),
                    $application->getFilePath()
                ));

                $form->add($formType->getForm());
            }
        });
    }
}
