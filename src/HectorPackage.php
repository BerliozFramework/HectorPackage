<?php
/**
 * This file is part of Berlioz framework.
 *
 * @license   https://opensource.org/licenses/MIT MIT License
 * @copyright 2021 Ronan GIRON
 * @author    Ronan GIRON <https://github.com/ElGigi>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code, to the root.
 */

declare(strict_types=1);

namespace Berlioz\Package\Hector;

use Berlioz\Config\Exception\ConfigException;
use Berlioz\Config\ExtendedJsonConfig;
use Berlioz\Core\Core;
use Berlioz\Core\Exception\BerliozException;
use Berlioz\Core\Package\AbstractPackage;
use Berlioz\ServiceContainer\Exception\ContainerException;
use Berlioz\ServiceContainer\Service;
use Hector\Connection\Connection;
use Hector\Orm\Exception\OrmException;
use Hector\Orm\Orm;
use Hector\Orm\OrmFactory;
use Hector\Schema\Exception\SchemaException;
use Psr\SimpleCache\InvalidArgumentException;

class HectorPackage extends AbstractPackage
{
    private static ?Debug\Hector $debugSection = null;

    ///////////////
    /// PACKAGE ///
    ///////////////

    /**
     * @inheritDoc
     * @throws ConfigException
     */
    public static function config()
    {
        return new ExtendedJsonConfig(
            implode(
                DIRECTORY_SEPARATOR,
                [
                    __DIR__,
                    '..',
                    'resources',
                    'config.default.json',
                ]
            ), true
        );
    }

    /**
     * @inheritDoc
     * @throws ContainerException
     */
    public static function register(Core $core): void
    {
        $connectionService = new Service(Connection::class, 'connection');
        $connectionService->setFactory(HectorPackage::class . '::hectorConnectionFactory');
        self::addService($core, $connectionService);

        $ormService = new Service(Orm::class, 'hector');
        $ormService->setFactory(HectorPackage::class . '::hectorOrmFactory');
        self::addService($core, $ormService);
    }

    /**
     * @inheritDoc
     * @throws ConfigException
     * @throws BerliozException
     */
    public function init(Core $core = null): void
    {
        // Init ORM
        $core->getServiceContainer()->get(Orm::class);

        static::debugSection($core);
    }

    /////////////////
    /// FACTORIES ///
    /////////////////

    /**
     * Get debug section.
     *
     * @param Core $core
     *
     * @return Debug\Hector|null
     * @throws BerliozException
     * @throws ConfigException
     */
    private static function debugSection(Core $core): ?Debug\Hector
    {
        if (null === static::$debugSection) {
            if (null !== $core && $core->getConfig()->get('berlioz.debug', false)) {
                static::$debugSection = new Debug\Hector($core);
                $core->getDebug()->addSection(static::$debugSection);
            }
        }

        return static::$debugSection;
    }

    /**
     * Init hector orm package.
     *
     * @param Core $core
     *
     * @return Connection
     * @throws ConfigException
     * @throws OrmException
     */
    public static function hectorConnectionFactory(Core $core): Connection
    {
        $options = $core->getConfig()->get('hector');
        $options['log'] = $core->getConfig()->get('berlioz.debug.enable', false) == true;

        return OrmFactory::connection($options);
    }

    /**
     * Init hector orm package.
     *
     * @param Connection $connection
     * @param Core $core
     *
     * @return Orm
     * @throws ConfigException
     * @throws InvalidArgumentException
     * @throws OrmException
     * @throws SchemaException
     * @throws BerliozException
     */
    public static function hectorOrmFactory(Connection $connection, Core $core): Orm
    {
        $options = $core->getConfig()->get('hector');
        $orm = OrmFactory::orm($options, $connection, $core->getCacheManager());

        // Debug activate?
        self::debugSection($core)?->setHector($orm);

        $orm->setExternalEnvironment($core);

        return $orm;
    }
}
