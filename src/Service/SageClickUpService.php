<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Entity\AccountancyPractice;
use App\Entity\Company;
use App\Entity\FinancialPeriod;
use App\Service\ClientHttpService;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Dotenv\Dotenv;
use App\Service\App\SerializeService;


class SageClickUpService
{
    private $em;
    private $cltHttpService;
    private $ConnectedUser;
    private $security;
    private $statusNotFound=['401','402','403','404'];
    private $statusErrorServer=['500','501','503'];
    /** @var SerializeService $serializer */
    private $serializer;
    /**
     * ClientHttpService constructor.
     *
     */
    public function __construct(EntityManagerInterface $em,ClientHttpService $cltHttpService,Security $security,SerializeService $serializeService)
    {
        $this->em=$em;
        $this->cltHttpService=$cltHttpService;
        $this->security = $security;
        $this->serializer = $serializeService;
        $this->loginSage();

    }
    public function getAccountingPractices(){
        $base_url="https://cloudconnector.linkup-sage.com/v1";
        $user=$this->ConnectedUser;
        $app_id=$user->getSageconfigs()->first()->getAppId();
        $tokenAccess=$user->getSageconfigs()->first()->getToken();
        $url=$base_url.'/applications/'.$app_id.'/accountancypractices';
        $result=$this->cltHttpService->execute($url,"GET",[],$tokenAccess);
        $em=$this->em;
        if(in_array($result["status"],$this->statusNotFound)){
            return $result["content"];
        }else if(in_array($result["status"],$this->statusErrorServer)){
            $listLocaly=$em->getRepository(AccountancyPractice::class)->findBySageModelAll($user->getSageconfigs()->first());
            return $this->serializer->SerializeContent($listLocaly);
        }else{
            $this->saveAccountancyPractices(json_decode($result["content"],true),$em,$user);
            return $result["content"];
        }
    }

    public function getOptionAccountingPractice($accountPractice){
		$base_url="https://cloudconnector.linkup-sage.com/v1";
        $user=$this->ConnectedUser;
        $app_id=$user->getSageconfigs()->first()->getAppId();
        $tokenAccess=$user->getSageconfigs()->first()->getToken();
        $accountPractice='5a84d143-5fb1-4fce-bac0-b19ec942231c';
        $url=$base_url . '/accountancypractices/' . $accountPractice . '/applications/' . $app_id . '/options';
        return $this->cltHttpService->execute($url,"GET",[],$tokenAccess);
    }

    public function getPeriods($accountPractice,$companyId)
    {       
		$base_url="https://cloudconnector.linkup-sage.com/v1";
        $user=$this->ConnectedUser;
        $app_id=$user->getSageconfigs()->first()->getAppId();
        $tokenAccess=$user->getSageconfigs()->first()->getToken();
        $url=$base_url.'/applications/'.$app_id.'/accountancypractices/'.$accountPractice.'/companies/'.$companyId.'/accounting/periods';
        $result = $this->cltHttpService->execute($url,"GET",[],$tokenAccess);
        $em=$this->em;
        $company = $em->getRepository(Company::class)->findOneBy(["SageId"=>$companyId]);
        if(in_array($result["status"],$this->statusNotFound)){
            return $result["content"];
        }else if(in_array($result["status"],$this->statusErrorServer)){
            $listLocaly=$em->getRepository(FinancialPeriod::class)->findByCompanyAll($company);
            return $this->serializer->SerializeContent($listLocaly);
        }else{
            $this->saveFinancialPeriods(json_decode($result["content"],true),$em,$company);
            return $result["content"];
        }
    }

    public function createEntry()
    {       
		$base_url="https://cloudconnector.linkup-sage.com/v1";
        $user=$this->ConnectedUser;
        $app_id=$user->getSageconfigs()->first()->getAppId();
        $tokenAccess=$user->getSageconfigs()->first()->getToken();
        $url=$base_url.'/applications/'.$app_id.'/accountancypractices/{accountancyPracticeId}/companies/487697724/accounting/periods/{periodId}/entries';
        return $this->cltHttpService->execute($url,"POST",[],$tokenAccess);
    }
	
