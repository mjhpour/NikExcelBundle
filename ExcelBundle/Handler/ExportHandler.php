<?php

namespace Nik\ExcelBundle\Handler;

use Symfony\Component\HttpFoundation\Response;

/**
 * Export handler.
 *
 * @author Nikmodern co <info@nikmodern.com>
 */
class ExportHandler extends AbstractHandler
{

    /**
     * handel export operation.
     *
     * @param string $tableName The name of the target table.
     * @return Response
     */
    public function handle($tableName)
    {
        return $this->injector->fetchStreamFromTable($tableName);
    }
}
