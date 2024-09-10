<?php

namespace App\Service;

use App\Entity\ApiToken;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class ApiTokenService
{
    private EntityManagerInterface $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    public function generateToken(User $user): string
    {
        $apiToken = new ApiToken($user);

        $this->entityManager->persist($apiToken);
        $this->entityManager->flush();

        return $apiToken->getToken();
    }

}
