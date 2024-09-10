<?php

namespace App\DataFixtures;

use App\Entity\ApiToken;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixture extends Fixture
{
    const COMMON_PASS = 1234;

    private UserPasswordHasherInterface $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setLogin('user1');
        $user->setPassword($this->userPasswordHasher->hashPassword($user, self::COMMON_PASS));

        $apiToken = new ApiToken($user);

        $manager->persist($apiToken);
        $manager->persist($user);


        $user = new User();
        $user->setLogin('user2');
        $user->setPassword($this->userPasswordHasher->hashPassword($user, self::COMMON_PASS));

        $apiToken = new ApiToken($user);

        $manager->persist($apiToken);
        $manager->persist($user);

        $user = new User();
        $user->setLogin('admin');
        $user->setRoles([User::ROLE_ADMIN]);
        $user->setPassword($this->userPasswordHasher->hashPassword($user, self::COMMON_PASS));

        $apiToken = new ApiToken($user);

        $manager->persist($apiToken);
        $manager->persist($user);

        $manager->flush();
    }
}
