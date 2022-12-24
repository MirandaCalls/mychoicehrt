<?php

namespace App\Controller\Admin;

use App\Entity\DuplicateLink;
use App\Repository\ClinicRepository;
use App\Repository\DuplicateLinkRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\PercentField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DuplicateLinkCrudController extends AbstractCrudController
{
    private AdminUrlGenerator $adminUrlGenerator;
    private ClinicRepository $clinics;
    private DuplicateLinkRepository $duplicates;
    private EntityManagerInterface $entityManager;

    public function __construct(
        AdminUrlGenerator $adminUrlGenerator,
        ClinicRepository $clinics,
        DuplicateLinkRepository $duplicates,
        EntityManagerInterface $entityManager,
    ) {
        $this->adminUrlGenerator = $adminUrlGenerator;
        $this->clinics = $clinics;
        $this->duplicates = $duplicates;
        $this->entityManager = $entityManager;
    }

    public static function getEntityFqcn(): string
    {
        return DuplicateLink::class;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        return $this->duplicates->createQueryBuilder('d')
            ->andWhere('d.dismissed = :notDismissed')
            ->setParameter('notDismissed', false)
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $keepClinicA = Action::new('keepClinicA', 'Keep Clinic A', 'fa fa-floppy-disk')
            ->linkToCrudAction('keepClinicA')
        ;
        $keepClinicB = Action::new('keepClinicB', 'Keep Clinic B', 'fa fa-floppy-disk')
            ->linkToCrudAction('keepClinicB')
        ;
        $dismissDuplicate = Action::new('dismissDuplicate', 'Dismiss', 'fa-solid fa-shield')
            ->linkToCrudAction('dismissDuplicate')
        ;

        return $actions
            ->disable(
                Action::NEW,
                Action::EDIT,
                Action::BATCH_DELETE,
                Action::DELETE,
                Action::SAVE_AND_RETURN,
                Action::SAVE_AND_ADD_ANOTHER,
                Action::SAVE_AND_CONTINUE,
            )
            ->add(Action::INDEX, $dismissDuplicate)
            ->add(Action::INDEX, $keepClinicB)
            ->add(Action::INDEX, $keepClinicA)
        ;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInPlural('Duplicates')
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('clinicA');
        yield AssociationField::new('clinicB');
        yield PercentField::new('similarity');
    }

    public function keepClinicA(AdminContext $context): RedirectResponse
    {
        /* @var DuplicateLink $duplicate */
        $duplicate = $context->getEntity()->getInstance();
        $clinicToDelete = $duplicate->getClinicB();
        $this->clinics->remove($clinicToDelete, true);
        return $this->redirectToIndex();
    }

    public function keepClinicB(AdminContext $context): RedirectResponse
    {
        /* @var DuplicateLink $duplicate */
        $duplicate = $context->getEntity()->getInstance();
        $clinicToDelete = $duplicate->getClinicA();
        $this->clinics->remove($clinicToDelete, true);
        return $this->redirectToIndex();
    }

    public function dismissDuplicate(AdminContext $context): RedirectResponse
    {
        /* @var DuplicateLink $duplicate */
        $duplicate = $context->getEntity()->getInstance();
        $duplicate->setDismissed(true);
        $this->entityManager->flush();
        return $this->redirectToIndex();
    }

    private function redirectToIndex(): RedirectResponse
    {
        $duplicatesIndex = $this->adminUrlGenerator
            ->setController(DuplicateLinkCrudController::class)
            ->setAction(Action::INDEX)
            ->unset('entityId')
            ->setReferrer('')
            ->generateUrl();

        return $this->redirect($duplicatesIndex);
    }
}
