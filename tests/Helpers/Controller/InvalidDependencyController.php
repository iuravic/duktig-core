<?php
namespace Duktig\Test\Helpers\Controller;

use Duktig\Core\Controller\BaseController;

class InvalidDependencyController extends BaseController
{
    protected $service;
    
    public function __construct(TypeDoesntExist $service)
    {
    }
}