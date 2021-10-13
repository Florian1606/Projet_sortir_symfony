<?php

namespace App\Form;

use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Site;
use App\Entity\Sortie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateIntervalType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('duree')
            ->add('nbIncriptionMax')
            ->add('description')
//            ->add('urlPhoto', FileType::class)
            ->add('lieu', EntityType::class, array(
                    'class' => Lieu::class,
                    'choice_label' => 'nomLieu',
                    'expanded' => false,
                    'multiple' => false,
                    'attr' => ['class' =>'form-control']
                )
            )
            ->add('add', SubmitType::class)
            ->add('save', SubmitType::class)
            ->add('cancel', SubmitType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class
        ]);
    }
}
