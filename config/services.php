<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use BaconQrCode\Renderer\PlainTextRenderer;
use BaconQrCode\Writer;
use NoFlash\ROSAutoWireGuard\Command\GenerateCommand;
use NoFlash\ROSAutoWireGuard\UseCase\AddNewPeers;
use NoFlash\ROSAutoWireGuard\UseCase\BuildClientConfiguration;
use NoFlash\ROSAutoWireGuard\WireGuard\Configuration\PeerProjector;
use NoFlash\ROSAutoWireGuard\WireGuard\QrGenerator;

return function(ContainerConfigurator $configurator) {
    $services = $configurator
        ->services()
            ->defaults()
                ->autowire()
                ->autoconfigure()
    ;

    $services->load('NoFlash\\ROSAutoWireGuard\\', '../src/*')
             ->exclude('../src/{DependencyInjection}');

    $services->set('app.qr_text_renderer')
             ->class(PlainTextRenderer::class);
    $services->set('app.qr_text_writer')
             ->class(Writer::class)
             ->arg(0, service('app.qr_text_renderer'));
    $services->set(QrGenerator::class)
             ->arg('$qrWriter', service('app.qr_text_writer'))
            ->public()
    ;

    $services->set(GenerateCommand::class)->public();
    $services->set(AddNewPeers::class)->public();
    $services->set(BuildClientConfiguration::class)->share(false)->public();
    $services->set(PeerProjector::class)->share(false);
};
