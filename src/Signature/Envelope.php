<?php

namespace Dkbs\DkbsignBundle\Signature;

use Dkbs\DkbsignBundle\Client\Client;
use Symfony\Component\Mime\Part\DataPart;

class Envelope
{
    public function __construct(private Client $client)
    {
    }

    public function create(string $pdfPath, array $payload): array
    {
        return $this->client->sendForm('/api/v4/envelopes', [
            'documents[]' => DataPart::fromPath($pdfPath),
            'payload' => json_encode($payload),
        ]);
    }

    public function sendInvitation(string $recipientEmail, string $subject, string $signingUrl): array
    {
        $link = htmlspecialchars($signingUrl);
        $body = '<p>Bonjour,</p>'
            .'<p>Un document vous attend pour signature.</p>'
            .'<p><a href="'.$link.'">Cliquer ici pour signer</a></p>';

        return $this->client->request('POST', '/api/notifications/email', [
            'json' => [
                'email' => $recipientEmail,
                'subject' => $subject,
                'body' => $body,
            ],
        ]);
    }
}
