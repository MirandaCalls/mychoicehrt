<?php

namespace App\Controller;

use App\Entity\FeedbackMessage;
use App\Form\FeedbackType;
use App\Repository\FeedbackMessageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Notifier\Notification\Notification;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Routing\Annotation\Route;

class FeedbackController extends AbstractController
{
    private FeedbackMessageRepository $feedback;
    private NotifierInterface $notifier;

    public function __construct(
        FeedbackMessageRepository $feedback,
        NotifierInterface $notifier,
    ) {
        $this->feedback = $feedback;
        $this->notifier = $notifier;
    }

    #[Route('/feedback', name: 'app_feedback')]
    public function index(Request $req): Response
    {
        $message = new FeedbackMessage();
        $feedbackType = $req->get('feedbackType');
        if ($feedbackType && in_array($feedbackType, FeedbackMessage::FEEDBACK_TYPES)) {
            $message->setFeedbackType($feedbackType);
        }
        $message->setMessageText($req->get('messageText', ''));

        $feedbackForm = $this->createForm(FeedbackType::class, $message);
        $feedbackForm->handleRequest($req);

        if ($feedbackForm->isSubmitted() && $feedbackForm->isValid()) {
            $this->feedback->save($message, true);
            $this->notifier->send(new Notification('Your feedback has been received. Thank you!', ['browser']));

            unset($message);
            unset($feedbackForm);
            $message = new FeedbackMessage();
            $feedbackForm = $this->createForm(FeedbackType::class, $message);
        }

        return $this->render('feedback/index.html.twig', [
            'feedbackForm' => $feedbackForm,
        ]);
    }
}
