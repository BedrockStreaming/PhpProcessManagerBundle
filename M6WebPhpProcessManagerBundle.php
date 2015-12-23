<?php
namespace M6Web\Bundle\PhpProcessManagerBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class PhpProcessManagerBundle
 */
class M6WebPhpProcessManagerBundle extends Bundle
{
    /**
     * {@inheritDoc}
     * @see \Symfony\Component\HttpKernel\Bundle\Bundle::getContainerExtension()
     */
    public function getContainerExtension()
    {
        return new DependencyInjection\M6WebPhpProcessManagerExtension();
    }
}