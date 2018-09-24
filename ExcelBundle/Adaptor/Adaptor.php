<?php

namespace Nik\ExcelBundle\Adaptor;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Nik\ExcelBundle\Exception\MethodNotRegisteredException;
use Nik\ExcelBundle\Injector\ExcelInjector;
use Nik\ExcelBundle\Provider\ItemProvider;
use Nik\ExcelBundle\Steps\StepInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Validator\Exception\ValidatorException;

class Adaptor
{
    /** @var ExcelInjector  */
    protected $excelInjector;
    protected $sheetsName;
    protected $tablesName;
    protected $allClassNames;

    /**
     * QuerySteps was a queue of query filters.
     *
     * @var StepInterface $querySteps Register query step to handler.
     */
    protected $querySteps;

    /**
     * Steps was a queue of field filters.
     *
     * @var StepInterface $fieldSteps Register field step to handler.
     */
    protected $fieldSteps;

    /** @var  EntityManager */
    protected $em;
    /** @var  Connection */
    protected $connection;

    /**
     * Adaptor constructor.
     * @param ExcelInjector $excelInjector
     */
    function __construct(ExcelInjector $excelInjector)
    {
        $this->excelInjector = $excelInjector;
    }

    /**
     * Register query step to adaptor. it is required.
     *
     * @param StepInterface $queryStep
     */
    public function setQueryStep(StepInterface $queryStep)
    {
        $this->querySteps = $queryStep;
    }

    /**
     * Register field step to adaptor. it is required.
     *
     * @param StepInterface $fieldStep
     */
    public function setFieldStep(StepInterface $fieldStep)
    {
        $this->fieldSteps = $fieldStep;
    }

    /**
     * @param File $file
     * @param Connection $connection
     * @param EntityManagerInterface $entityManager
     */
    public function injectExcelToDatabase(File $file, Connection $connection, EntityManagerInterface $entityManager)
    {
        // Must run before all things
        $excelProvider = $this->excelInjector->excelProvider($file);
        $this->provideUtils($connection, $entityManager);
        $this->em = $entityManager;
        $this->connection = $connection;

        $this->validateSheetName($excelProvider->getSheetsName());
        $this->injectExcelToTable();
    }

    /**
     * @param $tableName
     * @param Connection $connection
     * @param EntityManager $entityManager
     * @return Response
     */
    public function fetchStreamedResponseFromTable($tableName, $connection, $entityManager)
    {
        if (is_null($this->querySteps) || is_null($this->fieldSteps)) {
            throw new MethodNotRegisteredException('Method not registered. did you forget that add queryStep and fieldSteps to Adaptor?');
        }

        // Must run before all things
        $excelProvider = $this->excelInjector->excelProvider();
        $this->provideUtils($connection, $entityManager);
        $this->em = $entityManager;
        $this->connection = $connection;

        $excelProvider->setProperty();

        return $this->exportTable($tableName);
    }

    /**
     * Inject excel to table. table find with sheet name in the uploaded excel file.
     */
    protected function injectExcelToTable()
    {
        $this->injectToTable();
    }

    /**
     * Inject data to table. table find with sheet name in the uploaded excel file.
     *
     * Using sheet name to find entity.
     */
    protected function injectToTable()
    {
        foreach ($this->sheetsName as $sheetName) {
            if (in_array($this->fixSheetName($sheetName), $this->allClassNames)) {
                $className = $this->fixSheetName($sheetName);
                // Get ids of real database object from excel file.
                $ids = $this->excelInjector->getAllId($sheetName);
                foreach ($ids as $key => $arrayId) {
                    /** @var int $id Real id of object in database */
                    foreach ($arrayId as $k => $id) {
                        // TODO: Create new object if id is empty.
                        $item = ItemProvider::getItem($this->em, $className, $id);
                        $this->saveToDatabase($item, $key + 2, $sheetName, $className);
                    }
                }
            }
        }
    }

    /**
     * Main export method.
     *
     * @param string $name Name of table in database.
     * @return Response
     */
    private function exportTable($name)
    {
        $this->setSheetTitle($this->getSheetNameFromFullClassName($name));
        if (in_array($name, $this->em->getConfiguration()->getMetadataDriverImpl()->getAllClassNames())) {
            $className = $name;
            // Get Items from database with help of query steps that contain all registered query.
            $items = $this->querySteps->process($className);
            $this->generateExcelFromFields($items, $className);
            return $this->createStreamResponse($this->getSheetNameFromFullClassName($name, true));
        }
        throw new ValidatorException(sprintf('not meta with name: %s', $name), 400);
    }

