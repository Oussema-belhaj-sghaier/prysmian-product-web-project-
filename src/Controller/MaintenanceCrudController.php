<?php
declare(strict_types=1);
namespace App\Controller;
use App\Entity\MaintenanceLog;
use App\Enum\MaintenanceResultStatus;
use App\Enum\MaintenanceType;
use App\Repository\CableRepository;
use App\Repository\MaintenanceLogRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/maintenance')]
class MaintenanceCrudController extends AbstractController
{
    #[Route('/create', name: 'app_maintenance_create', methods: ['POST'])]
    public function create(Request $request, CableRepository $cableRepo, EntityManagerInterface $em): Response
    {
        $cableId = $request->request->get('cableId');
        $productionLine = $request->request->get('maintenanceType');
        $description = $request->request->get('description');
        $cost = $request->request->get('cost');
        $resultStatus = $request->request->get('resultStatus');
        $targetLength = $request->request->get('targetLengthMeters');
        $producedLength = $request->request->get('producedLengthMeters');
        $orderNumber = $request->request->get('orderNumber');

        $cable = $cableRepo->find($cableId);
        if (!$cable) {
            $this->addFlash('error', 'Produit câble sélectionné introuvable.');
            return $this->redirectToRoute('app_home');
        }

        $order = new MaintenanceLog();
        $order->setCable($cable)
            ->setMaintenanceType(MaintenanceType::from($productionLine ?: 'EXTRUSION'))
            ->setDescription($description ?: 'Ordre de production')
            ->setStartDate(new \DateTimeImmutable())
            ->setCost($cost !== null && $cost !== '' ? (float) $cost : 0.0)
            ->setResultStatus(MaintenanceResultStatus::from($resultStatus ?: 'PLANNED'))
            ->setTechnician($this->getUser())
            ->setTargetLengthMeters($targetLength !== null && $targetLength !== '' ? (float) $targetLength : null)
            ->setProducedLengthMeters($producedLength !== null && $producedLength !== '' ? (float) $producedLength : null)
            ->setOrderNumber($orderNumber ?: 'OF-' . date('Y') . '-' . rand(1000, 9999));

        $em->persist($order);
        $em->flush();

        $this->addFlash('success', "Ordre de production {$order->getOrderNumber()} créé pour {$cable->getReferenceCode()}.");
        return $this->redirectToRoute('app_home');
    }

    #[Route('/{id}/delete', name: 'app_maintenance_delete', methods: ['POST', 'DELETE'])]
    public function delete(string $id, MaintenanceLogRepository $repo, EntityManagerInterface $em): Response
    {
        $order = $repo->find($id);
        if ($order) {
            $em->remove($order);
            $em->flush();
            $this->addFlash('success', 'Ordre de production supprimé.');
        }
        return $this->redirectToRoute('app_home');
    }
}
