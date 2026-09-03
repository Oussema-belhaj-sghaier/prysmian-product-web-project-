<?php
declare(strict_types=1);
namespace App\Controller;
use App\Entity\Cable;
use App\Enum\CableStatus;
use App\Enum\CableType;
use App\Repository\CableRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/cable')]
class CableCrudController extends AbstractController
{
    #[Route('/create', name: 'app_cable_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $refCode = $request->request->get('referenceCode');
        $designation = $request->request->get('designation');
        $cableType = $request->request->get('cableType');
        $status = $request->request->get('status');
        $nominalVoltage = $request->request->get('nominalVoltage');
        $conductorSection = $request->request->get('conductorSection');
        $conductorMaterial = $request->request->get('conductorMaterial');
        $numberOfConductors = $request->request->get('numberOfConductors');
        $insulation = $request->request->get('insulation');
        $standards = $request->request->get('standards');
        $pricePerMeter = $request->request->get('pricePerMeter');
        $stockMeters = $request->request->get('stockMeters');
        $stockAlertThreshold = $request->request->get('stockAlertThreshold');
        $factory = $request->request->get('factory');
        $description = $request->request->get('description');
        $image = $request->files->get('image');

        $cable = new Cable();
        $cable->setReferenceCode($refCode ?: 'PRY-' . strtoupper(substr($cableType ?: 'BT', 0, 2)) . '-' . rand(100, 999))
            ->setDesignation($designation ?: 'Câble ' . ($cableType ?: 'BT'))
            ->setCableType(CableType::from($cableType ?: 'BT'))
            ->setStatus(CableStatus::from($status ?: 'IN_STOCK'))
            ->setNominalVoltage($nominalVoltage !== null && $nominalVoltage !== '' ? (float) $nominalVoltage : null)
            ->setConductorSection($conductorSection !== null && $conductorSection !== '' ? (float) $conductorSection : null)
            ->setConductorMaterial($conductorMaterial ?: 'COPPER')
            ->setNumberOfConductors($numberOfConductors !== null && $numberOfConductors !== '' ? (int) $numberOfConductors : 3)
            ->setInsulation($insulation ?: 'XLPE')
            ->setStandards($standards ?: 'IEC 60502')
            ->setPricePerMeter($pricePerMeter !== null && $pricePerMeter !== '' ? (float) $pricePerMeter : null)
            ->setStockMeters($stockMeters !== null && $stockMeters !== '' ? (float) $stockMeters : 0.0)
            ->setStockAlertThreshold($stockAlertThreshold !== null && $stockAlertThreshold !== '' ? (float) $stockAlertThreshold : 1000.0)
            ->setFactory($factory ?: 'Usine Bizerte')
            ->setDescription($description ?: null);

        $this->applyImage($cable, $image);

        $em->persist($cable);
        $em->flush();

        $this->addFlash('success', "Produit {$cable->getReferenceCode()} ajouté au catalogue avec succès !");
        return $this->redirectToRoute('app_home');
    }

    #[Route('/{id}/edit', name: 'app_cable_edit', methods: ['POST'])]
    public function edit(string $id, Request $request, CableRepository $repo, EntityManagerInterface $em): Response
    {
        $cable = $repo->find($id);
        if (!$cable) {
            $this->addFlash('error', 'Produit introuvable.');
            return $this->redirectToRoute('app_home');
        }

        $designation = $request->request->get('designation');
        $cableType = $request->request->get('cableType');
        $status = $request->request->get('status');
        $nominalVoltage = $request->request->get('nominalVoltage');
        $conductorSection = $request->request->get('conductorSection');
        $conductorMaterial = $request->request->get('conductorMaterial');
        $numberOfConductors = $request->request->get('numberOfConductors');
        $insulation = $request->request->get('insulation');
        $standards = $request->request->get('standards');
        $pricePerMeter = $request->request->get('pricePerMeter');
        $stockMeters = $request->request->get('stockMeters');
        $stockAlertThreshold = $request->request->get('stockAlertThreshold');
        $factory = $request->request->get('factory');
        $description = $request->request->get('description');
        $referenceCode = $request->request->get('referenceCode');
        $image = $request->files->get('image');

        if ($referenceCode) $cable->setReferenceCode($referenceCode);
        if ($designation) $cable->setDesignation($designation);
        if ($cableType) $cable->setCableType(CableType::from($cableType));
        if ($status) $cable->setStatus(CableStatus::from($status));
        if ($nominalVoltage !== null && $nominalVoltage !== '') $cable->setNominalVoltage((float) $nominalVoltage);
        if ($conductorSection !== null && $conductorSection !== '') $cable->setConductorSection((float) $conductorSection);
        if ($conductorMaterial) $cable->setConductorMaterial($conductorMaterial);
        if ($numberOfConductors !== null && $numberOfConductors !== '') $cable->setNumberOfConductors((int) $numberOfConductors);
        if ($insulation) $cable->setInsulation($insulation);
        if ($standards !== null) $cable->setStandards($standards);
        if ($pricePerMeter !== null && $pricePerMeter !== '') $cable->setPricePerMeter((float) $pricePerMeter);
        if ($stockMeters !== null && $stockMeters !== '') $cable->setStockMeters((float) $stockMeters);
        if ($stockAlertThreshold !== null && $stockAlertThreshold !== '') $cable->setStockAlertThreshold((float) $stockAlertThreshold);
        if ($factory) $cable->setFactory($factory);
        if ($description !== null) $cable->setDescription($description);
        $this->applyImage($cable, $image);

        $em->flush();
        $this->addFlash('success', "Produit {$cable->getReferenceCode()} mis à jour.");
        return $this->redirectToRoute('app_home');
    }

    private function applyImage(Cable $cable, mixed $image): void
    {
        if (!$image instanceof UploadedFile || !$image->isValid()) {
            return;
        }

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
        $extension = strtolower($image->guessExtension() ?: '');
        if (!in_array($extension, $allowedExtensions, true)) {
            throw new \InvalidArgumentException('Format image non supporté. Utilisez JPG, PNG ou WEBP.');
        }

        $directory = $this->getParameter('kernel.project_dir') . '/public/images/products';
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }

        $filename = bin2hex(random_bytes(12)) . '.' . $extension;
        $image->move($directory, $filename);
        $cable->setImagePath('/images/products/' . $filename);
    }

    #[Route('/{id}/delete', name: 'app_cable_delete', methods: ['POST', 'DELETE'])]
    public function delete(string $id, CableRepository $repo, EntityManagerInterface $em): Response
    {
        $cable = $repo->find($id);
        if ($cable) {
            $ref = $cable->getReferenceCode();
            $em->remove($cable);
            $em->flush();
            $this->addFlash('success', "Produit $ref supprimé du catalogue.");
        }
        return $this->redirectToRoute('app_home');
    }
}
