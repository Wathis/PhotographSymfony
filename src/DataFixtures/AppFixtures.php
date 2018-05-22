<?php
namespace App\DataFixtures;

use App\Entity\Format;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        foreach ($this->getUserData() as [$fullname, $username, $password, $email, $roles]) {
            $user = new User();
            $user->setFullName($fullname);
            $user->setUsername($username);
            $user->setPassword($this->passwordEncoder->encodePassword($user, $password));
            $user->setEmail($email);

            $manager->persist($user);
            $this->addReference($username, $user);
        }

        foreach ($this->getFormats() as [$ratio_taille, $prix, $category]) {
            $format = new Format();
            $format->setRatioTaille($ratio_taille);
            $format->setPrix($prix);
            $format->setCategorie($category);
            $manager->persist($format);
        }

        $manager->flush();
    }

    private function getFormats(): array
    {
        return [
            // $formatData = [$ratio_taille, , $prix, $category];
            [0.25, 50   ,'professionnel'],
            [0.50, 90   ,'professionnel'],
            [0.75, 130  ,'professionnel'],
            [1   , 150  ,'professionnel'],

            [0.5 , 10   ,'particulier'],
            [1   , 30   ,'particulier'],
        ];
    }

    private function getUserData(): array
    {
        return [
            // $userData = [$fullname, $username, $password, $email, $roles];
//            ['Nicolas NOEL', 'nicolas_admin', '@J!zZ6+7dY;v8Fe', 'noelnicola@gmail.com', 'ROLE_ADMIN'],
//            ['Mathis DELAUNAY', 'mathis_admin', '@J!zZ6+7dY;v8Fe', 'delaunaymathis@yahoo.fr', 'ROLE_ADMIN'],
            ['Nicolas NOEL', 'nicolas_admin', 'nicolas_admin', 'noelnicola@gmail.com', 'ROLE_ADMIN'],
            ['Mathis DELAUNAY', 'mathis_admin', 'mathis_admin', 'delaunaymathis@yahoo.fr', 'ROLE_ADMIN'],
        ];
    }


}