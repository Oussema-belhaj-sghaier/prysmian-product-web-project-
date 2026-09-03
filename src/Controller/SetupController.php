<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SetupController extends AbstractController
{
    #[Route('/setup-db', name: 'app_setup_db')]
    public function setup(): Response
    {
        $setupScript = $this->getParameter('kernel.project_dir') . '/public/setup.php';
        if (!file_exists($setupScript)) {
            return new Response('Script introuvable', 500);
        }

        ob_start();
        require $setupScript;
        $content = ob_get_clean();

        return new Response($content);
    }
}
