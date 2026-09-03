<?php
declare(strict_types=1);
namespace App\Controller;
use App\Entity\Cable;
use App\Enum\CableStatus;
use App\Enum\CableType;
use App\Repository\CableRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/cables')]
class CableApiController extends AbstractController
{
    #[Route('', name: 'api_cables_list', methods: ['GET'])]
    public function list(Request $request, CableRepository $repo): JsonResponse
    {
        $type = $request->query->get('type');
        $status = $request->query->get('status');
        $search = $request->query->get('search');
        $cables = $repo->findByFilters(null, $type, $status, $search);
        return $this->json([
            'data' => array_map(fn($c) => [
                'id' => $c->getId(),
                'referenceCode' => $c->getReferenceCode(),
                'designation' => $c->getDesignation(),
                'type' => $c->getCableType()->value,
                'status' => $c->getStatus()->value,
                'nominalVoltage' => $c->getNominalVoltage(),
                'conductorSection' => $c->getConductorSection(),
                'conductorMaterial' => $c->getConductorMaterial(),
                'pricePerMeter' => $c->getPricePerMeter(),
                'stockMeters' => $c->getStockMeters(),
                'isLowStock' => $c->isLowStock(),
                'factory' => $c->getFactory(),
                'standards' => $c->getStandards(),
                'imagePath' => $c->getImagePath(),
            ], $cables),
            'count' => count($cables),
        ]);
    }

    #[Route('/{id}', name: 'api_cables_show', methods: ['GET'])]
    public function show(Cable $cable): JsonResponse
    {
        return $this->json([
            'id' => $cable->getId(),
            'referenceCode' => $cable->getReferenceCode(),
            'designation' => $cable->getDesignation(),
            'type' => $cable->getCableType()->value,
            'status' => $cable->getStatus()->value,
            'nominalVoltage' => $cable->getNominalVoltage(),
            'conductorSection' => $cable->getConductorSection(),
            'conductorMaterial' => $cable->getConductorMaterial(),
            'numberOfConductors' => $cable->getNumberOfConductors(),
            'insulation' => $cable->getInsulation(),
            'standards' => $cable->getStandards(),
            'pricePerMeter' => $cable->getPricePerMeter(),
            'stockMeters' => $cable->getStockMeters(),
            'stockAlertThreshold' => $cable->getStockAlertThreshold(),
            'isLowStock' => $cable->isLowStock(),
            'factory' => $cable->getFactory(),
            'description' => $cable->getDescription(),
            'imagePath' => $cable->getImagePath(),
            'createdAt' => $cable->getCreatedAt()->format('c'),
        ]);
    }

    #[Route('', name: 'api_cables_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $cable = new Cable();
        $cable->setReferenceCode($data['referenceCode'] ?? '')
            ->setDesignation($data['designation'] ?? '')
            ->setCableType(CableType::from($data['cableType'] ?? 'BT'))
            ->setStatus(CableStatus::from($data['status'] ?? 'IN_STOCK'))
            ->setNominalVoltage($data['nominalVoltage'] ?? null)
            ->setConductorSection($data['conductorSection'] ?? null)
            ->setConductorMaterial($data['conductorMaterial'] ?? 'COPPER')
            ->setNumberOfConductors($data['numberOfConductors'] ?? 3)
            ->setInsulation($data['insulation'] ?? 'XLPE')
            ->setStandards($data['standards'] ?? null)
            ->setPricePerMeter($data['pricePerMeter'] ?? null)
            ->setStockMeters($data['stockMeters'] ?? 0.0)
            ->setFactory($data['factory'] ?? null);
        $em->persist($cable);
        $em->flush();
        return $this->json(['id' => $cable->getId(), 'message' => 'Produit créé'], 201);
    }

    #[Route('/{id}', name: 'api_cables_update', methods: ['PUT'])]
    public function update(Cable $cable, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (isset($data['designation'])) $cable->setDesignation($data['designation']);
        if (isset($data['status'])) $cable->setStatus(CableStatus::from($data['status']));
        if (isset($data['stockMeters'])) $cable->setStockMeters($data['stockMeters']);
        if (isset($data['pricePerMeter'])) $cable->setPricePerMeter($data['pricePerMeter']);
        if (isset($data['description'])) $cable->setDescription($data['description']);
        $em->flush();
        return $this->json(['message' => 'Produit mis à jour']);
    }

    #[Route('/{id}', name: 'api_cables_delete', methods: ['DELETE'])]
    public function delete(Cable $cable, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($cable);
        $em->flush();
        return $this->json(['message' => 'Produit supprimé']);
    }
}
