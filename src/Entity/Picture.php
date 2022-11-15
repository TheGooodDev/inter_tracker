<?php

namespace App\Entity;

use App\Repository\PictureRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Serializer\Annotation\Groups;
use OpenApi\Annotations as OA;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PictureRepository::class)]
/**
 * @Vich\Uploadable()
 */
class Picture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(message: "Picture must have realName")]
    #[Assert\NotNull(message: "Picture must have realName")]
    #[ORM\Column(length: 255)]
    private ?string $realName = null;
    
    
    #[Assert\NotBlank(message: "Picture must have realPath")]
    #[Assert\NotNull(message: "Picture must have realPath")]
    #[ORM\Column(length: 255)]
    private ?string $realPath = null;
    
    
    #[Assert\NotBlank(message: "Picture must have publicPath")]
    #[Assert\NotNull(message: "Picture must have publicPath")]
    #[ORM\Column(length: 255)]
    private ?string $publicPath = null;
    
    #[Assert\NotBlank(message: "Picture must have publicPath")]
    #[Assert\NotNull(message: "Picture must have publicPath")]
    #[ORM\Column(length: 50)]
    private ?string $mimeType = null;
    
    #[ORM\Column]
    private ?bool $status = null;

    /**
     * @var File|null
     * @Vich\UploadableField(mapping="pictures", fileNameProperty="realPath")
     */

    /**
    *  @OA\Property(type="string")
    */
    private ?File $file;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRealName(): ?string
    {
        return $this->realName;
    }

    public function setRealName(string $realName): self
    {
        $this->realName = $realName;

        return $this;
    }

    public function getRealPath(): ?string
    {
        return $this->realPath;
    }

    public function setRealPath(string $realPath): self
    {
        $this->realPath = $realPath;

        return $this;
    }


    public function getFile(): ?File {
        return $this->file;
    }

    public function setFile(?File $file): ?Picture
    {
        $this->file = $file;
        return $this;
    }

    public function getPublicPath(): ?string
    {
        return $this->publicPath;
    }

    public function setPublicPath(string $publicPath): self
    {
        $this->publicPath = $publicPath;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType(string $mimeType): self
    {
        $this->mimeType = $mimeType;

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
