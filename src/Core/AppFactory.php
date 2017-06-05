<?php
namespace Duktig\Core;

use Duktig\Core\DI\ContainerFactory;
use Duktig\Core\DI\ContainerInterface;

class AppFactory
{
    /**
     * Resolves and returns an instance of $class.
     * 
     * @param string $configAppFile
     * @param string $class
     * @return mixed Resolved instance
     */
    public function make(string $configAppFile, string $class)
    {
        $configArr = $this->getConfigArr($configAppFile);
        $container = $this->getContainer($configArr);
        $instance = $container->get($class);
        return $instance;
    }
    
    /**
     * Gets a configuration array.
     * 
     * @param string $configAppFile
     * @return array
     */
    protected function getConfigArr(string $configAppFile) : array
    {
        $configCoreFile = __DIR__.'/../Config/config.php';
        $configCore = $this->requireFile($configCoreFile);
        $configApp = $this->requireFile($configAppFile);
        
        $services = [];
        $skipCoreServices = $configApp['skipCoreServices'] ?? false;
        if (isset($configCore['services']) && !$skipCoreServices) {
            $services[] = $configCore['services'];
        }
        if (isset($configApp['services'])) {
            $services[] = $configApp['services'];
        }
        $configArr = array_replace_recursive($configCore, $configApp);
        $configArr['services'] = $services;
        
        return $configArr;
    }
    
    /**
     * @param string $file
     * @throws \InvalidArgumentException
     * @return mixed
     */
    protected function requireFile(string $file)
    {
        if (!file_exists($file)) {
            throw new \InvalidArgumentException('Can not open file for reading '.$file);
        }
        return require $file;
    }
    
    /**
     * Returns the configured container.
     * 
     * @param array $configArr
     * @return ContainerInterface
     */
    protected function getContainer(array $configArr) : ContainerInterface
    {
        return (new ContainerFactory())->make($configArr);
    }
}