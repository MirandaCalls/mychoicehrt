<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return new Response('
            <html>
                <head>
                    <link rel="icon" type="image/x-icon" href="/images/favicon.ico"> 
                </head>
                <body style="background-color: #fcfbf7;">
                    <h1>Under Construction</h1>
                    <img src="/images/under_construction.jpg">
                </body> 
            </html> 
        ');
    }
}
