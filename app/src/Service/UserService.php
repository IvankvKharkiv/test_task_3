<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserService
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $userPasswordHasher;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $userPasswordHasher
    ) {
        $this->entityManager = $entityManager;
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function save(User $user): User
    {
        $user->setPassword($this->userPasswordHasher->hashPassword($user, $user->getPassword()));
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    public function update(User $userUpdateFrom, User $userUpdateTo): User
    {
        $userUpdateTo->setLogin($userUpdateFrom->getLogin());
        $userUpdateTo->setPassword($this->userPasswordHasher->hashPassword($userUpdateTo, $userUpdateFrom->getPassword()));
        $userUpdateTo->setPhone($userUpdateFrom->getPhone());

        $this->entityManager->persist($userUpdateTo);
        $this->entityManager->flush();

        return $userUpdateTo;
    }

    public function remove(User $user): User
    {
        $this->entityManager->remove($user);
        $this->entityManager->flush();

        return $user;
    }

}
