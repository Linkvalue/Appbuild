<?php

namespace AppBuild\Bundle\UserBundle\Controller;

use AppBuild\Bundle\UserBundle\Entity\User;
use AppBuild\Bundle\UserBundle\Form\Type\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class SecurityController extends Controller
{
    public function loginAction(Request $request)
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

    public function registerAction(Request $request)
    {
        $user = new User();
        $form = $this->createForm(new UserType(), $user);

        $form->handleRequest($request);
        if ($form->isValid() && $form->isSubmitted()) {
            $password = $this->get('security.password_encoder')
                ->encodePassword($user, $user->getPassword());
            $user->setPassword($password);
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute('appbuild_user_login');
        }

        return $this->render(
            'AppBuildUserBundle:Security:register.html.twig',
            array('form' => $form->createView())
        );
    }
}
