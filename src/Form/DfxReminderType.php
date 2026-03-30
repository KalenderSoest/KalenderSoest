<?php

namespace App\Form;

use App\Entity\DfxReminder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DfxReminderType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('datum',DateType::class, ['widget' => 'single_text', 'html5' => true,'label' => 'Erinnern am', 'required' => false, 'attr' => ['picker' => 'date', 'placeholder' => 'Datum wählen', 'class' =>'form-control']])
            ->add('email',EmailType::class, ['label' => 'Ihre Mailadresse', 'required' => false])
            ->add('cCode',TextType::class, ['label' => false, 'required' => true, 'mapped' => false,  'attr' => ['placeholder' => '4-stellige Codezahl', 'aria-label'=>'4-stellige Codezahl']])
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
            'data_class' => DfxReminder::class,

        	'cKey' => ''
        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix(): string
    {
        return 'dfxreminder';
    }
}
