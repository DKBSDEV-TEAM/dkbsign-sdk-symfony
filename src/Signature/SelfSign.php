<?php

namespace Dkbs\DkbsignBundle\Signature;

use Dkbs\DkbsignBundle\Client\Client;
use Symfony\Component\Mime\Part\DataPart;

class SelfSign
{
    public function __construct(private Client $client)
    {
    }

    public function sendOtp(): array
    {
        return $this->client->request('POST', '/api/v4/sign/otp');
    }

    public function selfSign(
        string $pdfPath,
        string $imagePath,
        string $signatureLevel,
        array $pages,
        int $otpCode,
    ): array {
        $params = [
            'pages' => $pages,
            'signature_level' => $signatureLevel,
            'otp_code' => (string) $otpCode,
        ];

        return $this->client->sendForm('/api/v4/sign', [
            'file' => DataPart::fromPath($pdfPath),
            'params' => json_encode($params),
            'signature_image' => DataPart::fromPath($imagePath),
        ]);
    }
}
