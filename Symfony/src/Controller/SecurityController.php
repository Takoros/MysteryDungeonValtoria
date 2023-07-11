<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Security\DiscordAuthenticator;
use App\Service\DiscordApiService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends AbstractController
{
    private $discordApiService;

    function __construct(DiscordApiService $discordApiService)
    {
        $this->discordApiService = $discordApiService;   
    }

    #[Route('/discord/connect', name: 'app_discord_connect')]
    public function discordConnect(Request $request): Response
    {
        $token = $request->request->get('token');

        if($this->isCsrfTokenValid('discord-auth', $token)){
            $request->getSession()->set(DiscordAuthenticator::DISCORD_AUTH_KEY, true);
            $scope = [
                'identify', 'email'
            ];
            return $this->redirect($this->discordApiService->getAuthorizationUrl($scope));
        }

        return $this->redirectToRoute('app_discord_connect');
    }

    #[Route('/discord/login', name: 'app_discord_login')]
    public function discordLogin(Request $request, EntityManagerInterface $em, UserRepository $userRepository): Response
    {
        $accessToken = $request->get('access_token');

        if(!$accessToken){
            return $this->render('discord_login_check.html.twig');
        }

        $discordUser = $this->discordApiService->fetchUser($accessToken);

        $user = $userRepository->findOneBy(['discordTag' => $discordUser->getDiscordTag()]);

        if($user){

            if($user->getEmail() !== $discordUser->getEmail()){
                $user->setEmail($discordUser->getEmail());
            }

            if($user->getUsername() !== $discordUser->getUsername()){
                $user->setUsername($discordUser->getUsername());
            }

            if($user->getRoles() === null){
                $user->setRoles(['ROLE_USER']);
            }

            if($user->getAccessToken() !== $accessToken){
                $user->setAccessToken($accessToken);
            }

            $em->flush();

            return $this->redirectToRoute('app_discord_auth', [
                'accessToken' => $accessToken
            ]);
        }

        return new JsonResponse(['message' => 'Veuillez créer un personnage sur le bot discord avant de vous connecter']);
    }

    #[Route('/discord/auth', name: 'app_discord_auth')]
    public function auth(): Response
    {
        return $this->redirectToRoute('app_home');
    }
    
    #[Route('/logout', name: 'app_logout')]
    public function logout(): Response
    {
        return $this->redirectToRoute('app_home'); 
    }
}
