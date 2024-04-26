<?php

namespace App\DataFixtures;

use App\Entity\Sortie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class SortieFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR'); // Utilisation de la localisation française

        // Création de quelques sorties fictives
        for ($i = 0; $i < 10; $i++) {
            $sortie = new Sortie();
            $sortie->setNom($faker->sentence);
            $sortie->setDateHeureDebut($faker->dateTimeBetween('+15 days', '+30 days'));
            $sortie->setDuree($faker->numberBetween(1, 24));
            $sortie->setDateLimiteInscription($faker->dateTimeBetween('-7 days', '+7 days'));
            $sortie->setNbInscriptionsMax($faker->numberBetween(5, 50));
            $sortie->setInfosSortie($faker->paragraph);

            // Association aléatoire à un état existant
            $etat = $this->getReference('etat_' . rand(0, 3));
            $sortie->setEtat($etat);

            // Association aléatoire à un lieu existant
            $lieu = $this->getReference('lieu_' . rand(0, 9));
            $sortie->setLieu($lieu);

            // Association aléatoire à un campus existant
            $campus = $this->getReference('campus_' . rand(0, 2));
            $sortie->setCampus($campus);

            // Association aléatoire à un organisateur existant
            $organisateur = $this->getReference('participant_' . rand(0, 9));
            $sortie->setOrganisateur($organisateur);

            // Ajout de participants aléatoires
            $sortie->addParticipants($this->getRandomParticipants());

            $sortie->setDateCreation(new \DateTime()); // Date de création définie à maintenant

            $manager->persist($sortie);
        }

        $manager->flush();
    }

    private function getRandomParticipants(): ArrayCollection
    {
        $participants = new ArrayCollection();
        $participantCount = rand(1, 5); // Nombre aléatoire de participants entre 1 et 5

        while ($participants->count() < $participantCount) {
            $index = rand(0, 9); // Sélection aléatoire d'un index de participant
            $participantReference = $this->getReference('participant_' . $index);

            // Vérification si le participant n'a pas déjà été ajouté
            if ($participantReference !== null && !$participants->contains($participantReference)) {
                $participants->add($participantReference); // Ajout du participant à la collection
            }
        }

        return $participants;
    }

    public function getDependencies(): array
    {
        return [
            EtatFixtures::class,
            LieuFixtures::class,
            CampusFixtures::class,
            ParticipantFixtures::class,
        ];
    }

}
