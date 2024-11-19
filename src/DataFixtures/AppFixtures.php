<?php

namespace App\DataFixtures;

use App\Entity\Bay;
use App\Entity\Customer;
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
        for($i=1;$i < 31;$i++){
            $bay = new Bay();
            $bay->setReference(str_pad($i, 4, "B000", STR_PAD_LEFT));
            $bay[] = $bay;
        }

        $customers = [
            [
                'Dupont',
                'Jean',
                'jean.dupont@example.com',
                '123 Rue de Paris',
                'Paris',
                '75001',
                'France'
            ],
            [
                'Paul',
                'Leclerc',
                'paul.leclerc@example.com',
                '789 Boulevard Saint-Germain',
                'Paris',
                '75005',
                'France'
            ],
            [
                'Sophie',
                'Lemoine',
                'sophie.lemoine@example.com',
                '321 Rue Victor Hugo',
                'Lyon',
                '69001',
                'France'
            ],
            [
                'first_name' => 'Durand',
                'last_name' => 'Luc',
                'email' => 'client@client.com',
                'role' => 'ROLE_USER',
                'address' => '12 Rue de la République',
                'city' => 'Marseille',
                'post_code' => '13001',
                'country' => 'France'
            ],
            ];
        $customerEntities = [];
        foreach($customers as $customer){
            $customerEntity = new Customer();
            $customerEntity->setFirstname($customer['first_name']);
            $customerEntity->setLastName($customer['last_name']);
            $customerEntity->setName($customer['first_name'] . ' ' . $customer['last_name']);
            $customerEntity->setRole($customer['role']);
            $customerEntity->setPassword($this->hasher->hashPassword($customerEntity, 'password'));
            $customerEntity->setEmail($customer['email']);
            $customerEntity->setAddress($customer['address']);
            $customerEntity->setCity($customer['city']);
            $customerEntity->setPostCode($customer['post_code']);
            $customerEntity->setCountry($customer['country']);
            $customerEntities[] = $customerEntity;
        }

        foreach ($customerEntities as $customer) {
            $manager->persist($customer);
        }


        $manager->flush();
    }
}
