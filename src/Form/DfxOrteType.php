<?php

namespace App\Form;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use App\Entity\DfxOrte;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;
use App\Entity\DfxRegion;


class DfxOrteType extends AbstractType
{
        /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        	->add('plz',TextType::class,['label' => 'Plz', 'required' => false])
            ->add('ort',TextType::class,['label' => 'Ortsname', 'required' => false])
            ->add('lg', TextType::class, ['label' => false, 'required' => false, 'attr' => ['placeholder' => 'Längengrad', 'aria-label'=>'Längengrad']])
            ->add('bg', TextType::class, ['label' => false, 'required' => false, 'attr' => ['placeholder' => 'Breitengrad', 'aria-label'=>'Breitengrad']])
            ->add('region', EntityType::class, [
            		'class' => DfxRegion::class,
            		'query_builder' => fn(EntityRepository $er): QueryBuilder => $er->createQueryBuilder('r')
        			->select(['r'])
        			->where('r.datefix IN (:kid)')
        			->setParameters(new ArrayCollection([new Parameter('kid', $options['konf']->getId())]))
        			->orderBy('r.region', 'ASC'),
            		'choice_label' => 'region',
            		'placeholder' => 'Region aus Datenbank wählen',
            		'label' => 'Region zuordnen',
            		'required'=> false
            ])
            ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DfxOrte::class,
        	'konf' => ''
        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix(): string
    {
        return 'orte';
    }
}
