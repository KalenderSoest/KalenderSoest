<?php

namespace App\Form;

use App\Entity\DfxNfxUser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;


class DfxNfxUserType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
    	// $arRoles = array('ROLE_ADMIN' => 'Adminuser','ROLE_DFX_ALL' => 'Zugriff auf alle Termine','ROLE_DFX_PUB' => 'Termine veröffentlichen/löschen', 'ROLE_DFX_META' => 'Freigabe für Meta- und Gruppenkalender','ROLE_NFX_ALL' => 'Newsfix - Zugriff auf alle Artikel','ROLE_NFX_PUB'  => 'Newsfix - Artikel veröffentlichen/löschen','ROLE_NFX_META' => 'Newsfix - Freigabe Meta-/Gruppenaccounts');
    	$arRoles = ['ROLE_ADMIN' => 'Adminuser','ROLE_DFX_ALL' => 'Zugriff auf alle Termine','ROLE_DFX_PUB' => 'Termine veröffentlichen/löschen'];
    	$arRolesM = ['ROLE_DFX_META' => 'Freigabe für Meta- und Gruppenkalender'];
    	$arRolesG = ['ROLE_DFX_GROUP' => 'Freigabe nur für Gruppenkalender'];

        $builder
        	->add('password',repeatedType::class, [
        		'first_name'  => 'first',
        		'second_name' => 'second',
        		'type'        => passwordType::class,
        		'first_options' => ['label' => 'Passwort'],
        		'second_options' => ['label' => 'Passwort wiederholen'],
        		'invalid_message' => "Die Passwort-Eingaben stimmen nicht überein",
                'mapped' => false,
                'required' => $options['password_required'],
        	])
        	// ->add('user', new DfxUserType())
            ->add('nameLang')
            ->add('email', TextType::class, ['label' => 'E-Mail', 'required' => true, 'attr'=>['placeholder' => 'Email']])
            ->add('roles', ChoiceType::class, ['label' => 'Zugriffsrechte', 'required' => false,'multiple' => true, 'expanded' => 'true', 'choices' => array_flip($arRoles)])
            ->add('rolesM', ChoiceType::class, ['label' => 'Zugriffsrechte auf übergeordnete Kalender', 'mapped' => false, 'required' => false,'multiple' => true, 'expanded' => 'true', 'choices' =>array_flip($arRolesM), 'data' => $options['rolesM']])
            ->add('rolesG', ChoiceType::class, ['label' => 'Zugriffsrechte auf Gruppenkalender', 'mapped' => false, 'required' => false,'multiple' => true, 'expanded' => 'true', 'choices' => array_flip($arRolesG), 'data' => $options['rolesG']])
            ->add('init', hiddenType::class, ['data' => md5(time())]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DfxNfxUser::class,
        	'rolesM' => [],
        	'rolesG' => [],
            'password_required' => true,

        ]);
    }

    /**
     * @return string
     */
    public function getBlockPrefix(): string
    {
        return 'user';
    }

    /*
    private function refactorRoles($originRoles)
    {
    	$roles = array();
    	$rolesAdded = array();

    	// Add herited roles
    	foreach ($originRoles as $roleParent => $rolesHerit) {
    		$tmpRoles = array_values($rolesHerit);
    		$rolesAdded = array_merge($rolesAdded, $tmpRoles);
    		$roles[$roleParent] = array_combine($tmpRoles, $tmpRoles);
    	}
    	// Add missing superparent roles
    	$rolesParent = array_keys($originRoles);
    	foreach ($rolesParent as $roleParent) {
    		if (!in_array($roleParent, $rolesAdded)) {
    			$roles['-----'][$roleParent] = $roleParent;
    		}
    	}

    	return $roles;
    }
    */
}
