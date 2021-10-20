<?php

namespace App\Form;

use Symfony\Component\Form\FormTypeInterface;
use App\Data\SearchData;
use App\Entity\Site;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('q', TextType::class, [
                'label' => false,
                'required' => false,
                'attr' => [
                    'placehorder' => "Tapez votre recherche.."
                ]
            ])
            ->add('villes', EntityType::class, [
                'label' => false,
                'required' => false,
                'class' => Site::class,
                'expanded' => true,
                'multiple' => true,
            ])
            ->add('dateDebut', DateType::class, [
                'label' => "Date de début"
                'required' => false,
            ])
            ->add('dateDebut', DateType::class, [
                'label' => "Date de début"
                'required' => false,
            ])
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            // Classe utilisée
            'data_class' => SearchData::class,
            'method' => 'GET',
            // Pas de besoin de csrf protection car simple formulaire de recherche
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }
}
