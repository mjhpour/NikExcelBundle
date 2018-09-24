<?php

namespace Nik\ExcelBundle\Storage;

use Nik\ExcelBundle\Mapping\PropertyMapping;

/**
 * StorageInterface.
 *
 * @author Nikmodern co <info@nikmodern.com>
 */
interface StorageInterface
{
    /**
     * Uploads the file in the uploadable field of the specified object
     * according to the property configuration.
     *
     * @param object          $obj     The object.
     * @param PropertyMapping $mapping The mapping representing the field to upload.
     */
    public function upload($obj, PropertyMapping $mapping);
}
