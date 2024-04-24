<?php

namespace App\Controller;

use App\Exception\FileMoveException;
use App\Exception\InvalidMimeTypeException;
use App\Exception\PasswordMismatchException;
use App\Form\ParticipantType;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class MainController extends AbstractController
{

    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    #[Route('/', name: 'main')]
    public function main(
        AuthorizationCheckerInterface $authorizationChecker
    ): Response {
        // Rediriger vers la page d'accueil si l'utilisateur est connecté, sinon vers la page de connexion
        if ($authorizationChecker->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('main_home');
        } else {
            return $this->redirectToRoute('app_login');
        }
    }

    #[Route('/home', name: 'main_home')]
    public function home(): Response
    {
        // Afficher la page d'accueil
        return $this->render('main/home.html.twig');
    }

    #[Route('/profil', name: 'main_profil')]
    public function profil(
        Request $request,
        EntityManagerInterface $entityManager,
        SessionInterface $session
    ): Response {

        // Récupérer le participant connecté
        $participant = $this->getUser();

        // Créer le formulaire de modification du profil en utilisant le type de formulaire ParticipantType et en l'associant aux données du participant actuel
        $form = $this->createForm(ParticipantType::class, $participant);

        // Gérer la soumission du formulaire et lier les données de la requête au formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            try {

                // Gérer la modification de la photo de profil
                $this->handleProfilePhoto($form);

                // Gérer la modification du mot de passe
                $this->handlePassword($form);

                // Enregistrer les modifications dans la base de données
                $entityManager->flush();

                // Ajouter un flash message pour indiquer que la modification a été effectuée avec succès
                $session->getFlashBag()->add('success', 'Modification effectuée avec succès');

            } catch (UniqueConstraintViolationException $e) {
                // Gérez l'erreur de contrainte d'unicité ici
                $this->addFlash('error', 'Ce pseudo est déjà utilisé. Veuillez en choisir un autre !');
            } catch (InvalidMimeTypeException $e) {
                // Ajouter un flash message pour indiquer que seuls les fichiers JPEG, GIF et PNG sont autorisés
                $session->getFlashBag()->add('error', $e->getMessage());
            } catch (FileMoveException $e) {
                // Ajouter un flash message pour indiquer qu'une erreur s'est produite lors du déplacement de la photo de profil
                $session->getFlashBag()->add('error', $e->getMessage());
            } catch (PasswordMismatchException $e) {
                // Ajouter un flash message pour indiquer que les mots de passe ne correspondent pas
                $session->getFlashBag()->add('error', $e->getMessage());
            } finally {
                // Rediriger vers la page actuelle pour afficher le message
                return $this->redirectToRoute('main_profil');
            }

        }

        // Afficher le formulaire de modification du profil
        return $this->render('main/profil.html.twig', [
            'form' => $form->createView(),
            'participant' => $participant
        ]);

    }

    private function handleProfilePhoto(
        FormInterface $form,
    ): void {

        $newPhotoDeProfil = $form->get('newPhotoDeProfil')->getData();

        // Vérifier si le champ newPhotoDeProfil est renseigné
        if (!empty($newPhotoDeProfil)) {

            $allowedMimeTypes = ['image/jpeg', 'image/gif', 'image/png'];
            $mimeType = $newPhotoDeProfil->getMimeType();

            // Vérifier si le type MIME est autorisé
            if (!in_array($mimeType, $allowedMimeTypes)) {
                // Lancer une exception InvalidMimeTypeException si le type MIME n'est pas autorisé
                throw new InvalidMimeTypeException('Seuls les fichiers JPEG, GIF et PNG sont autorisés');
            }

            // Générer le nom de fichier avec l'ID du participant
            $participant = $form->getData();
            $newFilename = $participant->getId() . '.' . $newPhotoDeProfil->guessExtension();

            // Déplacer le fichier vers le répertoire img/profil
            try {
                $newPhotoDeProfil->move(
                    $this->getParameter('kernel.project_dir') . '/public/img/profil',
                    $newFilename
                );
            } catch (FileException $e) {
                // Lancer une exception FileMoveException si une erreur se produit lors du déplacement du fichier
                throw new FileMoveException("Une erreur s'est produite lors du déplacement de la photo de profil : " . $e->getMessage());
            }

            // Modifier la propriété de l'entité Participant avec le nouveau nom de fichier
            $participant->setPhotoDeProfil($newFilename);

        }

    }

    private function handlePassword(
        FormInterface $form,
    ) : void {

        // Récupérer les données des champs motDePasse et confirmMotDePasse
        $newPassword = $form->get('newMotDePasse')->getData();
        $confirmNewPassword = $form->get('confirmNewMotDePasse')->getData();

        // Vérifier si newPassword est vide OU confirmNewPassword est vide, mais pas les deux
        if (!empty($newPassword)) {

            // Vérifier si les mots de passe ne correspondent pas
            if ($newPassword !== $confirmNewPassword) {

                // Lancer une exception PasswordMismatchException si les mots de passe ne correspondent pas
                throw new PasswordMismatchException("Impossible de modifier le mot de passe... La confirmation ne correspond pas !");

            } else {

                // Récupérer l'entité Participant
                $participant = $form->getData();

                // Hasher le mot de passe
                $hashedPassword = $this->passwordHasher->hashPassword($participant, $newPassword);

                // Définir le mot de passe haché sur l'entité Participant
                $participant->setMotDePasse($hashedPassword);

            }

        }

    }

}
