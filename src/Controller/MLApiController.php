<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\CableRepository;
use App\Service\MLPredictionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/ml')]
class MLApiController extends AbstractController
{
    #[Route('/predict/{id}', name: 'api_ml_predict_single', methods: ['POST'])]
    public function predictSingle(string $id, CableRepository $cableRepo, MLPredictionService $mlService): JsonResponse
    {
        $cable = $cableRepo->find($id);
        if (!$cable) {
            return $this->json(['error' => 'Câble non trouvé'], 404);
        }

        $prediction = $mlService->predictForCable($cable);
        if (!$prediction) {
            return $this->json(['message' => 'Aucune prédiction nécessaire'], 200);
        }

        return $this->json([
            'cableReference' => $cable->getReferenceCode(),
            'predictedDate' => $prediction->getPredictedDate()->format('Y-m-d'),
            'confidence' => $prediction->getConfidenceScore(),
            'urgency' => $prediction->getMaintenanceUrgency(),
            'reason' => $prediction->getReason(),
            'modelVersion' => $prediction->getModelVersion(),
            'recommendMaintenance' => $prediction->getMaintenanceUrgency() > 70,
        ]);
    }

    #[Route('/batch', name: 'api_ml_batch', methods: ['POST'])]
    public function batchPredict(MLPredictionService $mlService): JsonResponse
    {
        $mlService->runBatchPredictions();
        return $this->json(['message' => 'Batch prédictions exécuté']);
    }

    #[Route('/version', name: 'api_ml_version', methods: ['GET'])]
    public function version(MLPredictionService $mlService): JsonResponse
    {
        return $this->json(['version' => $mlService->getModelVersion()]);
    }
}
