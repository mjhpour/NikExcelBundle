<?php

namespace Nik\ExcelBundle\Injector;

use Liuggio\ExcelBundle\Factory;
use Symfony\Component\HttpFoundation\File\File;

/**
 * ExcelInjector.
 *
 * @author Nikmodern co <info@nikmodern.com>
 */
class ExcelInjector
{
    protected $phpExcel;

    /** @var  \PHPExcel */
    protected $excelObject;

    function __construct(Factory $PHPExcel)
    {
        $this->phpExcel = $PHPExcel;
    }

    /**
     * Main provider method.
     *
     * @param File $file
     * @return $this
     */
    public function excelProvider(File $file = null)
    {
        $this->providePCLZip();
        if (!is_null($file)) {
            $pathName = $file->getPathname();
        } else {
            $pathName = null;
        }
        $this->excelObject = $this->phpExcel->createPHPExcelObject($pathName);

        return $this;
    }

    public function getSheetsName()
    {
        $sheetNames = [];
        foreach ($this->excelObject->getAllSheets() as $sheet) {
            $sheetNames[] = $sheet->getTitle();
        }
        return $sheetNames;
    }

    public function getAllId($sheetName)
    {
        $worksheet = $this->excelObject->getSheetByName($sheetName);
        $columnOfId = $this->findColumnOfValue($worksheet, 'id');
        $rowAndColumn = $worksheet->getHighestRowAndColumn();
        return $worksheet->rangeToArray($this->fixColumnName(current($columnOfId)).'2:'.$this->fixColumnName(current($columnOfId)).$rowAndColumn['row']);
    }

    public function getAllValueOfRow($sheetName, $row)
    {
        $worksheet = $this->excelObject->getSheetByName($sheetName);
        $rowAndColumn = $worksheet->getHighestRowAndColumn();
        return $worksheet->rangeToArray('A'.$row.':'.$rowAndColumn['column'].$row);
    }

    public function getAllHeader($sheetName)
    {
        $worksheet = $this->excelObject->getSheetByName($sheetName);
        $rowAndColumn = $worksheet->getHighestRowAndColumn();
        return $worksheet->rangeToArray('A1:'.$rowAndColumn['column'].'1');
    }

    public function setProperty()
    {
        $this->excelObject->getProperties()->setCreator('Nikmodern')
            ->setLastModifiedBy("Nikmodern Excel Bundle")
            ->setTitle("Office XLSX Database Table")
            ->setDescription("Provide Excel, using PHP classes.");
    }

    public function setValueToLatestRow($values)
    {
        $worksheet = $this->excelObject->getActiveSheet();
        $latestRow = $worksheet->getHighestRow() + 1;
        foreach ($values as $key => $value) {
            if (!is_array($value)) {
                $worksheet->setCellValueByColumnAndRow($key, $latestRow, $value);
            }
        }
    }

    public function setActiveSheetTitle($title)
    {
        $this->excelObject->getActiveSheet()->setTitle($title);
    }

    public function createStreamedResponse()
    {
        // create the writer
        $writer = $this->phpExcel->createWriter($this->excelObject, 'Excel5');
        // create the response
        return $this->phpExcel->createStreamedResponse($writer);
    }

    public function buildHeader($name)
    {
        $worksheet = $this->excelObject->getActiveSheet();
        foreach ($name as $key => $headerName) {
            if ($headerName != $worksheet->getCellByColumnAndRow($key, 1)) {
                $worksheet->setCellValueByColumnAndRow($key, 1, $headerName);
            }
        }
    }

    private function providePCLZip()
    {
        \PHPExcel_Settings::setZipClass(\PHPExcel_Settings::PCLZIP);
    }

    private function findColumnOfValue(\PHPExcel_Worksheet $worksheet, $value)
    {
        $foundInCells = [];
        foreach ($worksheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(true);
            /** @var \PHPExcel_Cell $cell */
            foreach ($cellIterator as $cell) {
                if ($cell->getValue() == $value) {
                    $foundInCells[] = $cell->getCoordinate();
                }
            }
        }
        return $foundInCells;
    }

    private function fixColumnName($name)
    {
        return preg_replace('/[0-9]+/', '', $name);
    }
}
