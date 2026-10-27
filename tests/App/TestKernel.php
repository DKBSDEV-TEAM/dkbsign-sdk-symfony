<?php

namespace Dkbs\DkbsignBundle\Tests\App;

use Dkbs\DkbsignBundle\DkbsignBundle;
use Dkbs\DkbsignBundle\Signature\SelfSign;
use Dkbs\DkbsignBundle\Signature\Envelope;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;

final class TestKernel extends Kernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        yield new FrameworkBundle();
        yield new DkbsignBundle();
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir().'/dkbsign-bundle-tests/cache';
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir().'/dkbsign-bundle-tests/log';
    }

    protected function configureContainer(ContainerConfigurator $container): void
    {
        $container->extension('framework', [
            'test' => true,
            'secret' => 'test',
            'http_client' => [
                'mock_response_factory' => RecordingResponseFactory::class,
            ],
        ]);

        $container->extension('dkbsign', [
            'api_url' => 'https://api.dkbsign.test',
            'api_token' => 'test-token',
        ]);

        $services = $container->services();
        $services->set(RecordingResponseFactory::class)->public();
        $services->alias('test.self_sign', SelfSign::class)->public();
        $services->alias('test.envelope', Envelope::class)->public();
    }
}
