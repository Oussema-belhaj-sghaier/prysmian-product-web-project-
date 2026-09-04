<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\PlantAssistantService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class AssistantWebController extends AbstractController
{
    #[Route('/assistant/ask', name: 'app_assistant_ask', methods: ['POST'])]
    public function ask(Request $request, PlantAssistantService $assistant): JsonResponse
    {
        if (!$this->getUser()) {
            return $this->json(['answer' => 'Votre session a expiré. Veuillez vous reconnecter.'], 401);
        }

        $payload = json_decode($request->getContent(), true);
        $question = (string) ($payload['question'] ?? '');

        return $this->json([
            'answer' => $assistant->answer($question),
            'engine' => 'Prysmian Plant Assistant 1.1',
        ]);
    }
}