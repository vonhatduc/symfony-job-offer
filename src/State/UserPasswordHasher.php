<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Identity\Domain\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserPasswordHasher implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if (!$data instanceof User || !$data->getPlainPassword()) {
            // If no password change, just persist (if we were decorating).
            // But since we are replacing the processor (or are we decorating?), we must persist.
            // Note: If we use this processor as the ONLY processor for the operation, we must handle persist.
            // If we are decorating, we should call inner. 
            // Since we couldn't find inner, we assume we are the handler.
            $this->entityManager->persist($data);
            $this->entityManager->flush();
            return $data;
        }

        $hashedPassword = $this->passwordHasher->hashPassword(
            $data,
            $data->getPlainPassword()
        );
        $data->setPassword($hashedPassword);
        $data->eraseCredentials();

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        return $data;
    }
}
