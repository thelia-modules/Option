<?php

declare(strict_types=1);

namespace Option\Tests\Integration;

use Option\Api\Resource\Option as OptionResource;
use Thelia\Api\Security\AdminApiResourcePermissions;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Test\IntegrationTestCase;

/**
 * Everything under /api/admin is refused by default: a resource the permission map does
 * not name is denied to every administrator but the superadministrator, and logged.
 */
final class AdminApiPermissionTest extends IntegrationTestCase
{
    public function testTheAdminOptionResourceIsMappedToAPermission(): void
    {
        /** @var AdminApiResourcePermissions $permissions */
        $permissions = $this->getService(AdminApiResourcePermissions::class);

        self::assertSame(
            AdminResources::MODULE,
            $permissions->resolve(OptionResource::class),
            'the code the module back-office controllers already check for the same data',
        );
    }
}
