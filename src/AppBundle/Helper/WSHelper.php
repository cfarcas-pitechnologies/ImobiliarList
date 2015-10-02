<?php

namespace AppBundle\Helper;

class WSHelper
{

    protected $communicationService;
    protected $serviceUrl;
    protected $em;
    protected $router;

    public function __construct($communicationService, $serviceUrl, $em, $router)
    {
        $this->communicationService = $communicationService;
        $this->serviceUrl = $serviceUrl;
        $this->em = $em;
        $this->router = $router;
    }

    public function getParserResponse($parseUrl)
    {
        $url = $this->serviceUrl . "store/connector/_magic?url=" . $parseUrl . "&_apikey=cb4f555a-45ec-4f56-a7a8-16dfe8a4a4e7%3AvP0UT%2Fw6%2BH32IStRvpikzzZ9roEqG0DkbUgACO7HWZlx9XXPwv7lv4poc8fvxkcuaEXVbQo4TQ7cJbYbivrpsA%3D%3D";
        $responseArray = $this->communicationService->callService("GET", $url);

        return $responseArray;
    }
//
//    public function getApiKey($password) {
//        $url = $this->serviceUrl . "auth/apikeyadmin?password=" . $password;
//
//        $responseArray = $this->communicationService->callService("GET", $url);
//
//        return $responseArray;
//    }

}

