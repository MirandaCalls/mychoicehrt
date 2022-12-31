<?php

namespace App\Controller\Admin;

use App\Entity\Clinic;
use App\Entity\DuplicateLink;
use App\Repository\ClinicRepository;
use App\Repository\DuplicateLinkRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    private ClinicRepository $clinics;
    private DuplicateLinkRepository $duplicates;

    public function __construct(
        ClinicRepository $clinics,
        DuplicateLinkRepository $duplicates,
    ) {
        $this->clinics = $clinics;
        $this->duplicates = $duplicates;
    }

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $clinicsCount = $this->clinics->countClinics();
        $recentClinicsCount = $this->clinics->countClinics(recent: true);
        $unpublishedCount = $this->clinics->countClinics(published: false);
        $duplicatesCount = $this->duplicates->countDuplicates();

        return $this->render('admin/dashboard.html.twig', [
            'clinicsCount' => $clinicsCount,
            'recentClinicsCount' => $recentClinicsCount,
            'unpublishedCount' => $unpublishedCount,
            'duplicatesCount' => $duplicatesCount,
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('mychoicehrt Admin')
            ->setFaviconPath('/images/favicon.ico')
        ;
    }

    public function configureAssets(): Assets
    {
        return Assets::new()->addCssFile('admin.css');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToUrl('Back to public website', 'fa fa-arrow-left', '/');
        yield MenuItem::section();
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud('Clinics', 'fas fa-hospital', Clinic::class);

        $duplicatesCount = $this->duplicates->countDuplicates();
        if ($duplicatesCount > 0) {
            yield MenuItem::linkToCrud('Duplicates', 'fa-regular fa-copy', DuplicateLink::class);
        }
    }
}
