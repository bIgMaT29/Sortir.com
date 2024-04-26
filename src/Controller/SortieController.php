<?php

namespace App\Controller;

use App\Entity\Sortie;
use App\Repository\CampusRepository;
use App\Repository\ParticipantRepository;
use App\Repository\SortieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: "/sortie/", name: "sortie_")]
class SortieController extends AbstractController
{

    #[Route(path: 'viewList', name: 'viewList')]
    public function list(
        Request $request,
        CampusRepository $campusRepository,
        SortieRepository $sortieRepository
    ): Response {
        // Récupérer la liste des campus pour la liste déroulante
        $campuses = $campusRepository->findAll();

        // Récupérer les paramètres de requête du formulaire
        $campusId = $request->query->get('campus');
        $search = $request->query->get('search');
        $startDate = $request->query->get('startDate');
        $endDate = $request->query->get('endDate');
        $organisateur = $request->query->get('organisateur');
        $inscrit = $request->query->get('inscrit');
        $nonInscrit = $request->query->get('nonInscrit');
        $passees = $request->query->get('passees');

        // Créer un tableau avec les filtres non nuls
        $filters = [];
        if ($campusId) {
            $filters['campus'] = $campusId;
        }
        if ($search) {
            $filters['search'] = $search;
        }
        if ($startDate) {
            $filters['startDate'] = $startDate;
        }
        if ($endDate) {
            $filters['endDate'] = $endDate;
        }
        if ($organisateur) {
            $filters['organisateur'] = true;
        }
        if ($inscrit) {
            $filters['inscrit'] = true;
        }
        if ($nonInscrit) {
            $filters['nonInscrit'] = true;
        }
        if ($passees) {
            $filters['passees'] = true;
        }

        // Récupérer les données filtrées en fonction des paramètres du formulaire
        $sorties = $sortieRepository->findFilteredSorties($filters);

        return $this->render('sortie/viewList.html.twig', [
            'campuses' => $campuses,
            'sorties' => $sorties,
        ]);
    }

    #[Route(path: 'desister/{id}', name: 'desister')]
    public function desister(
        Request $request,
        EntityManagerInterface $entityManager,
        ParticipantRepository $participantRepository,
    ): RedirectResponse {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        // Récupérer l'ID de la sortie depuis la requête
        $sortieId = $request->attributes->get('id');

        // Rechercher la sortie correspondante dans la base de données
        $sortie = $entityManager->getRepository(Sortie::class)->find($sortieId);

        // Vérifier si la sortie a été trouvée
        if (!$sortie) {
            throw $this->createNotFoundException('La sortie demandée n\'a pas été trouvée.');
        }

        // Rechercher le participant correspondant dans la base de données
        $participant = $participantRepository->findOneBy(['eMail' => $user->getUserIdentifier()]);

        // Vérifier si l'utilisateur est inscrit à cette sortie
        if ($participant && $sortie->getParticipants()->contains($participant)) {
            // Retirer l'utilisateur de la liste des participants
            $sortie->removeParticipant($participant);

            // Enregistrer les changements dans la base de données
            $entityManager->flush();

            // Message de succès
            $this->addFlash('success', 'Vous vous êtes désisté de la sortie avec succès.');
        } else {
            // Message d'erreur si l'utilisateur n'est pas inscrit à la sortie
            $this->addFlash('error', 'Vous ne pouvez pas vous désister de cette sortie.');
        }

        // Redirection vers la liste des sorties
        return $this->redirectToRoute('sortie_viewList');
    }

    #[Route(path: 'inscrire/{id}', name: 'inscrire')]
    public function inscription(
        Request $request,
        EntityManagerInterface $entityManager,
        ParticipantRepository $participantRepository
    ): RedirectResponse {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();

        // Récupérer l'ID de la sortie depuis la requête
        $sortieId = $request->attributes->get('id');

        // Rechercher la sortie correspondante dans la base de données
        $sortie = $entityManager->getRepository(Sortie::class)->find($sortieId);

        // Vérifier si la sortie a été trouvée
        if (!$sortie) {
            throw $this->createNotFoundException('La sortie demandée n\'a pas été trouvée.');
        }

        // Rechercher le participant correspondant dans la base de données
        $participant = $participantRepository->findOneBy(['eMail' => $user->getUserIdentifier()]);

        // Vérifier si l'utilisateur est déjà inscrit à cette sortie
        if ($participant && !$sortie->getParticipants()->contains($participant)) {
            // Créez une ArrayCollection contenant le participant
            $participants = new ArrayCollection([$participant]);

            // Ajoutez le participant à la sortie en utilisant la méthode addParticipants
            $sortie->addParticipants($participants);

            // Enregistrer les changements dans la base de données
            $entityManager->flush();

            // Message de succès
            $this->addFlash('success', 'Vous vous êtes inscrit à la sortie avec succès.');
        } else {
            // Message d'erreur si l'utilisateur est déjà inscrit à la sortie
            $this->addFlash('error', 'Vous êtes déjà inscrit à cette sortie.');
        }

        // Redirection vers la liste des sorties
        return $this->redirectToRoute('sortie_viewList');
    }

    #[Route(path: 'sortie/{id}', name: 'view')]
    public function afficherSortie(int $id, SortieRepository $sortieRepository): Response
    {
        // Recherche de la sortie par son ID
        $sortie = $sortieRepository->find($id);

        if (!$sortie) {
            throw $this->createNotFoundException('La sortie demandée n\'existe pas.');
        }

        // Vous pouvez ensuite passer la sortie à votre vue ou effectuer d'autres opérations avec elle
        return $this->render('sortie/view.html.twig', [
            'sortie' => $sortie,
        ]);
    }

}
