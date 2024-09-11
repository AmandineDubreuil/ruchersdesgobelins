<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ResetPasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use App\Service\JWTService;
use App\Service\SendMailService;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\MakerBundle\Security\Model\Authenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class SecurityController extends AbstractController
{

    // *********************
    // LOGIN / LOGOUT
    // *********************

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    // ***********************
    // VERIFICATION MAIL USER
    // ************************

    #[Route('/verify/{token}', name: 'app_verify_user')]
    public function verifyUser(
        $token,
        JWTService $jWTService,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager
    ): Response {
        // vérifier si le token est valide, n'a pas expiré et n'a pas été modifié
        if ($jWTService->isValidToken($token) && !$jWTService->isExpiredToken($token) && $jWTService->checkSignatureToken($token, $this->getParameter('app.jwtsecret'))) {
            // récupération du payload
            $payload = $jWTService->getPayload($token);
            // récupération du user du token
            $user = $userRepository->find($payload['user_id']);
            //vérification que le user existe et n'a pas encore activé so compte
            if ($user && !$user->isVerified()) {
                $user->setVerified(true);
                $entityManager->flush($user);

                // dd($user);
                $this->addFlash('success', 'Félicitations ! Votre compte est activé !');
                return $this->redirectToRoute('app_user_index');
            }
        }
        // ici un problème dans le token
        $this->addFlash('danger', 'Le Token est invalide ou a expiré.');
        return $this->redirectToRoute('app_login');
    }

    // renvoi de la vérification
    #[Route('/renvoiverif', name: 'app_resend_verif')]
    public function resendVerif(
        JWTService $jWTService,
        SendMailService $sendMailService,
        UserRepository $userRepository,
        EntityManagerInterface $entityManager,
        User $user
    ): Response {
        $user = $this->getUser();

        // vérifie que l'utilisateur est connecté
        if (!$user) {
            $this->addFlash('danger', 'Vous devez être connecté pour accéder à cette page.');
            return $this->redirectToRoute('app_login');
        }

        //vérifie que l'utilisateur n'a pas déjà été vérifié
        //dd($user->getIsVerified());
        if ($user->isVerified()) {
            $this->addFlash('warning', 'Ce compte utilisateur est déjà activé.');
            return $this->redirectToRoute('app_user_index');
        }

        // Génération du JWT de l'utilisateur
        // créer le header
        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256'
        ];

        //créer le payload
        $payload = [
            'user_id' => $user->getId()
        ];
        //définir la durée de validité en nb de secondes
        $validity = 86400;

        // générer le token
        $token = $jWTService->generate($header, $payload, $this->getParameter('app.jwtsecret'), $validity);

        // dd($token);

        //envoi d'un mail
        $sendMailService->send(
            'no-reply@lesruchersdesgobelins.fr',
            $user->getEmail(),
            'Activation de votre compte Les Ruchers des Gobelins',
            'register',
            compact('user', 'token'),
        );
        $this->addFlash('success', 'Un e-mail vient de vous être envoyé à l\'adresse que vous nous avez communiquée.');
        return $this->redirectToRoute('app_user_index');
    }

    #[Route('/mot-de-passe-oublie', name: 'app_forgotten_password')]
    public function forgottenPassword(
        Request $request,
        Security $security,
        UserRepository $userRepository,
        JWTService $jWTService,
        SendMailService $mail,
    ): Response {

        $form = $this->createForm(ResetPasswordRequestFormType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            //formulaire est envoyé et valide
            // on va chercher le user dans la base

            $user = $userRepository->findOneByEmail($form->get('email')->getData());

            // vérifie s'il y a un user
            if ($user) {
                // ok user
                // Génération du JWT de l'utilisateur
                // créer le header
                $header = [
                    'typ' => 'JWT',
                    'alg' => 'HS256'
                ];

                //créer le payload
                $payload = [
                    'user_id' => $user->getId()
                ];
                //définir la durée de validité en nb de secondes
                $validity = 1200;

                // générer le token
                $token = $jWTService->generate($header, $payload, $this->getParameter('app.jwtsecret'), $validity);

                // on génère l'url vers app_reset_password
                $url = $this->generateUrl('app_reset_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

                //envoyer l'e-mail
                $mail->send(
                    'no_reply@lesruchersdesgobelins.fr',
                    $user->getEmail(),
                    'Rénitialisation de votre mot de passe sur le site Les Ruchers des Gobelins',
                    'password_reset',
                    compact('user', 'url') // ['user' => $user, 'url' => $url ]
                );

                $this->addFlash('success', 'E-mail envoyé avec succès !');
                return $this->redirectToRoute('app_login');
            }

            //$user est nul 
            $this->addFlash('danger', 'Un problème est survenu');
            return $this->redirectToRoute('app_login');
        }


        return $this->render('security/reset_password_request.html.twig', [
            'requestPassForm' => $form->createView()
        ]);
    }

    #[Route('/mot-de-passe-oublie/{token}', name: 'app_reset_password')]
    public function resetPassword(
        $token,
        JWTService $jWTService,
        UserRepository $userRepository,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $em,
        Request $request,
    ): Response {

        //on vérifie si le token est valide (cohérent, pas expiré et signature correcte)
        if ($jWTService->isValidToken($token) && !$jWTService->isExpiredToken($token) && $jWTService->checkSignatureToken($token, $this->getParameter('app.jwtsecret'))) {
            // le token est valide
            // on récupère les données (payload)
            $payload = $jWTService->getPayload($token);

            // on récupère le user
            $user = $userRepository->find($payload['user_id']);

            if ($user) {

                $form = $this->createForm(ResetPasswordFormType::class);
                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {
                    $user->setPassword(
                        $passwordHasher->hashPassword($user, $form->get('plainPassword')->getData())
                    );
                    $em->flush();

                    $this->addFlash('success', 'Mot de passe changé avec succès !');
                    return $this->redirectToRoute('app_login');
                }

                return $this->render('security/reset_password.html.twig', [
                    'passForm' => $form->createView()
                ]);
            }
        }

        // le token n'est pas valide 
        $this->addFlash('danger', 'Le token est invalide ou a expiré');
        return $this->redirectToRoute('app_login');
    }
}
