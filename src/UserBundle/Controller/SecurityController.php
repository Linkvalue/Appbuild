<?php

namespace Majora\OTAStore\UserBundle\Controller;

use Majora\OTAStore\UserBundle\Entity\User;
use Majora\OTAStore\UserBundle\Form\Type\UserType;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Exception\ValidatorException;

class SecurityController extends Controller
{
    /**
     * Login.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function loginAction()
    {
        $authenticationUtils = $this->container->get('security.authentication_utils');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render(
            'MajoraOTAStoreUserBundle:Security:login.html.twig',
            array(
                // last username entered by the user
                'last_username' => $lastUsername,
                'error' => $error,
            )
        );
    }

    /**
     * List all users.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function listAction(Request $request)
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $users = new ArrayCollection($this->getDoctrine()->getRepository('MajoraOTAStoreUserBundle:User')->findAll());

        list($enabled, $disabled) = $users->partition(function ($i, User $user) {
            return $user->isEnabled();
        });

        return $this->render(
            'MajoraOTAStoreUserBundle:Security:list.html.twig',
            array('users' => $request->get('enabled', true) ? $enabled : $disabled)
        );
    }

    /**
     * Create a user.
     *
     * @param Request $request
     *
     * @return Response|RedirectResponse
     */
    public function createAction(Request $request)
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $user = new User();
        $form = $this->container->get('form.factory')->create(
            UserType::class,
            $user,
            array('csrf_token_id' => UserType::TOKEN_CREATION)
        );

        if ($request->isMethod(Request::METHOD_POST)) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                // Encode password
                if (!$password = $form->get('password')->getData()) {
                    throw new ValidatorException('Password must be set.');
                }
                $user->setPassword($this->container->get('security.password_encoder')->encodePassword($user, $password));

                // Set role
                if ($role = $form->get('roles')->getData()) {
                    $user->setRoles(array($role));
                }

                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                $this->addFlash('success', $this->container->get('translator')->trans('user.create.flash.success'));

                return $this->redirectToRoute('majoraotastore_user_create');
            }
        }

        return $this->render(
            'MajoraOTAStoreUserBundle:Security:create.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * Update user.
     *
     * @param User    $user
     * @param Request $request
     *
     * @return Response
     */
    public function updateAction(User $user, Request $request)
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $form = $this->container->get('form.factory')->create(
            UserType::class,
            $user,
            array('csrf_token_id' => UserType::TOKEN_EDITION)
        );

        if ($request->isMethod(Request::METHOD_POST)) {
            $form->handleRequest($request);
            if ($form->isValid()) {
                // Encode password if it is set
                if ($password = $form->get('password')->getData()) {
                    $user->setPassword($this->container->get('security.password_encoder')->encodePassword($user, $password));
                }

                // Set role
                if ($role = $form->get('roles')->getData()) {
                    $user->setRoles(array($role));
                }

                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                $this->addFlash('success', $this->container->get('translator')->trans('user.update.flash.success'));

                return new RedirectResponse($this->container->get('router')->generate(
                    'majoraotastore_user_list'
                ));
            }
        }

        return $this->render(
            'MajoraOTAStoreUserBundle:Security:update.html.twig',
            array(
                'form' => $form->createView(),
                'user' => $user,
            )
        );
    }

    /**
     * Delete user.
     *
     * @param User $user
     *
     * @return Response
     */
    public function deleteAction(User $user)
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $em = $this->container->get('doctrine.orm.entity_manager');
        $em->remove($user);
        $em->flush();

        return new RedirectResponse($this->container->get('router')->generate('majoraotastore_user_list'));
    }

    /**
     * Toggles the enabled property of the user.
     *
     * @param User    $user
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function toggleEnableAction(User $user, Request $request)
    {
        if (!$this->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $em = $this->getDoctrine()->getManager();

        $user->setEnabled(!$user->isEnabled());

        $em->persist($user);
        $em->flush();

        return new RedirectResponse(
            $request->headers->get('referer')
            ?: $this->container->get('router')->generate('majoraotastore_user_list')
        );
    }

    /**
     * Allow current user to edit his information.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function myAccountAction(Request $request)
    {
        if (!$this->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $user = $this->getUser();
        $form = $this->container->get('form.factory')->create(
            UserType::class,
            $user,
            array('csrf_token_id' => UserType::TOKEN_MY_ACCOUNT)
        );

        $form->handleRequest($request);
        if ($form->isValid()) {
            // Encode password if it is set
            if ($password = $form->get('password')->getData()) {
                $user->setPassword($this->container->get('security.password_encoder')->encodePassword($user, $password));
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('success', $this->container->get('translator')->trans('user.my_account.flash.success'));

            return $this->redirectToRoute('majoraotastore_admin_application_list');
        }

        return $this->render(
            'MajoraOTAStoreUserBundle:Security:my-account.html.twig',
            array('form' => $form->createView())
        );
    }
}
