<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\PlantAssistantService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/ai')]
class AIAssistantController extends AbstractController
{
    #[Route('/assistant', name: 'api_ai_assistant', methods: ['POST'])]
    public function ask(Request $request, PlantAssistantService $assistant): JsonResponse
    {
        $question = strtolower(trim((string) (json_decode($request->getContent(), true)['question'] ?? '')));
        return $this->json(['answer' => $assistant->answer($question), 'engine' => 'Prysmian Plant Assistant 1.1']);
    }
}