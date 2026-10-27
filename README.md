# dkbsign-sdk-symfony

Bundle Symfony pour l'API DKBSign : envoi d'OTP et auto-signature d'un document PDF.

Compatible Symfony 7.4 et 8.x, PHP 8.2+.

## Installation

```bash
composer require dkbs/dkbsign-sdk-symfony
```

Si Symfony Flex n'a pas enregistré le bundle, ajoutez-le dans `config/bundles.php` :

```php
return [
    // ...
    Dkbs\DkbsignBundle\DkbsignBundle::class => ['all' => true],
];
```

## Configuration

Créez `config/packages/dkbsign.yaml` :

```yaml
dkbsign:
    api_url: '%env(DKBSIGN_API_URL)%'   # facultatif, https://api.dkbsigns.com par défaut
    api_token: '%env(DKBSIGN_API_TOKEN)%'
```

Puis définissez le token dans `.env.local` (jamais dans un fichier commité) :

```dotenv
DKBSIGN_API_URL=https://api.dkbsigns.com
DKBSIGN_API_TOKEN=votre-token
```

## Utilisation

Injectez `AutoSignature` dans vos services ou contrôleurs :

```php
use Dkbs\DkbsignBundle\Signature\AutoSignature;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

final class SignatureController extends AbstractController
{
    #[Route('/signature/otp', methods: ['POST'])]
    public function otp(AutoSignature $autoSignature): JsonResponse
    {
        return $this->json($autoSignature->sendOtp());
    }

    #[Route('/signature/sign', methods: ['POST'])]
    public function sign(AutoSignature $autoSignature, #[MapQueryParameter] int $code): JsonResponse
    {
        return $this->json($autoSignature->selfSign(
            '/chemin/vers/document.pdf',
            '/chemin/vers/signature.png',
            'simple',
            [[
                'page' => 0,
                'signatures' => [
                    ['x' => 20, 'y' => 30, 'width' => 60, 'height' => 25, 'type' => 'signature'],
                ],
            ]],
            $code,
        ));
    }
}
```

### Enveloppe (plusieurs signataires)

Injectez `Enveloppe` pour créer une enveloppe puis envoyer le lien de signature par e-mail :

```php
use Dkbs\DkbsignBundle\Signature\Enveloppe;

$resultat = $enveloppe->creer('/chemin/vers/document.pdf', [
    'title' => 'Contrat',
    'message' => 'Merci de signer',
    'signing_order' => 'ordered',
    'signature_level' => 'simple',
    'signers' => [[
        'name' => 'Nom',
        'first_name' => 'Prénom',
        'email' => 'signataire@example.com',
        'phone' => '+2250000000000',
        'priority' => 1,
        'positions' => [
            '0' => [
                ['page' => 0, 'x' => 20, 'y' => 30, 'width' => 60, 'height' => 25, 'type' => 'signature'],
            ],
        ],
    ]],
]);

$signataire = $resultat['body']['signers'][0];

$enveloppe->sendInvitation(
    $signataire['email'],
    'Document en attente de signature',
    $signataire['signing_url'],
);
```

Chaque méthode renvoie un tableau `['statusCode' => int, 'body' => array]`.

## Démo locale

Le dépôt contient aussi une petite application Symfony (`src/Kernel.php`, `src/Controller/TestController.php`) qui utilise le bundle :

```bash
composer install
# renseignez DKBSIGN_API_TOKEN dans .env.local
symfony serve -d
```

- `GET /test-otp` : envoie l'OTP
- `GET /test-sign?code=123456` : signe le document de test avec l'OTP reçu

## Tests

```bash
composer install
composer test
```
