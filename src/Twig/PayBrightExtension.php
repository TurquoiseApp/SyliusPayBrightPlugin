<?php

declare(strict_types=1);

namespace Turquoise\SyliusPayBrightPlugin\Twig;

use Payum\Core\Model\GatewayConfigInterface;
use Sylius\Component\Core\Model\PaymentMethodInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class PayPalExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('turquoise_is_paybright_enabled', [$this, 'isPayBrightEnabled']),
            new TwigFunction('turquoise_get_paybright_api_key', [$this, 'getPayBrightApiKey']),
        ];
    }

    public function isPayBrightEnabled(iterable $paymentMethods): bool
    {
        /** @var PaymentMethodInterface $paymentMethod */
        foreach ($paymentMethods as $paymentMethod) {
            /** @var GatewayConfigInterface $gatewayConfig */
            $gatewayConfig = $paymentMethod->getGatewayConfig();
            if ($gatewayConfig->getFactoryName() === 'paybright') {
                return true;
            }
        }

        return false;
    }

    // TODO: Get js api url

    public function getPayBrightApiKey() {
        // TODO: remove that. we will load payment method from template , like PayPal plugin
    }
}