    /**
     * @param $sheetName
     * @return StreamedResponse
     */
    private function createStreamResponse($sheetName)
    {
        $date = new \DateTime('now');
        $finalName = $sheetName . '_' . $date->format('Y-m-d_His');
        $response = $this->excelInjector->createStreamedResponse();
        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $finalName . '.xls'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

    private function setSheetTitle($name)
    {
        $this->excelInjector->setActiveSheetTitle($name);
    }

    /**
     * Generate excel sheet from all given object.
     *
     * @param array $items
     * @param $className
     */
    private function generateExcelFromFields(array $items, $className)
    {
        // Get valid allowed fields name from field step.
        $fieldsName = $this->getFieldNameFromFieldStep($this->getClassFieldsName($className));
        $this->buildHeader($fieldsName);
        foreach ($items as $item) {
            // Get current object all value.
            $currentObjValues = $this->getValueFromClassFieldNames($className, $item);
            $this->setValuesToRow($currentObjValues);
        }
    }

    /**
     * Set object value to excel row.
     *
     * @param $values
     */
    private function setValuesToRow($values)
    {
        $this->excelInjector->setValueToLatestRow($values);
    }

    private function buildHeader($name)
    {
        $this->excelInjector->buildHeader($name);
    }

    private function getClassFieldsName($className)
    {
        return $this->em->getClassMetadata($className)->getFieldNames();
    }

    private function getValueFromClassFieldNames($className, $item)
    {
        $values = [];
        // Get valid allowed fields name from field step.
        $classFieldNames = $this->getFieldNameFromFieldStep($this->em->getClassMetadata($className)->getFieldNames());
        foreach ($classFieldNames as $classFieldName) {
            $method = 'get' . $this->fixMethodName($classFieldName);
            $values[] = $item->$method();
        }
        return $values;
    }

    /**
     * Function to save data to database.
     *
     * @param $item
     * @param $row
     * @param $sheetName
     * @param $className
     */
    private function saveToDatabase($item, $row, $sheetName, $className)
    {
        // Get all value from excel to inject to database.
        $allValueOfRow = current($this->excelInjector->getAllValueOfRow($sheetName, $row));
        // Get header (field name) in database from excel.
        $allHeaderName = current($this->excelInjector->getAllHeader($sheetName));
        $classFieldNames = $this->em->getClassMetadata($className)->getFieldNames();
        foreach ($allHeaderName as $key => $headerName) {
            foreach ($classFieldNames as $classFieldName) {
                if ($headerName == $classFieldName && $headerName != 'id') {
                    // Set data to item.
                    $this->setDataToObject($item, $classFieldName, $allValueOfRow, $key);
                }
            }
        }
        $this->em->persist($item);
    }

    private function fixMethodName($methodName)
    {
        return implode('',array_map('ucfirst',explode('_',$methodName)));
    }

    /**
     * @param array $sheetsName
     * @throws ValidatorException
     */
    private function validateSheetName(array $sheetsName)
    {
        foreach ($sheetsName as $sheetName) {
            if (in_array($this->fixSheetName($sheetName), $this->allClassNames)) {
                return;
            }
        }
        throw new ValidatorException('not.valid.sheet.name', Response::HTTP_BAD_REQUEST);
    }

    /**
     * @param Connection $connection
     * @param EntityManagerInterface $entityManager
     */
    private function provideUtils(Connection $connection, EntityManagerInterface $entityManager)
    {
        $this->sheetsName = $this->excelInjector->getSheetsName();
        $this->tablesName = $connection->getSchemaManager()->listTableNames();
        $this->allClassNames = $entityManager->getConfiguration()->getMetadataDriverImpl()->getAllClassNames();
    }

    private function getSheetNameFromFullClassName($fullClassName, $latestName = false)
    {
        $classNames = explode('\\', $fullClassName);
        if ($latestName) {
            return end($classNames);
        }

        $result = '';
        foreach ($classNames as $className) {
            $result .= $className . '-';
        }
        return rtrim($result,'-');
    }

    /**
     * Set data to given object with using of set method.
     * Validation of method that is exists or not do in this function.
     *
     * @param object $item object that find in in database.
     * @param string $classFieldName Entity property for using in setter and getter.
     * @param array $allValueOfRow Value given from excel file.
     * @param int $currentKey current key for use in find given value from allValueOfRow.
     */
    private function setDataToObject($item, $classFieldName, $allValueOfRow, $currentKey)
    {
        $getterMethod = 'get' . $this->fixMethodName($classFieldName);
        $setterMethod = 'set'.$this->fixMethodName($classFieldName);
        // Check method exists or not.
        if (!method_exists($item, $getterMethod) || !method_exists($item, $setterMethod)) {
            return;
        }
        // Check empty data or object
        if (gettype($item->$getterMethod()) === 'object' || empty(trim($allValueOfRow[$currentKey]))) {
            return;
        }
        // All thing is ok. set data to object.
        $item->$setterMethod($allValueOfRow[$currentKey]);
    }

    /**
     * @param string $sheetName
     * @return string
     */
    private function fixSheetName($sheetName)
    {
        return str_replace('-', '\\', $sheetName);
    }

    /**
     * Get all valid fields name from class by helping with fields step.
     *
     * @param $fieldsName
     * @return array
     */
    private function getFieldNameFromFieldStep($fieldsName)
    {
        $classFieldNames = ['id'];
        foreach ($fieldsName as $fieldName) {
            if ($this->fieldSteps->process($fieldName)) {
                $classFieldNames[] = $fieldName;
            }
        }
        return $classFieldNames;
    }
}