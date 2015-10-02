<?php

namespace AppBundle\Service;

use AppBundle\Entity\Offer;
use AppBundle\Entity\Image;

class ParseService
{

    protected $compareService;
    private $em;
    private $logger;

    public function __construct($compareService, $logger, $em)
    {
        $this->compareService = $compareService;
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

    public function parseHtmlContent()
    {
        $dom = new \DOMDocument();
        $offers = array();

        $segments = $this->em->getRepository("AppBundle:Segment")->findAll();

        //take all the segments parameters
        foreach ($segments as $segment) {
            //get the html from the url (provided in the segments parameters) and then load them with the DOMDocument object
            $html = $this->getUrlHtml($segment->getUrl());

            if (empty($html)) {
                $this->logger->addError("Error - La page n’est pas trouvée. Segment: " . $segment->getSegment() . ", with url: " . $segment->getUrl());
            }

            @$dom->loadHTML($html);
            $xpath[$segment->getSegment()] = new \DOMXPath($dom);

            //get all the divs with the class "block-type-08", using xpath
            $blocks[$segment->getSegment()]['object'] = $xpath[$segment->getSegment()]->query('//div[contains(concat(" ", normalize-space(@class), " "), " block-type-08 ")]');
            $blocks[$segment->getSegment()]['segment'] = $segment;
        }

        //iterate the blocks array, which contains the segments and the DOMNodeList objects
        foreach ($blocks as $segment => $block) {
            //walk the block (DOMNodeList objects) array and get the elements from it
            foreach ($block['object'] as $element) {
                //get each html element and create new Offer objects
                $blockElements = $this->getBlockElements($xpath[$segment], $element);
                $offer = $this->createOfferFromBlock($blockElements, $block['segment'], Offer::OFFER_PRIVES);
                //build the offers array
                $offers[$segment][$offer->getOfferCode()] = $offer;
            }
        }

        $this->compareService->compareContent($offers);
    }

    public function getBlockElements($xpath, $block)
    {
        $blockElements = array();

        $blockElements['bookingLink'] = $xpath->query('.//div[@class="desc"]/a[2]/@href', $block);
        $blockElements['destination'] = $xpath->query('.//div[@class="title"]/p/a[1]', $block);
        $blockElements['destinationLink'] = $xpath->query('.//div[@class="title"]/p/a[1]/@href', $block);
        $blockElements['hotelName'] = $xpath->query('.//div[@class="title"]/p/a[2]', $block);
        $blockElements['hotelLink'] = $xpath->query('.//div[@class="title"]/p/a[2]/@href', $block);
        $blockElements['offerDescription'] = $xpath->query('.//div[@class="desc"]/p', $block);
        $blockElements['offerLink'] = $xpath->query('.//div[@class="desc"]/a/@href', $block);
        $blockElements['oldPrice'] = $xpath->query('.//div[@class="price"]/p/s', $block);
        $blockElements['newPrice'] = $xpath->query('.//div[@class="price"]/p/span', $block);
        $blockElements['image'] = $xpath->query('.//div[@class="inner"]/img/@src', $block);

        return $blockElements;
    }

    public function createOfferFromBlock($blockElements, $segment, $type = null)
    {
        $offer = new Offer();

        $offer->addSegment($segment);
        $offerCodePosition = strpos($blockElements['bookingLink']->item(0)->textContent, 'of=');
        $offerLanguagePosition = strpos($blockElements['bookingLink']->item(0)->textContent, 'l=');
        $offer->setOfferCode(substr($blockElements['bookingLink']->item(0)->textContent, $offerCodePosition + 3));
        $offer->setLanguage(substr($blockElements['bookingLink']->item(0)->textContent, $offerLanguagePosition + 2, 2));
        $offer->setBookingLink($blockElements['bookingLink']->length ? $blockElements['bookingLink']->item(0)->textContent : null);
        $offer->setDestinationName($blockElements['destination']->length ? $blockElements['destination']->item(0)->textContent : null);
        $offer->setDestinationLink($blockElements['destinationLink']->length ? $blockElements['destinationLink']->item(0)->textContent : null);
        $offer->setHotelName($blockElements['hotelName']->length ? $blockElements['hotelName']->item(0)->textContent : null);
        $offer->setHotelLink($blockElements['hotelLink']->length ? $blockElements['hotelLink']->item(0)->textContent : null);
        $offer->setOfferDescription($blockElements['offerDescription']->length ? $blockElements['offerDescription']->item(0)->textContent : null);
        $offer->setOfferLink($blockElements['offerLink']->length ? $blockElements['offerLink']->item(0)->textContent : null);
        $offer->setOldPrice($blockElements['oldPrice']->length ? str_replace('€', '', $blockElements['oldPrice']->item(0)->textContent) : null);
        $offer->setNewPrice($blockElements['newPrice']->length ? str_replace('€', '', $blockElements['newPrice']->item(0)->textContent) : null);
        $offer->setOfferType($type);

        $image = new Image($blockElements['image']->length ? $blockElements['image']->item(0)->textContent : null);
        $offer->addImage($image);

        return $offer;
    }

}


//
//namespace AppBundle\Service;
//
//use AppBundle\Entity\Offer;
//use AppBundle\Entity\Image;
//
//class PageParserService
//{
//
////    protected $compareService;
//    private $em;
//    private $logger;
//
//    public function __construct($logger, $em)
//    {
////        $this->compareService = $compareService;
//        $this->logger = $logger;
//        $this->em = $em;
//    }
//
//    public function getUrlHtml($url)
//    {
//        $curl = curl_init($url);
//        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
//        curl_setopt($curl, CURLOPT_FAILONERROR, true);
//        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US) AppleWebKit/534.10 (KHTML, like Gecko) Chrome/8.0.552.224 Safari/534.10');
//        $html = curl_exec($curl);
//        curl_close($curl);
//
//        return $html;
//    }
//
//    public function parseHtmlContent()
//    {
//        $dom = new \DOMDocument();
//        $offers = array();
//
////        $segments = $this->em->getRepository("AppBundle:Segment")->findAll();
//
//        $url = 'http://anaimobil.ro/';
//        //take all the segments parameters
////        foreach ($segments as $segment) {
//        //get the html from the url (provided in the segments parameters) and then load them with the DOMDocument object
//            $html = $this->getUrlHtml($url);
//
//        if (empty($html)) {
//                var_dump('esuat');
//                die;
//            }
//
//            @$dom->loadHTML($html);
//            $xpath = new \DOMXPath($dom);
//            $blocks = $xpath->query('//div[contains(concat(" ", normalize-space(@id), " "), " able-list ")]');
//        //get all the divs with the class "block-type-08", using xpath
//        //            $blocks[$segment->getSegment()]['object'] = $xpath[$segment->getSegment()]->query('//div[contains(concat(" ", normalize-space(@class), " "), " block-type-08 ")]');
////            $blocks[$segment->getSegment()]['segment'] = $segment;
////        }
//        //iterate the blocks array, which contains the segments and the DOMNodeList objects
////        foreach ($blocks as $segment => $block) {
//        //walk the block (DOMNodeList objects) array and get the elements from it
//            foreach ($blocks as $element) {
//                //get each html element and create new Offer objects
//                $blockElements = $this->getBlockElements($xpath, $element);
//                $offer = $this->createOfferFromBlock($blockElements);
//            //build the offers array
//                $offers[] = $offer;
//            }
//    //        }
//
//        var_dump('offers', $offers);
//        die;
////        $this->compareService->compareContent($offers);
//    }
//
//    public function getBlockElements($xpath, $block)
//    {
//        $blockElements = array();
//
//        $blockElements['image'] = $xpath->query('.//img/@src', $block);
//        $blockElements['link'] = $xpath->query('.//div[@class="imagewrapper"]/a/@href', $block);
////        var_dump('link', $blockElements['link']->item(0)->textContent);
////        die;
//        $blockElements['title'] = $xpath->query('.//h3/a/@title', $block);
////        var_dump($blockElements['title']->item(0)->textContent);die;
//        $blockElements['priceValue'] = $xpath->query('.//span[@class="price-value"]', $block);
//        $blockElements['priceSymbol'] = $xpath->query('.//span[@class="symbol"]', $block);
//        $blockElements['rooms'] = $xpath->query('.//div[@class="title-info"]/span[@class="line-top"]/span', $block);
////        $blockElements['rooms'] = $xpath->query('.//div[@class="title-info"]/span[@class="pull-right"]', $block);
//
////        var_dump($blockElements['price']->item(0)->textContent);die;
//        foreach ($blockElements as $key => $element) {
//            var_dump($key, $element->item(0)->textContent);
//        }
//            die;
////        $blockElements['destination'] = $xpath->query('.//div[@class="title"]/p/a[1]', $block);
////        $blockElements['destinationLink'] = $xpath->query('.//div[@class="title"]/p/a[1]/@href', $block);
////        $blockElements['hotelName'] = $xpath->query('.//div[@class="title"]/p/a[2]', $block);
////        $blockElements['hotelLink'] = $xpath->query('.//div[@class="title"]/p/a[2]/@href', $block);
////        $blockElements['offerDescription'] = $xpath->query('.//div[@class="desc"]/p', $block);
////        $blockElements['offerLink'] = $xpath->query('.//div[@class="desc"]/a/@href', $block);
////        $blockElements['oldPrice'] = $xpath->query('.//div[@class="price"]/p/s', $block);
////        $blockElements['newPrice'] = $xpath->query('.//div[@class="price"]/p/span', $block);
////        $blockElements['image'] = $xpath->query('.//div[@class="inner"]/img/@src', $block);
//
//        return $blockElements;
//    }
//
//    public function createOfferFromBlock($blockElements)
//    {
//        $offer = new Offer();
//
//        $offer->setOfferLink($blockElements['link']->length ? $blockElements['link']->item(0)->textContent : null);
//        $offer->setLabel($blockElements['title']->length ? $blockElements['title']->item(0)->textContent : null);
//        $offer->setNewPrice($blockElements['price']->length ? $blockElements['price']->item(0)->textContent : null);
//
//        $image = new Image($blockElements['image']->length ? $blockElements['image']->item(0)->textContent : null);
//        $offer->addImage($image);
//
////        $offer->setLanguage(substr($blockElements['bookingLink']->item(0)->textContent, $offerLanguagePosition + 2, 2));
////        $offer->setBookingLink($blockElements['bookingLink']->length ? $blockElements['bookingLink']->item(0)->textContent : null);
////        $offer->setDestinationName($blockElements['destination']->length ? $blockElements['destination']->item(0)->textContent : null);
////        $offer->setDestinationLink($blockElements['destinationLink']->length ? $blockElements['destinationLink']->item(0)->textContent : null);
////        $offer->setHotelName($blockElements['hotelName']->length ? $blockElements['hotelName']->item(0)->textContent : null);
////        $offer->setHotelLink($blockElements['hotelLink']->length ? $blockElements['hotelLink']->item(0)->textContent : null);
////        $offer->setOfferDescription($blockElements['offerDescription']->length ? $blockElements['offerDescription']->item(0)->textContent : null);
////        $offer->setOfferLink($blockElements['offerLink']->length ? $blockElements['offerLink']->item(0)->textContent : null);
////        $offer->setOldPrice($blockElements['oldPrice']->length ? str_replace('€', '', $blockElements['oldPrice']->item(0)->textContent) : null);
////        $offer->setNewPrice($blockElements['newPrice']->length ? str_replace('€', '', $blockElements['newPrice']->item(0)->textContent) : null);
////        $offer->setOfferType($type);
////
////        $image = new Image($blockElements['image']->length ? $blockElements['image']->item(0)->textContent : null);
////        $offer->addImage($image);
//
//        return $offer;
//    }
//
//}
//
