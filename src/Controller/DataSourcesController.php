<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DataSourcesController extends AbstractController
{
    #[Route('/data-sources', name: 'app_data_sources')]
    public function dataSources(): Response
    {
        return $this->render('data-sources.html.twig');
    }

}