<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\File;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Email field
            ->add('email', EmailType::class, [
                'label' => 'Email Address',
                ],
            )
            
            // Password and Confirm Password fields
           // Password and Confirm Password fields
           ->add('plainPassword', RepeatedType::class, [
            'type' => PasswordType::class,
            'first_options' => [
                'label' => 'Password',
            ],
            'second_options' => [
                'label' => 'Confirm Password',
            ],
            'invalid_message' => 'Passwords do not match.',
            'mapped' => false,
            'attr' => ['autocomplete' => 'new-password'],
        ])
            
            // Agree terms checkbox
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'required' => true,
            ])
            
            // Birthday field (optional)
            ->add('birthday', DateType::class, [
                'widget' => 'single_text',
                'required' => false,
            ])
            
            // Gender field (optional)
            ->add('gender', TextType::class, [
                'label' => 'Gender',
                'required' => false,
            ])

            // Newsletter subscription (checkbox)
            ->add('newsletter', CheckboxType::class, [
                'label' => 'I would like to receive newsletters',
                'required' => false,
            ])

            // Profile picture upload (optional)
            ->add('profile_picture', FileType::class, [
                'label' => 'Profile Picture (Optional)',
                'required' => false,
                'attr' => [
                    'accept' => 'image/jpeg,image/png,image/gif', // MIME types accepted
                ],
            ]);
    }

}
