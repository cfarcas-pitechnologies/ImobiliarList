<?php

namespace AppBundle\Service\Parsing;

use Guzzle\Http\Client;
use Guzzle\Http\Exception\BadResponseException;

class CommunicationService
{

    private $logger;

    public function __construct(\Monolog\Logger $logger)
    {
        $this->logger = $logger;
    }

    public function callService($method, $url, $headers = null, $body = null)
    {
        $client = new Client();
        if (($method != "GET") && (empty($headers['Content-Type']))) {
            $headers['Content-Type'] = 'application/json';
        }
        $wsRequest = $client->createRequest($method, $url, $headers, $body);

        try {
            $response = $wsRequest->send();
        } catch (BadResponseException $e) {
            $this->logger->addError($e->getMessage());

            return null;
        }

        return $response->json();
    }

}
