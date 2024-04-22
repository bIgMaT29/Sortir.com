<?php

namespace App\Security;

use App\Entity\Participant;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class AppAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    private EntityManagerInterface $entityManager;

    public const LOGIN_ROUTE = 'app_login';

    public function __construct(
        private readonly UrlGeneratorInterface $urlGenerator,
        EntityManagerInterface $entityManager
    )
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Méthode pour authentifier l'utilisateur
     */
    public function authenticate(Request $request): Passport
    {
        // Récupérer l'email ou le pseudo et le mot de passe à partir de la requête
        $eMailOrPseudo = $request->request->get('eMailOrPseudo');
        $password = $request->request->get('password');

        // Enregistrer l'email ou le pseudo dans la session
        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $eMailOrPseudo);

        // Rechercher l'utilisateur en fonction de l'email dans la base de données
        $participant = $this->entityManager->getRepository(Participant::class)->findOneBy([
            'eMail' => $eMailOrPseudo,
        ]);

        // Si aucun utilisateur n'est trouvé par email, recherchez par pseudo
        if (!$participant) {
            $participant = $this->entityManager->getRepository(Participant::class)->findOneBy([
                'pseudo' => $eMailOrPseudo,
            ]);
        }

        // Vérifier si un utilisateur a été trouvé et si le mot de passe est correct
        if (!$participant || !password_verify($password, $participant->getMotDePasse())) {
            // Si l'utilisateur n'est pas trouvé ou le mot de passe est incorrect, générer une exception
            throw new AuthenticationException('Identifiant ou Mot de Passe Incorrect !');
        }

        // Créer un objet Passport avec les badges nécessaires
        return new Passport(
        // Utiliser l'email de l'utilisateur s'il est trouvé, sinon une chaîne vide
            new UserBadge($participant ? ($participant->getEMail() ?? '') : ''),
            new PasswordCredentials($password),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
                new RememberMeBadge(),
            ]
        );
    }

    /**
     * Méthode exécutée lorsque l'authentification réussit
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // Rediriger l'utilisateur vers la page précédente si elle existe
        if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) {
            return new RedirectResponse($targetPath);
        }

        // Sinon, rediriger l'utilisateur vers la page d'accueil
        return new RedirectResponse($this->urlGenerator->generate('main_home'));
    }

    /**
     * Méthode pour obtenir l'URL de connexion
     */
    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
