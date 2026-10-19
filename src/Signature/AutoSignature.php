<?php

namespace Dkbs\DkbsignBundle\Signature;

use Dkbs\DkbsignBundle\Client\Client;
use Symfony\Component\Mime\Part\DataPart;

class AutoSignature
{
    public function __construct(private Client $client)
    {
    }

    public function sendOtp(): array
    {
        return $this->client->requete('POST', '/api/v4/sign/otp');
    }

    public function selfSign(
        string $cheminPdf,
        string $cheminImage,
        string $niveau,
        array $pages,
        int $codeOtp,
    ): array {
        $params = [
            'pages' => $pages,
            'signature_level' => $niveau,
            'otp_code' => (string) $codeOtp,
        ];

        return $this->client->envoyerFormulaire('/api/v4/sign', [
            'file' => DataPart::fromPath($cheminPdf),
            'params' => json_encode($params),
            'signature_image' => DataPart::fromPath($cheminImage),
        ]);
    }
}
