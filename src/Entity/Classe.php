<?php

namespace App\Entity;

use App\Repository\ClasseRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: ClasseRepository::class)]
class Classe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(message: "Classe must have name")]
    #[Assert\NotNull(message: "Classe must have name")]
    #[Groups(['getAllClasse','getClasse','getPlayer','getAllPlayer'])]
    #[ORM\Column(length: 50)]
    #[Groups(['getClasse'])]
    private ?string $name = null;
    
    #[Assert\NotBlank(message: "Classe must have status")]
    #[Assert\NotNull(message: "Classe must have status")]
    #[ORM\Column]
    #[Groups(['getClasse'])]
    private ?bool $status = null;
    
    #[Groups(['getAllClasse','getClasse','getPlayer','getAllPlayer'])]
    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['getClasse'])]
    private ?Picture $picture = null;

    private Generator $faker;

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

    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getPicture(): ?Picture
    {
        return $this->picture;
    }

    public function setPicture(Picture $picture): self
    {
        $this->picture = $picture;

        return $this;
    }
}
