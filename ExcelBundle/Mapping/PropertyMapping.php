<?php

namespace Nik\ExcelBundle\Mapping;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Kernel;

/**
 * PropertyMapping.
 *
 * @author Nikmodern co <info@nikmodern.com>
 */
class PropertyMapping
{
    /**
     * @var
     */
    protected $directory;

    protected $fileName;

    protected $uploadedFile;

    /**
     * PropertyMapping constructor.
     * @param Kernel $kernel
     * @param UploadedFile $uploadedFile
     */
    public function __construct(Kernel $kernel, UploadedFile $uploadedFile)
    {
        $d_s = DIRECTORY_SEPARATOR;

        $this->directory =
            $kernel->getRootDir() .$d_s.'..'.$d_s.'web'.$d_s.'upload'.$d_s.'excel'. $d_s;
        $this->setFile($uploadedFile);
        $this->setFileName($uploadedFile->getClientOriginalName());
    }

    /**
     * Sets the file property.
     *
     * @param UploadedFile $uploadedFile
     */
    public function setFile(UploadedFile $uploadedFile)
    {
        $this->uploadedFile = $uploadedFile;
    }

    /**
     * Gets the file property.
     *
     * @return UploadedFile The file.
     */
    public function getFile()
    {
        return $this->uploadedFile;
    }

    /**
     * Set the fileName property by the given value.
     * @param $value
     */
    public function setFileName($value)
    {
        $this->fileName = $value;
    }

    public function getFileName()
    {
        return$this->fileName;
    }

    /**
     * Gets the upload directory.
     *
     * @return string The upload directory.
     */
    public function getUploadDir()
    {
        $dir = $this->directory;
        // strip the trailing directory separator if needed
        $dir = $dir ? rtrim($dir, '/\\') : $dir;

        return $dir;
    }
}
