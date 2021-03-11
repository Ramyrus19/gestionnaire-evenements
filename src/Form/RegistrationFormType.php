<?php

namespace App\Form;

use App\Entity\Participant;
use App\Entity\Site;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pseudo', TextType::class, [
                'label' => false
            ])
            ->add('nom', TextType::class, [
                'label' => false
            ])
            ->add('prenom', TextType::class, [
                'label' => false
            ])
            ->add('telephone', TelType::class, [
                'label' => false
            ])
            ->add('mail', EmailType::class, [
                'label' => false
            ])
            ->add('admin', CheckboxType::class, [
                'label'    => false,
                'required' => false,
                'attr' => [
                    'data-role' => 'switch',
                    'data-material' => 'true',
                ]
            ])
            ->add('actif', CheckboxType::class, [
                'label'    => false,
                'required' => false,
                'attr' => [
                    'data-role' => 'switch',
                    'data-material' => 'true',
                ]
            ])
            ->add('site', EntityType::class, [
                'class' => Site::class,
                'choice_label' => 'nom',
                'label' => false,
                'attr' => [
                    'data-role' => 'select',
                ]
            ])
            ->add('plainPassword', PasswordType::class, [
                'mapped' => false,
                'label' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'label' => false,
                'attr' => [
                    'data-role' => 'switch',
                    'data-material' => 'true',
                ],
                'constraints' => [
                    new IsTrue([
                        'message' => 'Vous devez accepter les terms.',
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
        ]);
    }
}
