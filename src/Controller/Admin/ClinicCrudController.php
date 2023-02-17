<?php

namespace App\Controller\Admin;

use App\Entity\Clinic;
use App\HereMaps\Client;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Oefenweb\DamerauLevenshtein\DamerauLevenshtein as Levenshtein;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Validator\Constraints\Choice;

class ClinicCrudController extends AbstractCrudController
{

    private AdminUrlGenerator $adminUrlGenerator;
    private Client $hereClient;
    private EntityManagerInterface $entityManager;

    public function __construct(
        AdminUrlGenerator $adminUrlGenerator,
        Client $hereClient,
        EntityManagerInterface $entityManager,
    ) {
        $this->adminUrlGenerator = $adminUrlGenerator;
        $this->hereClient = $hereClient;
        $this->entityManager = $entityManager;
    }

    public static function getEntityFqcn(): string
    {
        return Clinic::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        $openMapsAction = Action::new('openMaps', 'OpenStreetMap', 'fa-solid fa-link')
            /* @see self::redirectToOpenStreetMap() */
            ->linkToCrudAction('redirectToOpenStreetMap')
        ;

        $addHereMapsDataAction = Action::new('addHereMapsData', 'Add Here Maps Data', 'fa-solid fa-cloud-arrow-down')
            /* @see self::addHereMapsData() */
            ->linkToCrudAction('addHereMapsData')
        ;

        return $actions
            ->add(Action::EDIT, $openMapsAction)
            ->add(Action::EDIT, $addHereMapsDataAction)
        ;
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
            ]))
            ->add(ChoiceFilter::new('dataSource')->canSelectMultiple()->setChoices([
                'Trans in the South' => 'transInTheSouth',
                'Erin Reed' => 'erinReed',
            ]))
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        $name = TextField::new('name');
        $description = TextEditorField::new('description');
        $address = TextField::new('address');
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
            yield $address;
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
            yield $address;
            yield $latitude;
            yield $longitude;
        } elseif (Crud::PAGE_DETAIL === $pageName) {
            yield $published;
            yield FormField::addPanel('Details');
            yield $name;
            yield $description;
            yield $address;
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

    public function redirectToOpenStreetMap(AdminContext $context): RedirectResponse
    {
        /* @var ?Clinic $clinic */
        $clinic = $context->getEntity()->getInstance();
        if (!$clinic) {
            return $this->redirect($context->getReferrer());
        }

        $openMapsUrl = 'https://www.openstreetmap.org/?mlat=' . $clinic->getLatitude() . '&mlon=' . $clinic->getLongitude();
        return $this->redirect($openMapsUrl);
    }

    public function addHereMapsData(AdminContext $context): RedirectResponse
    {
        /* @var ?Clinic $clinic */
        $clinic = $context->getEntity()->getInstance();
        if (!$clinic) {
            return $this->redirect($context->getReferrer());
        }

        $editUrl = $this->adminUrlGenerator
            ->setController(self::class)
            ->setAction(Crud::PAGE_EDIT)
            ->setEntityId($clinic->getId())
            ->generateUrl()
        ;

        try {
            $locations = $this->hereClient->discover(
                $clinic->getName(),
                $clinic->getLatitude(),
                $clinic->getLongitude(),
            );
        } catch(\Throwable $e) {
            $this->addFlash('danger', $e->getMessage());
            return $this->redirect($editUrl);
        }

        if (count($locations['items']) === 0) {
            $this->addFlash('danger', 'No matching location found');
            return $this->redirect($editUrl);
        }

        $address = $locations['items'][0]['address']['label'];
        $addressParts = explode(', ', $address);
        $levenshtein = new Levenshtein($clinic->getName(), $addressParts[0]);
        if ($levenshtein->getRelativeDistance() < 0.4) {
            $this->addFlash('danger', 'No matching location found');
            return $this->redirect($editUrl);
        }
        unset($addressParts[0]);
        $address = implode(', ', $addressParts);

        $clinic->setAddress($address);
        $this->entityManager->flush();

        $this->addFlash('success', 'Successfully added data from Here maps');
        return $this->redirect($editUrl);
    }

}
