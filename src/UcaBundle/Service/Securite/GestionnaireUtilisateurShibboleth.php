<?php

namespace UcaBundle\Service\Securite;

use Doctrine\ORM\EntityManager;
use FOS\UserBundle\Model\UserManager;
use Symfony\Component\Security\Core\User\UserInterface;
use UcaBundle\Entity\ProfilUtilisateur;
use UcaBundle\Entity\TypeAutorisation;
use UcaBundle\Entity\Utilisateur;
use UcaBundle\Exception\ShibbolethException;
use UniceSIL\ShibbolethBundle\Security\User\ShibbolethUserProviderInterface as SUP;

class GestionnaireUtilisateurShibboleth implements SUP
{
    private $em;
    private $um;
    private $firstConnection = false;

    public function __construct(EntityManager $em, UserManager $um)
    {
        $this->em = $em;
        $this->um = $um;
    }

    public function isFirstConnection()
    {
        return $this->firstConnection;
    }

    public function loadUser(array $credentials)
    {
        $utilisateur = $this->um->findUserByEmail($credentials['mail']);
        if (empty($utilisateur)) {
            $this->firstConnection = true;
            $utilisateur = new Utilisateur();
        }

        $transco = [
            // 'alum' => 8,
            'student' => 4,
            'faculty' => 8,
            'staff' => 8,
            'employee' => 8,
            // 'member' => 8,
            // 'affiliate' => 8,
            'researcher' => 8,
            // 'retired' => 8,
            // 'emeritus' => 8,
            'teacher' => 8,
            'registered-reader' => 8,
        ];

        // 4 => Etudiant
        // 8 => Personnel

        $arrayAffiliationText = explode(';', $credentials['eduPersonAffiliation']);
        $arrayAffiliationNumber = [];
        foreach ($arrayAffiliationText as $k => $affiliation) {
            if (isset($transco[$affiliation])) {
                array_push($arrayAffiliationNumber, $transco[trim($affiliation)]);
            }
        }

        $arrayAffiliationNumber = array_unique($arrayAffiliationNumber);

        if (!$this->firstConnection && !$utilisateur->isEnabled()) {
            throw new ShibbolethException('shibboleth.error.utilisateurbloque');
        }
        if (empty(array_diff($arrayAffiliationText, ['student', 'employee', 'researcher', 'member']))
            && empty(array_diff(['student', 'employee', 'researcher', 'member'], $arrayAffiliationText))) {
            throw new ShibbolethException('shibboleth.error.doctorant');
        }
        if (in_array(4, $arrayAffiliationNumber) && $credentials['ptdrouv'] > 0) {
            throw new ShibbolethException('shibboleth.error.dossierincomplet');
        }
        if (!in_array(4, $arrayAffiliationNumber) && !in_array(8, $arrayAffiliationNumber)) {
            throw new ShibbolethException('shibboleth.error.profilinconnu');
        }
        if (in_array(4, $arrayAffiliationNumber)) {
            $profil = 4;
        } elseif (in_array(8, $arrayAffiliationNumber)) {
            $profil = 8;
        }

        $objProfil = $this->em->getReference(ProfilUtilisateur::class, $profil);
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
            ->setEnabled(true)
        ;
        if (!$utilisateur->hasAutorisation($objCotisationSportive) && in_array(4, $arrayAffiliationNumber)) {
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
