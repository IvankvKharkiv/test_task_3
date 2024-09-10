<?php

namespace App\Controller\Api\V1;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\UserService;
use App\Validator\ConstraintUserUnique;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/v1/api', name: 'app_v1_api')]
class UserController extends AbstractController
{
    const ERROR_FORBIDDEN_MESSAGE = [
        "title" => "An error occurred",
        "status" => 403,
        "detail" => "Forbidden"
    ];

    #[Route('/users', name: '_users', methods: ['GET'])]
    public function index(SerializerInterface $serializer, UserRepository $userRepository): JsonResponse
    {
        return $this->json($serializer->normalize($userRepository->findAll(), null, ['groups' => 'main']));
    }

    #[Route('/user/{user}', name: '_user_get', methods: ['GET'])]
    public function user(SerializerInterface $serializer, User $user): JsonResponse
    {
        if (!$this->isUserAllowedToAccessResource($user)) {
            return $this->json(self::ERROR_FORBIDDEN_MESSAGE, 403);
        }
        return $this->json($serializer->normalize($user, null, ['groups' => 'main']));
    }

    #[Route('/user', name: '_user_add', methods: ['POST'])]
    public function addUser(SerializerInterface $serializer, Request $request, ValidatorInterface $validator, UserService $userService): JsonResponse
    {
        $this->denyAccessUnlessGranted(User::ROLE_ADMIN);

        $user = $serializer->deserialize($request->getContent(), User::class, 'json');

        $violations = $validator->validate($user);

        if (count($violations) > 0) {
            return $this->json($serializer->normalize($violations), 400);
        }

        $user = $userService->save($user);

        return $this->json($serializer->normalize($user));
    }

    #[Route('/user/{user}', name: '_user_edit', methods: ['PUT'])]
    public function editUser(User $user, SerializerInterface $serializer, Request $request, ValidatorInterface $validator, UserService $userService): JsonResponse
    {
        if (!$this->isUserAllowedToAccessResource($user)) {
            return $this->json(self::ERROR_FORBIDDEN_MESSAGE, 403);
        }

        $updatedUser = $serializer->deserialize($request->getContent(), User::class, 'json');

        $violations = $validator->validate($updatedUser);

        if (count($violations) > 0 && $this->isViolationUserUniqueValid($violations, $user, $updatedUser)) {
            return $this->json($serializer->normalize($violations), 400);
        }

        $userService->update($updatedUser, $user);

        return $this->json($serializer->normalize($user));
    }

    #[Route('/user/{user}', name: '_user_delete', methods: ['DELETE'])]
    public function deleteUser(User $user, UserService $userService): JsonResponse
    {
        $this->denyAccessUnlessGranted(User::ROLE_ADMIN);

        if (in_array(User::ROLE_ADMIN, $user->getRoles(), true)) {
            return $this->json(['error' => 'Admin users cannot be deleted.'], 400);
        }

        $userService->remove($user);

        return $this->json(['success' => 'true']);
    }

    private function isUserAllowedToAccessResource(User $resource): bool
    {
        return $this->getUser()->getId() === $resource->getId() || in_array(User::ROLE_ADMIN, $this->getUser()->getRoles(), true);
    }

    private function isViolationUserUniqueValid(ConstraintViolationListInterface $violations, User $user, User $updatedUser): bool
    {
        if (count($violations) > 1) {
            return true;
        }

        if (count($violations) === 1 && $violations[0]->getConstraint() instanceof ConstraintUserUnique && $updatedUser->getLogin() !== $user->getLogin()) {
            return true;
        }

        if (count($violations) === 1 && !$violations[0]->getConstraint() instanceof ConstraintUserUnique) {
            return true;
        }

        return false;
    }
}
