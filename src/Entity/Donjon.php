<?php

namespace App\Entity;

use App\Repository\DonjonRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DonjonRepository::class)]
class Donjon
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(message: "Donjon must have name")]
    #[Assert\NotNull(message: "Donjon must have name")]
    #[ORM\Column(length: 255)]
    #[Groups(['getDonjon','getAllDonjons'])]
    private ?string $name = null;

    #[Assert\NotBlank(message: "Donjon must have a level")]
    #[Assert\NotNull(message: "Donjon must have a level")]
    #[ORM\Column]
    #[Groups(['getAllChallenges','getDonjon','getAllDonjons','getChallenge'])]
    private ?int $level = null;

    #[Assert\NotBlank(message: "Donjon must have challenges")]
    #[Assert\NotNull(message: "Donjon must have challenges")]
    #[ORM\ManyToOne(inversedBy: 'donjons')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['getAllChallenges','getDonjon','getAllDonjons','getChallenge'])]
    private ?Challenge $challenges = null;

    #[Assert\NotNull(message: "Donjon must have challenges")]
    #[ORM\Column]
    private ?bool $status = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getLevel(): ?int
    {
        return $this->level;
    }

    public function setLevel(int $level): self
    {
        $this->level = $level;

        return $this;
    }

    public function getChallenges(): ?Challenge
    {
        return $this->challenges;
    }

    public function setChallenges(?Challenge $challenges): self
    {
        $this->challenges = $challenges;

        return $this;
    }

    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }
}
