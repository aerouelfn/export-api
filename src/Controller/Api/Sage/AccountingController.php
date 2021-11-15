<?php

namespace App\Controller\Api\Sage;

use App\Controller\Api\Sage\SageController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Service\SageClickUpService;

class AccountingController extends SageController
{
    /**
     * @Route("/api/sage/accounting/getPeriods/accountPractice/{accountPractice}/companyId/{companyId}", name="sage_accounting_get_periods")
     */
    public function getPeriods(Request $request,SageClickUpService $sageService){
        $accountPractice=( $request->attributes->get('accountPractice')) ? $request->attributes->get('accountPractice') :'5a84d143-5fb1-4fce-bac0-b19ec942231c';
        $companyId=( $request->attributes->get('companyId')) ? $request->attributes->get('companyId') :'22df8495-6357-44b2-8ea0-05272756d1da';
        $resp=$sageService->getPeriods($accountPractice,$companyId);
        $response = new Response();
        $response->setContent($resp);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
    /**
     * @Route("/api/sage/accounting/createEntry/accountPractice/{accountPractice}/companyId/{companyId}/periodId/{periodId}", name="sage_accounting_create_entry")
     */
    public function createEntry(Request $request,SageClickUpService $sageService){
        $accountPractice=( $request->attributes->get('accountPractice')) ? $request->attributes->get('accountPractice') :'5a84d143-5fb1-4fce-bac0-b19ec942231c';
        $companyId=( $request->attributes->get('companyId')) ? $request->attributes->get('companyId') :'22df8495-6357-44b2-8ea0-05272756d1da';
        $periodId=( $request->attributes->get('periodId')) ? $request->attributes->get('periodId') :'5a84d143-5fb1-4fce-bac0-b19ec942231c';
        $resp=$sageService->createEntry($accountPractice,$companyId,$periodId);
        $response = new Response();
        $response->setContent($resp);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
}
