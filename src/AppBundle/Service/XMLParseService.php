<?php

namespace AppBundle\Service;

use AppBundle\Entity\Offer;
use AppBundle\Entity\Image;
use AppBundle\Entity\Segment;

class XMLParseService
{

    protected $compareService;
    private $em;
    private $logger;
    private $xmlToParse;

    public function __construct($compareService, $logger, $em, $xmlToParse)
    {
        $this->compareService = $compareService;
        $this->logger = $logger;
        $this->em = $em;
        $this->xmlToParse = $xmlToParse;
    }

    public function parseXmlContent()
    {
        $xmlDom = new \DOMDocument();
        $xmlDom->loadXML(file_get_contents($this->xmlToParse));
        $xmlObject = new \SimpleXMLElement($xmlDom->saveXML());

        $offers = array();
        $segments = $this->em->getRepository("AppBundle:Segment")->findAll();

        if (empty($xmlDom)) {
            $this->logger->addError("Error - La page n’est pas trouvée. Segment: " . $segment->getSegment());
        }

        //take all the segments parameters
        foreach ($segments as $segment) {
            $blocks[$segment->getSegment()]['object'] = $xmlObject;
            $blocks[$segment->getSegment()]['segment'] = $segment;
        }
        //add Tous publics Offer Types for EN and FR
        $offerPublicAccessSegmentEn = new Segment();
        $offerPublicAccessSegmentEn->setSegment(Offer::OFFER_PUBLIC_ACCESS . Offer::EN);
        $blocks[Offer::OFFER_PUBLIC_ACCESS . Offer::EN]['object'] = $xmlObject;
        $blocks[Offer::OFFER_PUBLIC_ACCESS . Offer::EN]['segment'] = $offerPublicAccessSegmentEn;
        $offerPublicAccessSegmentFr = new Segment();
        $offerPublicAccessSegmentFr->setSegment(Offer::OFFER_PUBLIC_ACCESS . Offer::FR);
        $blocks[Offer::OFFER_PUBLIC_ACCESS . Offer::FR]['object'] = $xmlObject;
        $blocks[Offer::OFFER_PUBLIC_ACCESS . Offer::FR]['segment'] = $offerPublicAccessSegmentFr;
        //add Hidden Offer Types (no Offer Type specified in XML) for EN and FR
        $offerHideSegmentEn = new Segment();
        $offerHideSegmentEn->setSegment(Offer::OFFER_HIDE . Offer::EN);
        $blocks[Offer::OFFER_HIDE . Offer::EN]['object'] = $xmlObject;
        $blocks[Offer::OFFER_HIDE . Offer::EN]['segment'] = $offerHideSegmentEn;
        $offerHideSegmentFr = new Segment();
        $offerHideSegmentFr->setSegment(Offer::OFFER_HIDE . Offer::FR);
        $blocks[Offer::OFFER_HIDE . Offer::FR]['object'] = $xmlObject;
        $blocks[Offer::OFFER_HIDE . Offer::FR]['segment'] = $offerHideSegmentFr;

        //iterate the blocks array, which contains the segments and the DOMNodeList objects
        foreach ($blocks as $segment => $block) {
            //walk the block (DOMNodeList objects) array and get the elements from it
            foreach ($block['object']->Offers->Offer as $offer) {
                //get each html element and create new Offer objects
                $blockElements = $this->getBlockElements($offer);

                $blockOfferType = substr($block['segment']->getSegment(), 0, -3);
                if (in_array($blockOfferType, $blockElements['accessTypes'])) {//has the curently parsed access type
                    $offerType = $blockOfferType == Offer::OFFER_PUBLIC_ACCESS ? Offer::OFFER_PUBLIC_ACCESS : Offer::OFFER_PRIVES;
                    $offer = $this->createOfferFromBlock($blockElements, $block['segment'], $offerType);
                    //build the offers array
                    $offers[$segment][$offer->getOfferCode()] = $offer;
                } elseif (in_array('', $blockElements['accessTypes']) && $blockOfferType == Offer::OFFER_HIDE) {//Hidden Offers check
                    $offer = $this->createOfferFromBlock($blockElements, $block['segment'], Offer::OFFER_HIDE);
                    //build the offers array
                    $offers[$segment][$offer->getOfferCode()] = $offer;
                }
            }

            foreach ($block['object']->OffersLocalized->OfferLocalized as $offerLocalized) {
                $blockElements = $this->getBlockElements($offerLocalized);
                if ($blockElements['language'] == substr($segment, -2)) {
                    if (isset($offers[$segment][$blockElements['offerCode']])) {
                        $offer = $this->updateOfferFromBlock($blockElements, $offers[$segment][$blockElements['offerCode']]);
                        //update the offers array
                        $offers[$segment][$offer->getOfferCode()] = $offer;
                    }
                }
            }

            foreach ($block['object']->Hotels->Hotel as $hotel) {
                $blockElements = $this->getBlockElements($hotel);
                if (isset($offers[$segment])) {
                    $offersToUpdate = $this->getOffersToUpdate($blockElements, $offers[$segment], $segment);

                    foreach ($offersToUpdate as $offerToUpdate) {
                        //update the offers array
                        $offers[$segment][$offerToUpdate->getOfferCode()] = $offerToUpdate;
                    }
                }
            }

            foreach ($block['object']->HotelsLocalized->HotelLocalized as $hotelLocalized) {
                $blockElements = $this->getBlockElements($hotelLocalized);

                if (isset($offers[$segment]) && $blockElements['language'] == substr($segment, -2)) {
                    $offersToUpdate = $this->getOffersToUpdate($blockElements, $offers[$segment], $segment);

                    foreach ($offersToUpdate as $offerToUpdate) {
                        //update the offers array
                        $offers[$segment][$offerToUpdate->getOfferCode()] = $offerToUpdate;
                    }
                }
            }

            foreach ($block['object']->PlacesLocalized->PlaceLocalized as $placeLocalized) {
                $blockElements = $this->getBlockElements($placeLocalized);
                if (isset($offers[$segment]) && isset($blockElements['language']) && $blockElements['language'] == substr($segment, -2)) {
                    $offersToUpdate = $this->getOffersToUpdate($blockElements, $offers[$segment], $segment);

                    foreach ($offersToUpdate as $offerToUpdate) {
                        //update the offers array
                        $offers[$segment][$offerToUpdate->getOfferCode()] = $offerToUpdate;
                    }
                }
            }

            foreach ($block['object']->ItemsPublicUrls->PublicUrls as $publicUrls) {
                $blockElements = $this->getBlockElements($publicUrls);

                if (isset($offers[$segment]) && isset($blockElements['language']) && $blockElements['language'] == substr($segment, -2)) {
                    $offersToUpdate = $this->getOffersToUpdate($blockElements, $offers[$segment], $segment);

                    foreach ($offersToUpdate as $offerToUpdate) {
                        //update the offers array
                        $offers[$segment][$offerToUpdate->getOfferCode()] = $offerToUpdate;
                    }
                }
            }
        }

        $this->compareService->compareContent($offers);
    }

