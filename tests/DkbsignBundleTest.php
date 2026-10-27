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

        $result = $container->get('test.self_sign')->sendOtp();

        $this->assertSame(['statusCode' => 200, 'body' => ['success' => true]], $result);

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

            $result = $container->get('test.self_sign')->selfSign(
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

        $this->assertSame(200, $result['statusCode']);

        $request = $container->get(RecordingResponseFactory::class)->requests[0];
        $this->assertSame('https://api.dkbsign.test/api/v4/sign', $request['url']);
        $this->assertStringStartsWith('Content-Type: multipart/form-data; boundary=', $request['options']['normalized_headers']['content-type'][0]);
    }

    public function testCreateEnvelopeSendsTheDocumentAndPayload(): void
    {
        $pdf = tempnam(sys_get_temp_dir(), 'pdf');
        file_put_contents($pdf, '%PDF-1.4');

        try {
            self::bootKernel();
            $container = static::getContainer();

            $result = $container->get('test.envelope')->create($pdf, ['title' => 'Test enveloppe']);
        } finally {
            unlink($pdf);
        }

        $this->assertSame(200, $result['statusCode']);

        $request = $container->get(RecordingResponseFactory::class)->requests[0];
        $this->assertSame('POST', $request['method']);
        $this->assertSame('https://api.dkbsign.test/api/v4/envelopes', $request['url']);
        $this->assertSame(['Authorization: Bearer test-token'], $request['options']['normalized_headers']['authorization']);
        $this->assertStringStartsWith('Content-Type: multipart/form-data; boundary=', $request['options']['normalized_headers']['content-type'][0]);
    }

    public function testSendInvitationSendsAnEmailWithTheEscapedSigningLink(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $result = $container->get('test.envelope')->sendInvitation(
            'signataire@example.com',
            'Document en attente de signature',
            'https://sign.example.com/s?id=1&token=abc',
        );

        $this->assertSame(200, $result['statusCode']);

        $request = $container->get(RecordingResponseFactory::class)->requests[0];
        $this->assertSame('POST', $request['method']);
        $this->assertSame('https://api.dkbsign.test/api/notifications/email', $request['url']);

        $body = json_decode($request['options']['body'], true);
        $this->assertSame('signataire@example.com', $body['email']);
        $this->assertSame('Document en attente de signature', $body['subject']);
        $this->assertStringContainsString('href="https://sign.example.com/s?id=1&amp;token=abc"', $body['body']);
    }
}
