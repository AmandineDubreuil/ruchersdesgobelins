<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\JWTService;
use App\Form\ResetPasswordType;
use App\Service\SendMailService;
use App\Repository\UserRepository;
use App\Form\ResetPasswordRequestType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\MakerBundle\Security\Model\Authenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
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
            'no-reply@lechalprev.fr',
            $user->getEmail(),
            'Activation de votre compte Lechal\'Prév.fr',
            'register',
            compact('user', 'token')
        );
        $this->addFlash('success', 'Un e-mail vient de vous être envoyé à l\'adresse que vous nous avez communiquée.');
        return $this->redirectToRoute('app_user_index');
    }


}
