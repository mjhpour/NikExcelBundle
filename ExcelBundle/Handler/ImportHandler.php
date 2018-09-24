<?php

namespace Nik\ExcelBundle\Handler;

use Nik\ExcelBundle\Event\Event;
use Nik\ExcelBundle\Event\Events;
use Nik\ExcelBundle\Injector\DatabaseInjectorInterface;
use Nik\ExcelBundle\Mapping\PropertyMapping;
use Nik\ExcelBundle\Storage\StorageInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Import handler.
 *
 * @author Nikmodern co <info@nikmodern.com>
 */
class ImportHandler extends AbstractHandler
{
    /**
     * @var EventDispatcherInterface $dispatcher
     */
    protected $dispatcher;

    /**
     * Constructs a new instance of UploaderListener.
     *
     * @param StorageInterface             $storage    The storage.
     * @param DatabaseInjectorInterface    $injector   The injector.
     * @param EventDispatcherInterface     $dispatcher The event dispatcher.
     * @param Kernel $kernel
     */
    public function __construct(StorageInterface $storage, DatabaseInjectorInterface $injector, EventDispatcherInterface $dispatcher, Kernel $kernel)
    {
        parent::__construct($storage, $kernel, $injector);

        $this->dispatcher = $dispatcher;
    }

    /**
     * Checks for file to upload.
     *
     * @param UploadedFile $obj       The object.
     */
    public function handle($obj)
    {
        $mapping = new PropertyMapping($this->kernel, $obj);
        /*$date = new \DateTime('now');
        $finalName = $tableName . '-' . $date->format('Y-m-d_H-i-s'). '-' .$obj->getClientOriginalName();*/
        $finalName = 'tempTable.xls';

        $mapping->setFileName($finalName);

        $this->dispatch(Events::PRE_UPLOAD, new Event($obj, $mapping));

        $file = $this->storage->upload($obj, $mapping);
        $this->injector->injectFileToTable($file);

        $this->dispatch(Events::POST_UPLOAD, new Event($obj, $mapping));
    }

    protected function dispatch($eventName, Event $event)
    {
        $this->dispatcher->dispatch($eventName, $event);
    }
}
