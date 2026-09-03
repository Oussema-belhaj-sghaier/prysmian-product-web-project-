<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Alert;
use App\Entity\Cable;
use App\Entity\User;
use App\Enum\AlertSeverity;
use App\Enum\AlertStatus;
use App\Enum\AlertType;
use App\Enum\CableStatus;
use App\Enum\CableType;
use App\Enum\UserRole;
use App\Enum\UserStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'app:seed', description: 'Crée un admin et des données de démo')]
class SeedCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $admin = $this->em->getRepository(User::class)->findOneBy(['email' => 'admin@prysmian.tn']);
        if (!$admin) {
            $admin = new User();
            $admin->setEmail('admin@prysmian.tn')
                ->setFirstName('Admin')
                ->setLastName('Prysmian')
                ->setRole(UserRole::ADMIN)
                ->setStatus(UserStatus::ACTIVE)
                ->setPassword($this->passwordHasher->hashPassword($admin, 'Admin123!'));
            $this->em->persist($admin);
            $io->success('Utilisateur admin@prysmian.tn / Admin123! créé');
        } else {
            $io->note('Admin déjà présent');
        }

        if ($this->em->getRepository(Cable::class)->count([]) === 0) {
            $cable = new Cable();
            $cable->setReferenceCode('PRY-BT-0001')
                ->setDesignation('Câble BT industriel 4G35')
                ->setCableType(CableType::BT)
                ->setStatus(CableStatus::IN_STOCK)
                ->setNominalVoltage(0.4)
                ->setConductorSection(35.0)
                ->setConductorMaterial('COPPER')
                ->setNumberOfConductors(4)
                ->setInsulation('XLPE')
                ->setStandards('IEC 60502')
                ->setStockMeters(12500.0)
                ->setStockAlertThreshold(2000.0)
                ->setFactory('Usine Bizerte')
                ->setDescription('Référence de démonstration pour la production basse tension.');
            $this->em->persist($cable);

            $alert = new Alert();
            $alert->setCable($cable)
                ->setAlertType(AlertType::LOW_STOCK)
                ->setSeverity(AlertSeverity::MEDIUM)
                ->setStatus(AlertStatus::OPEN)
                ->setMessage('Température au-dessus du seuil de confort');
            $this->em->persist($alert);
            $io->success('Câble et alerte de démo créés');
        }

        $this->em->flush();
        $io->success('Seed terminé');

        return Command::SUCCESS;
    }
}
