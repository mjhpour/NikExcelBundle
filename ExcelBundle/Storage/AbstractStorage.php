<?php

namespace Nik\ExcelBundle\Storage;

use Nik\ExcelBundle\Mapping\PropertyMapping;
use Symfony\Component\HttpFoundation\File\UploadedFile;


/**
 * FileSystemStorage.
 *
 * @author Nikmodern co <info@nikmodern.com>
 */
abstract class AbstractStorage implements StorageInterface
{
    /**
     * Do real upload
     *
     * @param UploadedFile    $file
     * @param string          $dir
     * @param string          $name
     *
     * @return UploadedFile
     */
    abstract protected function doUpload(UploadedFile $file, $dir, $name);

    /**
     * Upload function
     *
     * @param $obj
     * @param PropertyMapping $mapping
     * @return UploadedFile
     */
    public function upload($obj, PropertyMapping $mapping)
    {
        $mapping->setFile($obj);

        $file = $mapping->getFile();

        if ($file === null || !($file instanceof UploadedFile)) {
            throw new \LogicException('No uploadable file found');
        }

        // determine the file's directory
        $dir = $mapping->getUploadDir();

        return $this->doUpload($file, $dir, $mapping->getFileName());
    }
}
