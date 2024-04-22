<?php

namespace App\DataFixtures;

use App\Entity\Campus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CampusFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Création de quelques campus fictifs
        $campusNoms = ['Campus A', 'Campus B', 'Campus C'];

        foreach ($campusNoms as $index => $nom) {
            $campus = new Campus();
            $campus->setNom($nom);
            $campus->setDateCreation(new \DateTime()); // Date de création définie à maintenant

            $manager->persist($campus);

            // Ajout d'une référence pour chaque campus
            $this->addReference('campus_' . $index, $campus);
        }

        $manager->flush();
    }
}
