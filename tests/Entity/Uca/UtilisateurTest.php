<?php

namespace App\Tests\Entity\Uca;

use App\Entity\Uca\Commande;
use App\Entity\Uca\CommandeDetail;
use App\Entity\Uca\ComportementAutorisation;
use App\Entity\Uca\Creneau;
use App\Entity\Uca\DhtmlxEvenement;
use App\Entity\Uca\DhtmlxSerie;
use App\Entity\Uca\FormatAchatCarte;
use App\Entity\Uca\FormatAvecCreneau;
use App\Entity\Uca\FormatSimple;
use App\Entity\Uca\Groupe;
use App\Entity\Uca\Inscription;
use App\Entity\Uca\ProfilUtilisateur;
use App\Entity\Uca\Reservabilite;
use App\Entity\Uca\TypeAutorisation;
use App\Entity\Uca\Utilisateur;
use App\Entity\Uca\UtilisateurCreditHistorique;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @internal
 * @coversNothing
 */
class UtilisateurTest extends TestCase
{
    /**
     * @var Utilisateur
     */
    private $utilisateur;

    /**
     * Fonction qui s'exécute avant chaque test.
     */
    protected function setUp(): void
    {
        $this->utilisateur = (new Utilisateur())
            ->setNom('nom')
            ->setprenom('prenom')
            ->setUsername('username')
            ->setMatricule('matricule')
        ;

        $this->commande = (new Commande($this->utilisateur))
            ->setStatut('panier')
            ->setMatricule('mat')
        ;

        $this->utilisateur->addCommande($this->commande);

        $comportementAutorisationFormat = (new ComportementAutorisation())
            ->setdescriptionComportement('desc')
            ->setLibelle('libelle comportement')
            ->setCodeComportement('code')
        ;

        $typeAutorisationFormat = (new TypeAutorisation())
            ->setComportement(
                $comportementAutorisationFormat
            )
            ->setLibelle('')
        ;

        $this->format =
            (new FormatAchatCarte())
                ->setCarte(
                    $typeAutorisationFormat
                )
        ;

        $comportementAutorisation = (new ComportementAutorisation())
            ->setdescriptionComportement('desc2')
            ->setLibelle('libelle comportement2')
            ->setCodeComportement('code')
        ;

        $this->typeAutorisation = (new TypeAutorisation())
            ->setComportement(
                $comportementAutorisation
            )
            ->setLibelle('')
        ;

        $this->inscription = new Inscription($this->format, $this->utilisateur, []);

        $date = new \DateTime('2022-06-09');

        $formatActivite =
            (new FormatAvecCreneau())
                ->setLibelle('FormatAvecCreneau')
                ->addAutorisation($this->typeAutorisation)
                ->setCapacite(10)
                ->setDescription('')
                ->setDateDebutPublication($date)
                ->setDateFinPublication($date)
                ->setDateDebutInscription($date)
                ->setDateFinInscription($date)
                ->setDateDebutEffective($date)
                ->setDateFinEffective($date)
                ->setImage('')
                ->setEstPayant(false)
                ->setEstEncadre(false)
        ;

        $this->creneau = (new Creneau())
            ->setFormatActivite($formatActivite)
        ;

        $reservabilite = new Reservabilite();

        $date = new \Datetime();
        $this->evenement =
            (new DhtmlxEvenement())
                ->setDependanceSerie(true)
                ->setDescription('evenement Test')
                ->setDateDebut($date)
                ->setDateFin($date)
            ;

        $reservabilite->setEvenement($this->evenement);

        $this->inscription->setFormatActivite($formatActivite);
        $this->inscription->setCreneau($this->creneau);
        $this->inscription->setReservabilite($reservabilite);

        $this->utilisateur->addAutorisation($this->typeAutorisation);

        $this->profilUtilisateur = (new ProfilUtilisateur())
            ->setNbMaxInscriptions(10)
            ->setNbMaxInscriptionsRessource(10)
        ;

        $this->utilisateur->setProfil($this->profilUtilisateur);

        $commandeDetail = new CommandeDetail($this->commande, 'inscription', $this->inscription);
        $commandeDetail->setAvoir($this->commande);
        $commandeDetail->setReferenceAvoir(10);

        $this->inscription->addCommandeDetail($commandeDetail);

        $this->userAddAutorisation = new utilisateur();
        $inscriptionAddAutorisation = new Inscription($this->format, $this->userAddAutorisation, []);

        $comportementAutorisation = (new ComportementAutorisation())
            ->setdescriptionComportement('d')
            ->setLibelle('libelle')
            ->setCodeComportement('c')
        ;

        $this->typeAutorisationAddAutorisation = (new TypeAutorisation())
            ->setComportement(
                $comportementAutorisation
            )
            ->setLibelle('autorisation')
        ;

        $tmp_commandeDetail = new CommandeDetail($this->commande, 'inscription', $inscriptionAddAutorisation);

        $commandeDetail = new CommandeDetail($this->commande, 'autorisation', $this->typeAutorisationAddAutorisation, $tmp_commandeDetail);
        $commandeDetail->setAvoir($this->commande);
        $commandeDetail->setReferenceAvoir(10);

        $inscriptionAddAutorisation->addCommandeDetail($commandeDetail);
    }

