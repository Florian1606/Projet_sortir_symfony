<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use App\Form\ResetType;
use App\Form\EmailResetType;
use App\Form\RegistrationType;
use App\Entity\User;
use Mailgun\Mailgun;
class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
         if ($this->getUser()) {
             return $this->redirectToRoute('main');
         }

        // get the login error if there is one
        $errorFR = "";
        $error = $authenticationUtils->getLastAuthenticationError();
        dump($error);

        if ($error != null && $error->getMessage() !== "Votre compte est inactif")  {
            if ($error->getMessage() === 'Bad credentials.' || $error->getMessage() === 'The presented password is invalid.')
                $errorFR = "Mauvais mot de passe/email";
        }


        
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $errorFR]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

}
