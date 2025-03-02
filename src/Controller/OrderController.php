<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Offer;
use App\Repository\OrderRepository;
use App\Repository\TypeUnitRepository;
use App\Repository\UnitRepository;
use App\Repository\StateUnitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends AbstractController
{
    #[Route("/offer/{id}/reserve", name: "offer_reserve")]
    public function reserve(Offer $offer, EntityManagerInterface $entityManager, UnitRepository $unitRepository): Response
    {
        $customer = $this->getUser(); // Récupérer l'utilisateur connecté

        if (!$customer) {
            $this->addFlash('danger', 'Vous devez être connecté pour réserver une offre.');
            return $this->redirectToRoute('app_login');
        }

        // Vérifier si l'utilisateur a déjà réservé cette offre
        foreach ($customer->getOrders() as $order) {
            if ($order->getOffer()->getId() === $offer->getId()) {
                $this->addFlash('warning', 'Vous avez déjà réservé cette offre.');
                return $this->redirectToRoute('offer_index');
            }
        }

        // Définir le nombre d'unités à réserver
        $unitsToReserve = $offer->getUnitLimit();

        // Vérifier les unités disponibles
        $availableUnits = $unitRepository->findAvailableUnits($unitsToReserve);

        if (count($availableUnits) < $unitsToReserve) {
            $this->addFlash('danger', 'Pas assez d\'unités disponibles pour cette offre.');
            return $this->redirectToRoute('offer_index');
        }

        // Création de la commande
        $order = new Order();
        $order->setCustomer($customer);
        $order->setOffer($offer);
        $order->setStartDate(new \DateTime()); // Date de début actuelle
        $order->setEndDate((new \DateTime())->modify('+1 month')); // Fin dans un mois
        $order->setUnitPrice($offer->getPrice()); // Prix de l'offre

        // Réserver les unités
        foreach (array_slice($availableUnits, 0, $unitsToReserve) as $unit) {
            $order->addUnit($unit);
            $unit->addOrder($order);
        }

        $entityManager->persist($order);
        $entityManager->flush();

        $this->addFlash('success', 'Votre réservation a été effectuée avec succès.');

        // Rediriger vers la page de confirmation
        return $this->redirectToRoute('reservation_confirmation', ['orderId' => $order->getId()]);
    }

    #[Route('/reservation/confirmation/{orderId}', name: 'reservation_confirmation')]
    public function reservationConfirmation(int $orderId, OrderRepository $orderRepository): Response
    {
        $order = $orderRepository->find($orderId);

        if (!$order) {
            throw $this->createNotFoundException('Commande non trouvée');
        }

        return $this->render('order/reservation_confirmation.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/order/unit/{unitId}/update-state/{state}', name: 'update_unit_state', methods: ['POST'])]
    public function updateUnitState(Request $request, EntityManagerInterface $entityManager, UnitRepository $unitRepository, StateUnitRepository $stateUnitRepository, $unitId, $state)
    {
        $unit = $unitRepository->find($unitId);

        if (!$unit) {
            throw $this->createNotFoundException('Unité non trouvée');
        }

        // Récupérer l'objet StateUnit correspondant à l'état souhaité
        $newState = $stateUnitRepository->findOneBy(['name' => $state]);

        if (!$newState) {
            throw $this->createNotFoundException('État non trouvé');
        }

        // Mettre à jour l'état de l'unité
        $unit->setState($newState);
        $entityManager->flush();

        // Récupérer la commande associée
        $order = $unit->getOrders()->first(); // Assurez-vous que l'unité est associée à une commande
        if (!$order) {
            throw $this->createNotFoundException('Commande associée non trouvée');
        }

        // Redirection vers la page des détails de la commande
        return $this->redirectToRoute('app_order_detail', ['id' => $order->getId()]);
    }

    #[Route('/order/{id}', name: 'app_order_detail')]
    public function orderDetail(Order $order): Response
    {
        $types = [
            "Libre" => "#403f3f",
            "Serveur Web" => "#47a7c9",
            "Stockage" => "#6e6b6b",
            "Base de donnée" => "#3fba60"
        ];

        return $this->render('order/order_detail.html.twig', [
            'order' => $order,
            'types' => $types,
        ]);
    }

    #[Route('/order/unit/{unitId}/update-type', name: 'update_unit_type', methods: ['POST'])]
    public function updateUnitType(Request $request, EntityManagerInterface $entityManager, UnitRepository $unitRepository, TypeUnitRepository $typeUnitRepository, $unitId)
    {
        $unit = $unitRepository->find($unitId);

        if (!$unit) {
            throw $this->createNotFoundException('Unité non trouvée');
        }

        $typeReference = $request->request->get('type');
        $newType = $typeUnitRepository->findOneBy(['reference' => $typeReference]);

        if (!$newType) {
            throw $this->createNotFoundException('Type d\'unité non trouvé');
        }

        // Update the unit's type
        $unit->setType($newType);
        $entityManager->flush();

        // Redirect to the order detail page
        $order = $unit->getOrders()->first();
        if (!$order) {
            throw $this->createNotFoundException('Commande associée non trouvée');
        }

        return $this->redirectToRoute('app_order_detail', ['id' => $order->getId()]);
    }

    #[Route('/order/{id}/cancel', name: 'cancel_order', methods: ['POST'])]
    public function cancelOrder(Order $order, EntityManagerInterface $entityManager, UnitRepository $unitRepository, TypeUnitRepository $typeUnitRepository): Response
    {
        // Check if the order exists
        if (!$order) {
            throw $this->createNotFoundException('Commande non trouvée');
        }

        // Retrieve the units associated with the order
        $units = $order->getUnits();

        // Define the type for a free unit
        $freeType = $typeUnitRepository->findOneBy(['reference' => 'Libre']);

        if (!$freeType) {
            throw $this->createNotFoundException('Type "Libre" non trouvé');
        }

        // Update the type of each unit to "Libre"
        foreach ($units as $unit) {
            $unit->setType($freeType);
            $unit->removeOrder($order); // Remove the order from the unit
        }

        // Remove the order from the customer
        $customer = $order->getCustomer();
        $customer->removeOrder($order);

        // Remove the order from the entity manager
        $entityManager->remove($order);
        $entityManager->flush();

        $this->addFlash('success', 'Votre commande a été annulée avec succès.');

        // Redirect to the order index or another relevant page
        return $this->redirectToRoute('offer_index');
    }
}
