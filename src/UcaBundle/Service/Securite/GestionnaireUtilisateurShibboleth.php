<?php

namespace UcaBundle\Service\Securite;

use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Model\UserManager;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Security\Core\User\UserInterface;
use UcaBundle\Entity\ProfilUtilisateur;
use UcaBundle\Entity\Utilisateur;
use UniceSIL\ShibbolethBundle\Security\User\ShibbolethUserProviderInterface as SUP;

class GestionnaireUtilisateurShibboleth implements SUP
{
    private $em;
    private $um;

    public function __construct(EntityManager $em, UserManager $um)
    {
        $this->em = $em;
        $this->um = $um;
    }

    public function loadUser(array $credentials)
    {
        // Quel username ? 
        $utilisateur = $this->um->findUserByUsername($credentials['uid']);
        if (empty($utilisateur)) {
            $utilisateur = new Utilisateur();
        }

        $profil = $credentials['eduPersonPrimaryAffiliation'];
        $transco = [
            // 'alum' => 4,
            'student' => 4,
            'faculty' => 8,
            'staff' => 8,
            'employee' => 8,
            // 'member' => 8,
            // 'affiliate' => 8,
            // 'researcher' => 8,
            // 'retired' => 8,
            'emeritus' => 8,
            'teacher' => 8
        ];
        
        if (!empty($credentials['supannEtuId']) && strpos($credentials['eduPersonAffiliation'], 'student') !== false) {
            throw new \Exception('Vous devez vous connecter avec votre compte étudiant.');
        }
        if($credentials['eduPersonPrimaryAffiliation'] == 'student' && $credentials['ptdrouv'] = 0) {
            throw new \Exception('Dossier incomplet.');
        }
        if (!in_array($profil, array_keys($transco))) {
            throw new \Exception('Ce type de compte n\'est pas autorisé.');
        }

        $objProfil = $this->em->getReference(ProfilUtilisateur::class, $transco[$profil]);

        $utilisateur
            ->setUsername($credentials['uid'])
            ->setPlainPassword(Utilisateur::getRandomPassword())
            ->setEmail($credentials['mail'])
            ->setMatricule($credentials['uid'])
            ->setProfil($objProfil)
            ->setNom($credentials['sn'])
            ->setPrenom($credentials['givenName'])
            ->setShibboleth(true)
            ->setEnabled(true);
        $this->em->persist($utilisateur);
        $this->em->flush();
        return  $utilisateur;
    }

    public function refreshUser(UserInterface $utilisateur)
    {
        return $utilisateur;
    }
    public function loadUserByUsername($username)
    { }

    public function supportsClass($class)
    { }
}
