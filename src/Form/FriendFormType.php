<?php

namespace App\Form;

use App\Entity\Friend;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FriendFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'attr' => [
                    'placeholder' => 'First name...'
                ]
            ])
            ->add('lastName', TextType::class, [
                'attr' => [
                    'placeholder' => 'Last name...'
                ]
            ])
            ->add('birthDate', DateType::class, array(
                'years' => range(date('1940'), date('Y')),
            ))
            ->add('phoneNumber', TelType::class, [
                'required' => false
            ])
            ->add('email', EmailType::class, [
                'attr' => [
                    'placeholder' => 'friend@example.com'
                ],
                'required' => false
            ])
            ->add('checkBox', CheckboxType::class, [
                'label' => 'Receive email notifications announcing their birthday?',
                'required' => false,
            ])
            ->add('notificationOffset', IntegerType::class, [
                'required' => false,
                'label' => 'Number of days you want to be notified before birthday',
                'attr' => [
                    'class' => 'notification-offset w-50 mx-auto',
                ]
            ])
            ->add('create', SubmitType::class, [
                'label' => 'Register friend',
                'attr' => [
                    'class' => 'btn btn-primary'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Friend::class,
        ]);
    }
}