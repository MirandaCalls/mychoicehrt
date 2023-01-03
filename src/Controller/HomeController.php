<?php

namespace App\Controller;

use App\Form\SearchFormType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $searchForm = $this->createForm(SearchFormType::class);

        return $this->render('home.html.twig', [
            'title' => 'Home',
            'searchForm' => $searchForm,
        ]);
    }
}