    public function getBlockElements($offer)
    {
        $blockElements = array();

        //NEW
        foreach ($offer->attributes() as $label => $value) {
            //offer code - Offer
            if ($label == 'code') {
                $blockElements['offerCode'] = (string) $value;
            }
            //hotel code - Offer / HotelLocalized
            if ($label == 'hotel') {
                $blockElements['hotelCode'] = (string) $value;
            }
            //offer code - OfferLocalized
            if ($label == 'offerCode') {
                $blockElements['offerCode'] = (string) $value;
            }
            //language - OfferLocalized
            if ($label == 'language') {
                $blockElements['language'] = (string) $value;
            }
            //hotel name - HotelLocalized
            if ($label == 'name') {
                $blockElements['hotelName'] = (string) $value;
            }
        }
        if ($offer->OperaMinAmount) {
            //new price - Offer/OperaMinAmount
            foreach ($offer->OperaMinAmount->attributes() as $label => $value) {
                if ($label == 'price') {
                    $blockElements['newPrice'] = (string) $value;
                }
            }
        }
        //old price - Offer/OperaComparedAmount
        if ($offer->OperaComparedAmount) {
            foreach ($offer->OperaComparedAmount->attributes() as $label => $value) {
                if ($label == 'price') {
                    $blockElements['oldPrice'] = (string) $value;
                }
            }
        }
        //access types - Offer/AccessTypes
        if ($offer->AccessTypes) {
            foreach ($offer->AccessTypes as $accessTypes) {
                $accessTypesArray = (array) $accessTypes->AccessType;
                if (!empty($accessTypes)) {
                    foreach ($accessTypesArray as $accessType) {
                        $blockElements['accessTypes'][] = $accessType;
                    }
                } else {//special case for empty AccessTypes / missing AccessType
                    $blockElements['accessTypes'][] = (string) $accessTypes->AccessType;
                }
            }
        }
        //authorized rate codes - Offer/OperaRateCode - required for Booking link
        if ($offer->OperaRateCode) {
            foreach ($offer->OperaRateCode as $rateCode) {
                foreach ($rateCode->attributes() as $label => $value) {
                    if ($label == "rateCode") {
                        $blockElements['rateCode'] = (string) $value;
                    }
                }
            }
        }
        //image link - Hotel/FirstPictureFromSmallList
        if ($offer->FirstPictureFromSmallList) {
            foreach ($offer->FirstPictureFromSmallList->attributes() as $label => $value) {
                if ($label == "absolutePath") {
                    $blockElements['image'] = (string) $value;
                }
            }
        }
        //offer description - OfferLocalized/TagLine or OfferLocalized/RawTagLine
        if ($offer->RawTagLine) {
            $blockElements['offerDescription'] = (string) $offer->RawTagLine;
        }
        //hotel name - PublicUrls / Hotel
        if ($offer->Code) {
            $blockElements['hotelCode'] = (string) $offer->Code;
        }
        //hotel place - Hotel / PlaceLocalized
        if ($offer->Place) {
            $blockElements['destinationCode'] = (string) $offer->Place;
        }
        //hotel link & language - PublicUrls
        if ($offer->UrlPerLanguage) {
            //UrlType == homepage
            if ("homepage" == (string) $offer->UrlType) {
                $hotelLinkBlock = $offer->UrlPerLanguage->DictionaryEntryOfStringString;
                //hotel link - PublicUrls
                $blockElements['hotelLink'] = str_replace(Offer::HOTEL_LINK_EXTRA, "", (string) $hotelLinkBlock->Value);

                foreach ($hotelLinkBlock->attributes() as $label => $value) {
                    //language - PublicUrls
                    if ($label == 'key') {
                        $blockElements['language'] = (string) $value;
                    }
                }
            }
        }
        //destination name - PlaceLocalized
        //language & code for mapping - PlaceLocalized
        if ($offer->Name && $offer->Language) {
            $blockElements['destinationName'] = (string) $offer->Name;
            $blockElements['language'] = (string) $offer->Language;
        }

        return $blockElements;
    }

