<?php

namespace App\Controller;

use App\Signature\AutoSignature;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

class TestController
{
    public function __construct(private AutoSignature $autoSignature)
    {
    }

    #[Route('/test-otp', name: 'app_test_otp')]
    public function otp(): JsonResponse
    {
        $resultat = $this->autoSignature->sendOtp();

        return new JsonResponse($resultat);
    }
    #[Route('/test-sign', name: 'app_test_sign')]
    public function sign(Request $request): JsonResponse
    {
    $resultat = $this->autoSignature->selfSign(
        'C:\laragon\www\test\CV_DADI_SYLVIA_LABOTHEE_photo_remplacee.pdf',
        'C:\laragon\www\test\image.jpeg',
        'simple',
        [[
            'page' => 0,
            'signatures' => [
                ['x' => 20, 'y' => 30, 'width' => 60, 'height' => 25, 'type' => 'signature'],
            ],
        ]],
        (int) $request->query->get('code'),
    );

    return new JsonResponse($resultat);
    }
}