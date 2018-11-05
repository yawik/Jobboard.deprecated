<?php

namespace YawikDemoJobboard;

use Core\Asset\AssetProviderInterface;

/**
 * Bootstrap class of the YAWIK Jobboard
 */
class Module implements AssetProviderInterface
{
    /**
     * indicates, that the autoload configuration for this module should be loaded.
     * @see
     *
     * @var bool
     */
    public static $isLoaded=false;

    public function getConfig()
    {
        return include __DIR__ . '/../config/module.config.php';
    }

    public function onBootstrap()
    {
        self::$isLoaded=true;
    }

    public function getPublicDir()
    {
        return realpath(__DIR__.'/../public');
    }
}
