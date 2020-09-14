<?php
declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use NoFlash\ROSAutoWireGuard\ContainerProvider;
use Symfony\Component\Console\Application;

$container = ContainerProvider::getAppContainer();
return new Application();

