<?php

namespace App\Controller\Admin;

use App\Entity\Clinic;
use App\Repository\ClinicRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardAdminController extends AbstractDashboardController
{
    private ClinicRepository $clinics;

    public function __construct(ClinicRepository $clinics)
    {
        $this->clinics = $clinics;
    }

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $clinicsCount = $this->clinics->countClinics();
        $recentClinicsCount = $this->clinics->countClinics(recent: true);
        $unpublishedCount = $this->clinics->countClinics(published: false);

        return $this->render('admin/dashboard.html.twig', [
            'clinicsCount' => $clinicsCount,
            'recentClinicsCount' => $recentClinicsCount,
            'unpublishedCount' => $unpublishedCount,
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('mychoicehrt Admin')
            ->setFaviconPath('/images/favicon.ico')
        ;
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToUrl('Back to public website', 'fa fa-arrow-left', '/');
        yield MenuItem::section();
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud('Clinics', 'fas fa-hospital', Clinic::class);
    }
}
