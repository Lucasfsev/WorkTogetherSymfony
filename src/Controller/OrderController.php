<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\Offer;
use App\Repository\UnitRepository; // Assuming you have a UnitRepository to handle unit-related queries
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpFoundation\Response;

class OrderController extends AbstractController
{
    #[Route('/offer/{id}/reserve', name: 'offer_reserve')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function reserve(Offer $offer, EntityManagerInterface $entityManager, UnitRepository $unitRepository): Response
    {
        $customer = $this->getUser(); // Get the connected customer

        // Check if the user has already reserved this offer
        foreach ($customer->getOrders() as $order) {
            if ($order->getOffer()->getId() === $offer->getId()) {
                $this->addFlash('warning', 'Vous avez déjà réservé cette offre.');
                return $this->redirectToRoute('offer_index');
            }
        }

        // Define the number of units to reserve
        $unitsToReserve = $offer->getUnitLimit(); // Adjust according to your needs

        // Check available units
        $availableUnits = $unitRepository->findAvailableUnits($unitsToReserve); // Fetch available units from the repository

        if (count($availableUnits) < $unitsToReserve) {
            $this->addFlash('danger', 'Pas assez d\'unités disponibles pour cette offre.');
            return $this->redirectToRoute('offer_index');
        }

        // Create a new order
        $order = new Order();
        $order->setCustomer($customer);
        $order->setOffer($offer);
        $order->setStartDate(new \DateTime()); // Current date as start date
        $order->setUnitPrice($offer->getPrice()); // Record the offer price

        // Reserve the units
        for ($i = 0; $i < $unitsToReserve; $i++) {
            if (empty($availableUnits)) {
                break; // Safety check to avoid errors if units run out
            }
            $unit = array_pop($availableUnits); // Take an available unit
            $order->addUnit($unit); // Add the unit to the order
            $unit->addOrder($order); // Link the unit to the order
        }

        $entityManager->persist($order);
        $entityManager->flush();

        $this->addFlash('success', 'Votre réservation a été effectuée avec succès.');

        return $this->redirectToRoute('offer_index'); // Redirect to the offers list
    }
}
