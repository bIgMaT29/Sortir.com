<?php

namespace App\DataFixtures;

use App\Entity\Ville;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class VilleFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR'); // Utilisation de la localisation française

        // Création de 10 villes fictives
        for ($i = 0; $i < 10; $i++) {
            $ville = new Ville();
            $ville->setNom($faker->city);
            $ville->setCodePostal($faker->postcode);
            $ville->setDateCreation(new \DateTime()); // Date de création définie à maintenant

            $manager->persist($ville);

            // Ajout d'une référence pour chaque ville
            $this->addReference('ville_' . $i, $ville);
        }

        $manager->flush();
    }
}
