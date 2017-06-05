<?php
namespace Duktig\Core\DI;

use Duktig\Core\DI\ContainerInterface;
use Duktig\Core\Config\ConfigInterface;

class ContainerFactory
{
    private $container;
    
    /**
     * Instantiates the container and registers the services.
     * 
     * @param array $configArr
     * @return ContainerInterface
     */
    public function make(array $configArr) : ContainerInterface
    {
        $this->container = $this->initObjectWithCheck(
            $configArr['serviceContainer'],
            ContainerInterface::class,
            $this->makeConstructParams($configArr['serviceContainer'])
        );

        $this->registerServices($configArr['services']);
        $this->setConfigToConfiguratorService($configArr);
        
        return $this->container;
    }
    
    /**
     * Returns instantiated constructor params for a class.
     * 
     * @param string $class
     * @return array Array of instantiated constructor params
     */
    private function makeConstructParams($class)
    {
        $params = [];
        $reflectionClass = new \ReflectionClass($class);
        if ($reflectionClass->getConstructor()) {
            foreach ($reflectionClass->getConstructor()->getParameters() as $param) {
                $type = $param->getClass()->name;
                $params[] = new $type;
            }
        }
        return $params;
    }
    
    /**
     * Runs the container through the configurator.
     * 
     * @param array $serviceClosureArr
     * @throws \Exception Throws if the configurator is not a closure which takes
     *      the container as its parameter and returns it
     */
    public function registerServices(array $serviceClosureArr) : void
    {
        foreach ($serviceClosureArr as $closure) {
            $reflection = new \ReflectionFunction($closure);
            $arguments  = $reflection->getParameters();
            if (!$closure instanceof \Closure || count($arguments) !== 1) {
                throw new \Exception(
                    "Configuration 'services' definition must be a closure"
                    ." which takes the container as its parameter and also"
                    ." returns it after configuring it."
                );
            }
            $this->container = $closure($this->container);
        }
    }
    
    /**
     * Takes the configuration array and sets it to the configurator service.
     * 
     * @param array $configArr
     * @return void
     */
    private function setConfigToConfiguratorService(array $configArr) : void
    {
        $config = $this->container->get(ConfigInterface::class);
        $config->setConfig($configArr);
    }
    
    /**
     * Safely instantiates a class object with a type check and optional
     * constructor parameters.
     *
     * @param string $class Fully qualified class name
     * @param string $expected Fully qualified class/interface name, instanceof
     * @param array $params [optional] Constructor params
     * @throws \InvalidArgumentException
     * @return mixed Instantiated object
     */
    private function initObjectWithCheck(string $class, string $expected, array $params = [])
    {
        $reflectionClass = new \ReflectionClass($class);
        $instance = $reflectionClass->newInstanceArgs($params);
        if (!$instance instanceof $expected) {
            throw new \InvalidArgumentException(
                "Provided service class missmatch: given $class, expected $expected."
            );
        }
        return $instance;
    }
}