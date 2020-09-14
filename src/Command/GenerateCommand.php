<?php
declare(strict_types=1);

namespace NoFlash\ROSAutoWireGuard\Command;

use NoFlash\ROSAutoWireGuard\Factory\ROSClientFactory;
use NoFlash\ROSAutoWireGuard\RouterOS\ClientProvider;
use NoFlash\ROSAutoWireGuard\Struct\Peer;
use NoFlash\ROSAutoWireGuard\UseCase\AddNewPeers;
use NoFlash\ROSAutoWireGuard\UseCase\BuildClientConfiguration;
use NoFlash\ROSAutoWireGuard\WireGuard\QrGenerator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

class GenerateCommand extends Command
{
    /** {@inheritdoc} */
    protected static $defaultName = 'generate';

    private ClientProvider $clientProvider;
    private BuildClientConfiguration $configBuilder;
    private QrGenerator $qrGenerator;
    private AddNewPeers $peersUC;

    public function __construct(
        ClientProvider $clientProvider,
        BuildClientConfiguration $configBuilder,
        QrGenerator $qrGenerator,
        AddNewPeers $peersUC
    ) {
        $this->clientProvider = $clientProvider;
        $this->configBuilder = $configBuilder;
        $this->qrGenerator = $qrGenerator;
        $this->peersUC = $peersUC;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Generates VPN peers configurations')
             ->addOption(
                 'template',
                 't',
                 InputOption::VALUE_REQUIRED,
                 'Output template',
                 \realpath(__DIR__ . '/../../resources/template/advanced.twig')
             )
             ->addOption('num', '#', InputOption::VALUE_REQUIRED, 'Number of peers to create', 1)
             ->addOption(
                 'user-list',
                 'l',
                 InputOption::VALUE_REQUIRED,
                 'File with list of users. Cannot combine with --num'
             )
             ->addOption(
                 'user',
                 'u',
                 InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                 'Add username(s). Cannot combine with --num'
             )
             ->addOption('psk', 's', InputOption::VALUE_NONE, 'Whether to use additional PSKs for peers')
             ->addOption(
                 'allowed',
                 'a',
                 InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                 'Network(s) which should be router through VPN',
                 ['0.0.0.0/0', '::/0']
             )
             ->addOption('keep-alive', 'k', InputOption::VALUE_REQUIRED, 'Keep-alive in seconds')
             ->addOption('interface', 'i', InputOption::VALUE_REQUIRED, 'WireGuard interface name', 'wireguard1')
             ->addOption(
                 'pool',
                 'o',
                 InputOption::VALUE_REQUIRED,
                 'IP > Pool to get addresses from. If not set it will use addresses on the interface'
             )
             ->addOption(
                 'vpn-host',
                 null,
                 InputOption::VALUE_REQUIRED,
                 'Externally-accessible VPN gateway host/IP (default: router-host)'
             )
             ->addOption(
                 'vpn-port',
                 null,
                 InputOption::VALUE_REQUIRED,
                 'Externally-accessible VPN gateway port (default: read from interface)'
             )
             ->addOption(
                 'public-key',
                 null,
                 InputOption::VALUE_REQUIRED,
                 'Public key to use (default: read from interface)'
             )
             ->addArgument('router-host', InputArgument::REQUIRED, 'Host/IP of a MikroTik router')
             ->addArgument('router-username', InputArgument::REQUIRED, 'Username for a MikroTik router')
             ->addArgument('router-password', InputArgument::REQUIRED, 'Password for a MikroTik router')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $users = $this->createUsersList($input, $output);
        if ($users === null) {
            return self::FAILURE;
        }

        $this->configureClient($input);
        $this->populateServer($input);

        $peers = $this->generatePeers($input, $users);

        $output->write($this->renderTemplate($input->getOption('template'), $peers));

        return self::SUCCESS;
    }

    private function configureClient(InputInterface $input): void
    {
        $client = ROSClientFactory::createClient(
            $input->getArgument('router-host'),
            $input->getArgument('router-username'),
            $input->getArgument('router-password')
        );

        $this->clientProvider->setClient($client);
    }

    /**
     * @return array<string>|null
     */
    private function createUsersList(InputInterface $input, OutputInterface $output): ?array
    {
        $hasNum = $input->hasParameterOption(['--num', '-#']);
        $hasUsers = $input->hasParameterOption(['--user', '-u']);
        $hasUserList = $input->hasParameterOption(['--user-list', '-l']);

        if ($hasNum && ($hasUsers || $hasUserList)) {
            $output->writeln('<error>Cannot combine --num with either --user or --user-list</error>');

            return null;
        }

        $users = [];
        if ($hasUserList) {
            $users = \file($input->getOption('user-list'), \FILE_IGNORE_NEW_LINES|\FILE_SKIP_EMPTY_LINES);
            if ($users === false) {
                $output->writeln('<error>Failed to read --user-list file</error>');

                return null;
            }
        }

        if ($hasUsers) {
            return \array_merge($users, $input->getOption('user'));
        }

        if (\count($users) !== 0) {
            return $users;
        }

        //Stupid... but for it will not be 30,000 elements at once but just a couple ;)
        return \array_fill(0, (int)$input->getOption('num'), null);
    }

    private function populateServer(InputInterface $input): void
    {
        $host = $input->getOption('vpn-host') ?: $input->getArgument('router-host');
        $port = (int)$input->getOption('vpn-port') ?: 443; //TODO: get from interface
        $publicKey = $input->getOption('public-key'); //TODO: get from interface
        if ($publicKey === null || $publicKey === '') {
            throw new \Exception('MT public key needed! (use --pk option)');
        }

        $this->configBuilder
            ->setServerAddress($host, $port)
            ->setServeryKey($publicKey)
        ;

        $keepAliveSeconds = (int)$input->getOption('keep-alive');
        if ($keepAliveSeconds > 0) {
            $this->configBuilder->setKeepAlive($keepAliveSeconds);
        }

        foreach ($input->getOption('allowed') as $allowed) {
            $this->configBuilder->addAllowedNetwork($allowed);
        }
    }

    /**
     * @param iterable<string> $users
     *
     * @return \SplObjectStorage<Peer, string|null>
     */
    private function generatePeers(InputInterface $input, iterable $users): \SplObjectStorage
    {
        $interface = $input->getOption('interface');
        $pool = $input->getOption('pool') ?: null;
        $usePsk = $input->getOption('psk');

        //TODO: should probably add usename in comment in ROS
        $peers = $this->peersUC->addWithConsecutiveIPs($interface, $pool, $usePsk, \count($users));
        $out = new \SplObjectStorage();
        $i = 0;
        foreach ($users as $user) {
            $out->attach($peers[$i++], $user);
        }

        return $out;
    }

    /**
     * @param Peer              $server
     * @param \SplObjectStorage<Peer, string|null> ...$clients
     *
     */
    private function renderTemplate(string $templateFile, \SplObjectStorage $clients): string
    {
        $configs = [];
        foreach ($clients as $clientPeer) {
            $config = $this->configBuilder->buildForClient($clientPeer);
            $configs[] = [
                'user' => $clients->getInfo(),
                'clientPeer' => $clientPeer,
                'qr' => $this->qrGenerator->generateConfiguration($config),
                'text' => $config->render(),
            ];
        }

        $varsStack = [
            'serverPeer' => $this->configBuilder->getServerPeer(),
            'configs' => $configs,
        ];
        $loader = new ArrayLoader(['template' => \file_get_contents($templateFile),]);
        $twig = new Environment($loader);

        return $twig->render('template', $varsStack);
    }
}
