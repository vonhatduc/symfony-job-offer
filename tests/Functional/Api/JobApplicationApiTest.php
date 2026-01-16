<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Identity\Domain\Entity\User;
use App\JobManagement\Domain\Entity\JobOffer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class JobApplicationApiTest extends ApiTestCase
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();
        $this->entityManager = $container->get('doctrine')->getManager();
        $this->passwordHasher = $container->get(UserPasswordHasherInterface::class);
        
        $this->cleanDatabase();
    }

    private function cleanDatabase(): void
    {
        $connection = $this->entityManager->getConnection();
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=0');
        $connection->executeStatement('TRUNCATE TABLE job_application');
        $connection->executeStatement('TRUNCATE TABLE job_offer');
        $connection->executeStatement('TRUNCATE TABLE `role`');
        $connection->executeStatement('TRUNCATE TABLE `permission`');
        $connection->executeStatement('TRUNCATE TABLE user_role');
        $connection->executeStatement('TRUNCATE TABLE `user`');
        $connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');
    }

    private function createMemberUser(): User
    {
        $user = new User();
        $user->setEmail('user@test.com');
        $user->setName('Member');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));
        
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        return $user;
    }

    private function createJobOffer(): JobOffer
    {
        $job = new JobOffer('PHP Developer', 'Great job');
        $this->entityManager->persist($job);
        $this->entityManager->flush();
        
        return $job;
    }

    private function getToken($client): string
    {
        $this->createMemberUser();
        
        $response = $client->request('POST', '/api/v1/login', [
            'json' => [
                'email' => 'user@test.com',
                'password' => 'password'
            ]
        ]);
        
        return $response->toArray()['token'];
    }

    /**
     * @test
     */
    public function can_list_job_offers(): void
    {
        $client = static::createClient();
        $token = $this->getToken($client);
        
        $client->request('GET', '/api/v1/job-offers', [
            'headers' => ['Authorization' => 'Bearer ' . $token]
        ]);
        
        $this->assertResponseIsSuccessful();
    }

    /**
     * @test
     */
    public function user_can_apply_successfully(): void
    {
        $client = static::createClient();
        $token = $this->getToken($client);
        $job = $this->createJobOffer();

        $client->request('POST', '/api/v1/job-applications', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json'
            ],
            'json' => [
                'jobOfferId' => $job->getId()
            ]
        ]);

        $this->assertResponseStatusCodeSame(201);
    }

    private function getAdminToken($client): string
    {
        $roleAdmin = new \App\Identity\Domain\Entity\Role('ADMIN', 'Administrator');
        $this->entityManager->persist($roleAdmin);

        $user = new User();
        $user->setEmail('admin@test.com');
        $user->setName('Admin');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password'));
        $user->addRole($roleAdmin);
        
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        $response = $client->request('POST', '/api/v1/login', [
            'json' => [
                'email' => 'admin@test.com',
                'password' => 'password'
            ]
        ]);
        
        return $response->toArray()['token'];
    }

    /**
     * @test
     */
    public function user_cannot_apply_twice_to_same_job(): void
    {
        $client = static::createClient();
        $token = $this->getToken($client);
        $job = $this->createJobOffer();

        // First application
        $client->request('POST', '/api/v1/job-applications', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json'
            ],
            'json' => ['jobOfferId' => $job->getId()]
        ]);
        $this->assertResponseIsSuccessful();

        // Second application
        $client->request('POST', '/api/v1/job-applications', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/json'
            ],
            'json' => ['jobOfferId' => $job->getId()]
        ]);

        $this->assertResponseStatusCodeSame(400);
        $this->assertJsonContains(['message' => 'User 1 has already applied to job offer 1.']);
    }

    /**
     * @test
     */
    public function admin_can_list_all_applications(): void
    {
        $client = static::createClient();
        $userToken = $this->getToken($client);
        $adminToken = $this->getAdminToken($client);
        $job = $this->createJobOffer();

        // One application
        $client->request('POST', '/api/v1/job-applications', [
            'headers' => ['Authorization' => 'Bearer ' . $userToken],
            'json' => ['jobOfferId' => $job->getId()]
        ]);

        // Admin lists
        $client->request('GET', '/api/admin/v1/job-applications', [
            'headers' => [
                'Authorization' => 'Bearer ' . $adminToken,
                'Accept' => 'application/json'
            ]
        ]);

        $this->assertResponseIsSuccessful();
        $responseData = $client->getResponse()->toArray();
        $this->assertCount(1, $responseData);
        $this->assertEquals('PHP Developer', $responseData[0]['jobOfferTitle']);
        $this->assertEquals('user@test.com', $responseData[0]['userEmail']);
    }

    /**
     * @test
     */
    public function admin_can_see_application_count_in_job_details(): void
    {
        $client = static::createClient();
        $userToken = $this->getToken($client);
        $adminToken = $this->getAdminToken($client);
        $job = $this->createJobOffer();

        // Apply
        $client->request('POST', '/api/v1/job-applications', [
            'headers' => ['Authorization' => 'Bearer ' . $userToken],
            'json' => ['jobOfferId' => $job->getId()]
        ]);

        // Admin views job detail
        $client->request('GET', '/api/v1/job-offers/' . $job->getId(), [
            'headers' => ['Authorization' => 'Bearer ' . $adminToken]
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['applicationCount' => 1]);
    }
}