	public function getCompanies($accountPractice){
		$base_url="https://cloudconnector.linkup-sage.com/v1";
        $user=$this->ConnectedUser;
        $app_id=$user->getSageconfigs()->first()->getAppId();
        $tokenAccess=$user->getSageconfigs()->first()->getToken();
		//$accountPractice='5a84d143-5fb1-4fce-bac0-b19ec942231c';
        $url=$base_url.'/applications/'.$app_id.'/accountancypractices/'.$accountPractice.'/companies';
        $result= $this->cltHttpService->execute($url,"GET",[],$tokenAccess);
        $em=$this->em;
        $accountPracticeObj=$em->getRepository(AccountancyPractice::class)->findOneBy(["SageId"=>$accountPractice]);
        
        if(in_array($result["status"],$this->statusNotFound)){
            return $result["content"];
        }else if(in_array($result["status"],$this->statusErrorServer)){
            $listLocaly=$em->getRepository(Company::class)->findByAccountancyPracticeAll($accountPracticeObj);
            return $this->serializer->SerializeContent($listLocaly);
        }else{
            $this->saveCompanies(json_decode($result["content"],true),$em,$accountPracticeObj);
            return $result["content"];
        }
	}
	
    public function createBatch()
    {
		$base_url="https://cloudconnector.linkup-sage.com/v1";
        $user=$this->ConnectedUser;
        $app_id=$user->getSageconfigs()->first()->getAppId();
        $tokenAccess=$user->getSageconfigs()->first()->getToken();
		$accountPractice='5a84d143-5fb1-4fce-bac0-b19ec942231c';
		$companyId='22df8495-6357-44b2-8ea0-05272756d1da';
        $url=$base_url.'/applications/{applicationId}/accountancypractices/'.$accountPractice.'/companies/'.$companyId.'/queues/in/batches';
        return $this->cltHttpService->execute($url,"POST",[],$tokenAccess);
    }

    public function getAccountancyPractices()
    {
        $user=$this->ConnectedUser;

    }
    private function loginSage(){
        $user=$this->security->getUser();
        $em = $this->em;
        $sageModel= $user->getSageconfigs()->first();
        $today = date("Y-m-d H:i:s");
        $dateExpired = $sageModel->getExpiredtoken()->format('Y-m-d H:i:s');
        if(empty($sageModel->getToken()) || (!empty($sageModel->getToken()) && ( $today > $dateExpired ))){
            $url_auth=$user->getSageconfigs()->first()->getUrlAuth();
            $grant_type=$user->getSageconfigs()->first()->getGrantType();
            $client_id=$user->getSageconfigs()->first()->getClientId();
            $client_secret=$user->getSageconfigs()->first()->getClientSecret();
            $audience=$user->getSageconfigs()->first()->getAudience();
            $response=$this->cltHttpService->executeAuth($url_auth,"POST",
            [
                "grant_type"=>$grant_type,
                "client_id"=>$client_id,
                "client_secret"=>$client_secret,
                "audience"=>$audience
            ]
            );
            if(isset($response["access_token"])){
                $now = new \DateTime();
                $now->add(new \DateInterval('PT'.$response["expires_in"].'S'));
                $sageModel->setToken($response["access_token"]);
                
                $sageModel->setExpiredToken($now);
                $em->persist($sageModel);
                $em->flush();
            }else{
                return false;
            }
            $this->ConnectedUser=$em->getRepository(User::class)->findOneBy(['email' => 'admin2@admin.com']);
        }else{
            $this->ConnectedUser = $user;
        }
        
    }

