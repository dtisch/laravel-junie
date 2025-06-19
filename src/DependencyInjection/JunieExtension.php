<?php

namespace Dcblogdev\Junie\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

class JunieExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration;
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('junie.documents', $config['documents']);
        $container->setParameter('junie.output_path', $config['output_path']);
    }
}
