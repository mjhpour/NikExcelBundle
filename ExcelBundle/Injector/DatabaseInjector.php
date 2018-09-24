<?php

namespace Nik\ExcelBundle\Injector;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Nik\ExcelBundle\Adaptor\Adaptor;

/**
 * DatabaseInjector.
 *
 * @author Nikmodern co <info@nikmodern.com>
 */
class DatabaseInjector implements DatabaseInjectorInterface
{
    protected $adaptor;

    /** @var EntityManagerInterface  */
    protected $entityManager;

    /** @var Connection  */
    protected $connection;

    /**
     * DatabaseInjector constructor.
     * @param Adaptor $adaptor
     * @param EntityManagerInterface $entityManager
     * @param Connection $connection
     */
    function __construct(Adaptor $adaptor, EntityManagerInterface $entityManager, Connection $connection)
    {
        $this->adaptor = $adaptor;
        $this->entityManager = $entityManager;
        $this->connection = $connection;
    }

    /**
     * {@inheritDoc}
     */
    public function injectFileToTable($obj)
    {
        $this->adaptor->injectExcelToDatabase($obj, $this->connection, $this->entityManager);
        $this->entityManager->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function fetchStreamFromTable($tableName)
    {
        return $this->adaptor->fetchStreamedResponseFromTable($tableName, $this->connection, $this->entityManager);
    }
}
