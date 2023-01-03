<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('countryCode', type: ChoiceType::class, options: [
                'label' => 'Country',
                'required' => true,
                'choices' => [
                    'United States' => 'US',
                ]
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

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'method' => 'get',
            'action' => '/search',
        ]);
    }
}