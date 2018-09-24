<?php

namespace Nik\ExcelBundle\Handler;

use Nik\ExcelBundle\Injector\DatabaseInjectorInterface;
use Nik\ExcelBundle\Storage\StorageInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * @author Nikmodern co <info@nikmodern.com>
 */
abstract class AbstractHandler
{
    /**
     * @var DatabaseInjectorInterface $injector
     */
    protected $injector;

    /**
     * @var StorageInterface $storage
     */
    protected $storage;

    /**
     * @var Kernel
     */
    protected $kernel;

    /**
     * @param StorageInterface       $storage The storage.
     * @param Kernel    $kernel   The kernel.
     * @param DatabaseInjectorInterface    $injector   The injector.
     */
    public function __construct(StorageInterface $storage, Kernel $kernel, DatabaseInjectorInterface $injector)
    {
        $this->storage = $storage;
        $this->kernel = $kernel;
        $this->injector = $injector;
    }
}
