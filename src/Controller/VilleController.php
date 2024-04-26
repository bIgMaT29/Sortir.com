<?php

namespace App\Controller;

use App\Entity\Ville;
use App\Form\VilleType;
use App\Repository\VilleRepository;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: "/ville/", name: "ville_")]
class VilleController extends AbstractController
{
    #[Route(path: 'viewlist', name: 'viewList')]
    public function view(VilleRepository $villeRepository): Response
    {
        $villes = $villeRepository->findAll();

        return $this->render('ville/viewList.html.twig', [
            'villes' => $villes,
        ]);
    }

    #[Route('add', name: 'add')]
    public function add(
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {

        $ville = new Ville();
        $ville->setDateCreation(new \DateTime()); // Initialisation de la date de création avant la persistance
        $form = $this->createForm(VilleType::class, $ville);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($ville);
            $entityManager->flush();

            return $this->redirectToRoute('ville_viewList');
        }

        return $this->render('ville/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('edit/{id}', name: 'edit')]
    public function edit(
        Request $request,
        EntityManagerInterface $entityManager,
        Ville $ville
    ): Response {
        $form = $this->createForm(VilleType::class, $ville);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Mettre à jour la date de modification
            $ville->setDateModification(new \DateTime());

            $entityManager->flush();

            return $this->redirectToRoute('ville_viewList');
        }

        return $this->render('ville/edit.html.twig', [
            'ville' => $ville,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: 'delete/{id}', name: 'delete')]
    public function delete(
        Request $request,
        EntityManagerInterface $entityManager,
        Ville $ville
    ): Response {
        $villeNom = $ville->getNom();
        try {
            if ($this->isCsrfTokenValid('delete' . $ville->getId(), $request->request->get('_token'))) {
                $entityManager->remove($ville);
                $entityManager->flush();
                $this->addFlash('success', 'La ville "'.$villeNom.'" a été supprimée avec succès.');
            }
        } catch (ForeignKeyConstraintViolationException $e) {
            // Gérer l'erreur de violation de contrainte d'intégrité référentielle ici
            $this->addFlash('error', 'Impossible de supprimer la ville car elle est associée à des lieux.');
        }

        return $this->redirectToRoute('ville_viewList');
    }

}