    /**
     * @covers \App\Entity\Uca\Utilisateur::__toString
     */
    public function testToString(): void
    {
        $this->assertEquals($this->utilisateur->__toString(), 'Prenom Nom');
    }

    /**
     * @covers \App\Entity\Uca\Utilisateur::getUserIdentifier
     */
    public function testGetUserIdentifier(): void
    {
        $this->assertEquals($this->utilisateur->getUserIdentifier(), 'username');
    }

    /**
     * @covers \App\Entity\Uca\Utilisateur::getRandomPassword
     */
    public function testGetRandomPassword(): void
    {
        $this->assertIsString($this->utilisateur->getRandomPassword());
        $this->assertEquals(strlen($this->utilisateur->getRandomPassword()), 16);
    }

    /**
     * @covers \App\Entity\Uca\Utilisateur::jsonSerializeProperties
     */
    public function testJsonSerializeProperties(): void
    {
        $this->assertEquals($this->utilisateur->jsonSerializeProperties(), ['prenom', 'nom']);
    }

    /**  @covers \App\Entity\Uca\Utilisateur::getCommandesByCriteria
     */
    public function testGetCommandesByCriteria(): void
    {
        $this->assertEquals($this->utilisateur->getCommandesByCriteria([['statut', 'eq', 'panier']])->first(), $this->commande);
    }

    /**  @covers \App\Entity\Uca\Utilisateur::getCommandesByStatut
     */
    public function testGetCommandesByStatut(): void
    {
        $this->assertEquals($this->utilisateur->getCommandesByStatut('panier')->first(), $this->commande);
    }

    /**  @covers \App\Entity\Uca\Utilisateur::getCommandeByAvoir
     */
    public function testGetCommandeByAvoir(): void
    {
        $this->assertTrue($this->utilisateur->getCommandeByAvoir(10) === $this->commande->getId());
        $this->assertFalse($this->utilisateur->getCommandeByAvoir(1));
    }

    /**  @covers \App\Entity\Uca\Utilisateur::getPanier
     */
    public function testGetPanier(): void
    {
        $this->assertFalse($this->utilisateur->getPanier()->getMatricule() === (new Commande($this->utilisateur))->getMatricule());
        $this->utilisateur->removeCommande($this->commande);
        $this->assertTrue($this->utilisateur->getPanier()->getMatricule() === (new Commande($this->utilisateur))->getMatricule());
    }

    /**  @covers \App\Entity\Uca\Utilisateur::getInscriptionsByCriteria
     */
    public function testGetInscriptionsByCriteria(): void
    {
        $this->assertTrue($this->utilisateur->getInscriptionsByCriteria([['statut', 'eq', 'attentepaiement']])->first() === $this->inscription);
    }

    /**  @covers \App\Entity\Uca\Utilisateur::hasInscriptionsByCriteria
     */
    public function testHasInscriptionsByCriteria(): void
    {
        $this->assertTrue($this->utilisateur->hasInscriptionsByCriteria([['statut', 'eq', 'attentepaiement']]));
    }

    /**  @covers \App\Entity\Uca\Utilisateur::hasAutorisation
     */
    public function testHasAutorisation(): void
    {
        $this->assertTrue($this->utilisateur->hasAutorisation($this->typeAutorisation));
    }

    /**  @covers \App\Entity\Uca\Utilisateur::getNbInscriptionCreneau
     */
    public function testGetNbInscriptionCreneau(): void
    {
        $this->assertEquals($this->utilisateur->getNbInscriptionCreneau(), 1);
    }

