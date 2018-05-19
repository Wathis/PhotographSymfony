<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ClientRepository")
 */
class Client
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=150, unique=true)
     */
    private $email;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Achat", mappedBy="client")
     */
    private $achats;

    public function getId()
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getAchats()
    {
        return $this->achats;
    }

    public function addAchat(?Achat $achat): void
    {
        $achat->setPost($this);
        if (!$this->achats->contains($achat)) {
            $this->achats->add($achat);
        }
    }
    public function removeAchat(Achat $achat): void
    {
        $achat->setPost(null);
        $this->achats->removeElement($achat);
    }
}