    public function getOffersToUpdate($blockElements, $offers, $segment)
    {
        $offersToUpdate = array();
        if (isset($blockElements['hotelCode'])) {
            foreach ($offers as $offer) {
                if ($blockElements['hotelCode'] == $offer->getHotelCode()) {
                    $offersToUpdate[] = $this->updateOfferFromBlock($blockElements, $offer);
                }
            }
        } else {
            foreach ($offers as $offer) {
                if ($blockElements['destinationCode'] == $offer->getDestinationCode()) {
                    $offersToUpdate[] = $this->updateOfferFromBlock($blockElements, $offer);
                }
            }
        }

        return $offersToUpdate;
    }

    //replaces special characters and spaces with '-'
    public function createDestinationLink($string)
    {
        $pattern = array("'é'", "'è'", "'ë'", "'ê'", "'É'", "'È'", "'Ë'", "'Ê'", "'á'", "'à'", "'ä'", "'â'", "'å'", "'Á'", "'À'", "'Ä'", "'Â'", "'Å'", "'ó'", "'ò'", "'ö'", "'ô'", "'Ó'", "'Ò'", "'Ö'", "'Ô'", "'í'", "'ì'", "'ï'", "'î'", "'Í'", "'Ì'", "'Ï'", "'Î'", "'ú'", "'ù'", "'ü'", "'û'", "'Ú'", "'Ù'", "'Ü'", "'Û'", "'ý'", "'ÿ'", "'Ý'", "'ø'", "'Ø'", "'œ'", "'Œ'", "'Æ'", "'ç'", "'Ç'", "' '");
        $replace = array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E', 'a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A', 'A', 'o', 'o', 'o', 'o', 'O', 'O', 'O', 'O', 'i', 'i', 'i', 'I', 'I', 'I', 'I', 'I', 'u', 'u', 'u', 'u', 'U', 'U', 'U', 'U', 'y', 'y', 'Y', 'o', 'O', 'a', 'A', 'A', 'c', 'C', '-');

        return preg_replace($pattern, $replace, $string) . Offer::LINK_HTML;
    }

