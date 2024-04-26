<?php

namespace App\Controller;

use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route(path: "/security/", name: "security_")]
class SecurityController extends AbstractController
{
    #[Route(path: 'login', name: 'login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // if ($this->getUser()) {
        //     return $this->redirectToRoute('target_path');
        // }

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // Récupérer le message d'erreur
        $errorMessage = $error?->getMessage();

        return $this->render('security/login.html.twig', ['last_username' => $lastUsername, 'error' => $errorMessage]);
    }

    #[Route(path: 'logout', name: 'logout')]
    public function logout(): void
    {
        throw new LogicException('Cette méthode peut être vide - elle sera interceptée par la clé de déconnexion sur votre pare-feu.');
    }

}
