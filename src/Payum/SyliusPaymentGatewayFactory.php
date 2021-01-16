<?php

declare(strict_types=1);

namespace Turquoise\SyliusPayBrightPlugin\Payum;

use Turquoise\SyliusPayBrightPlugin\Payum\Action\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

final class SyliusPaymentGatewayFactory extends GatewayFactory
{
    protected function populateConfig(ArrayObject $config): void
    {
        $config->defaults([
            'payum.factory_name' => 'paybright',
            'payum.factory_title' => 'PayBright',
            'payum.action.status' => new StatusAction(),
        ]);

        $config['payum.api'] = function (ArrayObject $config) {
            return new SyliusApi($config['api_key'], $config['api_token']);
        };
    }
}
