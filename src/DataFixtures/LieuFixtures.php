<?php

namespace App\DataFixtures;

use App\Entity\Lieu;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class LieuFixtures  extends Fixture implements DependentFixtureInterface
{

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR'); // Utilisation de la localisation française

        // Création de 10 lieux fictifs
        $lieuNoms = [
            'Bar sympa',
            'Bonne boite de nuit',
            'Restaurant chic',
            'Café convivial',
            'Salle de concert',
            'Espace culturel',
            'Parc public',
            'Salle de sport',
            'Théâtre local',
            'Cinéma de quartier'
        ];

        foreach ($lieuNoms as $index => $nom) {
            $lieu = new Lieu();
            $lieu->setNom($nom);
            $lieu->setRue($faker->streetAddress);
            $lieu->setLatitude($faker->latitude);
            $lieu->setLongitude($faker->longitude);
            $lieu->setDateCreation(new \DateTime()); // Date de création définie à maintenant

            // Association aléatoire à une ville existante
            $ville = $this->getReference('ville_' . rand(0, 9));
            $lieu->setVille($ville);

            $manager->persist($lieu);
            $this->addReference('lieu_' . $index, $lieu);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            VilleFixtures::class,
        ];
    }
}
