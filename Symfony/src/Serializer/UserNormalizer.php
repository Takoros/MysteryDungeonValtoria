<?php

namespace App\Serializer;

use App\Entity\User;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class UserNormalizer implements DenormalizerInterface
{
    public function __construct(
        #[Autowire(service: ObjectNormalizer::class)]
        private NormalizerInterface $normalizer,
    ) {
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = [])
    {
        $user = new User();
        $user->setUsername($data['username'])
             ->setDiscordTag($data['id'])
             ->setEmail($data['email']);

        return $user;
    }
    
    public function supportsDenormalization(mixed $data, string $type, string $format = null)
    {
        return true;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            'object' => null,             // Doesn't support any classes or interfaces
            '*' => false,                 // Supports any other types, but the result is not cacheable
            User::class => true, // Supports MyCustomClass and result is cacheable
        ];
    }
}