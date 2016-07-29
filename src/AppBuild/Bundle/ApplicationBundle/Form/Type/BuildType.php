<?php

namespace AppBuild\Bundle\ApplicationBundle\Form\Type;

use AppBuild\Bundle\ApplicationBundle\Entity\Build;
use AppBuild\Bundle\ApplicationBundle\Form\DataTransformer\BuildUploadTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Form type for Build entity.
 */
class BuildType extends AbstractType
{
    const TOKEN_CREATION = 'creation';
    const TOKEN_EDITION = 'edition';

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
    public function getBlockPrefix()
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

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($builder, $options) {
            $form = $event->getForm();

            /* @var \AppBuild\Bundle\ApplicationBundle\Entity\Build $build */
            if ($this->buildApplicationDir
                && ($build = $event->getData())
                && ($application = $build->getApplication())
            ) {
                $formType = $builder->create('filePath', FileType::class, array(
                    'required' => $options['csrf_token_id'] === self::TOKEN_CREATION,
                    'label' => 'build.form.filePath',
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
