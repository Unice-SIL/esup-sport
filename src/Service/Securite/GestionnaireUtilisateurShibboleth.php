<?php

/*
 * classe - GestionnaireUtilisateurShibboleth
 *
 * Service gérant la remonter des informations de shibboleth pour valider la connexion
*/

namespace App\Service\Securite;

use App\Entity\Uca\ProfilUtilisateur;
use App\Entity\Uca\TypeAutorisation;
use App\Entity\Uca\Utilisateur;
use App\Exception\ShibbolethException;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\User\UserInterface;
use UniceSIL\ShibbolethBundle\Security\Provider\AbstractShibbolethUserProvider;

class GestionnaireUtilisateurShibboleth extends AbstractShibbolethUserProvider
{
    private $em;
    private $userRepo;
    private $firstConnection = false;

    public function __construct(EntityManagerInterface $em, UtilisateurRepository $userRepo, RequestStack $requestStack)
    {
        parent::__construct($requestStack);
        $this->em = $em;
        $this->userRepo = $userRepo;
    }

    public function isFirstConnection()
    {
        return $this->firstConnection;
    }

    /**
     * @codeCoverageIgnore
     *
     * @param mixed $identifier
     */
    public function loadUserByIdentifier($identifier)
    {
        $shibbolethUserAttributes = $this->getAttributes();

        return $this->loadUser($shibbolethUserAttributes);
    }

    public function loadUser(array $credentials)
    {
        $utilisateur = $this->userRepo->findOneByUsername($credentials['eppn']);
        if (empty($utilisateur)) {
            $utilisateur = $this->userRepo->findOneByEmail($credentials['mail']);
        }
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
            ->setUsername($credentials['eppn'])
            ->setPassword(Utilisateur::getRandomPassword())
            ->setEmail($credentials['mail'])
            ->setMatricule($credentials['uid'])
            ->setProfil($objProfil)
            ->setNom($credentials['sn'])
            ->setPrenom($credentials['givenName'])
            ->setShibboleth(true)
            ->setEnabled(true)
            ->setNumeroNfc($credentials['mifare'])
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

    /**
     * @param mixed $username
     */
    public function loadUserByUsername($username)
    {
    }

    /**
     * @param mixed $class
     */
    public function supportsClass($class)
    {
    }
}
