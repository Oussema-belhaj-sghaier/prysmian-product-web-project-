<?php

declare(strict_types=1);

namespace App\Controller;

use App\Enum\UserStatus;
use App\Repository\UserRepository;
use App\Service\PlantMailerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ContactController extends AbstractController
{
    #[Route('/contacts', name: 'app_contacts', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('contacts/index.html.twig', [
            'contacts' => $userRepository->findBy(['status' => UserStatus::ACTIVE], ['firstName' => 'ASC', 'lastName' => 'ASC']),
        ]);
    }

    #[Route('/contacts/{id}/email', name: 'app_contact_email', methods: ['POST'])]
    public function sendEmail(
        string $id,
        Request $request,
        UserRepository $userRepository,
        PlantMailerService $mailer,
    ): Response {
        $recipient = $userRepository->find($id);
        if (!$recipient) {
            $this->addFlash('contact_error', 'Contact introuvable.');
            return $this->redirectToRoute('app_contacts');
        }

        $subject = trim((string) $request->request->get('subject'));
        $message = trim((string) $request->request->get('message'));
        if ($subject === '' || $message === '') {
            $this->addFlash('contact_error', 'Le sujet et le message sont obligatoires.');
            return $this->redirectToRoute('app_contacts');
        }

        $result = $mailer->sendContactMessage($recipient, $subject, $message);
        if ($result === 'sent') {
            $this->addFlash('contact_success', "Email remis au serveur SMTP pour {$recipient->getFullName()}. En mode local, consultez Mailpit sur http://localhost:8025.");
        } elseif ($result === 'local_mode') {
            $this->addFlash('contact_success', 'Email traité en mode local.');
        } else {
            $this->addFlash('contact_error', 'Envoi impossible : aucun serveur SMTP n’est disponible. Configurez MAILER_DSN ou démarrez Mailpit.');
        }

        return $this->redirectToRoute('app_contacts');
    }
}
