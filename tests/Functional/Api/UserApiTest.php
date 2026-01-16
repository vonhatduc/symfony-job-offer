<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Identity\Domain\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Functional Tests for User API
 */
class UserApiTest extends ApiTestCase
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $this->entityManager = $container->get('doctrine')->getManager();
        $this->passwordHasher = $container->get(UserPasswordHasherInterface::class);
        
        // Clean database before each test
        $this->cleanDatabase();
    }

    private function cleanDatabase(): void
    {
        $connection = $this->entityManager->getConnection();
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=0');
        $connection->executeStatement('TRUNCATE TABLE job_application');
        $connection->executeStatement('TRUNCATE TABLE job_offer');
        $connection->executeStatement('TRUNCATE TABLE user_role');
        $connection->executeStatement('TRUNCATE TABLE `role`'); // Clean roles too
        $connection->executeStatement('TRUNCATE TABLE `permission`');
        $connection->executeStatement('TRUNCATE TABLE `user`');
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');
    }

    private function createAdminUser(): User
    {
        // specific role for test
        $roleAdmin = new \App\Identity\Domain\Entity\Role('ADMIN', 'Administrator');
        $this->entityManager->persist($roleAdmin);

        $user = new User();
        $user->setEmail('admin@test.com');
        $user->setName('Admin');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));
        $user->addRole($roleAdmin);
        
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        return $user;
    }

    private function getAdminToken(): string
    {
        $this->createAdminUser();
        
        $response = static::createClient()->request('POST', '/api/v1/login', [
            'json' => [
                'email' => 'admin@test.com',
                'password' => 'password'
            ]
        ]);
        
        $data = $response->toArray();
        return $data['token'];
    }

    /**
     * @test
     */
    public function login_returns_jwt_token(): void
    {
        $this->createAdminUser();

        $response = static::createClient()->request('POST', '/api/v1/login', [
            'json' => [
                'email' => 'admin@test.com',
                'password' => 'password'
            ]
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertArrayHasKey('token', $response->toArray());
    }

    /**
     * @test
     */
    public function unauthenticated_user_cannot_access_protected_routes(): void
    {
        static::createClient()->request('GET', '/api/admin/v1/users');

        $this->assertResponseStatusCodeSame(401);
    }

    /**
     * @test
     */
    public function authenticated_admin_can_list_users(): void
    {
        $token = $this->getAdminToken();

        $response = static::createClient()->request('GET', '/api/admin/v1/users', [
            'headers' => ['Authorization' => 'Bearer ' . $token]
        ]);

        $this->assertResponseIsSuccessful();
    }
}
