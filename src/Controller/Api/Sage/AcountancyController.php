<?php

namespace App\Controller\Api\Sage;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Service\SageClickUpService;

class AcountancyController extends AbstractController
{
    /**
     * @Route("/api/sage/accountancy/getAccountancyPractices", name="sage_accountancy_practices")
     */
    public function getAccountancyPractices(SageClickUpService $sageService)
    {
        $resp=$sageService->getAccountingPractices();
        $response = new Response();
        $response->setContent($resp);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }
	
	/**
     * @Route("/api/sage/accountancy/getAccountancyPracticesOption/accountPractice/{accountPractice}", name="sage_options_accountancy_practices")
     */
    public function getAccountancyPracticesOptions(Request $request,SageClickUpService $sageService)
    {
		$accountPractice=( $request->attributes->get('accountPractice')) ? $request->attributes->get('accountPractice') :'5a84d143-5fb1-4fce-bac0-b19ec942231c';
		$resp=$sageService->getOptionAccountingPractice($accountPractice);
        $response = new Response();
        $response->setContent($resp);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

}
