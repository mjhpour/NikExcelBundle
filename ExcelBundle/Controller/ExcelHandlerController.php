<?php

namespace Nik\ExcelBundle\Controller;

use FOS\RestBundle\Controller\Annotations as Rest;
use Nik\ExcelBundle\Filter\CategoryFilter;
use Nik\ExcelBundle\Filter\FieldFilter;
use Nik\ExcelBundle\Filter\GteUpdatedAtFilter;
use Nik\ExcelBundle\Filter\IdFilter;
use Nik\ExcelBundle\Filter\LteUpdatedAtFilter;
use Nik\ExcelBundle\Provider\ItemProvider;
use Nik\ExcelBundle\Steps\Step\FieldStep;
use Nik\SystemBundle\Utils\NikMessageGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Excel Handler controller.
 *
 * Download attachments
 */
class ExcelHandlerController extends Controller
{

    private $mimeTypeErrorMessage = 'Excel file with format \'%s\' is not valid';

    /**
     * Import excel to database.
     *
     * @Rest\QueryParam(name="defaultValues", description="In json format with exact entity property name. example: {category: 5, shop: 2}")
     * @param Request $request
     *
     * @return Response
     */
    public function importAction(Request $request)
    {
        if (!$this->isGranted("ROLE_SUPER_ADMIN")) {
            return $this->createAccessDenied();
        }
        $form = $this->getFormBuilder();

        $defaultValues = $request->get('defaultValues');
        // Set default value for use in create new item in adaptor.
        ItemProvider::setDefaultValues(empty(trim($defaultValues)) ? [] : json_decode($defaultValues, true));

        if ($request->getMethod() == 'POST') {
            $form->handleRequest($request);
            $data = $form->getData();

            if (!isset($data['excel'])) {
                return $this->createInfoPage('not.valid.excelFile', Response::HTTP_BAD_REQUEST);
            }
            /** @var UploadedFile $excelFile */
            $excelFile = $data['excel'];

            // Check file extension.
            if (!$this->checkFileExtension($excelFile)) {
                return $this->createInfoPage(sprintf($this->mimeTypeErrorMessage, $excelFile->getMimeType()), Response::HTTP_BAD_REQUEST);
            }

            // Handle excel
            $this->get('nik_excel.handler.import_handler')->handle($excelFile);

            return $this->createInfoPage('upload.success', Response::HTTP_OK);
        } else {
            return $this->render(
                'NikExcelBundle:Excel:new.html.twig',
                array(
                    'form' => $form->createView(),
                )
            );
        }
    }

    /**
     * Export excel from database.
     *
     * @Rest\QueryParam(name="ids", description="Split id by ',' deliminator")
     * @Rest\QueryParam(name="categoryIds", description="Split category id by ',' deliminator")
     * @Rest\QueryParam(name="fields", description="Split fields by ',' deliminator", default="title")
     * @Rest\QueryParam(name="fromDate", description="Export data from date")
     * @Rest\QueryParam(name="toDate", description="Export data to date")
     * @Rest\QueryParam(name="filterBy", default="{}", description="Filter with 'IN' SQL expression by requested field. eg. {category: 5, shop: 2}")
     * @param Request $request
     *
     * @return Response
     */
    public function exportAction(Request $request)
    {
        if (!$this->isGranted("ROLE_SUPER_ADMIN")) {
            return $this->createAccessDenied();
        }
        // Ids
        $ids = $request->get('ids', null);
        $isIdsEmpty = empty(trim($ids)) ? true : false;
        $ids = explode(',', $ids);
        // Category ids
        $catIds = $request->get('categoryIds', null);
        $isCatIdsEmpty = empty(trim($catIds)) ? true : false;
        $catIds = explode(',', $catIds);
        // From date
        $fromDate = $request->get('fromDate', null);
        // To date
        $toDate = $request->get('toDate', null);
        // Fields
        $fields = explode(',', $request->get('fields'));
        // Filter by
        $filtersBy = json_decode($request->get('filterBy', null), true);

        // Query step. register all needed query that execute when excel exporting.
        $queryStep = $this->get('nik_excel.steps_step.query_step');
        $queryStep
            ->add(new IdFilter($ids, !$isIdsEmpty))
            ->add(new CategoryFilter($catIds, !$isCatIdsEmpty))
            ->add(new GteUpdatedAtFilter(new \DateTime($fromDate), !empty(trim($fromDate))))
            ->add(new LteUpdatedAtFilter(new \DateTime($toDate), !empty(trim($toDate))));

        // Automatic filter by requested fields.
        $fieldFilter = new FieldFilter();
        foreach ($filtersBy as $filterBy => $value) {
            $queryStep->add($fieldFilter->setFieldName($filterBy)->setValue($value));
        }

        // Field step that register field assert that specify fields for export.
        $fieldStep = new FieldStep();
        $fieldStep->add(function ($input) use ($fields) { return in_array($input, $fields); });

        // Register step to adaptor.
        $this->get('nik_excel.adaptor.adaptor')->setQueryStep($queryStep);
        $this->get('nik_excel.adaptor.adaptor')->setFieldStep($fieldStep);
        $entityName = $request->get('entityName', null);
        $response = $this->get('nik_excel.handler.export_handler')->handle($entityName);

        return $response;
    }

    /**
     * @return \Symfony\Component\Form\FormInterface
     */
    protected function getFormBuilder()
    {
        return
            $this->createFormBuilder()
                ->add('excel', FileType::class, ['required' => true])
                ->add('submit', SubmitType::class)
                ->getForm();
    }

    /**
     * @return \Doctrine\DBAL\Schema\AbstractSchemaManager
     */
    protected function getTablesName()
    {
        return
            $this->get('doctrine.dbal.default_connection')
                ->getSchemaManager();
    }

    /**
     * @return Response
     */
    protected function createAccessDenied(){
        $data = NikMessageGenerator::create(403, $this->get('translator')->trans('you.must.super.admin'));

        return $this->render("::access_denied.html.twig",[
            'data'=>$data
        ]);
    }

    /**
     * @param $message
     * @return Response
     */
    protected function createInfoPage($message, $code)
    {
        return new Response(
            $this->render(
                'NikExcelBundle:Excel:new.html.twig',
                array('message' => $this->get('translator')->trans($message))
            ), $code
        );
    }

    /**
     * Check excel extension.
     *
     * allowed format:
     * application/vnd.ms-excel
     * application/vnd.ms-office.activex+xml
     * application/vnd.openxmlformats-officedocument.spreadsheetml.sheet
     * application/octet-stream
     *
     * @param File $excelFile
     * @return bool
     */
    private function checkFileExtension(File $excelFile)
    {
        try {
            // application/octet-stream
            // application/vnd.ms-excel
            // application/vnd.ms-office.activex+xml
            // application/vnd.openxmlformats-officedocument.spreadsheetml.sheet
            if (strlen($excelFile->getMimeType()) >= 24) {
                if (
                    substr_compare($excelFile->getMimeType(), "office", 19, 6, false)
                    &&
                    substr_compare($excelFile->getMimeType(), "excel", 19, 5, false)
                    &&
                    substr_compare($excelFile->getMimeType(), "openxmlformats", 16, 14, false)
                    &&
                    substr_compare('application/octet-stream', 'octet-stream', 12, 12, false)
                ) {
                    return false;
                }
            } else {
                return false;
            }
        } catch (\Exception $exception) {
            $this->errorMessage = 'php.configuration.problem';
            return false;
        }

        return true;
    }
}
