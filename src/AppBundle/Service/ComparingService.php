<?php

namespace AppBundle\Service;

use AppBundle\Entity\Offer;

class ComparingService
{

    private $em;
    private $logger;

    public function __construct($em, $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
    }

    public function compareContent($parsedOffers)
    {
        $initialOffers = $this->em->getRepository('AppBundle:Offer')->getAllOffers(array(
            Offer::OFFER_PRIVES,
            Offer::OFFER_PUBLIC_ACCESS,
            Offer::OFFER_HIDE
        ));
        //check if the sent array is not empty
        if (empty($parsedOffers)) {
            return;
        }

        foreach ($initialOffers as $offer) {
            $segment = $offer->getOfferType() == Offer::OFFER_PRIVES ? $offer->getSegments()->first()->getSegment() :
                ($offer->getOfferType() == Offer::OFFER_PUBLIC_ACCESS ? (Offer::OFFER_PUBLIC_ACCESS . ' ' . $offer->getLanguage()) :
                    (Offer::OFFER_HIDE . ' ' . $offer->getLanguage()));
            $code = $offer->getOfferCode();
            //check if the offers parsed are in the database too
            if (empty($parsedOffers[$segment][$code])) {
                try {
                    //if there was an initial offer stored in the database, but it was
                    //removed from the site, remove that offer from the database too
                    $this->em->remove($offer);

                    //logging removal of offer
                    $this->logger->addNotice("Offre - $segment  ($code) a été supprimée de la bdd.");
                    continue;
                } catch (\ErrorException $e) {
                    //logging error on removal of offer
                    $this->logger->addError("Error - $segment  ($code) a été supprimée de la bdd.");
                }
            }

            $initialOffer = clone $offer;
            $offer = $this->updateOffer($offer, $parsedOffers[$segment][$code]);

            //check if something changed in the offer parsed from the
            //site that corresponds to an offer existent in our database
            if (!$this->compareOffers($offer, $initialOffer)) {
                //if something changed, update the database
                try {
                    $this->em->persist($offer);
                    //logging offer update
                    $this->logger->addNotice("Offre - $segment  ($code) a changé dans la bdd.");
                } catch (\ErrorException $e) {
                    //logging offer update error
                    $this->logger->addError("Error - $segment ($code) a changé dans la bdd.");
                }
            } else {
                $this->em->detach($offer);
            }
            //the offer was updated, so we remove it from the array
            //so that later on, we can check if there are new offers parsed
            unset($parsedOffers[$segment][$code]);
        }

        //at this moment, offers array contains just the new parsed offers
        $this->createNewOffers($parsedOffers);

        $this->em->flush();
    }

    private function createNewOffers($offers)
    {
        $newOffers = array();
        $entityManager = $this->em;
        $logger = $this->logger;
        array_walk_recursive($offers, function($value, $key) use($offers, &$newOffers, $entityManager, $logger) {
                if (!empty($value)) {
                    try {
                        $code = $value->getOfferCode();
                        $segment = $value->getSegments()->first()->getSegment();
                        $subSegment = substr($segment, 0, -3);

                        if ($subSegment == Offer::OFFER_HIDE || $subSegment == Offer::OFFER_PUBLIC_ACCESS) {
                            $value->setSegments(NULL);
                        }

                        $entityManager->persist($value);

                        //logging new insertion in DB
                        $logger->addNotice("Offre - $segment  ($code) a été inséré dans la bdd.");
                    } catch (\ErrorException $e) {
                        //logging error from insertion in DB
                        $logger->addError("Error - $segment  ($code) a été inséré dans la bdd.");
                    }
                }
            });
    }

    private function updateOffer($initialOffer, $offer)
    {
        $initialOffer->setOfferType($offer->getOfferType());
        $initialOffer->setHotelName($offer->getHotelName());
        $initialOffer->setDestinationName($offer->getDestinationName());
        $initialOffer->setImages($offer->getImages());
        $initialOffer->setOfferLink($offer->getOfferLink());
        $initialOffer->setDestinationLink($offer->getDestinationLink());
        $initialOffer->setHotelLink($offer->getHotelLink());
        $initialOffer->setOfferDescription($offer->getOfferDescription());
        $initialOffer->setOldPrice($offer->getOldPrice());
        $initialOffer->setNewPrice($offer->getNewPrice());
        $initialOffer->setBookingLink($offer->getBookingLink());
        $initialOffer->setLabel($offer->getLabel());
        $initialOffer->setIntroduction($offer->getIntroduction());
        $initialOffer->setDetail($offer->getDetail());
        $initialOffer->setReservation($offer->getReservation());

        return $initialOffer;
    }

    private function compareOffers($offer, $initialOffer)
    {
        //check if the image changed
        foreach ($initialOffer->getImages() as $key => $image) {
            $offerImages = $offer->getImages();
            if (!($offerImages[$key] instanceof \AppBundle\Entity\Image) || ($offerImages[$key]->getImageLink() != $image->getImageLink())) {
                return false;
            }
        }
        //check all the other offer properties for changes
        $properties = array(
            'OfferType',
            'HotelName',
            'DestinationName',
            'OfferLink',
            'DestinationLink',
            'HotelLink',
            'OfferDescription',
            'OldPrice',
            'NewPrice',
            'BookingLink',
            'Label',
            'Introduction',
            'Detail',
            'Reservation'
        );
        foreach ($properties as $property) {
            $getter = 'get' . $property;
            if ($initialOffer->{$getter}() != $offer->{$getter}()) {
                return false;
            }
        }

        return true;
    }

}