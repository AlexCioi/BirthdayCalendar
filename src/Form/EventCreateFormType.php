<?php

namespace App\Form;

use App\Entity\Event;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class EventCreateFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'attr' => [
                    'placeholder' => 'Name of the special person you are going to celebrate...'
                ]
            ])
            ->add('dueDate', DateType::class)
            ->add('description', TextareaType::class,[
                'constraints' => [
                    new NotBlank([
                        'message' => 'Write some words about the event you want to schedule.'
                    ])
                ],
                'attr' => [
                    'placeholder' => 'Gift ideas,  something to remember later...'
                ]
            ])
            ->add('create', SubmitType::class, [
                'label' => 'Schedule event',
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
           'data_class' => Event::class,
        ]);
    }
}