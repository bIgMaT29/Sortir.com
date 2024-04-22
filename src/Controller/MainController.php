<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Security;

class MainController extends AbstractController
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    #[Route('/', name: 'main')]
    public function main(): Response
    {
        if ($this->security->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('main_home');
        } else {
            return $this->redirectToRoute('app_login');
        }
    }

    #[Route('/home', name: 'main_home')]
    public function home(): Response
    {
        return $this->render('main/home.html.twig');
    }

}
