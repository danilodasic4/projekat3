<?php

namespace App\Form;

use App\Entity\Appointment;
use App\Enum\AppointmentTypeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Contracts\Translation\TranslatableInterface;

class AppointmentType extends AbstractType
{
    public function __construct(
        private readonly TranslatorInterface $translator
    ){}

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('appointmentType', EnumType::class, [
            'class' => AppointmentTypeEnum::class,
            'choice_label' => function (AppointmentTypeEnum $choice) {
                return $choice->value;  
            },
            'expanded' => true,
            'multiple' => false,
        ])
            ->add('scheduledAt', DateTimeType::class, [
                'widget' => 'single_text',
                'input'  => 'datetime',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Schedule Appointment', 
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Appointment::class, 
        ]);
    }
}
