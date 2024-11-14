<?php
namespace App\Form;

use App\Entity\Car;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
            ->add('save', SubmitType::class, ['label' => 'Save Car']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Car::class,
        ]);
    }
}
