<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Form\CampusType;
use App\Repository\CampusRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/campus", name: "campus_")]
class CampusController extends AbstractController
{
    #[Route('/add', name: 'add')]
    public function add(Request $request, EntityManagerInterface $entityManager): Response
    {
        $campus = new Campus();
        $campus->setDateCreation(new \DateTime()); // Initialisation de la date de création avant la persistance
        $form = $this->createForm(CampusType::class, $campus);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($campus);
            $entityManager->flush();

            $this->addFlash('success', 'Le campus a été ajouté avec succès.');

            return $this->redirectToRoute('campus_viewList');
        }

        return $this->render('campus/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/edit/{id}', name: 'edit')]
    public function edit(Request $request, EntityManagerInterface $entityManager, Campus $campus): Response
    {
        $form = $this->createForm(CampusType::class, $campus);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $campus->setDateModification(new \DateTime());
            $entityManager->flush();

            $this->addFlash('success', 'Le campus a été modifié avec succès.');

            return $this->redirectToRoute('campus_viewList');
        }

        return $this->render('campus/edit.html.twig', [
            'campus' => $campus,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/viewlist', name: 'viewList')]
    public function viewList(CampusRepository $campusRepository): Response
    {
        $campuses = $campusRepository->findAll();

        return $this->render('campus/viewList.html.twig', [
            'campuses' => $campuses,
        ]);
    }

    #[Route('/delete/{id}', name: 'delete')]
    public function delete(Request $request, EntityManagerInterface $entityManager, Campus $campus): Response
    {
        try {
            $entityManager->remove($campus);
            $entityManager->flush();
            $this->addFlash('success', 'Le campus a été supprimé avec succès.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de la suppression du campus.');
        }

        return $this->redirectToRoute('campus_viewList');
    }
}
