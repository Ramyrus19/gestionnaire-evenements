<?php

namespace App\Form;

use App\Entity\Etat;
use App\Entity\Sortie;
use App\Entity\Site;
use App\Entity\Lieu;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom')
            ->add('dateDebut', DateTimeType::class, [
                'widget' => 'single_text',
                // this is actually the default format for single_text
                //'format' => 'yyyy-MM-dd HH:mm',
                //TODO: incompatible firefox
            ])
            ->add('duree')
            ->add('dateCloture', DateType::class, [
                'widget' => 'single_text',
                // this is actually the default format for single_text
                'format' => 'yyyy-MM-dd',
            ])
            ->add('nbPlaces')
            ->add('infos')
            ->add('etat' ,EntityType::class,[
                'class'=> Etat::class,
                'choice_label' =>'libelle'
            ])
            ->add('lieu',EntityType::class,[
                'class' => Lieu::class,
                'label' => false,
                'choice_label' => 'nom',
                'attr'=>[
                    'onchange' => 'ch_lieu(this.value)'
                ]
            ])
            //->add('participants', HiddenType::class, [])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
        ]);
    }
}
