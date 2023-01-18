<?php

namespace App\Form;

use App\Entity\FeedbackMessage;
use MeteoConcept\HCaptchaBundle\Form\HCaptchaType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FeedbackType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', type: EmailType::class)
            ->add('feedbackType', type: ChoiceType::class, options: [
                'choices' => FeedbackMessage::FEEDBACK_TYPES,
            ])
        ;

        if (!empty($options['linkedClinic'])) {
            $builder->add('clinic', type: ChoiceType::class, options: [
                'disabled' => true,
                'choices' => $options['linkedClinic'],
            ]);
        }

        $builder
            ->add('messageText')
            ->add('captcha', type: HCaptchaType::class)
            ->add('submit', type: SubmitType::class, options: [
                'label' => 'Send',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => FeedbackMessage::class,
            'linkedClinic' => [],
        ]);

        $resolver->setAllowedTypes('linkedClinic', 'array');
    }
}
