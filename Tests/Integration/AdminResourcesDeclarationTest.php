<?php

declare(strict_types=1);

namespace Option\Tests\Integration;

use Option\Api\Resource\Option as OptionResource;
use Option\Option;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Thelia\Core\Security\Resource\AdminResources;

/**
 * The "thelia.api.admin_resources" parameter is shared by every module that publishes
 * admin resources: the module adds its own to what is already declared.
 */
final class AdminResourcesDeclarationTest extends TestCase
{
    public function testTheResourceIsAddedToWhatAnotherModuleDeclared(): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('thelia.api.admin_resources', ['Other\\Resource' => AdminResources::ORDER]);

        foreach (Option::getCompilers() as $pass) {
            $pass->process($container);
        }

        self::assertSame(
            ['Other\\Resource' => AdminResources::ORDER, OptionResource::class => AdminResources::MODULE],
            $container->getParameter('thelia.api.admin_resources'),
        );
    }

    public function testTheResourceIsDeclaredWhenNoOtherModuleDeclaresAny(): void
    {
        $container = new ContainerBuilder();

        foreach (Option::getCompilers() as $pass) {
            $pass->process($container);
        }

        self::assertSame(
            [OptionResource::class => AdminResources::MODULE],
            $container->getParameter('thelia.api.admin_resources'),
        );
    }
}
