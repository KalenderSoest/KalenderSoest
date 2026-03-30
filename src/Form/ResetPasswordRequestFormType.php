<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class ResetPasswordRequestFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('require_username', HiddenType::class, [
                'mapped' => false,
                'data' => $options['require_username'] ? '1' : '0',
            ])
            ->add('email', EmailType::class, [
                'label' => 'E-Mail-Adresse',
                'attr' => ['autocomplete' => 'email'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Bitte eine E-Mail-Adresse eingeben.',
                    ]),
                ],
            ])
        ;

        if ($options['require_username']) {
            $builder->add('username', TextType::class, [
                'label' => 'Usernummer',
                'mapped' => false,
                'required' => true,
                'attr' => ['autocomplete' => 'username'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Bitte die Usernummer eingeben.',
                    ]),
                ],
            ]);
        } else {
            $builder->add('username', TextType::class, [
                'label' => 'Usernummer',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'autocomplete' => 'username',
                    'class' => 'd-none',
                ],
                'row_attr' => ['class' => 'd-none'],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'require_username' => false,
        ]);

        $resolver->setAllowedTypes('require_username', 'bool');
    }
}
