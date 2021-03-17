<?php

namespace App\Form;

use App\Entity\Participant;
use App\Entity\Site;
use App\Entity\Sortie;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ParticipantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $paramId = $this->requestStack->getCurrentRequest()->attributes->get('id');
        $currentUser = $this->security->getUser();
        $builder
            ->add('pseudo', TextType::class, [
                'label' => false
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'options' => ['attr' => ['class' => 'password-field']],
                'required' => false,
                'mapped' => false,
                'first_options'  => ['label' => false],
                'second_options' => ['label' => false],
            ])
            ->add('nom', TextType::class, [
                'label' => false
            ])
            ->add('prenom', TextType::class, [
                'label' => false
            ])
            ->add('telephone', TelType::class, [
                'label' => false,
                'required' => false
            ])
            ->add('mail', EmailType::class, [
                'label' => false
            ]);
            if (in_array('ROLE_USER', $options['role']) && $currentUser->getId() == $paramId) {
                $builder
                    ->add('urlPhoto', FileType::class, [
                        'label' => false,
                        'mapped' => false,
                        'required' => false,
                        'constraints' => [
                            new File([
                                'maxSize' => '2048k',
                                'mimeTypes' => [
                                    'image/jpeg',
                                    'image/jpg',
                                    'image/png'
                                ],
                                'mimeTypesMessage' => 'Format image invalid ! Formats acceptés: png, jpg, jpeg',
                            ])
                        ],
                    ]);
            }

        if (in_array('ROLE_ADMIN', $options['role'])) {
            $builder
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
                    'required' => false,
                    'label' => false,
                    'attr' => [
                        'data-role' => 'select',
                    ]
                ]);

        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Participant::class,
            'role' => ['ROLE_USER']
        ]);
    }

    private $security;
    private $requestStack;

    public function __construct(Security $security, RequestStack $requestStack)
    {
        $this->security = $security;
        $this->requestStack = $requestStack;
    }

}
