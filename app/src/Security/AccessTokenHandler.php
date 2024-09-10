<?php

namespace App\Security;

use App\Entity\ApiToken;
use App\Repository\ApiTokenRepository;
use Symfony\Component\Security\Http\AccessToken\AccessTokenHandlerInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

class AccessTokenHandler implements AccessTokenHandlerInterface
{
    public function __construct(
        private ApiTokenRepository $repository
    ) {
    }

    public function getUserBadgeFrom(string $accessToken): UserBadge
    {
        /**
         * @var ApiToken $accessToken
         */
        $accessToken = $this->repository->findOneByToken($accessToken);
        if (null === $accessToken || !$accessToken->isValid()) {
            throw new \Exception('Invalid credentials.');
        }

        // and return a UserBadge object containing the user identifier from the found token
        // (this is the same identifier used in Security configuration; it can be an email,
        // a UUID, a username, a database ID, etc.)
        return new UserBadge($accessToken->getUser()->getLogin());
    }
}
