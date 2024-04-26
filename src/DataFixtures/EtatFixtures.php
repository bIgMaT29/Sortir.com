<?php

namespace App\DataFixtures;

use App\Entity\Etat;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class EtatFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Création de quelques états fictifs
        $etats = ['Créée', 'Ouverte', 'Clôturée', 'Activité en cours', 'passée', 'Annulée'];

        foreach ($etats as $index => $libelle) {
            $etat = new Etat();
            $etat->setLibelle($libelle);
            $etat->setDateCreation(new \DateTime()); // Date de création définie à maintenant

            $manager->persist($etat);

            // Ajout d'une référence pour chaque état
            $this->addReference('etat_' . $index, $etat);
        }

        $manager->flush();
    }

}
