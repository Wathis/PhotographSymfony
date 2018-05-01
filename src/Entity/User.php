<?php
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="user", options={"collate"="utf8mb4_unicode_ci", "charset"="utf8mb4"})
 */
class User implements UserInterface, \Serializable
{
    /**
     * @var int
     *
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;
    /**
     * @var string
     *
     * @ORM\Column(type="string",length=30)
     */
    private $fullName;
    /**
     * @var string
     *
     * @ORM\Column(type="string", unique=true,length=31)
     */
    private $username;
    /**
     * @var string
     *
     * @ORM\Column(type="string", unique=true,length=190)
     */
    private $email;
    /**
     * @var string
     *
     * @ORM\Column(type="string",length=190)
     */
    private $password;
    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }
    /**
     * @return string
     */
    public function getFullName(): string
    {
        return $this->fullName;
    }
    /**
     * @param string $fullName
     */
    public function setFullName(string $fullName)
    {
        $this->fullName = $fullName;
    }
    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }
    /**
     * @param string $username
     */
    public function setUsername(string $username)
    {
        $this->username = $username;
    }
    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }
    /**
     * @param string $email
     */
    public function setEmail(string $email)
    {
        $this->email = $email;
    }
    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }
    /**
     * @param string $password
     */
    public function setPassword(string $password)
    {
        $this->password = $password;
    }
    /**
     * Retourne le role des users
     *
     * @return array
     */
    public function getRoles(): array
    {
        //Return role admin toujours car il n'y a que des admins dans la base
        return ["ROLE_ADMIN"];
    }
    /**
     * @param array $role
     */
    public function serialize()
    {
        return serialize([$this->id, $this->username, $this->password]);
    }
    public function unserialize($serialized)
    {
        [$this->id, $this->username, $this->password] = unserialize($serialized, ['allowed_classes' => false]);
    }
    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }
    public function getSalt()
    {
        // TODO: Implement getSalt() method.
        return null;
    }
}