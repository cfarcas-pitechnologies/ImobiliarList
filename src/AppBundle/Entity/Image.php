<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity()
 * @ORM\Table(name="images")
 */
class Image
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="image_link", type="string", length=255, nullable=true)
     */
    protected $imageLink;

    public function __construct($imageLink = null)
    {
        $this->imageLink = $imageLink;
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
     * Set imageLink
     *
     * @param string $imageLink
     * @return Image
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

    public function getUniqueIdentifier()
    {
        return $this->getImageLink();
    }

    public function getAbsoluteUrl()
    {
        $imageLink = $this->getImageLink();
        if (strpos($imageLink, 'http') === false) {
            $imageLink = 'http://www.lucienbarriere.com' . $imageLink;
        }

        return $imageLink;
    }

    public function __toString()
    {
        return $this->imageLink;
    }

}
