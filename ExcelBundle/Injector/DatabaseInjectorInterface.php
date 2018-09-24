<?php

namespace Nik\ExcelBundle\Injector;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

/**
 * FileInjectorInterface.
 *
 * @author Nikmodern co <info@nikmodern.com>
 */
interface DatabaseInjectorInterface
{
    /**
     * Injects the uploadable field of the specified object and mapping.
     *
     * The field is populated with a \Symfony\Component\HttpFoundation\File\File
     * instance.
     *
     * Table find with the name of excel sheet.
     *
     * @param UploadedFile $obj The object.
     */
    public function injectFileToTable($obj);

    /**
     * fetch the excel streamed response from table.
     *
     * @param string $tableName The table name.
     * @return Response
     */
    public function fetchStreamFromTable($tableName);
}
