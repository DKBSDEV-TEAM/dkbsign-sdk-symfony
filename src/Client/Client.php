<?php

namespace Dkbs\DkbsignBundle\Client;

use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class Client
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private string $apiUrl,
        private string $apiToken,
    ) {
    }

    public function request(string $method, string $path, array $options = []): array
    {
        $options['headers']['Authorization'] = 'Bearer '.$this->apiToken;
        $options['headers']['Accept'] = 'application/json';

        $response = $this->httpClient->request($method, rtrim($this->apiUrl, '/').$path, $options);

        return [
            'statusCode' => $response->getStatusCode(),
            'body' => $response->toArray(false),
        ];
    }

    public function sendForm(string $path, array $fields): array
    {
        $form = new FormDataPart($fields);

        return $this->request('POST', $path, [
            'headers' => $form->getPreparedHeaders()->toArray(),
            'body' => $form->bodyToIterable(),
        ]);
    }
}
