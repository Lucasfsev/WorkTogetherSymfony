<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\Customer;
use App\Entity\Order;

class ProfilController extends AbstractController
{
    #[Route('/profil', name: 'app_profil')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function index(): Response
    {
        $user = $this->getUser(); // Récupère l'utilisateur connecté

        // Vérifie si l'utilisateur connecté est un Customer
        $customer = $user instanceof Customer ? $user : null;

        // Récupère les commandes liées à l'utilisateur connecté
        $orders = $customer ? $customer->getOrders() : [];

        return $this->render('profil/index.html.twig', [
            'customer' => $customer,
            'orders' => $orders, // Ajout des commandes dans la vue
        ]);
    }
}
