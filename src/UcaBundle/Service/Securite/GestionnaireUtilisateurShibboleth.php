<?php

namespace UcaBundle\Service\Securite;

use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Model\UserManager;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Security\Core\User\UserInterface;
use UcaBundle\Entity\ProfilUtilisateur;
use UcaBundle\Entity\TypeAutorisation;
use UcaBundle\Entity\Utilisateur;
use UcaBundle\Entity\torisation;
use UniceSIL\ShibbolethBundle\Security\User\ShibbolethUserProviderInterface as SUP;

class GestionnaireUtilisateurShibboleth implements SUP
{
    private $em;
    private $um;
    private $firstConnection = false;

    public function isFirstConnection() {
        return $this->firstConnection;
    }

    public function __construct(EntityManager $em, UserManager $um)
    {
        $this->em = $em;
        $this->um = $um;
    }

    public function loadUser(array $credentials)
    {
        $utilisateur = $this->um->findUserByUsername($credentials['uid']);
        if (empty($utilisateur)) {
            $this->firstConnection = true;
            $utilisateur = new Utilisateur();
        }

        $profil = $credentials['eduPersonPrimaryAffiliation'];
        $transco = [
            'alum' => 8,
            'student' => 4,
            'faculty' => 8,
            'staff' => 8,
            'employee' => 8,
            // 'member' => 8,
            // 'affiliate' => 8,
            // 'researcher' => 8,
            'retired' => 8,
            'emeritus' => 8,
            'teacher' => 8
        ];

        // 4 => Etudiant 
        // 8 => Personnel

        $arrayAffiliationText = explode(';', $credentials['eduPersonAffiliation']);
        $arrayAffiliationNumber = [];
        foreach ($arrayAffiliationText as $k => $affiliation) {
            if (isset($transco[$affiliation]))
                array_push($arrayAffiliationNumber, $transco[trim($affiliation)]);
        }

        $numberAffiliation = str_replace(array_keys($transco), array_values($transco), $credentials['eduPersonAffiliation']);
        $arrayAffiliationNumber = array_unique($arrayAffiliationNumber);


        if (in_array(4, $arrayAffiliationNumber) && in_array(8, $arrayAffiliationNumber)) {
            throw new \Exception('shibboleth.error.doctorant');
        } elseif (in_array(4, $arrayAffiliationNumber) && $credentials['ptdrouv'] > 0) {
            throw new \Exception('shibboleth.error.dossierincomplet');
        } elseif (!in_array(4, $arrayAffiliationNumber) && !in_array(8, $arrayAffiliationNumber)) {
            throw new \Exception('shibboleth.error.profilinconnu');
        }

        $objProfil = $this->em->getReference(ProfilUtilisateur::class, $transco[$profil]);
        $objCotisationSportive = $this->em->getReference(TypeAutorisation::class, 2);



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
        if(!$utilisateur->hasAutorisation($objCotisationSportive) && in_array(4, $arrayAffiliationNumber)) {
            $utilisateur->addAutorisation($objCotisationSportive);
        }
        $this->em->persist($utilisateur);
        $this->em->flush();
        return $utilisateur;
    }

    public function refreshUser(UserInterface $utilisateur)
    {
        return $utilisateur;
    }

    public function loadUserByUsername($username)
    {
    }

    public function supportsClass($class)
    {
    }
}
