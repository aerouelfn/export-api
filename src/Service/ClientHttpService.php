<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

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
    public function executeAuth($url,$method,$params)
    {
        $response = $this->client->request(
            $method,
            $url,
            [
                'json' => $params,
            ]
            
        );
        $statusCode = $response->getStatusCode();
        $contentType = $response->getHeaders()['content-type'][0];
        // $contentType = 'application/json'
        $content = $response->getContent();
        // $content = '{"id":521583, "name":"symfony-docs", ...}'
        $content = $response->toArray();
        // $content = ['id' => 521583, 'name' => 'symfony-docs', ...]
        return $content;
    }
    public function execute($url,$method,$params,$token)
    {
        /*if(!empty($token)){
            $response = $this->client->request(
                $method,
                $url,
                ['auth_bearer' => $token]
            );
        }else{
            $response = $this->client->request(
                $method,
                $url
            );
        }
        $statusCode = $response->getStatusCode();
        // $statusCode = 200
        $content = $response->getContent();
        $statusOk=['200','201','202','204','205'];
        $statusNotFound=['401','402','403','404'];
        if(!empty($content)){
            $contentType = $response->getHeaders()['content-type'][0];
        }else{
            $content=null;
        }
        $result=[];*/
        $content='[{"code":"01191219","financialPeriodName":"Exercice du 01/01/2019 au 31/12/2019","startDate":"2019-01-01T00:00:00","endDate":"2019-12-31T00:00:00","closed":false,"extras.firstFinancialDate":"2019-01-01T00:00:00","extras.fiscalEndOfTheFirstFiscalPeriod":"2019-12-31T00:00:00","extras.accountLabelLength":30,"extras.tradingAccountLength":6,"extras.accountingLineLabelLength":30,"extras.accountLength":6,"extras.authorizationAlphaAccounts":true,"extras.amountsLength":10,"extras.withQuantities":true,"extras.withDueDates":true,"extras.withMultipleDueDates":true,"$uuid":"c389fa8e-3155-48e8-8266-71409e3b5728"},{"code":"01181218","financialPeriodName":"Exercice du 01/01/2018 au 31/12/2018","startDate":"2018-01-01T00:00:00","endDate":"2018-12-31T00:00:00","closed":true,"extras.firstFinancialDate":"2018-01-01T00:00:00","extras.fiscalEndOfTheFirstFiscalPeriod":"2018-12-31T00:00:00","extras.accountLabelLength":20,"extras.tradingAccountLength":6,"extras.accountingLineLabelLength":20,"extras.accountLength":5,"extras.authorizationAlphaAccounts":false,"extras.amountsLength":7,"extras.withQuantities":false,"extras.withDueDates":true,"extras.withMultipleDueDates":false,"$uuid":"becc5ee7-0e64-4ae7-8cc9-be7195879abc"}]';
        $statusCode=200;
        $result["content"]=$content;
        $result["status"]=$statusCode;
        return $result;        
    }

}