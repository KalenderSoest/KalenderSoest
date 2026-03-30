<?php

namespace App\Form;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DfxKartenBlockType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('blockName', TextType::class, ['label' => 'Block/Saalbereich Bezeichnung', 'required' => true])
            ->add('blockImg', ChoiceType::class, ['label' => 'Platzart', 'choices' => ['Sitzplatz', 'Stehplatz'], 'required' => false])
            ->add('blockRgb', TextType::class, ['label' => 'Farbe (Hex Code)', 'required' => false])
            ->add('blockNotiz', TextareaType::class, ['label' => 'Beschreibung', 'required' => false])
            ->add('kategorien', CollectionType::class, [
                'type' => new DfxKartenKatType(),
                'allow_add' => true,
                'delete_empty' => true,
                'by_reference' => false,
                'entry_options'  => [
                    'kategorien'  => $options['kategorien']]
            ])

        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
     public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\DfxKartenBlock',

            'kategorien' => []
        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix(): string
    {
        return 'dfxkartenblock';
    }
}
