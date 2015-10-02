<?php

namespace AppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ImobiParserType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options) {
        $builder
                ->add('location', 'text', array(
                    'required' => false,
                    'attr' => array(
                        'placeholder' => "Locatie",
                    ),
                ))
                ->add('county', 'text', array(
                    'required' => false,
                    'attr' => array(
                        'placeholder' => "Judet",
                    ),
                ))
                ->add('propertyType', 'choice', array(
                    'empty_value' => "Tip proprietate",
                    'required' => false,
                    'choices' => array(
                        'apartament' => 'Apartament',
                        'casa' => "Casa",
                        'comercial' => "Comercial",
                    ),
                    'attr' => array(
                        'class' => 'minimal',
                    ),
                ))
                ->add('minSurface', 'text', array(
                    'required' => false,
                    'attr' => array(
                        'placeholder' => "Suprafata de la",
                    ),
                ))
                ->add('maxSurface', 'text', array(
                    'required' => false,
                    'attr' => array(
                        'placeholder' => "Suprafata pana la",
                    ),
                ))
                ->add('rooms', 'text', array(
                    'required' => false,
                    'attr' => array(
                        'placeholder' => "Numar de camere",
                    ),
                ))
                ->add('bathrooms', 'text', array(
                    'required' => false,
                    'attr' => array(
                        'placeholder' => "Numar de bai",
                    ),
                ))
                ->add('minPrice', 'text', array(
                    'required' => false,
                    'attr' => array(
                        'placeholder' => "Pret de la",
                    ),
                ))
                ->add('maxPrice', 'text', array(
                    'required' => false,
                    'attr' => array(
                        'placeholder' => "Pret pana la",
                    ),
                ))
                ->add('floor', 'number', array(
                    'required' => false,
                    'attr' => array(
                        'placeholder' => "Etaj",
                    ),
                    'empty_data' => 0,
                ))
                ->add('contactType', 'choice', array(
                    'empty_value' => "Persoana de contact",
                    'required' => false,
                    'choices' => array(
                        'person' => 'Persoana fizica',
                        'agency' => "Agentie",
                    ),
                    'attr' => array(
                        'class' => 'minimal',
                    ),
                ))
                ->add('searchType', 'text', array(
                    'required' => false,
                    'empty_data' => 'all',
                    'attr' => array(
                        'class' => 'search-type-input dn',
                    ),
                ))
        ;
    }

    public function getName() {
        return 'imobiparser';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver) {
        $resolver->setDefaults(array(
            'csrf_protection' => false,
        ));
    }

}
