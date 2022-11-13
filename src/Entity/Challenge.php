<?php

namespace App\Entity;

use App\Repository\ChallengeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: ChallengeRepository::class)]
class Challenge
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(message: "Challenge must have name")]
    #[Assert\NotNull(message: "Challenge must have name")]
    #[ORM\Column(length: 255)]
    #[Groups(['getAllChallenges','getDonjon','getAllDonjons','getChallenge','getParty'])]
    private ?string $challengeName = null;

    #[Assert\NotNull(message: "Challenge description must not be null")]
    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['getAllChallenges','getDonjon','getAllDonjons','getChallenge','getParty'])]
    private ?string $description = null;

    #[Groups(['getAllChallenges','getAllDonjon'])]
    #[ORM\OneToMany(mappedBy: 'challenges', targetEntity: Donjon::class)]
    private Collection $donjons;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Picture $picture = null;

    public function __construct()
    {
        $this->donjons = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChallengeName(): ?string
    {
        return $this->challengeName;
    }

    public function setChallengeName(string $challengeName): self
    {
        $this->challengeName = $challengeName;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, Donjon>
     */
    public function getDonjons(): Collection
    {
        return $this->donjons;
    }

    public function addDonjon(Donjon $donjon): self
    {
        if (!$this->donjons->contains($donjon)) {
            $this->donjons->add($donjon);
            $donjon->setChallenges($this);
        }

        return $this;
    }

    public function removeDonjon(Donjon $donjon): self
    {
        if ($this->donjons->removeElement($donjon)) {
            // set the owning side to null (unless already changed)
            if ($donjon->getChallenges() === $this) {
                $donjon->setChallenges(null);
            }
        }

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
