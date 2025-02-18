<?php

namespace App\Controller;

use App\Entity\Offer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class OfferController extends AbstractController
{
    private $em;

    // Injection de EntityManagerInterface dans le constructeur
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Affiche la liste de toutes les offres.
     *
     * @return Response
     */
    #[Route('/offers', name: 'offer_index')]
    public function index(): Response
    {
        // Récupérer toutes les offres depuis la base de données
        $offers = $this->em->getRepository(Offer::class)->findAll();

        // Passer la liste des offres à la vue Twig
        return $this->render('offer/index.html.twig', [
            'offers' => $offers,
        ]);
    }

    /**
     * Affiche les détails d'une offre spécifique.
     *
     * @param int $id L'identifiant de l'offre.
     * @return Response
     */
    #[Route('/offer/{id}', name: 'offer_show')]
    public function show(int $id): Response
    {
        // Récupérer l'offre depuis la base de données
        $offer = $this->em->getRepository(Offer::class)->find($id);

        // Vérifier si l'offre existe
        if (!$offer) {
            throw $this->createNotFoundException('L\'offre n\'a pas été trouvée.');
        }

        // Passer l'offre à la vue Twig
        return $this->render('offer/show.html.twig', [
            'offer' => $offer,
        ]);
    }
}
