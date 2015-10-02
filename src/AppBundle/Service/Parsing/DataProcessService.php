<?php

namespace AppBundle\Service\Parsing;

use Symfony\Component\Yaml\Parser;
use AppBundle\Entity\Offer;

class DataProcessService
{

    protected $em;
    protected $wsEntityMapping;

    public function __construct($em)
    {
        $this->em = $em;

        $parser = new Parser();
        $mappings = $parser->parse(file_get_contents(__DIR__ . '../../../Resources/config/ws_entity_mapping.yml'));
        $this->wsEntityMapping = $mappings['entities'];
    }

    public function prepareDataForSaving($data, $url)
    {
        //verify if the response is multiple or not
        if (isset($this->wsEntityMapping[$url])) {
            foreach ($data as $value) {
                $this->saveData($value, $url);
            }
        }

        $this->em->flush();

        return true;
    }

    protected function saveData($data, $url)
    {
        //transform the data for DB format from webservice format to save the data
        $transformedData = $this->prepareDataForMapping($data, $url);
        //create an object for saving the data
        $transformedData = $this->denormalize($transformedData);
        $this->em->persist($transformedData);

        return $transformedData;
    }

    protected function prepareDataForMapping($data, $url)
    {
        if (!isset($this->wsEntityMapping[$url])) {
            return $data;
        }

        $mapping = $this->wsEntityMapping[$url];
        $data = $this->getDBFormat($data, $mapping);

        return $data;
    }

    //the function below transforms the data from WS format to DB format
    public function getDBFormat($data, $mapping)
    {
        $array = array();
        foreach ($mapping as $index => $value) {
            if (is_array($value)) {
                if (is_array($data[$value[0]]) && (array_key_exists($value[1], $data[$value[0]]))) {
                    $array[$index] = $data[$value[0]][$value[1]];
                }
                continue;
            }
            if (array_key_exists($value, $data)) {
                if (($index == 'title') && (stripos($data[$value], Offer::APARTMENT_TYPE) !== false)) {
                    $array['propertyType'] = Offer::APARTMENT_TYPE;
                } elseif (($index == 'title') && ((stripos($data[$value], Offer::HOUSE_TYPE) !== false) || (stripos($data[$value], Offer::MANSION_TYPE) !== false))) {
                    $array['propertyType'] = Offer::HOUSE_TYPE;
                }
                $array[$index] = $data[$value];
                continue;
            }
            if (!empty($value)) {
                $array[$index] = $value;
            }
        }

        return $array;
    }

    public function denormalize($data)
    {
        //get object if exists else create a new
        $object = $this->em->getRepository('AppBundle:Offer')->findOneBy(array(
            'title' => $data['title'],
            'offerLink' => $data['offerLink'],
        ));

        if (!($object instanceof Offer)) {
            $object = new Offer;
        }

        foreach ($data as $key => $value) {
            $setter = 'set' . ucfirst($key);
            if (method_exists($object, $setter)) {
                $object->{$setter}($value);
            }
        }

        return $object;
    }

}