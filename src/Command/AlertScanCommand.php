<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\AlertMonitorService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:alerts:scan',
    description: 'Scan tous les câbles et génère les alertes automatiques',
)]
class AlertScanCommand extends Command
{
    public function __construct(private AlertMonitorService $alertService)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('🔔 Scan Alertes — Prysmian Tunisia');

        $start = microtime(true);
        $this->alertService->scanAllCables();
        $duration = round(microtime(true) - $start, 2);

        $io->success("Scan terminé en {$duration}s");
        return Command::SUCCESS;
    }
}
