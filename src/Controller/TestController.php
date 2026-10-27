<?php

namespace App\Controller;

use Dkbs\DkbsignBundle\Signature\Envelope;
use Dkbs\DkbsignBundle\Signature\SelfSign;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class TestController
{
    public function __construct(
        private SelfSign $selfSign,
        private Envelope $envelope,
    ) {
    }

    #[Route('/test-otp', name: 'app_test_otp')]
    public function otp(): JsonResponse
    {
        $result = $this->selfSign->sendOtp();

        return new JsonResponse($result);
    }

    #[Route('/test-sign', name: 'app_test_sign')]
    public function sign(Request $request): JsonResponse
    {
        $result = $this->selfSign->selfSign(
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

        return new JsonResponse($result);
    }

    #[Route('/test-envelope', name: 'app_test_envelope')]
    public function envelope(): JsonResponse
    {
        $result = $this->envelope->create('C:\laragon\www\test\CV_DADI_SYLVIA_LABOTHEE_photo_remplacee.pdf', [
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

        $signer = $result['body']['signers'][0];

        $invitation = $this->envelope->sendInvitation(
            $signer['email'],
            'Document en attente de signature',
            $signer['signing_url'],
        );

        return new JsonResponse(['envelope' => $result, 'invitation' => $invitation]);
    }
}
