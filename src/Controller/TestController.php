<?php

namespace App\Controller;

use Dkbs\DkbsignBundle\Signature\AutoSignature;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Dkbs\DkbsignBundle\Signature\Enveloppe;

class TestController
{
    public function __construct(
        private AutoSignature $autoSignature,
        private Enveloppe $enveloppe,
    ) {
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
    #[Route('/test-enveloppe', name: 'app_test_enveloppe')]
    public function enveloppe(): JsonResponse
    {
    $resultat = $this->enveloppe->creer('C:\laragon\www\test\CV_DADI_SYLVIA_LABOTHEE_photo_remplacee.pdf', [
        'title' => 'Test enveloppe',
        'message' => 'Merci de signer',
        'signing_order' => 'ordered',
        'signature_level' => 'simple',
        'signers' => [[
            'name' => 'Nom',
            'first_name' => 'Prenom',
            'email' => 'touresara81@gmail.com',
            'phone' => '+2250717466692',
            'priority' => 1,
            'positions' => [
                '0' => [
                    ['page' => 0, 'x' => 20, 'y' => 30, 'width' => 60, 'height' => 25, 'type' => 'signature'],
                ],
            ],
        ]],
    ]);

    $signataire = $resultat['body']['signers'][0];

    $invitation = $this->enveloppe->sendInvitation(
    $signataire['email'],
    'Document en attente de signature',
    $signataire['signing_url'],
    );

    return new JsonResponse(['enveloppe' => $resultat, 'invitation' => $invitation]);
}
}