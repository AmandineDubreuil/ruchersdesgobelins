<?php

namespace App\Controller;

use App\DTO\ContactDTO;
use App\Form\ContactType;
use App\Service\SendMailService;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'app_contact')]
    public function contact(
        Request $request,
        MailerInterface $mailer,
    ): Response {

        $data = new ContactDTO();
        $form = $this->createForm(ContactType::class, $data);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $mail = (new TemplatedEmail())
                ->to('amandine.dubreuil@hotmail.com')
                ->from($data->email)
                ->subject('Demande de contact via lesruchersdesgobelins.fr')
                ->htmlTemplate('emails/contact_email.html.twig')
                ->context(['data' => $data]);

            $mailer->send($mail);
             $this->addFlash('success', 'Votre e-mail a bien été envoyé');
          return  $this->redirectToRoute('app_contact');
        }


        return $this->render('contact/contact.html.twig', [
            'controller_name' => 'ContactController',
            'form' => $form,

        ]);
    }
}
