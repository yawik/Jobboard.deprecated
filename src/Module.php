<?php

namespace Jobboard;

/**
 * Bootstrap class of the YAWIK Jobboard
 */
class Module
{
    const TEXT_DOMAIN = __NAMESPACE__;

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

    /**
     * Loads module specific autoloader configuration.
     *
     * @return array
     */
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function onBootstrap()
    {
        self::$isLoaded=true;
    }
}
