<?php
namespace App\Form;

use App\Entity\Appointment;
use App\Enum\AppointmentTypeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AppointmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('appointmentType', ChoiceType::class, [
                'choices' => array_combine(
                    array_map(fn($type) => ucfirst(strtolower($type->name)), AppointmentTypeEnum::cases()),
                    AppointmentTypeEnum::cases()
                ),
                'choice_label' => function (?AppointmentTypeEnum $choice) {
                    return $choice ? ucfirst(strtolower($choice->name)) : '';
                },
                'choice_value' => function (?AppointmentTypeEnum $choice) {
                    return $choice ? $choice->value : ''; 
                },
                'expanded' => true, 
                'multiple' => false, 
            ])
            ->add('scheduledAt', DateTimeType::class, [
                'widget' => 'single_text',
                'input'  => 'datetime',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Schedule Appointment'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Appointment::class,
        ]);
    }
}
