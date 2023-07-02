<?php

namespace App\Service;

use App\Entity\User;
use App\Serializer\UserNormalizer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class DiscordApiService
{
    const AUTHORIZATION_URI= 'https://discord.com/oauth2/authorize';
    const USERS_ME_ENDPOINT= '/api/users/@me';

    private $discordApiClient;
    private $serializer;
    private $clientId;
    private $redirectUri;

    public function __construct(HttpClientInterface $discordApiClient, SerializerInterface $serializer, string $clientId, string $redirectUri)
    {
        $this->discordApiClient = $discordApiClient;
        $this->serializer = $serializer;
        $this->clientId = $clientId;
        $this->redirectUri = $redirectUri;
    }

    public function getAuthorizationUrl(array $scope): string
    {
        $queryParameters = http_build_query([
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'response_type' => 'token',
            'scope' => implode(' ', $scope),
            'prompt' => 'none'
        ]);

        return self::AUTHORIZATION_URI . '?' . $queryParameters;
    }

    public function fetchUser(string $accessToken)
    {
        $response = $this->discordApiClient->request(Request::METHOD_GET, self::USERS_ME_ENDPOINT, [
            'auth_bearer' => $accessToken
        ]);

        $data = $response->getContent();

        $normalizers = [new UserNormalizer(new ObjectNormalizer())];
        $serializer = new Serializer($normalizers, [new JsonEncoder()]);

        return $serializer->deserialize($data, User::class, 'json');
    }
}