<?php

namespace AppBuild\Bundle\ApplicationBundle\Form\Type;

use AppBuild\Bundle\ApplicationBundle\Entity\Build;
use AppBuild\Bundle\ApplicationBundle\Form\DataTransformer\BuildUploadTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for Build entity.
 */
class BuildType extends AbstractType
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
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'appbuild_build';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'AppBuild\Bundle\ApplicationBundle\Entity\Build',
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
        $builder->add('version', 'text', array(
            'required' => true,
            'label' => 'admin.build.form.version',
        ));

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($builder) {
            $form = $event->getForm();

            if ($this->buildApplicationDir
                && ($build = $event->getData())
                && ($application = $build->getApplication())
            ) {
                $formType = $builder->create('filePath', 'file', array(
                    'required' => false,
                    'label' => 'admin.build.form.filePath',
                    'auto_initialize' => false,
                ));
                $formType->addModelTransformer(
                    new BuildUploadTransformer(
                        sprintf('%s/%s',
                            $this->buildApplicationDir,
                            $application->getSlug()
                        ),
                        $build->getFilePath()
                    )
                );

                $form->add($formType->getForm());
            }
        });
    }
}
