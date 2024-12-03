<?php
namespace App\Form;

use App\Entity\Car;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class CarFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('brand', TextType::class)
            ->add('model', TextType::class)
            ->add('year', NumberType::class)
            ->add('engineCapacity', NumberType::class)
            ->add('horsePower', NumberType::class)
            ->add('color', TextType::class, ['required' => false])
            ->add('registrationDate', DateType::class, [
                'widget' => 'single_text', 'required' => true
            ])
            // // Dodavanje polja za email korisnika
            // ->add('user', EntityType::class, [
            //     'class' => User::class, // povezuje sa entitetom User
            //     'choice_label' => 'email', // prikazuje email kao opciju
            //     'label' => 'Select User',
            //     'placeholder' => 'Choose a user', // Dodaje "placeholder" u formu
            //     'required' => true, // Polje je obavezno
            // ])
            ->add('save', SubmitType::class, ['label' => 'Save Car']);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Car::class,
        ]);
    }
}