    public function saveAccountancyPractices($content,$em,$user){
        $query = $em->createQuery(
            'DELETE FROM App\Entity\AccountancyPractice e WHERE e.sageModel = :id_sage_model'
         )->setParameter('id_sage_model', $user->getSageconfigs()->first())->execute();
        if(!empty($content)){
            foreach($content as $ind=>$val){
                $accountancyPractice = new AccountancyPractice();
                $accountancyPractice->setSageId($val["id"]);
                $accountancyPractice->setBusinessId($val["businessId"]);
                $accountancyPractice->setName($val["name"]);
                $accountancyPractice->setOriginSageApplication($val["originSageApplication"]);
                $accountancyPractice->setContactEmail($val["contactEmail"]);
                $accountancyPractice->setSageModel($user->getSageconfigs()->first());
                $em->persist($accountancyPractice);
              
            }
            $em->flush();
        }        
    }

    public function saveCompanies($content,$em,$accountancyPractice){
        $query = $em->createQuery(
            'DELETE FROM App\Entity\Company a WHERE a.accountancyPractice = :accountancy_practice'
         )->setParameter('accountancy_practice', $accountancyPractice)->execute();
        if(!empty($content)){
            foreach($content as $ind=>$val){
                $company = new Company();
                $company->setSageId($val["id"]);
                $company->setBusinessId($val["businessId"]);
                $company->setName($val["name"]);                
                $company->setIsAccountancyPractice($val["isAccountancyPractice"]);
                $company->setAccountancyPractice($accountancyPractice);
                $em->persist($company);
                
            }
            $em->flush();
        }        
    }

    public function saveFinancialPeriods($content,$em,$company){
        $query = $em->createQuery(
            'DELETE FROM App\Entity\FinancialPeriod f WHERE f.company = :company'
         )->setParameter('company', $company)->execute();
         $dateTimeObj=new \DateTime();
        if(!empty($content)){
            foreach($content as $ind=>$val){
                $fPeriods = new FinancialPeriod();            
                $fPeriods->setCode($val["code"]);
                $fPeriods->setFinancialPeriodName($val["financialPeriodName"]);
                $dateTimeObj->createFromFormat('Y-m-dTH:i:s', $val["startDate"]);
                $fPeriods->setStartDate($dateTimeObj);
                $dateTimeObj->createFromFormat('Y-m-dTH:i:s', $val["endDate"]);
                $fPeriods->setEndDate($dateTimeObj);                
                $fPeriods->setClosed($val["closed"]);
                $dateTimeObj->createFromFormat('Y-m-dTH:i:s', $val["extras.firstFinancialDate"]);
                $fPeriods->setExtrasFirstFinancialDate($dateTimeObj);
                $dateTimeObj->createFromFormat('Y-m-dTH:i:s', $val["extras.fiscalEndOfTheFirstFiscalPeriod"]);
                $fPeriods->setExtrasFiscalEndOfTheFirstFiscalPeriod($dateTimeObj);
                $fPeriods->setExtrasAccountLabelLength($val["extras.accountLabelLength"]);
                $fPeriods->setExtrasTradingAccountLength($val["extras.tradingAccountLength"]);                
                $fPeriods->setExtrasAccountingLineLabelLength($val["extras.accountingLineLabelLength"]);
                $fPeriods->setExtrasAccountLength($val["extras.accountLength"]);
                $fPeriods->setExtrasAuthorizationAlphaAccounts($val["extras.authorizationAlphaAccounts"]);
                $fPeriods->setExtrasAmountsLength($val["extras.amountsLength"]);
                $fPeriods->setExtrasWithQuantities($val["extras.withQuantities"]);                
                $fPeriods->setExtrasWithDueDates($val["extras.withDueDates"]);
                $fPeriods->setExtrasWithMultipleDueDates($val["extras.withMultipleDueDates"]);
                $fPeriods->setUuid($val['$uuid']);
                $fPeriods->setCompany($company);
                $em->persist($fPeriods);
                }
            $em->flush();
        }        
    }

}