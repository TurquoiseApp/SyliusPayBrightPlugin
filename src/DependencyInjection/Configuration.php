<?php

declare(strict_types=1);

namespace Turquoise\SyliusPayBrightPlugin\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('turquoise_sylius_paybright_plugin');
        $rootNode = $treeBuilder->getRootNode();

        return $treeBuilder;
    }
}
