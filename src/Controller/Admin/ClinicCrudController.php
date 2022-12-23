<?php

namespace App\Controller\Admin;

use App\Entity\Clinic;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;

class ClinicCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Clinic::class;
    }


    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Clinics')
            ->setTimezone('America/Chicago')
        ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(ChoiceFilter::new('published')->setChoices([
                'No' => false,
                'Yes' => true
            ]));
    }

    public function configureFields(string $pageName): iterable
    {
        $name = TextField::new('name');
        $description = TextareaField::new('description');
        $latitude = NumberField::new('latitude')->setColumns(5);
        $longitude = NumberField::new('longitude')->setColumns(5);
        $published = BooleanField::new('published')
            ->renderAsSwitch();
        $dataSource = TextField::new('dataSource');
        $updatedOn = DateTimeField::new('updatedOn')->setFormTypeOptions([
            'html5' => true,
            'widget' => 'single_text'
        ]);
        $importedOn = DateTimeField::new('importedOn')->setFormTypeOptions([
            'html5' => true,
            'widget' => 'single_text'
        ]);

        if (Crud::PAGE_EDIT === $pageName) {
            yield $published;
            yield FormField::addPanel('Details');
            yield $name;
            yield $description;
            yield $latitude;
            yield $longitude;

            yield FormField::addPanel('Metadata');
            yield $dataSource->setFormTypeOption('disabled', true);
            yield $updatedOn->setFormTypeOption('disabled', true);
            yield $importedOn->setFormTypeOption('disabled', true);
        } elseif (Crud::PAGE_NEW === $pageName) {
            yield $published;
            yield $name;
            yield $description;
            yield $latitude;
            yield $longitude;
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            yield $published;
            yield FormField::addPanel('Details');
            yield $name;
            yield $description;
            yield $latitude;
            yield $longitude;

            yield FormField::addPanel('Metadata');
            yield $dataSource;
            yield $updatedOn;
            yield $importedOn;
        } else {
            // list view
            yield $name;
            yield $dataSource;
            yield $updatedOn;
            yield $importedOn;
            yield $published;
        }
    }
}
