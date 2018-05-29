<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AlbumRepository")
 */
class Album
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $album_name;

    /**
     * @ORM\Column(type="date")
     */
    private $album_date;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Photo", mappedBy="photo_album")
     */
    private $album_photos;

    /**
     * @ORM\Column(type="string",nullable=true)
     */
    private $password;

    /**
     * @ORM\Column(type="string")
     */
    private $category;

    public function __construct()
    {
        $this->album_photos = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param mixed $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }




    public function getPhotos()
    {
        return $this->album_photos;
    }

    public function addPhoto(?Photo $photo): void
    {
        $photo->setPost($this);
        if (!$this->album_photos->contains($photo)) {
            $this->album_photos->add($photo);
        }
    }
    public function removePhoto(Photo $photo): void
    {
        $photo->setPost(null);
        $this->album_photos->removeElement($photo);
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAlbumName(): ?string
    {
        return $this->album_name;
    }

    public function setAlbumName(string $album_name): self
    {
        $this->album_name = $album_name;

        return $this;
    }

    public function getAlbumDate(): ?\DateTimeInterface
    {
        return $this->album_date;
    }

    public function setAlbumDate(\DateTimeInterface $album_date): self
    {
        $this->album_date = $album_date;

        return $this;
    }
}
