<?php

namespace App\DataFixtures;

use App\Entity\Bay;
use App\Entity\Customer;
use App\Entity\Intervention;
use App\Entity\Offer;
use App\Entity\Order;
use App\Entity\StateUnit;
use App\Entity\TypeIntervention;
use App\Entity\TypeUnit;
use App\Entity\Unit;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Création des Bays (max 30 unités par Bay)
        for ($i = 1; $i <= 30; $i++) {
            $bay = new Bay();
            $bay->setReference('B' . str_pad($i, 3, "0", STR_PAD_LEFT));
            $manager->persist($bay);
        }
        $manager->flush();

        echo "Bays successfully created.\n";

        // Création des Customers
        $customersData = [
            [
                'first_name' => 'Jean',
                'last_name' => 'Dupont',
                'email' => 'jean.dupont@example.com',
                'role' => 'ROLE_USER',
                'address' => '123 Rue de Paris',
                'city' => 'Paris',
                'post_code' => '75001',
                'country' => 'France',
            ],
            [
                'first_name' => 'Paul',
                'last_name' => 'Leclerc',
                'email' => 'paul.leclerc@example.com',
                'role' => 'ROLE_USER',
                'address' => '789 Boulevard Saint-Germain',
                'city' => 'Paris',
                'post_code' => '75005',
                'country' => 'France',
            ],
        ];

        $customers = [];
        foreach ($customersData as $customerData) {
            $customer = new Customer();
            $customer->setFirstname($customerData['first_name']);
            $customer->setLastName($customerData['last_name']);
            $customer->setName($customerData['first_name'] . ' ' . $customerData['last_name']);
            $customer->setRole($customerData['role']);
            $customer->setPassword($this->hasher->hashPassword($customer, 'password'));
            $customer->setEmail($customerData['email']);
            $customer->setAddress($customerData['address']);
            $customer->setCity($customerData['city']);
            $customer->setPostCode($customerData['post_code']);
            $customer->setCountry($customerData['country']);
            $customer->setIsActive(true); // ✅ Ajout du champ isActive (actif par défaut)
            $manager->persist($customer);
            $customers[] = $customer;
        }
        $manager->flush();

        // Création des Offers
        $offersData = [
            ['name' => 'Basic Plan', 'price' => 99.99],
            ['name' => 'Premium Plan', 'price' => 199.99],
        ];

        $offers = [];
        foreach ($offersData as $offerData) {
            $offer = new Offer();
            $offer->setName($offerData['name']);
            $offer->setPrice($offerData['price']);
            $manager->persist($offer);
            $offers[] = $offer;
        }
        $manager->flush();

        // Création des Units avec Type et State
        $typeUnitsData = ['Web', 'Stockage', 'DataBase'];
        $stateUnitsData = ['Available', 'In Use', 'Under Maintenance'];

        $typeUnits = [];
        $stateUnits = [];

        foreach ($typeUnitsData as $typeName) {
            $typeUnit = new TypeUnit();
            $typeUnit->setName($typeName);
            $manager->persist($typeUnit);
            $typeUnits[] = $typeUnit;
        }

        foreach ($stateUnitsData as $stateName) {
            $stateUnit = new StateUnit();
            $stateUnit->setName($stateName);
            $manager->persist($stateUnit);
            $stateUnits[] = $stateUnit;
        }

        $manager->flush();

        // Création des Orders
        for ($i = 1; $i <= 10; $i++) {
            $order = new Order();
            $offer = $offers[array_rand($offers)];
            $customer = $customers[array_rand($customers)];

            $dateStart = new DateTimeImmutable('-' . rand(1, 10) . ' days');
            $dateEnd = $dateStart->modify('+' . rand(30, 60) . ' days');

            $order->setOffer($offer);
            $order->setCustomer($customer);
            $order->setDateStart($dateStart);
            $order->setDateEnd($dateEnd);
            $order->setRenewal(true); // ✅ Par défaut, les commandes sont renouvelées
            $order->setPrice($offer->getPrice()); // ✅ Sauvegarde du prix au moment de l'achat
            $order->setStatus('active'); // ✅ Ajout du status (par défaut "active")

            $manager->persist($order);

            // Gestion du renouvellement automatique
            if ($order->isRenewal()) {
                $newOrder = new Order();
                $newOrder->setOffer($offer);
                $newOrder->setCustomer($customer);
                $newOrder->setDateStart($dateEnd);
                $newOrder->setDateEnd($dateEnd->modify('+' . rand(30, 60) . ' days'));
                $newOrder->setRenewal(true);
                $newOrder->setPrice($offer->getPrice()); // ✅ Prend le prix actuel pour la nouvelle commande
                $newOrder->setStatus('pending');
                $manager->persist($newOrder);
            }
        }

        $manager->flush();
    }
}