    /**  @covers \App\Entity\Uca\Utilisateur::nbCreneauMaximumAtteint
     */
    public function testNbCreneauMaximumAtteint(): void
    {
        $this->assertFalse($this->utilisateur->nbCreneauMaximumAtteint());
    }

    /**  @covers \App\Entity\Uca\Utilisateur::isValidInscriptionRessource
     */
    public function testIsValidInscriptionRessource(): void
    {
        $this->assertTrue($this->utilisateur->isValidInscriptionRessource($this->inscription, $this->inscription->getDate()));
    }

    /**  @covers \App\Entity\Uca\Utilisateur::getNbInscriptionRessource
     */
    public function testGetNbInscriptionRessource(): void
    {
        $this->assertEquals($this->utilisateur->getNbInscriptionRessource($this->inscription->getReservabilite()), 1);
    }

    /**  @covers \App\Entity\Uca\Utilisateur::nbRessourceMaximumAtteint
     */
    public function testNbRessourceMaximumAtteint(): void
    {
        $this->assertFalse($this->utilisateur->nbRessourceMaximumAtteint($this->inscription->getReservabilite()));
        $this->utilisateur->getProfil()->setNbMaxInscriptionsRessource(1);
        $this->assertTrue($this->utilisateur->nbRessourceMaximumAtteint($this->inscription->getReservabilite()));
    }

    /**  @covers \App\Entity\Uca\Utilisateur::setDocumentFile
     */
    public function testSetDocumentFile(): void
    {
        $lastUpdate = $this->utilisateur->getUpdatedAt();
        $file = new File('src/Entity/Uca/Utilisateur.php');
        $this->utilisateur->setDocumentFile($file);

        $this->assertEquals($this->utilisateur->getDocumentFile(), $file);
        $this->assertNotEquals($this->utilisateur->getUpdatedAt(), $lastUpdate);
    }

    /**  @covers \App\Entity\Uca\Utilisateur::getDocumentFile
     */
    public function testGetDocumentFile(): void
    {
        $lastUpdate = $this->utilisateur->getUpdatedAt();
        $file = new File('src/Entity/Uca/Utilisateur.php');
        $this->utilisateur->setDocumentFile($file);

        $this->assertEquals($this->utilisateur->getDocumentFile(), $file);
    }

    /**  @covers \App\Entity\Uca\Utilisateur::addAutorisation
     */
    public function testAddAutorisation(): void
    {
        $this->assertFalse($this->userAddAutorisation->getAutorisations()->contains($this->typeAutorisationAddAutorisation));

        $this->userAddAutorisation->addAutorisation($this->typeAutorisationAddAutorisation);

        $this->assertTrue($this->userAddAutorisation->getAutorisations()->contains($this->typeAutorisationAddAutorisation));
        
        $this->assertEquals($this->utilisateur, $this->utilisateur->addAutorisation($this->typeAutorisationAddAutorisation));
    }

    /**  @covers \App\Entity\Uca\Utilisateur::isEncadrantEvenement
     */
    public function testIsEncadrantEvenement(): void
    {
        $this->assertFalse($this->utilisateur->isEncadrantEvenement($this->evenement));

        $this->evenement->setFormatSimple(new FormatSimple());
        $serie = new DhtmlxSerie();
        $serie->setCreneau($this->creneau);
        $this->evenement->setSerie($serie);
        $this->evenement->getSerie()->getCreneau()->addEncadrant($this->utilisateur);

        $this->assertTrue($this->utilisateur->isEncadrantEvenement($this->evenement));

        $this->evenement->setFormatSimple((new FormatSimple())
        ->setEstEncadre(true)
        ->addEncadrant($this->utilisateur));

        $serie = new DhtmlxSerie();
        $serie->setCreneau(null);
        $this->evenement->setSerie(null);
        $this->assertTrue($this->utilisateur->isEncadrantEvenement($this->evenement));

    }

    /**  @covers \App\Entity\Uca\Utilisateur::getEmailDomain
     */
    public function testGetEmailDomain(): void
    {
        $this->assertEquals($this->utilisateur->getEmailDomain(), null);

        $this->utilisateur->setEmail('test@test.com');

        $this->assertEquals($this->utilisateur->getEmailDomain(), '@test.com');
    }

