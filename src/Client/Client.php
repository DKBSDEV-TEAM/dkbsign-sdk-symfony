<?php

namespace App\Client;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;

class Client{
    public function __construct(
        private HttpClientInterface $httpClient,
        #[Autowire('%dkbsign.api_url%')] private string $apiUrl,
        #[Autowire('%dkbsign.api_token%')] private string $apiToken,
    ) {
    }
    public function requete(string $methode, string $chemin, array $options = []): array
    {
    $options['headers']['Authorization'] = 'Bearer ' . $this->apiToken;
    $options['headers']['Accept'] = 'application/json';

    $reponse = $this->httpClient->request($methode, $this->apiUrl . $chemin, $options);

    return [
        'statusCode' => $reponse->getStatusCode(),
        'body' => $reponse->toArray(false),
    ];
    }
    public function envoyerFormulaire(string $chemin, array $champs): array
{
    $formulaire = new FormDataPart($champs);

    return $this->requete('POST', $chemin, [
        'headers' => $formulaire->getPreparedHeaders()->toArray(),
        'body' => $formulaire->bodyToIterable(),
    ]);
}

}