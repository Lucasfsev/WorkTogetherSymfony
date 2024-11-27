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
        // Bays
        for ($i = 1; $i <= 30; $i++) {
            $bay = new Bay();
            $bay->setReference('B' . str_pad($i, 3, "0", STR_PAD_LEFT));
            $manager->persist($bay);
        }

        $manager->flush();
        echo "Bays successfully created.\n";
        // Customers
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
            [
                'first_name' => 'Sophie',
                'last_name' => 'Lemoine',
                'email' => 'sophie.lemoine@example.com',
                'role' => 'ROLE_USER',
                'address' => '321 Rue Victor Hugo',
                'city' => 'Lyon',
                'post_code' => '69001',
                'country' => 'France',
            ],
            [
                'first_name' => 'Luc',
                'last_name' => 'Durand',
                'email' => 'client@client.com',
                'role' => 'ROLE_USER',
                'address' => '12 Rue de la République',
                'city' => 'Marseille',
                'post_code' => '13001',
                'country' => 'France',
            ],
        ];
        $manager->flush();

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
            $manager->persist($customer);
            $customers[] = $customer;
        }
        $manager->flush();
        // Offers
        $offersData = [
            ['name' => 'Basic Plan', 'price' => 99.99],
            ['name' => 'Premium Plan', 'price' => 199.99],
            ['name' => 'Enterprise Plan', 'price' => 299.99],
        ];
        $manager->flush();
        $offers = [];
        foreach ($offersData as $offerData) {
            $offer = new Offer();
            $offer->setName($offerData['name']);
            $offer->setPrice($offerData['price']);
            $manager->persist($offer);
            $offers[] = $offer;
        }

        // Types of Units
        $typeUnitsData = ['Web', 'Stockage', 'DataBase'];
        $typeUnits = [];
        $manager->flush();
        foreach ($typeUnitsData as $typeName) {
            $typeUnit = new TypeUnit();
            $typeUnit->setName($typeName);
            $manager->persist($typeUnit);
            $typeUnits[] = $typeUnit;
        }

        // States of Units
        $stateUnitsData = ['Available', 'In Use', 'Under Maintenance'];
        $stateUnits = [];

        foreach ($stateUnitsData as $stateName) {
            $stateUnit = new StateUnit();
            $stateUnit->setName($stateName);
            $manager->persist($stateUnit);
            $stateUnits[] = $stateUnit;
        }
        $manager->flush();
        // Units

        $bays = $manager->getRepository(Bay::class)->findAll();
        foreach ($bays as $bay) {
            for ($i = 1; $i <= 42; $i++) {
                $unit = new Unit();
                $unit->setReference($bay->getReference() . '_U' . str_pad($i, 3, "0", STR_PAD_LEFT));
                $unit->setType($typeUnits[array_rand($typeUnits)]);
                $unit->setState($stateUnits[array_rand($stateUnits)]);
                $unit->setBay($bay);
                $manager->persist($unit);
            }
        }

        // Types of Intervention
        $typeInterventionsData = ['Repair', 'Upgrade', 'Inspection'];
        $typeInterventions = [];

        foreach ($typeInterventionsData as $typeName) {
            $typeIntervention = new TypeIntervention();
            $typeIntervention->setName($typeName);
            $manager->persist($typeIntervention);
            $typeInterventions[] = $typeIntervention;
        }

        // Interventions
        for ($i = 1; $i <= 10; $i++) {
            $intervention = new Intervention();
            $intervention->setType($typeInterventions[array_rand($typeInterventions)]);
            $intervention->setReport("Report for intervention #$i");
            $intervention->setDateStart(new DateTimeImmutable('-' . rand(1, 10) . ' days'));
            $intervention->setDateEnd(new DateTimeImmutable('+' . rand(1, 10) . ' days'));
            $manager->persist($intervention);
        }

        // Orders
        for ($i = 1; $i <= 15; $i++) {
            $order = new Order();

            if (empty($offers) || empty($customers)) {
                throw new \Exception('No offers or customers available for creating orders.');
            }

            $order->setOffer($offers[array_rand($offers)]);
            $order->setCustomer($customers[array_rand($customers)]);

            $dateStart = new DateTimeImmutable('-' . rand(1, 10) . ' days');
            $dateEnd = $dateStart->modify('+' . rand(1, 10) . ' days');
            $order->setDateStart($dateStart);
            $order->setDateEnd($dateEnd);

            $order->setRenewal((bool)rand(0, 1));
            $order->setPrice(rand(100, 500));

            $units = $manager->getRepository(Unit::class)->findAll();
            if (!empty($units)) {
                $selectedUnits = array_rand($units, min(rand(1, 5), count($units)));
                if (!is_array($selectedUnits)) {
                    $selectedUnits = [$selectedUnits];
                }
                foreach ($selectedUnits as $unitIndex) {
                    $order->addUnit($units[$unitIndex]);
                }
            }

            $manager->persist($order);
        }

        $manager->flush();
    }
}
