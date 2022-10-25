<?php

namespace App\Entity;

use App\Repository\PlayerRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PlayerRepository::class)]
class Player
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(message: "Player must have name")]
    #[ORM\Column(length: 255)]
    private ?string $pseudo = null;

    #[Assert\Choice(callback: 'getClasses')]
    #[ORM\Column(length: 255)]
    private ?string $classe = null;

    #[Assert\NotNull(message: "Status cant be null")]
    #[ORM\Column]
    private ?bool $status = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): self
    {
        $this->pseudo = $pseudo;

        return $this;
    }

    public function getClasse(): ?string
    {
        return $this->classe;
    }

    public function setClasse(string $classe): self
    {
        $this->classe = $classe;

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

    public static function getClasses()
    {
        return ["ecaflip","eniripsa","iop","cra","feca","sacrieur","sadida","osamodas","enutrof","sram","xelor","pandawa","roublard","zobal","steamer","eliotrope","huppermage","ouginak"];
    }
}
