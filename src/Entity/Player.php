<?php

namespace App\Entity;

use App\Repository\PlayerRepository;
use Doctrine\ORM\Mapping as ORM;
// use Symfony\Component\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Hateoas\Configuration\Annotation as Hateoas;


/**
 * @Hateoas\Relation(
 *      "self",
 *      href=@Hateoas\Route(
 *          "player.getOne",
 *          parameters = {
 *              "idPlayer" = "expr(object.getId())"
 *          },
 *      ),
 *      exclusion = @Hateoas\Exclusion(groups="getAllPlayer")
 * )
 */

#[ORM\Entity(repositoryClass: PlayerRepository::class)]
class Player
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['getAllPlayer','getPlayer','getParty'])]
    #[Assert\NotBlank(message: "Player must have name")]
    #[ORM\Column(length: 255)]
    private ?string $pseudo = null;

    #[Groups(['getAllPlayer','getPlayer','getParty'])]
    #[Assert\Choice(callback: 'getClasses')]
    #[ORM\Column(length: 255)]
    private ?string $classe = null;

    #[Groups(['getAllPlayer','getPlayer','getParty'])]
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
