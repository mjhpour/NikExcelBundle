<?php

namespace Nik\ExcelBundle\Controller;

use Nik\SystemBundle\Utils\NikMessageGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Excel controller.
 */
class ExcelIndexController extends Controller
{
    /**
     * Lists all Database entities.
     *
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        if(!$this->isGranted("ROLE_SUPER_ADMIN")){
            return $this->createAccessDenied();
        }
        $entitiesList = $this
            ->get('doctrine.orm.entity_manager')->getConfiguration()
            ->getMetadataDriverImpl()->getAllClassNames();

        $modifiedEntitiesList = [];
        foreach ($entitiesList as $key => $entityName) {
            $modifiedEntitiesList[$key]['fullClassName'] = $entitiesList[$key];
            $classNames = explode('\\', $entityName);
            $modifiedEntitiesList[$key]['name'] = end($classNames);
        }

        $pagination = $this->get('knp_paginator')->paginate(
            $modifiedEntitiesList,
            $request->query->get('page', 1)/*page number*/,
            10/*limit per page*/
        );

        return $this->render(
            "NikExcelBundle:Excel:index.html.twig",
            [
                'pagination'     => $pagination
            ]
        );
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
}
