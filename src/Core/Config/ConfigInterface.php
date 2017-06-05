<?php
namespace Duktig\Core\Config;

interface ConfigInterface
{
    /**
     * Sets the configuration array.
     *  
     * @param array $config
     */
    public function setConfig(array $config) : void;
    
    /**
     * Gets the configuration param from config.
     * 
     * @param string $param
     * @return mixed
     */
    public function getParam(string $param);
}