<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

#[Route(path: "/", name: "main_")]
class MainController extends AbstractController
{

    #[Route(path: '', name: 'redirect')]
    public function main(
        AuthorizationCheckerInterface $authorizationChecker
    ): Response {
        // Rediriger vers la page d'accueil si l'utilisateur est connecté, sinon vers la page de connexion
        if ($authorizationChecker->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('main_home');
        } else {
            return $this->redirectToRoute('security_login');
        }
    }

    #[Route(path: 'home', name: 'home')]
    public function home(): Response
    {
        // Afficher la page d'accueil
        return $this->redirectToRoute('sortie_viewList');
    }

}
