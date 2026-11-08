<?php

declare(strict_types=1);

namespace Option\DependencyInjection;

use Option\Api\Resource\Option as OptionResource;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Thelia\Core\Security\Resource\AdminResources;

/**
 * Maps the admin option resource to the permission the module back-office controllers
 * already check for the same data. Without an entry in `thelia.api.admin_resources`, the
 * admin API refuses the endpoint to every administrator but a superadministrator.
 *
 * Merged rather than set: the parameter is one array shared by every module publishing
 * admin resources, and setting it would erase the others.
 */
final readonly class AdminApiResourcesPass implements CompilerPassInterface
{
    private const PARAMETER = 'thelia.api.admin_resources';

    public function process(ContainerBuilder $container): void
    {
        $declared = $container->hasParameter(self::PARAMETER) ? (array) $container->getParameter(self::PARAMETER) : [];

        $container->setParameter(self::PARAMETER, [
            ...$declared,
            OptionResource::class => AdminResources::MODULE,
        ]);
    }
}
