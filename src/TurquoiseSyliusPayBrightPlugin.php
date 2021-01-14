<?php

declare(strict_types=1);

namespace Turquoise\SyliusPayBrightPlugin;

use Sylius\Bundle\CoreBundle\Application\SyliusPluginTrait;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class TurquoiseSyliusPayBrightPlugin extends Bundle
{
    use SyliusPluginTrait;
}