    /**  @covers \App\Entity\Uca\Utilisateur::getCreditTotal
     */
    public function testGetCreditTotal(): void
    {
        $this->assertEquals($this->utilisateur->getCreditTotal(), 0);

        $credit = new UtilisateurCreditHistorique($this->utilisateur, 10, null, 'credit', null);
        $this->utilisateur->addCredit($credit);

        $this->assertEquals($this->utilisateur->getCreditTotal(), 10);
    }

    /**  @covers \App\Entity\Uca\Utilisateur::addRole
     */
    public function testAddRole(): void
    {
        $this->assertFalse(in_array('ROLE_USER', $this->utilisateur->getRoles()) && in_array('ROLE_ADMIN', $this->utilisateur->getRoles()));

        $this->utilisateur->addRole('ROLE_ADMIN');

        $this->assertTrue(in_array('ROLE_USER', $this->utilisateur->getRoles()) && in_array('ROLE_ADMIN', $this->utilisateur->getRoles()));

        $this->assertSame($this->utilisateur, $this->utilisateur->addRole('ROLE_USER'));
    }

    /**  @covers \App\Entity\Uca\Utilisateur::getRoles
     */
    public function testGetRoles(): void
    {
        $this->assertFalse(in_array('ROLE_USER', $this->utilisateur->getRoles()) && in_array('ROLE_ADMIN', $this->utilisateur->getRoles()));

        $this->utilisateur->addGroup(new Groupe('test', ['ROLE_ADMIN', 'ROLE_USER']));

        $this->assertTrue(in_array('ROLE_USER', $this->utilisateur->getRoles()) && in_array('ROLE_ADMIN', $this->utilisateur->getRoles()));
    }

    /**  @covers \App\Entity\Uca\Utilisateur::hasRole
     */
    public function testHasRole(): void
    {
        $this->assertTrue($this->utilisateur->hasRole('ROLE_USER'));
    }

    /**  @covers \App\Entity\Uca\Utilisateur::removeRole
     */
    public function testRemoveRole(): void
    {
        $this->assertTrue(in_array('ROLE_ADMIN', $this->utilisateur->addRole('ROLE_ADMIN')->getRoles()));
        $this->assertFalse(in_array('ROLE_ADMIN', $this->utilisateur->removeRole('ROLE_ADMIN')->getRoles()));
    }

    /**  @covers \App\Entity\Uca\Utilisateur::isPasswordRequestNonExpired
     */
    public function testIsPasswordRequestNonExpired(): void
    {
        $this->assertFalse($this->utilisateur->isPasswordRequestNonExpired(1));
    }

    /**  @covers \App\Entity\Uca\Utilisateur::setRoles
     */
    public function testSetRoles(): void
    {
        $this->assertTrue($this->utilisateur->setRoles(['ROLE_ADMIN'])->getRoles() == ['ROLE_ADMIN', 'ROLE_USER']);
    }

    /**  @covers \App\Entity\Uca\Utilisateur::getGroupNames
     */
    public function testGetGroupNames(): void
    {
        $this->assertFalse(in_array('test', $this->utilisateur->getGroupNames()));

        $this->utilisateur->addGroup(new Groupe('test'));

        $this->assertTrue(in_array('test', $this->utilisateur->getGroupNames()));
    }

    /**  @covers \App\Entity\Uca\Utilisateur::hasGroup
     */
    public function testHasGroup(): void
    {
        $this->assertFalse($this->utilisateur->hasGroup('test'));

        $this->utilisateur->addGroup(new Groupe('test'));

        $this->assertTrue($this->utilisateur->hasGroup('test'));
    }

    /**  @covers \App\Entity\Uca\Utilisateur::addGroup
     */
    public function testAddGroup(): void
    {
        $group = new Groupe('test');

        $this->assertFalse($this->utilisateur->getGroups()->contains($group));

        $this->utilisateur->addGroup($group);

        $this->assertTrue($this->utilisateur->getGroups()->contains($group));
    }

    /**  @covers \App\Entity\Uca\Utilisateur::removeGroup
     */
    public function testRemoveGroup(): void
    {
        $group = new Groupe('test');

        $this->utilisateur->addGroup($group);

        $this->assertTrue($this->utilisateur->getGroups()->contains($group));

        $this->utilisateur->removeGroup($group);

        $this->assertFalse($this->utilisateur->getGroups()->contains($group));
    }
}
