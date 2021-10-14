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
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $error]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    public function resetPassword(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $form = $this->createForm(EmailResetType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $entityManager->getRepository(User::class)->findOneByEmail($form->getData()['email']);
            if ($user !== null) {
                $token = uniqid();
                $user->setResetPassword($token);
                $entityManager->persist($user);
                $entityManager->flush();

                $mgClient   = new Mailgun($this->getParameter('mailgun_api_key'));
                $domain     = $this->getParameter('mailgun_domain');
                $mailFrom   = $this->getParameter('mail_mail_from');
                $nameFrom   = $this->getParameter('mail_name_from');
                $mailTo = $user->getEmail();
                $result = $mgClient->sendMessage($domain, array(
                    'from' => "$nameFrom <$mailFrom>",
                    'to' => "<$mailTo>",
                    'subject' => 'Mot de passe oublié ?',
                    'html' => $this->renderView('emails/reset-password.html.twig', array('token' => $token))
                ));

                return $this->render('authentication/reset-password-confirmation.html.twig');
            }
        }

        return $this->render('authentication/reset-password.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
