<?php

namespace Dkbs\DkbsignBundle\Signature;


use Dkbs\DkbsignBundle\Client\Client;
use Symfony\Component\Mime\Part\DataPart;

class Enveloppe
{
    public function __construct(private Client $client)
    {
    }

    public function creer(string $cheminPdf, array $payload): array
    {
        return $this->client->envoyerFormulaire('/api/v4/envelopes', [
            'documents[]' => DataPart::fromPath($cheminPdf),
            'payload' => json_encode($payload),
        ]);
    }
    public function sendInvitation(string $emailDestinataire, string $sujet, string $lienSignature): array
    {
    $lien = htmlspecialchars($lienSignature);
    $corps = '<p>Bonjour,</p>'
        . '<p>Un document vous attend pour signature.</p>'
        . '<p><a href="' . $lien . '">Cliquer ici pour signer</a></p>';

    return $this->client->requete('POST', '/api/notifications/email', [
        'json' => [
        'email' => $emailDestinataire,
        'subject' => $sujet,
        'body' => $corps,
],
    ]);
    }
}