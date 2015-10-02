<?php


namespace AppBundle\Service;


class ReservationService
{

    protected $em;

    public function __construct($em)
    {
        $this->em = $em;
    }

    public function adaptBookings($bookings, $locale)
    {
        $hotels = $this->em->getRepository('AppBundle:Hotel')->findByLocale($locale);
        $hotelsArray = array();
        $now = new \DateTime();

        foreach ($hotels as $hotel) {
            $hotelsArray[$hotel->getCode()] = $hotel;
        }

        foreach ($bookings as $key => &$booking) {
            $booking['DateBegin'] = new \DateTime($booking['DateBegin']);
            $booking['DateEnd'] = new \DateTime($booking['DateEnd']);
            $booking['hotel'] = array_key_exists($booking['HotelCode'], $hotelsArray) ? $hotelsArray[$booking['HotelCode']] : null;

            if ($booking['DateEnd'] < $now || (strtolower($booking['BookingStatus']) != 'reserved' && strtolower($booking['BookingStatus']) != 'waitlist')) {
                unset($bookings[$key]);
            }
        }

        return $bookings;
    }

}