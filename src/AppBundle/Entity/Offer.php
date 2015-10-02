<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\OfferRepository")
 * @ORM\Table(name="offers")
 */
class Offer
{

    const APARTMENT_TYPE = 'apartament';
    const HOUSE_TYPE = 'casa';
    const MANSION_TYPE = 'vila';
    const SELL_TYPE = 'sell';
    const RENT_TYPE = 'rent';

    /**
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     * @Assert\NotBlank()
     */
    protected $title;

    /**
     * @ORM\Column(name="price", type="string", length=255, nullable=false)
     * @Assert\NotBlank()
     */
    protected $price;

    /**
     * @ORM\Column(name="price_symbol", type="string", length=1, nullable=true)
     */
    protected $priceSymbol;

    /**
     * @ORM\Column(name="offer_link", type="string", length=255, nullable=false)
     * @Assert\NotBlank()
     */
    protected $offerLink;
//
//    /**
//     * @ORM\ManyToMany(targetEntity="Image", orphanRemoval=true, cascade={"persist"} )
//     * @ORM\JoinTable(name="offer_images",
//     *      joinColumns={@ORM\JoinColumn(name="offer_id", referencedColumnName="id")},
//     *      inverseJoinColumns={@ORM\JoinColumn(name="image_id", referencedColumnName="id", unique=true)}
//     *      )
//     **/
//    protected $images;

    /**
     * @ORM\Column(name="image_link", type="string", length=255, nullable=true)
     */
    protected $imageLink;

    /**
     * @ORM\Column(name="county", type="string", length=255, nullable=true)
     */
    protected $county;

    /**
     * @ORM\Column(name="location", type="string", length=255, nullable=true)
     */
    protected $location;

    /**
     * @ORM\Column(name="property_type", type="string", length=255, nullable=true)
     */
    protected $propertyType;

    /**
     * @ORM\Column(name="surface", type="string", length=255, nullable=true)
     */
    protected $surface;

    /**
     * @ORM\Column(name="rooms", type="string", length=255, nullable=true)
     */
    protected $rooms;

    /**
     * @ORM\Column(name="bathrooms", type="string", length=255, nullable=true)
     */
    protected $bathrooms;

    /**
     * @ORM\Column(name="floor", type="string", length=255, nullable=true)
     */
    protected $floor;

    /**
     * @ORM\Column(name="description", type="string", length=255, nullable=true)
     */
    protected $description;

    /**
     * @ORM\Column(name="contact_type", type="string", length=255, nullable=true)
     */
    protected $contactType;
    
    /**
     * @ORM\Column(name="type", type="string", length=255, nullable=true)
     */
    protected $type;
    


//    /**
//     * Constructor
//     */
//    public function __construct()
//    {
//        $this->images = new \Doctrine\Common\Collections\ArrayCollection();
//    }

    public function __toString()
    {
        return $this->id ? "Offer {$this->id}" : "New Offer";
    }


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Offer
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set price
     *
     * @param string $price
     *
     * @return Offer
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return string
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set priceSymbol
     *
     * @param string $priceSymbol
     *
     * @return Offer
     */
    public function setPriceSymbol($priceSymbol)
    {
        $this->priceSymbol = $priceSymbol;

        return $this;
    }

    /**
     * Get priceSymbol
     *
     * @return string
     */
    public function getPriceSymbol()
    {
        return $this->priceSymbol;
    }

    /**
     * Set offerLink
     *
     * @param string $offerLink
     *
     * @return Offer
     */
    public function setOfferLink($offerLink)
    {
        $this->offerLink = $offerLink;

        return $this;
    }

    /**
     * Get offerLink
     *
     * @return string
     */
    public function getOfferLink()
    {
        return $this->offerLink;
    }

    /**
     * Set imageLink
     *
     * @param string $imageLink
     *
     * @return Offer
     */
    public function setImageLink($imageLink)
    {
        $this->imageLink = $imageLink;

        return $this;
    }

    /**
     * Get imageLink
     *
     * @return string
     */
    public function getImageLink()
    {
        return $this->imageLink;
    }

    /**
     * Set county
     *
     * @param string $county
     *
     * @return Offer
     */
    public function setCounty($county)
    {
        $this->county = $county;

        return $this;
    }

    /**
     * Get county
     *
     * @return string
     */
    public function getCounty()
    {
        return $this->county;
    }

    /**
     * Set location
     *
     * @param string $location
     *
     * @return Offer
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location
     *
     * @return string
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Set propertyType
     *
     * @param string $propertyType
     *
     * @return Offer
     */
    public function setPropertyType($propertyType)
    {
        $this->propertyType = $propertyType;

        return $this;
    }

    /**
     * Get propertyType
     *
     * @return string
     */
    public function getPropertyType()
    {
        return $this->propertyType;
    }

    /**
     * Set surface
     *
     * @param string $surface
     *
     * @return Offer
     */
    public function setSurface($surface)
    {
        $this->surface = $surface;

        return $this;
    }

    /**
     * Get surface
     *
     * @return string
     */
    public function getSurface()
    {
        return $this->surface;
    }

    /**
     * Set rooms
     *
     * @param string $rooms
     *
     * @return Offer
     */
    public function setRooms($rooms)
    {
        $this->rooms = $rooms;

        return $this;
    }

    /**
     * Get rooms
     *
     * @return string
     */
    public function getRooms()
    {
        return $this->rooms;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Offer
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set contactType
     *
     * @param string $contactType
     *
     * @return Offer
     */
    public function setContactType($contactType)
    {
        $this->contactType = $contactType;

        return $this;
    }

    /**
     * Get contactType
     *
     * @return string
     */
    public function getContactType()
    {
        return $this->contactType;
    }
//
//    /**
//     * Add image
//     *
//     * @param \AppBundle\Entity\Image $image
//     *
//     * @return Offer
//     */
//    public function addImage(\AppBundle\Entity\Image $image)
//    {
//        $this->images[] = $image;
//
//        return $this;
//    }
//
//    /**
//     * Remove image
//     *
//     * @param \AppBundle\Entity\Image $image
//     */
//    public function removeImage(\AppBundle\Entity\Image $image)
//    {
//        $this->images->removeElement($image);
//    }
//
//    /**
//     * Get images
//     *
//     * @return \Doctrine\Common\Collections\Collection
//     */
//    public function getImages()
//    {
//        return $this->images;
//    }

    /**
     * Set bathrooms
     *
     * @param string $bathrooms
     *
     * @return Offer
     */
    public function setBathrooms($bathrooms)
    {
        $this->bathrooms = $bathrooms;

        return $this;
    }

    /**
     * Get bathrooms
     *
     * @return string
     */
    public function getBathrooms()
    {
        return $this->bathrooms;
    }

    /**
     * Set floor
     *
     * @param string $floor
     *
     * @return Offer
     */
    public function setFloor($floor)
    {
        $this->floor = $floor;

        return $this;
    }

    /**
     * Get floor
     *
     * @return string
     */
    public function getFloor()
    {
        return $this->floor;
    }
    
    /**
     * Set type
     *
     * @param string $type
     *
     * @return Offer
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

}
