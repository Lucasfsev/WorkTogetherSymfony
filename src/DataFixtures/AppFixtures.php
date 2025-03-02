<?php

namespace App\DataFixtures;

use App\Entity\Bay;
use App\Entity\Customer;
use App\Entity\Offer;
use App\Entity\Order;
use App\Entity\Setting;
use App\Entity\StateUnit;
use App\Entity\TypeIntervention;
use App\Entity\TypeUnit;
use App\Entity\Unit;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasherInterface) {
        $this->hasher = $userPasswordHasherInterface;
    }
    public function load(ObjectManager $manager): void
    {
        $states = [
            "OK" => "#02c41c",
            "Arrêt" => "#36010d",
            "Incident" => "#fcba03",
            "Maintenance" => "#04d6d6"
        ];
        $TypeUnit = [
            "Libre" => "#403f3f",
            "Serveur Web" => "#47a7c9",
            "Stockage" => "#6e6b6b",
            "Base de donnée" => "#3fba60"
        ];
        $TypeIntervention = [
            "Incident" => "#fcba03",
            "Maintenance" => "#04d6d6"
        ];
        $customers = [
            ["Emma", "Durand", "emma.durand@email.com", "emmy1234"],
            ["Lucas", "Martin", "lucas.martin@mail.fr", "lucas2025"],
            ["Amandine", "Lemoine", "amandine.lemoine@outlook.com", "secret123"],
            ["Maxime", "Dupont", "maxime.dupont@tiscali.com", "maxime987"],
        ];

        $users = [
            ["Julien", "Boucher", "julien.boucher@unmail.com", "password2025"],
            ["Clara", "Roche", "clara.roche@domain.com", "clara555"],
        ];
        $offers = [
            ["Pack PME", 20, 25, true, 1680, "Un pack conçu pour les petites et moyennes entreprises souhaitant sécuriser et optimiser leur infrastructure informatique."],
            ["Pack Start-up", 15, 5, true, 900, "Idéal pour les jeunes entreprises et start-ups en pleine croissance, ce pack offre des solutions évolutives adaptées à vos besoins."],
            ["Pack Entreprise", 50, 42, true, 2940, "Une solution complète et performante pour les grandes entreprises cherchant une gestion avancée de leurs ressources IT."],
            ["Acheter une unité", 0, 1, true, 100, "Besoin d'une capacité supplémentaire ? Achetez une unité individuelle selon vos besoins spécifiques."],
        ];
        $orderDates = [
            [new DateTimeImmutable("2024-01-01"), new DateTimeImmutable("2024-06-01")],
            [new DateTimeImmutable("2024-04-08"), new DateTimeImmutable("2024-10-08")],
            [new DateTimeImmutable("2023-04-18"), new DateTimeImmutable("2024-11-18")],
        ];

        $currentCustomers = [];
        $currentOffers = [];
        $currentStates = [];
        $currentTypes = [];

        $setting = new Setting();
        $setting->setSettingKey("currentUnitPrice");
        $setting->setValue("10");
        $manager->persist($setting);

        // ADDING DATA FOR STATE
        foreach ($states as $key => $value) {
            $state = new StateUnit();
            $state->setName($key);
            $state->setColor($value);
            array_push($currentStates, $state);
            $manager->persist($state);
        }

        // ADDING DATA FOR TYPEUNIT
        foreach ($TypeUnit as $key => $value) {
            $TypeUnit = new TypeUnit();
            $TypeUnit->setReference($key);
            $TypeUnit->setColor($value);
            array_push($currentTypes, $TypeUnit);
            $manager->persist($TypeUnit);
        }

        // ADDING DATA FOR INTERVENTIONTYPE
        foreach ($TypeIntervention as $key => $value) {
            $TypeIntervention = new TypeIntervention();
            $TypeIntervention->setReference($key);
            $TypeIntervention->setColor($value);
            $manager->persist($TypeIntervention);
        }

        // ADDING DATA FOR BAY AND UNIT
        for ($i=1; $i < 31; $i++) {
            $bay = new Bay();
            $bay->setReference(str_pad($i, 4, "B000", STR_PAD_LEFT));
            $manager->persist($bay);
            for ($j=1; $j < 43; $j++) {
                $unit = new Unit();
                $unit->setReference($bay->getReference() . str_pad($j, 4, "-U00", STR_PAD_LEFT));
                $unit->setBay($bay);
                $unit->setState($currentStates[1]);
                $unit->setType($currentTypes[0]);
                $manager->persist($unit);
            }
        }

        foreach ($customers as $customer) {
            $newCustomer = new Customer();
            $newCustomer->setFirstname($customer[0]);
            $newCustomer->setLastname($customer[1]);
            $newCustomer->setMailAddress($customer[2]);
            $newCustomer->setPassword($customer[3]);
            $newCustomer->setRole("ROLE_CLIENT");
            $password = $this->hasher->hashPassword($newCustomer, $customer[3]);
            $newCustomer->setPassword($password);
            array_push($currentCustomers,$newCustomer);
            $manager->persist($newCustomer);
        }

        // ADDING DATA FOR USER
        // ADDING DATA FOR USER
        foreach ($users as $user) {
            $newUser = new User();
            $newUser->setFirstname($user[0]);
            $newUser->setLastname($user[1]);
            $newUser->setMailAddress($user[2]);
            $password = $this->hasher->hashPassword($newUser, $user[3]);
            $newUser->setPassword($password);
            $newUser->setRole("ROLE_ADMIN");
            $manager->persist($newUser);
        }


        // ADDING DATA FOR OFFER
        foreach ($offers as $offer) {
            $newOffer = new Offer();
            $newOffer->setName($offer[0]);
            $newOffer->setPromotionPercentage($offer[1]);
            $newOffer->setUnitLimit($offer[2]);
            $newOffer->setAvailable($offer[3]);
            $newOffer->setPrice($offer[4]);
            $newOffer->setDescription($offer[5]);
            array_push($currentOffers,$newOffer);
            $manager->persist($newOffer);
        }

        // ADDING DATA FOR ORDER
        for ($i=0; $i < 3; $i++) {
            $newOrder = new Order();
            $newOrder->setStartDate($orderDates[$i][0]);
            $newOrder->setEndDate($orderDates[$i][1]);
            $newOrder->setCustomer($currentCustomers[$i]);
            $newOrder->setOffer($currentOffers[$i]);
            $newOrder->setUnitPrice($setting->getValue());
            $manager->persist($newOrder);
        }

        $manager->flush();
    }
}