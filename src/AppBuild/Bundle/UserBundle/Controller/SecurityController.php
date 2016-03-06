<?php

namespace AppBuild\Bundle\UserBundle\Controller;

use AppBuild\Bundle\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
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
        $authenticationUtils = $this->get('security.authentication_utils');

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render(
            'AppBuildUserBundle:Security:login.html.twig',
            array(
                // last username entered by the user
                'last_username' => $lastUsername,
                'error' => $error,
            )
        );
    }

    /**
     * Create a user.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function createAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            throw $this->createAccessDeniedException();
        }

        $user = new User();
        $form = $this->container->get('form.factory')->create(
            $this->container->get('appbuild.user.user.form_type'),
            $user,
            array('intention' => 'creation')
        );

        $form->handleRequest($request);
        if ($form->isValid()) {
            // Encode password
            if (!$password = $form->get('password')->getData()) {
                throw new ValidatorException('Password must be set.');
            }
            $user->setPassword($this->get('security.password_encoder')->encodePassword($user, $password));

            // Set role
            if ($role = $form->get('roles')->getData()) {
                $user->setRoles(array($role));
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('appbuild_user_create');
        }

        return $this->render(
            'AppBuildUserBundle:Security:create.html.twig',
            array('form' => $form->createView())
        );
    }

    /**
     * Allow current user to edit his information.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function myAccountAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_FULLY')) {
            throw $this->createAccessDeniedException();
        }

        $user = $this->getUser();
        $form = $this->container->get('form.factory')->create(
            $this->container->get('appbuild.user.user.form_type'),
            $user,
            array('intention' => 'my-account')
        );

        $form->handleRequest($request);
        if ($form->isValid()) {
            // Encode password if it is set
            if ($password = $form->get('password')->getData()) {
                $user->setPassword($this->get('security.password_encoder')->encodePassword($user, $password));
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('appbuild_admin_application_list');
        }

        return $this->render(
            'AppBuildUserBundle:Security:my-account.html.twig',
            array('form' => $form->createView())
        );
    }
}
