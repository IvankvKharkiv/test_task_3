<?php

namespace App\Validator;

use App\Repository\UserRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ConstraintUserUniqueValidator extends ConstraintValidator
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        $existingUsers = array_filter($this->userRepository->findAll(), function ($user) use ($value) {
            return $user->getLogin() === $value;
        });

        if (!empty($existingUsers)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ string }}', $value)
                ->addViolation();
        }
    }
}
