<?php

namespace App\Form;

use App\Repository\GeoCityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchFormType extends AbstractType
{
    private GeoCityRepository $cities;

    public function __construct(GeoCityRepository $cities)
    {
        $this->cities = $cities;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('countryCode', type: ChoiceType::class, options: [
                'label' => 'Country',
                'required' => true,
                'choices' => $this->loadCountries(),
                'preferred_choices' => ['US']
            ])
            ->add('searchType', type: ChoiceType::class, options: [
                'label' => 'Search using',
                'required' => true,
                'expanded' => true,
                'multiple' => false,
                'choices' => [
                    'City' => 'city',
                    'Postal Code' => 'postal',
                ],
            ])
            ->add('searchText', options: [
                'label' => 'Search Text',
                'required' => true,
            ])
            ->add('submit', type: SubmitType::class)
        ;
    }

    private function loadCountries(): array
    {
        $countryCodes = $this->cities->findUniqueCountryCodes();
        $countryOptions = [];
        foreach ($countryCodes as $code) {
            $displayName = \Locale::getDisplayRegion('-' . $code, 'US');
            $countryOptions[$displayName] = $code;
        }
        asort($countryOptions);
        return $countryOptions;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'method' => 'get',
            'action' => '/search',
            'csrf_protection' => false,
        ]);
    }
}