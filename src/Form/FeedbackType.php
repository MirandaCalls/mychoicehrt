<?php

namespace App\Form;

use App\Entity\FeedbackMessage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FeedbackType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('feedbackType', options: [
                'choices' => FeedbackMessage::FEEDBACK_TYPES,
            ])
            ->add('messageText')
            ->add('submittedOn')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FeedbackMessage::class,
        ]);
    }
}
