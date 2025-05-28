<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use App\Security\LoginFormAuthenticator;

class RegistrationController extends AbstractController
{
    private LoginFormAuthenticator $authenticator;

    public function __construct(LoginFormAuthenticator $authenticator)
    {
        $this->authenticator = $authenticator;
    }

    #[Route('/register', name: 'register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, UserAuthenticatorInterface $userAuthenticator): Response
    {
        $user = new Customer();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer les données du formulaire
            $user->setBillingAddress($form->get('billingAddress')->getData());
            $user->setPostCode($form->get('postCode')->getData());
            $user->setTown($form->get('town')->getData());
            $user->setCountry($form->get('country')->getData());

            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            // Encoder le mot de passe
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            $user->setRole("ROLE_CLIENT");

            $entityManager->persist($user);
            $entityManager->flush();

            // Authentifier l'utilisateur
            $userAuthenticator->authenticateUser($user, $this->authenticator, $request);

            // Ajouter un message de succès
            $this->addFlash('success', 'Votre compte a été créé avec succès !');

            // Rediriger vers la page d'accueil
            return $this->redirectToRoute('app_home');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }
}