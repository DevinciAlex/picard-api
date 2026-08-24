<?php

namespace App\Entity;

use App\Repository\LoyaltyAccountRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LoyaltyAccountRepository::class)]
class LoyaltyAccount
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\OneToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    #[ORM\Column(options: ['default' => 0])]
    private int $points = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getPoints(): int
    {
        return $this->points;
    }

    public function addPoints(int $points): static
    {
        if ($points < 0) {
            throw new \InvalidArgumentException('Le nombre de points ajouté doit être positif.');
        }

        $this->points += $points;

        return $this;
    }
}
