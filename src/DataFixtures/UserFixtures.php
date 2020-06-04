<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder){
        $this->encoder = $encoder;
    }
    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setUsername("Admin");
        $user->setPassword($this->encoder->encodePassword($user, 'admin'));
        $user->setRoles(['ROLE_ADMIN','ROLE_USER','ROLE_SUPERUSER']);
        $manager->persist($user);
        $manager->flush();

        $user = new User();
        $user->setUsername("User");
        $user->setPassword($this->encoder->encodePassword($user, 'user'));
        $user->setRoles(['ROLE_USER']);
        $manager->persist($user);
        $manager->flush();

        $user = new User();
        $user->setUsername("Superuser");
        $user->setPassword($this->encoder->encodePassword($user, 'superuser'));
        $user->setRoles(['ROLE_USER','ROLE_SUPERUSER']);
        $manager->persist($user);
        $manager->flush();
    }
}
