<?php
namespace Duktig\Core\Config;

use Duktig\Core\Config\ConfigInterface;

class Config implements ConfigInterface
{
    private $config;
    
    /**
     * {@inheritDoc}
     * @see \Duktig\Core\Config\ConfigInterface::setConfig()
     */
    public function setConfig(array $config) : void
    {
        $this->config = $config;
    }
    
    /**
     * {@inheritDoc}
     * @see \Duktig\Core\Config\ConfigInterface::getParam()
     */
    public function getParam(string $param)
    {
        return $this->config[$param] ?? null;
    }
}