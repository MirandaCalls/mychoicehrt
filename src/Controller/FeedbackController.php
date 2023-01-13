<?php

namespace App\Controller;

use App\Form\FeedbackType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FeedbackController extends AbstractController
{
    #[Route('/feedback', name: 'app_feedback')]
    public function index(): Response
    {
        $feedbackForm = $this->createForm(FeedbackType::class);

        return $this->render('feedback/index.html.twig', [
            'feedbackForm' => $feedbackForm,
        ]);
    }
}
