<?php

namespace NewsBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use NewsBundle\DependencyInjection\Compiler\RelatedServicesPass;

class NewsBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new RelatedServicesPass());
    }
}
