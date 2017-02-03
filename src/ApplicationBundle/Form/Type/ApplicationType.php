<?php

namespace Majora\OTAStore\ApplicationBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Majora\OTAStore\ApplicationBundle\Entity\Application;
use Majora\OTAStore\UserBundle\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Form type for Application entity.
 */
class ApplicationType extends AbstractType
{
    const TOKEN_CREATION = 'creation';
    const TOKEN_EDITION = 'edition';

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * Constructor.
     *
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'majoraotastore_application';
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Application::class,
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
        $builder->add('label', TextType::class, [
            'required' => true,
            'label' => 'application.form.label',
        ]);

        $availableSupports = [];
        foreach (Application::getAvailableSupports() as $support) {
            $availableSupports[sprintf('application.supports.%s', $support)] = $support;
        }
        $builder->add('support', ChoiceType::class, [
            'required' => true,
            'label' => 'application.form.support',
            'choices' => $availableSupports,
            'choices_as_values' => true,
        ]);

        $builder->add('packageName', TextType::class, [
            'required' => true,
            'label' => 'application.form.package_name',
        ]);

        $builder->add('users', EntityType::class, [
            'class' => User::class,
            'choice_label' => function (User $user) {
                return sprintf('%s %s - %s',
                    $user->getFirstname(),
                    $user->getLastname(),
                    $this->translator->trans('user.roles.'.$user->getRole())
                );
            },
            'multiple' => true,
            'expanded' => true,
            'query_builder' => function (EntityRepository $repository) {
                $qb = $repository->createQueryBuilder('u');

                return $qb
                    // Not ROLE_SUPER_ADMIN (they always have access to applications)
                    ->where($qb->expr()->notIn('u.roles', ':superAdmin'))->setParameter(':superAdmin', 'ROLE_SUPER_ADMIN')
                    // Sort by firstname
                    ->orderBy('u.firstname', 'ASC')
                ;
            },
            'label' => 'application.form.users',
        ]);
    }
}
