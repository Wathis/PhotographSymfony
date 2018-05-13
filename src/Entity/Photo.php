<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PhotoRepository")
 */
class Photo
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     *
     * @Assert\NotBlank(message="Veuillez choisir une photo à mettre en ligne")
     * @Assert\File(mimeTypes={ "image/png","image/jpeg" })
     */
    private $photo;

    /**
     * @ORM\Column(type="string")
     *
     * @Assert\File(mimeTypes={ "image/png","image/jpeg" })
     */
    private $watermark;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $photo_name;

    /**
     * @ORM\Column(type="date")
     */
    private $photo_date;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Album", inversedBy="album_photos")
     * @ORM\JoinColumn(nullable=false)
     */
    private $photo_album;

    public function getAlbum(): ?Album
    {
        return $this->photo_album;
    }

    public function setAlbum(?Album $album): self
    {
        $this->photo_album = $album;

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getPhoto()
    {
        return $this->photo;
    }

    public function setPhoto($photo): self
    {
        $this->photo = $photo;
        return $this;
    }

    public function getWatermark()
    {
        return $this->watermark;
    }

    public function setWatermark($watermark): self
    {
        $this->watermark = $watermark;
        return $this;
    }

    public function getPhotoName(): ?string
    {
        return $this->photo_name;
    }

    public function setPhotoName(string $photo_name): self
    {
        $this->photo_name = $photo_name;

        return $this;
    }


    public function getPhotoDate(): ?\DateTimeInterface
    {
        return $this->photo_date;
    }

    public function setPhotoDate(\DateTimeInterface $photo_date): self
    {
        $this->photo_date = $photo_date;

        return $this;
    }
}
