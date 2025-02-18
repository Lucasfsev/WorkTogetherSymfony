<?php

namespace App\Controller;

use App\Repository\OfferRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(OfferRepository $offerRepository): Response
    {
        // Récupérer toutes les offres triées par prix croissant
        $offers = $offerRepository->findBy([], ['price' => 'ASC']);

        // Retourner la vue en passant les offres triées comme paramètre
        return $this->render('home/index.html.twig', [
            'offers' => $offers,
        ]);
    }
}