    public function updateOfferFromBlock($blockElements, $offer)
    {
        if (isset($blockElements['language'])) {
            $languageInLink = ($blockElements['language'] == "EN") ? Offer::LINK_EN : Offer::LINK_FR;
            $offer->setLanguage(isset($blockElements['language']) ? $blockElements['language'] : $offer->getLanguage());
            //https://booking.lucienbarriere.com/lbwebbooking/?h=ENGGE&l=en-GB&rc=AVA&of=ENGGE-006
            $offer->setBookingLink(Offer::BOOKING_LINK_BASE .
                "?h=" . $offer->getHotelCode() .
                "&l=" . $languageInLink .
                "&rc=" . $offer->getRateCode() .
                "&of=" . $offer->getOfferCode());
        }

        $offer->setDestinationCode(isset($blockElements['destinationCode']) ? $blockElements['destinationCode'] : $offer->getDestinationCode());
        $offer->setDestinationName(isset($blockElements['destinationName']) ? $blockElements['destinationName'] : $offer->getDestinationName());
        $offer->setDestinationLink(isset($blockElements['destinationName']) ?
                $this->createDestinationLink($blockElements['destinationName']) : $offer->getDestinationLink());
        $offer->setHotelName(isset($blockElements['hotelName']) ? $blockElements['hotelName'] : $offer->getHotelName());
        $offer->setHotelLink(isset($blockElements['hotelLink']) ? $blockElements['hotelLink'] : $offer->getHotelLink());
        $offer->setOfferDescription(isset($blockElements['offerDescription']) ? $blockElements['offerDescription'] : $offer->getOfferDescription());

        if (isset($blockElements['image'])) {
            $image = new Image($blockElements['image']);
            $offer->addImage($image);
        }

        return $offer;
    }

    public function createOfferFromBlock($blockElements, $segment, $type = null)
    {

        $offer = new Offer();

        $offer->addSegment($segment);
        $offer->setOfferCode($blockElements['offerCode']);
        $offer->setHotelCode($blockElements['hotelCode']);
        $offer->setRateCode($blockElements['rateCode']);
        $offer->setOfferLink(Offer::OFFER_LINK_BASE . $blockElements['offerCode'] . Offer::LINK_HTML);
        $offer->setOldPrice(isset($blockElements['oldPrice']) ? $blockElements['oldPrice'] : null);
        $offer->setNewPrice(isset($blockElements['newPrice']) ? $blockElements['newPrice'] : null);
        $offer->setOfferType($type);

        return $offer;
    }

}