<?php

namespace App\Form;

use App\Entity\DfxNfxUser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegisterAccountType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('password',repeatedType::class, [
           		'first_name'  => 'first',
           		'second_name' => 'second',
           		'type'        => passwordType::class,
            	'first_options' => ['label' => 'Passwort'],
            	'second_options' => ['label' => 'Passwort wiederholen'],
            	'invalid_message' => "Die Passwort-Eingaben stimmen nicht überein",

        	])

        	->add('meta', CheckboxType::class, ['label' => 'Konfiguration, Rubriken, Zielgruppen aus Meta-Kalender übernehmen', 'required' => false, 'mapped' => false, 'attr' => ['noFormControl' => true]])
        ;



    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DfxNfxUser::class
        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix(): string
    {
        return 'user';
    }
}
