<?php

namespace App\Controller\Admin;

use App\Entity\FeedbackMessage;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

class FeedbackMessageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return FeedbackMessage::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('User Feedback')
            ->setEntityLabelInPlural('User Feedback')
            ->setTimezone('America/Chicago')
            ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->disable(Action::NEW)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield EmailField::new('email');
        yield ChoiceField::new('feedbackType')
            ->setChoices(FeedbackMessage::FEEDBACK_TYPES)
        ;
        yield AssociationField::new('clinic');
        yield TextareaField::new('messageText');
        yield DateTimeField::new('submittedOn')->setFormTypeOption('disabled', true);
    }

}
