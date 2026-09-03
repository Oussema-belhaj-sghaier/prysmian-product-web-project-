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

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(
        Request $request,
        UserRepository $userRepo,
        UserPasswordHasherInterface $hasher,
        EntityManagerInterface $em,
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        if ($request->isMethod('POST')) {
            $email = trim((string) $request->request->get('email'));
            $plainPassword = (string) $request->request->get('password');
            $firstName = trim((string) $request->request->get('firstName'));
            $lastName = trim((string) $request->request->get('lastName'));
            $phone = trim((string) $request->request->get('phone'));
            $roleStr = (string) $request->request->get('role', 'TECHNICIAN');
            $regionAssigned = trim((string) $request->request->get('regionAssigned'));
            $profileImage = $request->files->get('profileImage');

            if ($userRepo->findOneBy(['email' => $email])) {
                $this->addFlash('error', "Cet email ($email) est déjà utilisé.");
                return $this->render('security/register.html.twig', ['last_email' => $email]);
            }

            $user = new User();
            $user->setEmail($email)
                ->setFirstName($firstName ?: 'Utilisateur')
                ->setLastName($lastName ?: 'Prysmian')
                ->setPhone($phone ?: null)
                ->setRole(UserRole::from($roleStr))
                ->setStatus(UserStatus::ACTIVE)
                ->setRegionAssigned($regionAssigned ?: null);

            $this->applyProfileImage($user, $profileImage);

            $hashedPassword = $hasher->hashPassword($user, $plainPassword ?: 'password123');
            $user->setPassword($hashedPassword);

            $em->persist($user);
            $em->flush();

            $this->addFlash('success', "Votre compte a été créé avec succès ! Veuillez vous connecter.");
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/register.html.twig');
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
