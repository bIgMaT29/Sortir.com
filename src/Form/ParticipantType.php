<?php

namespace App\Form;

use App\Entity\Participant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParticipantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('newPhotoDeProfil', FileType::class, [
                'required' => false, // Champ facultatif
                'mapped' => false,   // Champ non lié à une propriété de l'entité Participant
                'attr' => [
                    'style' => 'display: none;', // Appliquer le style pour le rendre invisible
                    'accept' => 'image/jpeg, image/png, image/gif' // Restreindre les types de fichiers acceptés
                ]
            ])
            ->add('photoDeProfil', TextType::class, [
                'required' => false, // Champ facultatif
                'attr' => [
                    'style' => 'display: none;', // Appliquer le style pour le rendre invisible
                ]

            ])
            ->add('pseudo', TextType::class)
            ->add('newMotDePasse', PasswordType::class, [
                'required' => false, // Champ facultatif
                'mapped' => false,   // Champ non lié à une propriété de l'entité Participant
            ])
            ->add('confirmNewMotDePasse', PasswordType::class, [
                'required' => false, // Champ facultatif
                'mapped' => false,   // Champ non lié à une propriété de l'entité Participant
            ])
            ->add('eMail', EmailType::class)
            ->add('prenom', TextType::class)
            ->add('nom', TextType::class)
            ->add('telephone', TextType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
