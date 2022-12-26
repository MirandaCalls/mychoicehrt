<?php

namespace App\Controller\Admin;

use App\Entity\DuplicateLink;
use App\Repository\ClinicRepository;
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
use Symfony\Component\HttpFoundation\Response;

class DuplicateLinkCrudController extends AbstractCrudController
{
    private AdminUrlGenerator $adminUrlGenerator;
    private ClinicRepository $clinics;
    private EntityManagerInterface $entityManager;

    public function __construct(
        AdminUrlGenerator $adminUrlGenerator,
        ClinicRepository $clinics,
        EntityManagerInterface $entityManager,
    ) {
        $this->adminUrlGenerator = $adminUrlGenerator;
        $this->clinics = $clinics;
        $this->entityManager = $entityManager;
    }

    public static function getEntityFqcn(): string
    {
        return DuplicateLink::class;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        return parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters)
            ->andWhere('entity.dismissed = :notDismissed')
            ->setParameter('notDismissed', false)
        ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $resolve = Action::new('resolve', 'Resolve')
            ->linkToCrudAction('resolve')
        ;
        $dismissDuplicate = Action::new('dismissDuplicate', 'Dismiss')
            /* @see self::dismissDuplicate() */
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
            ->add(Action::INDEX, $resolve)
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

    public function resolve(AdminContext $context): Response
    {
        /* @var ?DuplicateLink $duplicate */
        $duplicate = $context->getEntity()->getInstance();
        if (!$duplicate) {
            return $this->redirect($context->getReferrer());
        }

        $clinicA = $duplicate->getClinicA();
        $clinicB = $duplicate->getClinicB();

        $request = $context->getRequest();
        $isSubmitted = $request->request->get('submitted', default: 0);
        $submittedToken = $request->request->get('token');

        if ($isSubmitted && $this->isCsrfTokenValid('resolveDuplicate', $submittedToken)) {
            $selectionId = (int) $request->request->get('clinicToKeep', default: 0);
            if ($clinicA->getId() === $selectionId) {
                $clinicToDelete = $clinicB;
            } elseif ($clinicB->getId() === $selectionId) {
                $clinicToDelete = $clinicA;
            } else {
                return $this->redirect($context->getReferrer());
            }

            $this->clinics->remove($clinicToDelete, true);
            return $this->redirect($context->getReferrer());
        }

        $url = $this->adminUrlGenerator
            ->setController(DuplicateLinkCrudController::class)
            ->setAction('resolve')
            ->setEntityId($duplicate->getId())
            ->setReferrer($context->getReferrer())
            ->generateUrl();

        return $this->render('admin/duplicate/resolve.html.twig', [
            'formUrl' => $url,
            'choices' => [$clinicA, $clinicB],
        ]);
    }

    public function dismissDuplicate(AdminContext $context): RedirectResponse
    {
        /* @var DuplicateLink $duplicate */
        $duplicate = $context->getEntity()->getInstance();
        $duplicate->setDismissed(true);
        $this->entityManager->flush();
        return $this->redirect($context->getReferrer());
    }

}
