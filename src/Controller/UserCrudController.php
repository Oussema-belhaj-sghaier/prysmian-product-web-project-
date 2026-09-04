<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use App\Enum\UserRole;
use App\Enum\UserStatus;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\PlantMailerService;

#[Route('/user')]
class UserCrudController extends AbstractController
{
    #[Route('/create', name: 'app_user_create', methods: ['POST'])]
    public function create(
        Request $request,
        UserRepository $userRepo,
        UserPasswordHasherInterface $hasher,
        EntityManagerInterface $em,
        PlantMailerService $mailer,
    ): Response {
        $email = trim((string) $request->request->get('email'));
        $firstName = trim((string) $request->request->get('firstName'));
        $lastName = trim((string) $request->request->get('lastName'));
        $roleStr = (string) $request->request->get('role', 'TECHNICIAN');
        $regionAssigned = trim((string) $request->request->get('regionAssigned'));
        $phone = trim((string) $request->request->get('phone'));
        $profileImage = $request->files->get('profileImage');

        if ($userRepo->findOneBy(['email' => $email])) {
            $this->addFlash('error', "Cet email ($email) est déjà utilisé.");
            return $this->redirectToRoute('app_home');
        }

        $user = new User();
        $user->setEmail($email)
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setRole(UserRole::from($roleStr))
            ->setRegionAssigned($regionAssigned ?: null)
            ->setPhone($phone ?: null)
            ->setStatus(UserStatus::ACTIVE);
        $this->applyProfileImage($user, $profileImage);

        $hashedPassword = $hasher->hashPassword($user, 'password123');
        $user->setPassword($hashedPassword);

        $em->persist($user);
        $em->flush();
        $mailer->sendWelcome($user);

        $this->addFlash('success', "Utilisateur {$user->getFullName()} créé avec succès.");
        return $this->redirectToRoute('app_home');
    }

    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['POST'])]
    public function edit(string $id, Request $request, UserRepository $userRepo, EntityManagerInterface $em): Response
    {
        $user = $userRepo->find($id);
        if (!$user) {
            $this->addFlash('error', "Utilisateur introuvable.");
            return $this->redirectToRoute('app_home');
        }

        $roleStr = $request->request->get('role');
        $statusStr = $request->request->get('status');
        $region = $request->request->get('regionAssigned');
        $phone = $request->request->get('phone');
        $profileImage = $request->files->get('profileImage');

        if ($roleStr) $user->setRole(UserRole::from($roleStr));
        if ($statusStr) $user->setStatus(UserStatus::from($statusStr));
        if ($region !== null) $user->setRegionAssigned($region ?: null);
        if ($phone !== null) $user->setPhone($phone ?: null);
        $this->applyProfileImage($user, $profileImage);

        $em->flush();

        $this->addFlash('success', "Compte de {$user->getFullName()} mis à jour.");
        return $this->redirectToRoute('app_home');
    }

    #[Route('/{id}/delete', name: 'app_user_delete', methods: ['POST', 'DELETE'])]
    public function delete(string $id, UserRepository $userRepo, EntityManagerInterface $em): Response
    {
        $user = $userRepo->find($id);
        if ($user) {
            $name = $user->getFullName();
            $em->remove($user);
            $em->flush();
            $this->addFlash('success', "Utilisateur $name supprimé.");
        }
        return $this->redirectToRoute('app_home');
    }

    private function applyProfileImage(User $user, mixed $image): void
    {
        if (!$image instanceof UploadedFile || !$image->isValid()) {
            return;
        }
        $extension = strtolower($image->guessExtension() ?: '');
        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            return;
        }
        $directory = $this->getParameter('kernel.project_dir') . '/public/images/profiles';
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }
        $filename = bin2hex(random_bytes(12)) . '.' . $extension;
        $image->move($directory, $filename);
        $user->setProfileImagePath('/images/profiles/' . $filename);
    }
}
