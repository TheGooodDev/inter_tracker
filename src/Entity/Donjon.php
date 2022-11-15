<?php

namespace App\Entity;

use App\Repository\DonjonRepository;
use Doctrine\ORM\Mapping as ORM;
// use Symfony\Component\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Groups;

use Symfony\Component\Validator\Constraints as Assert;
use Hateoas\Configuration\Annotation as Hateoas;


/**
 * @Hateoas\Relation(
 *      "self",
 *      href=@Hateoas\Route(
 *          "donjon.getOne",
 *          parameters = {
 *              "idDonjon" = "expr(object.getId())"
 *          },
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups="getAllDonjons")
 * )
 */

#[ORM\Entity(repositoryClass: DonjonRepository::class)]

class Donjon
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['getAllChallenges','getDonjon','getAllDonjons','getChallenge','getParty'])]
    private ?int $id = null;

    #[Assert\NotBlank(message: "Donjon must have name")]
    #[Assert\NotNull(message: "Donjon must have name")]
    #[ORM\Column(length: 255)]
    #[Groups(['getAllChallenges','getDonjon','getAllDonjons','getChallenge','getParty'])]
    private ?string $name = null;

    #[Assert\NotBlank(message: "Donjon must have a level")]
    #[Assert\NotNull(message: "Donjon must have a level")]
    #[ORM\Column]
    #[Groups(['getAllChallenges','getDonjon','getAllDonjons','getChallenge','getParty'])]
    private ?int $level = null;

    #[Assert\NotBlank(message: "Donjon must have challenges")]
    #[Assert\NotNull(message: "Donjon must have challenges")]
    #[ORM\ManyToOne(inversedBy: 'donjons')]
    #[ORM\JoinColumn(nullable: false)] 
    #[Groups(['getDonjon','getAllDonjons'])]
    private ?Challenge $challenges = null;

    #[Assert\NotNull(message: "Donjon must have challenges")]
    #[ORM\Column]
    private ?bool $status = null;

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
