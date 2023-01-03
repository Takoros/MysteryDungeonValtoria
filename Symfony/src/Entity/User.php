<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 30)]
    private ?string $discordTag = null;

    #[ORM\OneToOne(inversedBy: 'userI', cascade: ['persist', 'remove'])]
    private ?Character $Character = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDiscordTag(): ?string
    {
        return $this->discordTag;
    }

    public function setDiscordTag(string $discordTag): self
    {
        $this->discordTag = $discordTag;

        return $this;
    }

    public function getCharacter(): ?Character
    {
        return $this->Character;
    }

    public function setCharacter(?Character $Character): self
    {
        $this->Character = $Character;

        return $this;
    }
}
