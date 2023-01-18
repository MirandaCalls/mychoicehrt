<?php

namespace App\Controller;

use App\Entity\FeedbackMessage;
use App\Form\FeedbackType;
use App\Repository\ClinicRepository;
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
    private ClinicRepository $clinics;
    private NotifierInterface $notifier;

    public function __construct(
        FeedbackMessageRepository $feedback,
        ClinicRepository $clinics,
        NotifierInterface $notifier,
    ) {
        $this->feedback = $feedback;
        $this->clinics = $clinics;
        $this->notifier = $notifier;
    }

    #[Route('/feedback', name: 'app_feedback')]
    public function index(Request $req): Response
    {
        $message = new FeedbackMessage();

        $feedbackType = $req->get('feedbackType');
        $clinicId = $req->get('clinicId');
        $formOptions = [];
        if (
               (int)$feedbackType === FeedbackMessage::FEEDBACK_TYPE_BAD_DATA
            && $clinicId !== null
            && $linkedClinic = $this->clinics->find($clinicId)
        ) {
            $message->setFeedbackType($feedbackType);
            $message->setClinic($linkedClinic);
            $formOptions['linkedClinic'] = [
                $linkedClinic->getName() => $linkedClinic->getId(),
            ];
        }

        $feedbackForm = $this->createForm(FeedbackType::class, $message, $formOptions);
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
