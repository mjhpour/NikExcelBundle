<?php

namespace Nik\ExcelBundle\Storage;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * FileSystemStorage.
 *
 * @author Nikmodern co <info@nikmodern.com>
 */
class FileSystemStorage extends AbstractStorage
{
    /**
     * {@inheritDoc}
     */
    protected function doUpload(UploadedFile $file, $dir, $name)
    {
        return $file->move($dir, $name);
    }
}
