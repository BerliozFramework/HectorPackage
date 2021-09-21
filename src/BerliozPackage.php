<?php
/*
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

use Berlioz\Config\Adapter\JsonAdapter;
use Berlioz\Config\ConfigInterface;
use Berlioz\Config\Exception\ConfigException;
use Berlioz\Core\Core;
use Berlioz\Core\Package\AbstractPackage;
use Berlioz\Package\Hector\Container\ServiceProvider;
use Berlioz\Package\Hector\Debug\HectorSection;
use Berlioz\Package\Hector\Event\EventSubscriber;
use Berlioz\ServiceContainer\Container;
use Hector\Orm\Orm;

/**
 * Class BerliozPackage.
 */
class BerliozPackage extends AbstractPackage
{
    /**
     * @inheritDoc
     */
    public static function config(): ?ConfigInterface
    {
        return new JsonAdapter(__DIR__ . '/../resources/config.default.json5', true);
    }

    /**
     * @inheritDoc
     */
    public static function register(Container $container): void
    {
        $container->addProvider(new ServiceProvider());
    }

    /**
     * @inheritDoc
     * @throws ConfigException
     */
    public static function boot(Core $core): void
    {
        /** @var Orm $orm */
        $orm = $core->getContainer()->get(Orm::class);

        if (true === $core->getDebug()->isEnabled()) {
            $loggers = iterator_to_array($orm->getConnections()->getLoggers(), false);
            $core->getDebug()->addSection(new HectorSection(...$loggers));
        }

        // Dynamic events
        if ($core->getConfig()->get('hector.dynamic_events', true)) {
            $core->getEventDispatcher()->addSubscriber(new EventSubscriber($core->getContainer()));
        }
    }
}
