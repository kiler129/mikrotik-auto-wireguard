<?php
declare(strict_types=1);

namespace NoFlash\ROSAutoWireGuard;

use Symfony\Bridge\ProxyManager\LazyProxy\Instantiator\RuntimeInstantiator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class ContainerProvider
{
    private static ContainerInterface $container;

    private function __construct()
    {
        //noop
    }

    public static function getAppContainer(): ContainerInterface
    {
        if (isset(self::$container)) {
            return self::$container;
        }

        self::$container = new ContainerBuilder();
        self::$container->setProxyInstantiator(new RuntimeInstantiator());
        $loader = new PhpFileLoader(self::$container, new FileLocator(__DIR__ . '/../config'));
        $loader->load('services.php');
        self::$container->compile();

        return self::$container;
    }
}
