<?php

namespace App\Form;

use App\Date\SearchData as DateSearchData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SearchData extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        
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
