<?php

/*
 * Classe - GroupeType
 *
 * Formulaire d'ajout/édition d'un groupe
*/

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

class GroupeType extends AbstractType
{
    private $roleHierarchy;

    public function __construct(RoleHierarchyInterface $roleHierarchy)
    {
        $this->roleHierarchy = $roleHierarchy;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('libelle', TextType::class, [
                'label_format' => 'common.nom',
            ])
            ->add('roles', ChoiceType::class, [
                'label_format' => 'role.liste',
                'choices' => $this->getRoleChoices(),
                'choice_translation_domain' => true,
                'multiple' => true,
                'attr' => ['placeholder' => 'groupe.roles.placeholder'],
            ])
            ->add('save', SubmitType::class, [
                'label_format' => 'bouton.save',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'App\Entity\Uca\Groupe',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ucaSport_group';
    }

    private function getRoleChoices()
    {
        $roles = $this->roleHierarchy->getReachableRoleNames(['ROLE_SUPER_ADMIN']);
        $res = [];
        foreach ($roles as $role) {
            if (!in_array($role, ['ROLE_SUPER_ADMIN', 'ROLE_ADMIN', 'ROLE_USER'])) {
                $res['security.roles.'.$role] = $role;
            }
        }
        ksort($res);

        return $res;
    }
}
