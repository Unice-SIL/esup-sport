<?php

/*
 * Classe - GroupeType
 *
 * Formulaire d'ajout/édition d'un groupe
*/

namespace UcaBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\Role\RoleHierarchy;

class GroupeType extends AbstractType
{
    private $roleHierarchy;

    public function __construct(RoleHierarchy $roleHierarchy)
    {
        $this->roleHierarchy = $roleHierarchy;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->remove('name');
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

    public function getParent()
    {
        return 'FOS\UserBundle\Form\Type\GroupFormType';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'UcaBundle\Entity\Groupe',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'ucaSport_group';
    }

    private function getRoleChoices()
    {
        $roles = $this->roleHierarchy->getReachableRoles([new Role('ROLE_SUPER_ADMIN')]);
        $res = [];
        foreach ($roles as $role) {
            if (!in_array($role->getRole(), ['ROLE_SUPER_ADMIN', 'ROLE_ADMIN', 'ROLE_USER'])) {
                $res['security.roles.'.$role->getRole()] = $role->getRole();
            }
        }
        ksort($res);

        return $res;
    }
}
