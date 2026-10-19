<?php

namespace Dkbs\DkbsignBundle\Tests\App;

use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class RecordingResponseFactory
{
    /** @var list<array{method: string, url: string, options: array}> */
    public array $requests = [];

    public function __invoke(string $method, string $url, array $options): ResponseInterface
    {
        $this->requests[] = ['method' => $method, 'url' => $url, 'options' => $options];

        return new MockResponse('{"success":true}', ['http_code' => 200]);
    }
}
