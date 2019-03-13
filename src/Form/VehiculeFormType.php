<?php

namespace App\Form;

use App\Entity\Vehicule;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VehiculeFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('matricule')
            ->add('tonnage')
            ->add('ageVehicule')
            ->add('typeVehicule',ChoiceType::class,[
                'choices' =>$this->getChoice()
            ])
            ->add('nomChauffeur')
            ->add('telChauffeur')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Vehicule::class,
        ]);
    }
    private function getChoice(){
        $choices = Vehicule::TYPEVEHICULE;
        $output = [];
        foreach ($choices as $value => $item){
            $output[$item] = $value;
        }
        return $output;
    }
}
