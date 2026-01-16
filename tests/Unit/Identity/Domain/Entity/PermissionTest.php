<?php

declare(strict_types=1);

namespace App\Tests\Unit\Identity\Domain\Entity;

use App\Identity\Domain\Entity\Permission;
use PHPUnit\Framework\TestCase;

/**
 * TDD Tests for Permission Entity
 */
class PermissionTest extends TestCase
{
    /**
     * @test
     */
    public function it_creates_permission_with_name(): void
    {
        $permission = new Permission('user.create', 'Can create users');
        
        $this->assertEquals('user.create', $permission->getName());
        $this->assertEquals('Can create users', $permission->getDescription());
    }

    /**
     * @test
     */
    public function it_normalizes_name_to_lowercase(): void
    {
        $permission = new Permission('USER.CREATE');
        
        $this->assertEquals('user.create', $permission->getName());
    }

    /**
     * @test
     */
    public function it_starts_with_empty_roles(): void
    {
        $permission = new Permission('user.create');
        
        $this->assertCount(0, $permission->getRoles());
    }

    /**
     * @test
     */
    public function it_can_update_description(): void
    {
        $permission = new Permission('user.create');
        
        $permission->setDescription('New description');
        
        $this->assertEquals('New description', $permission->getDescription());
    }

    /**
     * @test
     */
    public function it_accepts_null_description(): void
    {
        $permission = new Permission('user.create');
        
        $this->assertNull($permission->getDescription());
    }
}
