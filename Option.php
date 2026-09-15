<?php

declare(strict_types=1);

/*
 * This file is part of the Thelia package.
 * http://www.thelia.net
 *
 * (c) OpenStudio <info@thelia.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Option;

use Propel\Runtime\Connection\ConnectionInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;
use Symfony\Component\Finder\Finder;
use Thelia\Core\Install\Database;
use Thelia\Module\BaseModule;

class Option extends BaseModule
{
    /** @var string */
    public const DOMAIN_NAME = 'option';

    // Technical title of the hidden category the module stores its option-products in.
    // It is an identifier, not a label: it must never be translated, or the row the
    // module writes stops being the row it looks up.
    public const OPTION_CATEGORY_TITLE = 'option_category_thelia';

    public const string PRODUCT_OPTION_TAB_ID = 'product_option_tab';

    public const string CATEGORY_OPTION_TAB_ID = 'category_option_tab';

    /** @var string */
    public const OPTION_CATEGORY_ID = 'option_category_id_thelia';

    public function postActivation(?ConnectionInterface $con = null): void
    {
        if (!$this->getConfigValue('is_initialized')) {
            $database = new Database($con);

            $database->insertSql(null, [__DIR__.'/Config/TheliaMain.sql']);

            $this->setConfigValue('is_initialized', '1');
        }
    }

    public function update($currentVersion, $newVersion, ?ConnectionInterface $con = null): void
    {
        $finder = (new Finder())
            ->files()
            ->name('#.*?\.sql#')
            ->sortByName()
            ->in(__DIR__.DS.'Config'.DS.'update');

        $database = new Database($con);

        /** @var \Symfony\Component\Finder\SplFileInfo $updateSQLFile */
        foreach ($finder as $updateSQLFile) {
            if (version_compare($currentVersion, str_replace('.sql', '', $updateSQLFile->getFilename()), '<')) {
                $database->insertSql(
                    null,
                    [
                        $updateSQLFile->getPathname(),
                    ]
                );
            }
        }
    }

    /**
     * Defines how services are loaded in your modules.
     */
    public static function configureServices(ServicesConfigurator $servicesConfigurator): void
    {
        $servicesConfigurator->load(self::getModuleCode().'\\', __DIR__)
            ->exclude([__DIR__.'/I18n/*'])
            ->autowire(true)
            ->autoconfigure(true);
    }
}
