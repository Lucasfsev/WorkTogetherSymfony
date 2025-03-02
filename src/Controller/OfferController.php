<?php

namespace App\Controller;

use App\Entity\Offer;
use App\Entity\Order;
use App\Repository\OfferRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OfferController extends AbstractController
{
    private EntityManagerInterface $em;
    private Security $security;

    public function __construct(EntityManagerInterface $em, Security $security)
    {
        $this->em = $em;
        $this->security = $security;
    }

    #[Route('/offers', name: 'offer_index')]
    public function index(OfferRepository $offerRepository): Response
    {
        // Récupérer toutes les offres triées par prix croissant
        $offers = $offerRepository->findBy([], ['price' => 'ASC']);

        return $this->render('offer/index.html.twig', [
            'offers' => $offers,
        ]);
    }

    #[Route('/offer/{id}', name: 'offer_show')]
    public function show(int $id): Response
    {
        $offer = $this->em->getRepository(Offer::class)->find($id);

        if (!$offer) {
            throw $this->createNotFoundException('L\'offre n\'a pas été trouvée.');
        }

        return $this->render('offer/show.html.twig', [
            'offer' => $offer,
        ]);
    }

    #[Route('/offer/{id}/reserve', name: 'offer_reserve')]
    public function reserve(Offer $offer): Response
    {
        $user = $this->security->getUser();

        if (!$user) {
            $this->addFlash('danger', 'Vous devez être connecté pour réserver une offre.');
            return $this->redirectToRoute('app_login');
        }

        // Check if the user has already reserved this offer
        foreach ($user->getOrders() as $order) {
            if ($order->getOffer()->getId() === $offer->getId()) {
                $this->addFlash('warning', 'Vous avez déjà réservé cette offre.');
                return $this->redirectToRoute('offer_index');
            }
        }

        // Check available units
        $availableUnits = $this->em->getRepository(Unit::class)->findAvailableUnits($offer->getUnitLimit());

        if (count($availableUnits) < $offer->getUnitLimit()) {
            $this->addFlash('danger', 'Pas assez d\'unités disponibles pour cette offre.');
            return $this->redirectToRoute('offer_index');
        }

        // Create a new order
        $order = new Order();
        $order->setOffer($offer);
        $order->setCustomer($user);
        $this->em->persist($order);

        // Reserve the units and link them to the order
        for ($i = 0; $i < $offer->getUnitLimit(); $i++) {
            if (empty($availableUnits)) {
                break; // Safety check to avoid errors if units run out
            }
            $unit = array_pop($availableUnits); // Take an available unit
            $order->addUnit($unit); // Add the unit to the order
            $unit->addOrder($order); // Link the unit to the order
        }

        $this->em->flush();

        $this->addFlash('success', 'Offre réservée avec succès !');
        return $this->redirectToRoute('offer_index');
    }
}
