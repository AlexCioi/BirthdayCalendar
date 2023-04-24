<?php

namespace App\Form;

use App\Entity\Event;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class EventFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'attr' => [
                    'placeholder' => 'Event name'
                ]
            ])
            ->add('dueDate', DateType::class, array(
                'years' => range(date('Y'), date('Y') + 10),
            ))
            ->add('description', TextareaType::class,[
                'constraints' => [
                    new NotBlank([
                        'message' => 'Write some words about the event you want to schedule.'
                    ])
                ],
                'attr' => [
                    'placeholder' => 'Something to remember later...'
                ]
            ])
//            ->add('attendees', CollectionType::class, [
//                'entry_type' => AttendeeFormType::class,
//                'allow_add' => true,
//                'allow_delete' => true,
//                'by_reference' => false,
//            ])
            ->add('attendees', CollectionType::class, [
                'entry_type' => EmailType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
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