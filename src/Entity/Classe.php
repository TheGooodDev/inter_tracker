<?php

namespace App\Entity;

use App\Repository\ClasseRepository;
use Doctrine\ORM\Mapping as ORM;
// use Symfony\Component\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Groups;

use Symfony\Component\Validator\Constraints as Assert;
use Hateoas\Configuration\Annotation as Hateoas;


/**
 * @Hateoas\Relation(
 *      "self",
 *      href=@Hateoas\Route(
 *          "classe.getOne",
 *          parameters = {
 *              "idClasse" = "expr(object.getId())"
 *          },
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups="getAllClasse")
 * )
 */
#[ORM\Entity(repositoryClass: ClasseRepository::class)]
class Classe
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getAllClasse','getClasse','getPlayer','getAllPlayer','getParty'])]
    private ?int $id = null;

    #[Assert\NotBlank(message: "Classe must have name")]
    #[Assert\NotNull(message: "Classe must have name")]
    #[Groups(['getAllClasse','getClasse','getPlayer','getAllPlayer','getParty'])]
    #[ORM\Column(length: 50)]
    private ?string $name = null;
    
    #[Assert\NotBlank(message: "Classe must have status")]
    #[Assert\NotNull(message: "Classe must have status")]
    #[ORM\Column]
    #[Groups(['getClasse','getParty'])]
    private ?bool $status = null;
    
    #[Groups(['getAllClasse','getClasse','getPlayer','getAllPlayer','getParty'])]
    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Picture $picture = null;


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
