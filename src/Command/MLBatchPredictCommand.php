<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\MLPredictionService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:ml:batch-predict',
    description: 'Exécute le batch de prédictions ML pour tous les câbles (à lancer chaque nuit)',
)]
class MLBatchPredictCommand extends Command
{
    public function __construct(private MLPredictionService $mlService)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('🤖 Batch Prédictions ML — Prysmian Tunisia');
        $io->note('Modèle: v' . $this->mlService->getModelVersion());

        $start = microtime(true);
        $this->mlService->runBatchPredictions();
        $duration = round(microtime(true) - $start, 2);

        $io->success("Batch terminé en {$duration}s");
        return Command::SUCCESS;
    }
}
