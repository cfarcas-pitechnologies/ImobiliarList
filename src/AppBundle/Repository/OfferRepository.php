<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Offer;
use Doctrine\ORM\EntityRepository;

class OfferRepository extends EntityRepository {

    public function getFilteredOffers($options, $asQuery = false) {

        $qb = $this->createQueryBuilder('o')
                ->where('o.offerLink IS NOT NULL');

        if (array_key_exists('priceSort', $options)) {
            $qb->orderBy('o.price', $options['priceSort']);
        } else {
            $qb->orderBy('o.price', 'ASC');
        }
        if (array_key_exists('searchType', $options) && ($options['searchType'] != 'all')) {
            $qb->andWhere('o.type = :type')
                    ->setParameter('type', $options['searchType']);
        }
        if (array_key_exists('location', $options)) {
            $qb->andWhere($qb->expr()->like('o.title', ':location')
                    )
                    ->orWhere($qb->expr()->like('o.description', ':location')
                    )
                    ->setParameter('location', '%' . $options['location'] . '%');
        }
        if (array_key_exists('county', $options)) {
            $qb->andWhere($qb->expr()->like('o.county', ':county')
                    )
                    ->setParameter('county', '%' . $options['county'] . '%');
        }
        if (array_key_exists('propertyType', $options)) {
            $qb->andWhere($qb->expr()->like('o.propertyType', ':propertyType')
                    )
                    ->setParameter('propertyType', '%' . $options['propertyType'] . '%');
        }
        if (array_key_exists('minSurface', $options)) {
            $qb->andWhere($qb->expr()->gte('o.surface', ':minSurface')
                    )
                    ->setParameter('minSurface', $options['minSurface']);
        }
        if (array_key_exists('maxSurface', $options)) {
            $qb->andWhere($qb->expr()->lte('o.surface', ':maxSurface')
                    )
                    ->setParameter('maxSurface', $options['maxSurface']);
        }
        if (array_key_exists('rooms', $options)) {
            $qb->andWhere('o.rooms = :rooms')
                    ->setParameter('rooms', $options['rooms']);
        }
        if (array_key_exists('bathrooms', $options)) {
            $qb->andWhere('o.bathrooms = :bathrooms')
                    ->setParameter('bathrooms', $options['bathrooms']);
        }
        if (array_key_exists('minPrice', $options)) {
            $qb->andWhere($qb->expr()->gte('o.price', ':minPrice')
                    )
                    ->setParameter('minPrice', $options['minPrice']);
        }
        if (array_key_exists('maxPrice', $options)) {
            $qb->andWhere($qb->expr()->lte('o.price', ':maxPrice')
                    )
                    ->setParameter('maxPrice', $options['maxPrice']);
        }
        if (array_key_exists('floor', $options)) {
            $qb->andWhere($qb->expr()->eq('o.floor', ':floor')
                    )
                    ->setParameter('floor', $options['floor']);
        }
        if (array_key_exists('contactType', $options)) {
            $qb->andWhere($qb->expr()->like('o.contactType', ':contactType')
                    )
                    ->setParameter('contactType', '%' . $options['contactType'] . '%');
        }

        return $asQuery ? $qb->getQuery() : $qb->getQuery()->getResult();
    }

    public function getAllOffersQuery($options = array(), $asQuery = true) {
        $qb = $this->createQueryBuilder('o')
                ->where('o.offerLink IS NOT NULL');

        if (array_key_exists('priceSort', $options)) {
            $qb->orderBy('o.price', $options['priceSort']);
        } else {
            $qb->orderBy('o.price', 'ASC');
        }

        return $asQuery ? $qb->getQuery() : $qb->getQuery()->getResult();
    }

}
