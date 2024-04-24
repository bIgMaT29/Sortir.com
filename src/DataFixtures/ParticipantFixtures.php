<?php

namespace App\DataFixtures;

use App\Entity\Participant;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ParticipantFixtures extends Fixture implements DependentFixtureInterface
{
    private UserPasswordHasherInterface $passwordHasherService;

    public function __construct(UserPasswordHasherInterface $passwordHasherService)
    {
        $this->passwordHasherService = $passwordHasherService;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR'); // Utilisation de la localisation française

        // Création de quelques participants fictifs
        for ($i = 0; $i < 10; $i++) {
            $participant = new Participant();
            $participant->setPseudo($faker->userName);
            $participant->setPrenom($faker->firstName);
            $participant->setNom($faker->lastName);
            $participant->setTelephone($faker->phoneNumber);
            $participant->setEMail($faker->email);

            // Hachage du mot de passe
            $hashedPassword = $this->passwordHasherService->hashPassword($participant, 'mdp_' . $i);
            $participant->setMotDePasse($hashedPassword);

            $participant->setPhotoDeProfil("defaut.png");
            $participant->setAdministrateur($faker->boolean);
            $participant->setActif($faker->boolean);
            $participant->setDateCreation(new \DateTime()); // Date de création définie à maintenant

            // Association aléatoire à un campus existant
            $campus = $this->getReference('campus_' . rand(0, 2));
            $participant->setCampus($campus);

            $manager->persist($participant);

            // Ajout d'une référence pour chaque participant
            $this->addReference('participant_' . $i, $participant);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            CampusFixtures::class,
        ];
    }
}
