<?php

namespace App\Controller\Api\V1;

use App\Entity\User;
use App\Service\ApiTokenService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/v1/api', name: 'app_v1_api')]
class ApiController extends AbstractController
{
    #[Route('/login', name: '_login', methods: ['POST'])]
    public function login(
        ApiTokenService $apiTokenService
    ): JsonResponse {
        /**
         * @var User $user
         */
        $user = $this->getUser();
        foreach ($user->getApiTokens() as $token) {
            if ($token->isValid()) {
                return $this->json([
                    'user' => $user->getLogin(),
                    'token' => $token->getToken(),
                ]);
            }
            $user->removeApiToken($token);
        }

        return $this->json([
            'user' => $user->getLogin(),
            'token' => $apiTokenService->generateToken($user)
        ]);
    }
}
