<?php

namespace Dkbs\DkbsignBundle\Tests;

use Dkbs\DkbsignBundle\Tests\App\RecordingResponseFactory;
use Dkbs\DkbsignBundle\Tests\App\TestKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class DkbsignBundleTest extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    public function testSendOtpCallsTheApiWithTheConfiguredUrlAndToken(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $resultat = $container->get('test.auto_signature')->sendOtp();

        $this->assertSame(['statusCode' => 200, 'body' => ['success' => true]], $resultat);

        $requests = $container->get(RecordingResponseFactory::class)->requests;
        $this->assertCount(1, $requests);
        $this->assertSame('POST', $requests[0]['method']);
        $this->assertSame('https://api.dkbsign.test/api/v4/sign/otp', $requests[0]['url']);
        $this->assertSame(['Authorization: Bearer test-token'], $requests[0]['options']['normalized_headers']['authorization']);
    }

    public function testSelfSignSendsAMultipartForm(): void
    {
        $pdf = tempnam(sys_get_temp_dir(), 'pdf');
        $image = tempnam(sys_get_temp_dir(), 'img');
        file_put_contents($pdf, '%PDF-1.4');
        file_put_contents($image, 'image');

        try {
            self::bootKernel();
            $container = static::getContainer();

            $resultat = $container->get('test.auto_signature')->selfSign(
                $pdf,
                $image,
                'simple',
                [['page' => 0, 'signatures' => [['x' => 20, 'y' => 30, 'width' => 60, 'height' => 25, 'type' => 'signature']]]],
                123456,
            );
        } finally {
            unlink($pdf);
            unlink($image);
        }

        $this->assertSame(200, $resultat['statusCode']);

        $request = $container->get(RecordingResponseFactory::class)->requests[0];
        $this->assertSame('https://api.dkbsign.test/api/v4/sign', $request['url']);
        $this->assertStringStartsWith('Content-Type: multipart/form-data; boundary=', $request['options']['normalized_headers']['content-type'][0]);
    }
}
