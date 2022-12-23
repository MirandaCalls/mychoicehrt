<?php

namespace App\Controller\Admin;

use App\Entity\DuplicateLink;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\PercentField;

class DuplicateLinkCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return DuplicateLink::class;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(
                Action::NEW,
                Action::EDIT,
                Action::BATCH_DELETE,
                Action::DELETE,
                Action::SAVE_AND_RETURN,
                Action::SAVE_AND_ADD_ANOTHER,
                Action::SAVE_AND_CONTINUE,
            );
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

}
