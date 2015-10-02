<?php

namespace AppBundle\Service;

use AppBundle\Entity\Offer;
use AppBundle\Entity\Image;

class PageParserService
{

//    protected $compareService;
    private $em;
    private $logger;

    public function __construct($logger, $em)
    {
//        $this->compareService = $compareService;
        $this->logger = $logger;
        $this->em = $em;
    }

    public function getUrlHtml($url)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_FAILONERROR, true);
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/534.10 (KHTML, like Gecko) Chrome/8.0.552.224 Safari/534.10');
        $html = curl_exec($curl);
        curl_close($curl);

        return $html;
    }
}