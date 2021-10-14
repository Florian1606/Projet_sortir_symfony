<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Participant;
use App\Entity\Site;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
class MonProfilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('pseudo')
            ->add('prenom')
            ->add('nom')
            ->add('telephone')
            ->add('email')
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'label' => "Password",
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'SVP entrer votre mot de passe',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Votre mot de passe ne peut contenir que {{ limit }} caractères',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('idSite', EntityType::class, array(
                'label' => 'Ville de rattachement',
                'class' => Site::class,
                'choice_label' => 'nom',
                'expanded' => false,
                'multiple' => false,
                'attr' => ['class' =>'form-control'])
            )
        )

         //   ->add('confirmePassword')
//             ->add('idSite', EntityType::class,
//                [
//                   'class' => Site::class,
//                    'choice_label' => 'name',
//                    'multiple' => true,
//                    'expanded' => false
//               ])
          //  ->add('maPhoto')
            ->add('Enregistrer',SubmitType::class)
            ->add('Annuler',SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
