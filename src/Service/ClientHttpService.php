<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;

class ClientHttpService
{
    private $client;
    /**
     * ClientHttpService constructor.
     *
     */
    public function __construct(HttpClientInterface $client)
    {
        $this->client = $client;
    }
    /**
     * Execute Api  function
     *
     * @param string $url
     * @param string $method
     * @param array $params
     * @param string $token
     * @param integer $typeContent
     * @return void
     */
    public function execute($url,$method,$params,$token,$typeContent=1)
    {
        $tmp_dir = ini_get('upload_tmp_dir') ? ini_get('upload_tmp_dir') : sys_get_temp_dir();
        $paramsBody=[];
        switch ($typeContent) {
            case 1:
                $paramsBody["json"]=$params; 
            break;
            case 2:
                if(isset($params["attachment"])){
                    $params['attachment'] = DataPart::fromPath($params["attachment"]);
                }
                $formData = new FormDataPart($params);
                $paramsBody["headers"]=$formData->getPreparedHeaders()->toArray(); 
                $paramsBody["body"]=$formData->bodyToIterable();
                
            break;
            default:
                $paramsBody=[];
        };

        if(!empty($token)){
            $paramsBody["auth_bearer"]=$token;
        }
        $response = $this->client->request(
            $method,
            $url,
            $paramsBody
            
        );
        $statusCode = $response->getStatusCode();
        $content = $response->getContent();
        $statusOk=['200','201','202','204','205'];
        $statusNotFound=['401','402','403','404','415'];
        if(!empty($content)){
            $contentType = $response->getHeaders()['content-type'][0];
        }else{
            $content=null;
        }
        $result=[];
        //$content='[{"code":"01191219","financialPeriodName":"Exercice du 01/01/2019 au 31/12/2019","startDate":"2019-01-01T00:00:00","endDate":"2019-12-31T00:00:00","closed":false,"extras.firstFinancialDate":"2019-01-01T00:00:00","extras.fiscalEndOfTheFirstFiscalPeriod":"2019-12-31T00:00:00","extras.accountLabelLength":30,"extras.tradingAccountLength":6,"extras.accountingLineLabelLength":30,"extras.accountLength":6,"extras.authorizationAlphaAccounts":true,"extras.amountsLength":10,"extras.withQuantities":true,"extras.withDueDates":true,"extras.withMultipleDueDates":true,"$uuid":"c389fa8e-3155-48e8-8266-71409e3b5728"},{"code":"01181218","financialPeriodName":"Exercice du 01/01/2018 au 31/12/2018","startDate":"2018-01-01T00:00:00","endDate":"2018-12-31T00:00:00","closed":true,"extras.firstFinancialDate":"2018-01-01T00:00:00","extras.fiscalEndOfTheFirstFiscalPeriod":"2018-12-31T00:00:00","extras.accountLabelLength":20,"extras.tradingAccountLength":6,"extras.accountingLineLabelLength":20,"extras.accountLength":5,"extras.authorizationAlphaAccounts":false,"extras.amountsLength":7,"extras.withQuantities":false,"extras.withDueDates":true,"extras.withMultipleDueDates":false,"$uuid":"becc5ee7-0e64-4ae7-8cc9-be7195879abc"}]';
        $statusCode=200;
        $result["content"]=$content;
        $result["status"]=$statusCode;
        return $result;        
    }

}