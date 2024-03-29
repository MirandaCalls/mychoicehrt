<?php

namespace App\Form;

use App\Repository\GeoCityRepository;
use App\SearchEngine\SearchEngineParams;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\AtLeastOneOf;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\NotBlank;

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
                'required' => true,
                'choices' => $this->loadCountries(),
                'preferred_choices' => ['US'],
                'constraints' => [
                    new NotBlank(message: 'This field is required.')
                ],
            ])
            ->add('searchType', type: ChoiceType::class, options: [
                'required' => true,
                'multiple' => false,
                'choices' => [
                    'Search by City' => 'city',
                    'Search by Postal Code' => 'postal',
                ],
                'constraints' => [
                    new NotBlank(message: 'This field is required.'),
                    new AtLeastOneOf([
                        new EqualTo(value: SearchEngineParams::SEARCH_TYPE_CITY),
                        new EqualTo(value: SearchEngineParams::SEARCH_TYPE_POSTAL),
                    ]),
                ],
            ])
            ->add('searchText', options: [
                'required' => true,
                'constraints' => [
                    new NotBlank(),
                ],
                'attr' => [
                    'placeholder' => 'Location'
                ],
            ])
            ->add('searchRadius', type: NumberType::class, options: [
                'required' => false,
                'attr' => [
                    'placeholder' => 'Radius (miles)',
                ],
            ])
            ->add('page', type: HiddenType::class, options: [
                'required' => false,
            ])
            ->add('submit', type: SubmitType::class, options: [
                'label' => 'Search',
            ])
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

    public function getBlockPrefix()
    {
        // Removes the search_form[] prefix from form input names
        return '';
    }
}
