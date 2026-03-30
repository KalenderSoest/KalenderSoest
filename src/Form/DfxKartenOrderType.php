<?php

namespace App\Form;

use App\Entity\DfxKartenOrder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DfxKartenOrderType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        	->add('nachname', TextType::class, ['label' => 'nachname', 'required' => true])
        	->add('vorname', TextType::class, ['label' => 'vorname', 'required' => true])
            ->add('org', TextType::class, ['label' => 'Organisation/Firma', 'required' => false])
            ->add('strasse', TextType::class, ['label' => 'Straße', 'required' => false])
            ->add('plz', TextType::class, ['label' => 'Plz', 'required' => false])
            ->add('ort', TextType::class, ['label' => 'Ort', 'required' => false])
            ->add('email', EmailType::class, ['label' => 'E-Mail', 'required' => true])
            ->add('tel', TextType::class, ['label' => 'Telefonnummer', 'required' => false])
            ->add('mobil',TextType::class, ['label' => 'Mobil', 'required' => false])
            ->add('anzahl', IntegerType::class, ['label' => 'Anzahl Personen', 'required' => true])
            ->add('notiz', TextareaType::class, ['label' => 'Ihre Nachricht an den Veranstalter', 'required' => false])
            ->add('cCode',TextType::class, ['label' => false, 'required' => true, 'mapped' => false, 'attr' => ['placeholder' => '4-stellige Codezahl', 'aria-label'=>'4-stellige Codezahl']])
    	    ->add('key',hiddenType::class, ['data'=> $options['cKey'], 'mapped' => false])
            ->add('datenschutz',checkboxType::class ,['label' => false, 'required' => true, 'attr' => ['noFormControl' => true, 'aria-label'=>'Checkbox Datenschutz']])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
     public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DfxKartenOrder::class,

        	'cKey' => ''
        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix(): string
    {
        return 'dfxkartenorder';
    }
}
