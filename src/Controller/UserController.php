<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Form\UserEmailType;
use App\Service\JWTService;
use App\Service\SendMailService;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/profil')]
 #[IsGranted('ROLE_USER')]
class UserController extends AbstractController
{
    #[Route('/', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, User $user): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        $user->setVerified(false);


        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/editEmail', name: 'app_user_edit_email', methods: ['GET', 'POST'])]
    public function editEmail(
        Request $request,
        User $user,
        EntityManagerInterface $entityManager,
        SendMailService $sendMailService,
        JWTService $jWTService
    ): Response {

         /* FORMULAIRE EDIT MAIL */

        $form = $this->createForm(UserEmailType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //passer isVerified à false puis génération du token de confirmation adresse e-mail :

            $user->setVerified(false);

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
            //envoi d'un mail
            $sendMailService->send(
                'no-reply@lesruchersdesgobelins.fr',
                $user->getEmail(),
                'Modification de votre adresse e-mail',
                'modif_email',
                compact('user', 'token')
            );

            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit_email.html.twig', [
            'user' => $user,
            'formEmail' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
