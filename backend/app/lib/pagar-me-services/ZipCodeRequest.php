<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ZipCodeService
 *
 * @author sidneisimmon
 */
class ZipCodeRequest implements Credentials {

    public static function getAddressByZipCode($zipCode) {
        try {
            // Base URL
            $uri = self::ENDPOINT . '/1/zipcodes/' . $zipCode . '?api_key=' . self::API_KEY;
            
            // Headers
            $params['headers'] = ['Accept' => 'application/json','Content-type' => 'application/json'];
            // Client HTTP
            $client = new GuzzleHttp\Client;
            // Get request
            $request = new GuzzleHttp\Psr7\Request('GET', $uri, $params);
            // Envia a requisição
            $response = $client->send($request);
            // Converte a resposta em objeto
            $contents = json_decode($response->getBody()->getContents());
            // Retorna o status code e o conteudo
            return ['code' => $response->getStatusCode(), 'response' => $contents];
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            return ['code' => $e->getCode(), 'response' => $e->getMessage()];
        }
    }

}
