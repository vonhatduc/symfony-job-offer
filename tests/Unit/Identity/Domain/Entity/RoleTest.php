<?php

declare(strict_types=1);

namespace App\Tests\Unit\Identity\Domain\Entity;

use App\Identity\Domain\Entity\Role;
use App\Identity\Domain\Entity\Permission;
use PHPUnit\Framework\TestCase;

/**
 * TDD Tests for Role Entity
 */
class RoleTest extends TestCase
{
    /**
     * @test
     */
    public function it_creates_role_with_name(): void
    {
        $role = new Role('admin', 'Administrator role');
        
        $this->assertEquals('ADMIN', $role->getName()); // Should uppercase
        $this->assertEquals('Administrator role', $role->getDescription());
    }

    /**
     * @test
     */
    public function it_converts_name_to_uppercase(): void
    {
        $role = new Role('member');
        
        $this->assertEquals('MEMBER', $role->getName());
    }

    /**
     * @test
     */
    public function it_starts_with_empty_permissions(): void
    {
        $role = new Role('admin');
        
        $this->assertCount(0, $role->getPermissions());
    }

    /**
     * @test
     */
    public function it_can_add_permission(): void
    {
        $role = new Role('admin');
        $permission = new Permission('user.create');
        
        $role->addPermission($permission);
        
        $this->assertCount(1, $role->getPermissions());
        $this->assertTrue($role->getPermissions()->contains($permission));
    }

    /**
     * @test
     */
    public function it_does_not_add_duplicate_permission(): void
    {
        $role = new Role('admin');
        $permission = new Permission('user.create');
        
        $role->addPermission($permission);
        $role->addPermission($permission); // Add again
        
        $this->assertCount(1, $role->getPermissions());
    }

    /**
     * @test
     */
    public function it_can_remove_permission(): void
    {
        $role = new Role('admin');
        $permission = new Permission('user.create');
        
        $role->addPermission($permission);
        $role->removePermission($permission);
        
        $this->assertCount(0, $role->getPermissions());
    }

    /**
     * @test
     */
    public function it_can_check_if_has_permission(): void
    {
        $role = new Role('admin');
        $permission = new Permission('user.create');
        
        $role->addPermission($permission);
        
        $this->assertTrue($role->hasPermission('user.create'));
        $this->assertFalse($role->hasPermission('user.delete'));
    }

    /**
     * @test
     */
    public function it_can_update_description(): void
    {
        $role = new Role('admin');
        
        $role->setDescription('Updated description');
        
        $this->assertEquals('Updated description', $role->getDescription());
    }
}
