<?php

declare(strict_types=1);

namespace Berlioz\Package\Hector;

use Berlioz\Core\Cache\FileCacheDriver;

/**
 * Class DefaultCacheDriver.
 */
class DefaultCacheDriver extends FileCacheDriver
{
    public const CACHE_DIRECTORY = 'hector';
}