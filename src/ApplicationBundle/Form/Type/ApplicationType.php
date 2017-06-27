<?php

namespace Majora\OTAStore\ApplicationBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Majora\OTAStore\ApplicationBundle\Entity\Application;
use Majora\OTAStore\UserBundle\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Form type for Application entity.
 */
class ApplicationType extends AbstractType
{
    const TOKEN_CREATION = 'creation';
    const TOKEN_EDITION = 'edition';

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * Constructor.
     *
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
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
        $builder->add('label', TextType::class, ['error_bubbling' => true]);
        $builder->add('description', TextareaType::class, ['error_bubbling' => true]);
        $builder->add('packageName', TextType::class, ['error_bubbling' => true]);
        $builder->add('support', ChoiceType::class, [
            'error_bubbling' => true,
            'choices' => Application::getAvailableSupports(),
        ]);

        $builder->add('users', EntityType::class, [
            'error_bubbling' => true,
            'class' => User::class,
            'multiple' => true,
            'expanded' => true,
            'query_builder' => function (EntityRepository $repository) {
                $qb = $repository->createQueryBuilder('u');

                return $qb
                    // Not current user (it should ask a super admin if he wants to lose control over an application)
                    ->andWhere($qb->expr()->neq('u', ':currentUser'))->setParameter(':currentUser', $this->tokenStorage->getToken()->getUser())
                    // Not ROLE_SUPER_ADMIN (they always have access to applications)
                    ->andWhere($qb->expr()->notIn('u.roles', ':superAdmin'))->setParameter(':superAdmin', 'ROLE_SUPER_ADMIN')
                    // Sort by firstname
                    ->orderBy('u.firstname', 'ASC')
                ;
            },
            'choice_label' => null,
        ]);
    }
}
