<?php

namespace App\Tests\Entity\Uca;

use App\Entity\Uca\Autorisation;
use App\Entity\Uca\Commande;
use App\Entity\Uca\CommandeDetail;
use App\Entity\Uca\ComportementAutorisation;
use App\Entity\Uca\Creneau;
use App\Entity\Uca\FormatAchatCarte;
use App\Entity\Uca\FormatActivite;
use App\Entity\Uca\FormatAvecReservation;
use App\Entity\Uca\FormatSimple;
use App\Entity\Uca\Inscription;
use App\Entity\Uca\Lieu;
use App\Entity\Uca\Reservabilite;
use App\Entity\Uca\TypeAutorisation;
use App\Entity\Uca\Utilisateur;
use App\Repository\UtilisateurRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 * @coversNothing
 */
class CommandeDetailTest extends KernelTestCase
{
    /**
     * @covers \App\Entity\Uca\CommandeDetail::__construct
     */
    public function testConstructInscription(): void
    {
        // Création d'un format
        $formatSimple = (new FormatSimple())
            ->setDateDebutEffective(new DateTime())
            ->setDateFinEffective(new DateTime())
        ;

        // Création d'une inscription
        $inscription = (new Inscription(
            $formatSimple,
            new Utilisateur(),
            ['typeInscription' => 'format']
        ));
        $inscription->initAutorisations();

        // Création de l'objet commande détail
        $commandeDetail = new CommandeDetail(
            $this->createCommande(),
            'inscription',
            $inscription
        );

        $this->assertInstanceOf(CommandeDetail::class, $commandeDetail);
        $this->assertInstanceOf(Inscription::class, $commandeDetail->getInscription());
        $this->assertEquals($inscription, $commandeDetail->getInscription());
        $this->assertInstanceOf(FormatActivite::class, $commandeDetail->getFormatActivite());
        $this->assertEquals($formatSimple, $commandeDetail->getFormatActivite());
    }

    /**
     * @covers \App\Entity\Uca\CommandeDetail::__construct
     * @covers \App\Entity\Uca\Traits\Article::getArticleTva
     */
    public function testConstructFormat(): void
    {
        // Création d'un format
        $formatSimple = (new FormatSimple())
            ->setDateDebutEffective(new DateTime())
            ->setDateFinEffective(new DateTime())
        ;

        // Création d'une inscription
        $inscription = (new Inscription(
            $formatSimple,
            new Utilisateur(),
            ['typeInscription' => 'format']
        ));
        $inscription->initAutorisations();

        $article = $this->createCommandeDetailInscription();

        // Création de l'objet commande détail
        $commandeDetail = new CommandeDetail(
            $this->createCommande(),
            'format',
            $inscription,
            $article
        );

        $this->assertInstanceOf(CommandeDetail::class, $commandeDetail);
        $this->assertInstanceOf(Inscription::class, $commandeDetail->getInscription());
        $this->assertEquals($inscription, $commandeDetail->getInscription());
        $this->assertInstanceOf(FormatActivite::class, $commandeDetail->getFormatActivite());
        $this->assertEquals($formatSimple, $commandeDetail->getFormatActivite());
        $this->assertNotEmpty($commandeDetail->getLigneCommandeReferences());
        $this->assertEquals($article, $commandeDetail->getLigneCommandeReferences()[0]);
    }

    /**
     * @covers \App\Entity\Uca\CommandeDetail::__construct
     */
    public function testConstructAutorisation(): void
    {
        // Création de l'autorisation
        $typeAutorisation = (new TypeAutorisation())
            ->setComportement(
                (new ComportementAutorisation())
                    ->setCodeComportement('1234')
            )
        ;

        $article = $this->createCommandeDetailInscription();

        // Création de l'objet commande détail
        $commandeDetail = new CommandeDetail(
            $this->createCommande(),
            'autorisation',
            $typeAutorisation,
            $article
        );

        $this->assertInstanceOf(CommandeDetail::class, $commandeDetail);
        $this->assertNotEmpty($commandeDetail->getLigneCommandeReferences());
        $this->assertEquals($article, $commandeDetail->getLigneCommandeReferences()[0]);
    }

    /**
     * @covers \App\Entity\Uca\CommandeDetail::affichageDetailCommande
     */
    public function testAffichageDetailCommande(): void
    {
        $commandeDetail = $this->createCommandeDetailInscription();

        $affichageCommandeDetail = $commandeDetail->affichageDetailCommande();

        $this->assertIsBool($affichageCommandeDetail);
        $this->assertTrue($affichageCommandeDetail);
    }

    /**
     * @covers \App\Entity\Uca\CommandeDetail::affichageDetailCommande
     */
    public function testAffichageDetailCommandeFormat(): void
    {
        // Création d'un format
        $formatSimple = (new FormatSimple())
            ->setDateDebutEffective(new DateTime())
            ->setDateFinEffective(new DateTime())
        ;

        // Création d'une inscription
        $inscription = (new Inscription(
            $formatSimple,
            new Utilisateur(),
            ['typeInscription' => 'format']
        ));
        $inscription->initAutorisations();

        $article = $this->createCommandeDetailInscription();

        // Création de l'objet commande détail
        $commandeDetail = (new CommandeDetail(
            $this->createCommande(),
            'format',
            $inscription,
            $article
        ))->setTypeArticle('FormatAvecCreneau');

        $affichageCommandeDetail = $commandeDetail->affichageDetailCommande();

        $this->assertIsBool($affichageCommandeDetail);
        $this->assertFalse($affichageCommandeDetail);
    }

    /**
     * @covers \App\Entity\Uca\CommandeDetail::jsonSerializeProperties
     */
    public function testJsonSerializeProperties(): void
    {
        $commandeDetail = $this->createCommandeDetailInscription();

        $arrayProperties = $commandeDetail->jsonSerializeProperties();

        $this->assertIsArray($arrayProperties);
        $this->assertCount(6, $arrayProperties);
        $this->assertContains('date', $arrayProperties);
        $this->assertContains('statut', $arrayProperties);
        $this->assertContains('montant', $arrayProperties);
        $this->assertContains('formatActivite', $arrayProperties);
        $this->assertContains('creneau', $arrayProperties);
        $this->assertContains('typeAutorisation', $arrayProperties);
    }

    /**
     * @covers \App\Entity\Uca\CommandeDetail::setItem
     */
    public function testSetItemFormat(): void
    {
        $commandeDetail = $this->createCommandeDetailInscription();
        $format = new FormatSimple();
        $commandeDetail->setItem($format);

        $this->assertInstanceOf(FormatActivite::class, $commandeDetail->getFormatActivite());
        $this->assertEquals($format, $commandeDetail->getFormatActivite());
    }

    /**
     * @covers \App\Entity\Uca\CommandeDetail::setItem
     */
    public function testSetItemCreneau(): void
    {
        $commandeDetail = $this->createCommandeDetailInscription();
        $creneau = new Creneau();
        $commandeDetail->setItem($creneau);

        $this->assertInstanceOf(Creneau::class, $commandeDetail->getCreneau());
        $this->assertEquals($creneau, $commandeDetail->getCreneau());
    }

    /**
     * @covers \App\Entity\Uca\CommandeDetail::setItem
     */
    public function testSetItemReservabilite(): void
    {
        $commandeDetail = $this->createCommandeDetailInscription();
        $format = new FormatSimple();
        $reservabilite = (new Reservabilite())->setFormatActivite($format);
        $commandeDetail->setItem($reservabilite);

        $this->assertInstanceOf(Reservabilite::class, $commandeDetail->getReservabilite());
        $this->assertEquals($reservabilite, $commandeDetail->getReservabilite());
        $this->assertInstanceOf(FormatActivite::class, $commandeDetail->getFormatActivite());
        $this->assertEquals($format, $commandeDetail->getFormatActivite());
    }

    /**
     * @covers \App\Entity\Uca\CommandeDetail::setItem
     */
    public function testSetItemTypeAutorisation(): void
    {
        $commandeDetail = $this->createCommandeDetailInscription();
        $typeAutorisation = new TypeAutorisation();
        $commandeDetail->setItem($typeAutorisation);

        $this->assertInstanceOf(TypeAutorisation::class, $commandeDetail->getTypeAutorisation());
        $this->assertEquals($typeAutorisation, $commandeDetail->getTypeAutorisation());
    }

    /**
     * @covers \App\Entity\Uca\CommandeDetail::getItem
     */
    public function testGetItemFormat(): void
    {
        $commandeDetail = $this->createCommandeDetailInscription();
        $format = new FormatSimple();
        $commandeDetail->setItem($format);

        $this->assertInstanceOf(FormatActivite::class, $commandeDetail->getItem());
        $this->assertEquals($format, $commandeDetail->getItem());
    }

    /**
     * @covers \App\Entity\Uca\CommandeDetail::getItem
     */
    public function testGetItemCreneau(): void
    {
        $commandeDetail = $this->createCommandeDetailInscription();
        $creneau = new Creneau();
        $commandeDetail->setItem($creneau);

        $this->assertInstanceOf(Creneau::class, $commandeDetail->getItem());
        $this->assertEquals($creneau, $commandeDetail->getItem());
    }

    /**
     * @covers \App\Entity\Uca\CommandeDetail::getItem
     */
    public function testGetItemReservabilite(): void
    {
        $commandeDetail = $this->createCommandeDetailInscription();
        $format = new FormatSimple();
        $reservabilite = (new Reservabilite())->setFormatActivite($format);
        $commandeDetail->setItem($reservabilite);

        $this->assertInstanceOf(Reservabilite::class, $commandeDetail->getItem());
        $this->assertEquals($reservabilite, $commandeDetail->getItem());
    }

    /**
     * @covers \App\Entity\Uca\CommandeDetail::getItem
     */
    public function testGetItemTypeAutorisation(): void
    {
        $commandeDetail = ($this->createCommandeDetailInscription())->setFormatActivite(null);
        $typeAutorisation = new TypeAutorisation();
        $commandeDetail->setItem($typeAutorisation);

        $this->assertInstanceOf(TypeAutorisation::class, $commandeDetail->getItem());
        $this->assertEquals($typeAutorisation, $commandeDetail->getItem());
    }

    /**
     * @covers \App\Entity\Uca\CommandeDetail::sauvegardeInformations
     * @covers \App\Entity\Uca\Traits\Article::getArticleType
     */
    public function testSauvegardeInformations(): void
    {
        $now = new DateTime();

        $commandeDetail = $this->createCommandeDetailInscription();
        $format = (new FormatSimple())
            ->setLibelle('Format')
            ->setDescription('Description')
            ->setDateDebutEffective($now)
            ->setDateFinEffective($now)
        ;
        $commandeDetail->setItem($format);
        $commandeDetail->sauvegardeInformations();

        $this->assertIsString($commandeDetail->getLibelle());
        $this->assertEquals('Format ['.$now->format('d/m/Y H:i').']', $commandeDetail->getLibelle());

        $this->assertIsString($commandeDetail->getDescription());
        $this->assertEquals('Description', $commandeDetail->getDescription());

        $this->assertInstanceOf(DateTime::class, $commandeDetail->getDateDebut());
        $this->assertEquals($now, $commandeDetail->getDateDebut());

        $this->assertInstanceOf(DateTime::class, $commandeDetail->getDateFin());
        $this->assertEquals($now, $commandeDetail->getDateFin());

        $this->assertIsString($commandeDetail->getTypeArticle());
        $this->assertEquals('FormatSimple', $commandeDetail->getTypeArticle());
    }

    /**
     * @covers \App\Entity\Uca\CommandeDetail::traitementPostPaiement
     */
    public function testTraitementPostPaiementInscription(): void
    {
        $commandeDetail = $this->createCommandeDetailInscription();
        $commandeDetail->traitementPostPaiement();

        $this->assertEquals('valide', $commandeDetail->getInscription()->getStatut());
    }

    /**
     * @covers \App\Entity\Uca\CommandeDetail::traitementPostPaiement
     */
    public function testTraitementPostPaiementFormat(): void
    {
        $commandeDetail = $this->createCommandeDetailInscription();
        $commandeDetail->setType('format');
        $commandeDetail->traitementPostPaiement();

        $this->assertEquals('valide', $commandeDetail->getInscription()->getStatut());
    }

    /**
     * @covers \App\Entity\Uca\CommandeDetail::traitementPostPaiement
     */
    public function testTraitementPostPaiementAutorisation(): void
    {
        $commandeDetail = $this->createCommandeDetailInscription();
        $typeAutorisation = new TypeAutorisation();
        $commandeDetail->setItem($typeAutorisation);
        $commandeDetail->setType('autorisation');
        $commandeDetail->traitementPostPaiement();

        $this->assertNotEmpty($commandeDetail->getCommande()->getUtilisateur()->getAutorisations());
        $this->assertEquals($typeAutorisation, $commandeDetail->getCommande()->getUtilisateur()->getAutorisations()->first());
    }

    /**
     * @covers \App\Entity\Uca\CommandeDetail::traitementPostPaiement
     */
    public function testTraitementPostPaiementInscriptionAvecPartenaireParent(): void
    {
        $em = static::getContainer()->get(EntityManagerInterface::class);

        // Création de la ressource partenaire
        $ressource = (new Lieu())
            ->setLibelle('Lieu')
            ->setNbPartenaires(1)
            ->setNbPartenairesMax(1)
        ;

        // Création de la réservabilité
        $reservabilite = (new Reservabilite())->setRessource($ressource);

        // Création du format avec réservation
        $format = (new FormatAvecReservation())->addRessource($ressource);

        // Création de l'inscription partenaire parent
        $inscription = (new Inscription(
            $format,
            new Utilisateur(),
            ['typeInscription' => 'format']
        ))->setUtilisateur(null)->setFormatActivite(null);

        $em->persist($inscription);
        $em->flush();

        $inscription->setFormatActivite($format);
        $inscription->setReservabilite($reservabilite);

        // Création du détail de la commande
        $commandeDetail = new CommandeDetail(
            $this->createCommande(),
            'inscription',
            $inscription
        );

        $commandeDetail->traitementPostPaiement($em);

        $this->assertTrue($commandeDetail->getCommande()->getInscriptionAvecPartenaires());
        $this->assertEquals('attentepartenaire', $commandeDetail->getInscription()->getStatut());

        $em->remove($inscription);
        $em->flush();
    }

    /**
     * @covers \App\Entity\Uca\CommandeDetail::traitementPostPaiement
     */
    public function testTraitementPostPaiementInscriptionAvecPartenairePartenaire(): void
    {
        $em = static::getContainer()->get(EntityManagerInterface::class);

        // Création de la ressource partenaire
        $ressource = (new Lieu())
            ->setLibelle('Lieu')
            ->setNbPartenaires(1)
            ->setNbPartenairesMax(1)
        ;

        // Création de la réservabilité
        $reservabilite = (new Reservabilite())->setRessource($ressource);

        // Création du format avec réservation
        $format = (new FormatAvecReservation())->addRessource($ressource);

        // Création de l'inscription parent
        $inscriptionParent = (new Inscription(
            $format,
            new Utilisateur(),
            ['typeInscription' => 'format']
        ))->setUtilisateur(null)->setFormatActivite(null)->setStatut('valide');
        $em->persist($inscriptionParent);
        $em->flush();

        // Création de l'inscription partenaire
        $inscriptionPartenaire = (new Inscription(
            $format,
            new Utilisateur(),
            ['typeInscription' => 'format']
        ))->setUtilisateur(null)->setFormatActivite(null)->setEstPartenaire($inscriptionParent->getId());
        $em->persist($inscriptionPartenaire);
        $em->flush();

        $inscriptionParent->setFormatActivite($format);
        $inscriptionParent->setReservabilite($reservabilite);
        $inscriptionPartenaire->setFormatActivite($format);
        $inscriptionPartenaire->setReservabilite($reservabilite);

        // Création du détail de la commande
        $commandeDetail = new CommandeDetail(
            $this->createCommande(),
            'inscription',
            $inscriptionPartenaire
        );

        $commandeDetail->traitementPostPaiement($em);

        $this->assertTrue($commandeDetail->getCommande()->getInscriptionAvecPartenaires());
        $this->assertEquals('valide', $commandeDetail->getInscription()->getStatut());

        $em->remove($inscriptionParent);
        $em->remove($inscriptionPartenaire);
        $em->flush();
    }

    /**
     * @covers \App\Entity\Uca\CommandeDetail::traitementPostAnnulation
     */
    public function testTraitementPostAnnulationInscription(): void
    {
        $commandeDetail = ($this->createCommandeDetailInscription())->setType('inscription');

        $commandeDetail->traitementPostAnnulation([]);

        $this->assertIsString($commandeDetail->getInscription()->getStatut());
        $this->assertEquals('annule', $commandeDetail->getInscription()->getStatut());
    }

    /**
     * @covers \App\Entity\Uca\CommandeDetail::traitementPostAnnulation
     */
    public function testTraitementPostAnnulationFormat(): void
    {
        $commandeDetail = ($this->createCommandeDetailInscription())->setType('format');

        $commandeDetail->traitementPostAnnulation([]);

        $this->assertIsString($commandeDetail->getInscription()->getStatut());
        $this->assertEquals('annule', $commandeDetail->getInscription()->getStatut());
    }

    /**
     * @covers \App\Entity\Uca\CommandeDetail::traitementPostAnnulation
     */
    public function testTraitementPostAnnulationAutorisation(): void
    {
        $commandeDetail = ($this->createCommandeDetailInscription())->setType('autorisation');

        $commandeDetail->traitementPostAnnulation([]);

        $this->assertIsString($commandeDetail->getInscription()->getStatut());
        $this->assertEquals('attentepaiement', $commandeDetail->getInscription()->getStatut());
    }

    /**
     * @covers \App\Entity\Uca\CommandeDetail::remove
     */
    public function testRemove(): void
    {
        $commandeDetail = ($this->createCommandeDetailInscription())->setType('format');
        $commande = $commandeDetail->getCommande();

        $commandeDetail->remove();

        $this->assertNull($commandeDetail->getCommande());
        $this->assertFalse($commande->getCommandeDetails()->contains($commandeDetail));
    }

    /**
     * @covers \App\Entity\Uca\CommandeDetail::isRemovable
     */
    public function testIsRemovableInscription(): void
    {
        $commandeDetail = ($this->createCommandeDetailInscription())->setType('inscription');

        $this->assertIsBool($commandeDetail->isRemovable());
        $this->assertTrue($commandeDetail->isRemovable());
    }

    /**
     * @covers \App\Entity\Uca\CommandeDetail::isRemovable
     */
    public function testIsRemovableFormatEmpty(): void
    {
        $commandeDetail = ($this->createCommandeDetailInscription())->setType('format');

        $this->assertIsBool($commandeDetail->isRemovable());
        $this->assertTrue($commandeDetail->isRemovable());
    }

    /**
     * @covers \App\Entity\Uca\CommandeDetail::isRemovable
     */
    public function testIsRemovableFormatNotEmpty(): void
    {
        $commandeDetail = ($this->createCommandeDetailInscription())->setType('format')->addLigneCommandeReference($this->createCommandeDetailInscription());

        $this->assertIsBool($commandeDetail->isRemovable());
        $this->assertFalse($commandeDetail->isRemovable());
    }

    /**
     * @covers \App\Entity\Uca\CommandeDetail::isRemovable
     */
    public function testIsRemovableAutorisationEmpty(): void
    {
        $commandeDetail = ($this->createCommandeDetailInscription())->setType('autorisation');

        $this->assertIsBool($commandeDetail->isRemovable());
        $this->assertTrue($commandeDetail->isRemovable());
    }

    /**
     * @covers \App\Entity\Uca\CommandeDetail::isRemovable
     */
    public function testIsRemovableAutorisationNotEmpty(): void
    {
        $commandeDetail = ($this->createCommandeDetailInscription())->setType('autorisation')->addLigneCommandeReference($this->createCommandeDetailInscription());

        $this->assertIsBool($commandeDetail->isRemovable());
        $this->assertFalse($commandeDetail->isRemovable());
    }

    /**
     * @covers \App\Entity\Uca\CommandeDetail::voir
     */
    public function testVoirAutorisation(): void
    {
        $commandeDetail = ($this->createCommandeDetailInscription())->setType('autorisation');

        $this->assertIsBool($commandeDetail->voir());
        $this->assertFalse($commandeDetail->voir());
    }

    /**
     * @covers \App\Entity\Uca\CommandeDetail::voir
     */
    public function testVoirInscription(): void
    {
        $commandeDetail = ($this->createCommandeDetailInscription())->setType('inscription');

        $this->assertIsBool($commandeDetail->voir());
        $this->assertTrue($commandeDetail->voir());
    }

    /**
     * @covers \App\Entity\Uca\CommandeDetail::voir
     */
    public function testVoirFormat(): void
    {
        $commandeDetail = ($this->createCommandeDetailInscription())->setType('format');

        $this->assertIsBool($commandeDetail->voir());
        $this->assertTrue($commandeDetail->voir());
    }

    /**
     * @covers \App\Entity\Uca\CommandeDetail::isFormatCarte
     */
    public function testIsFormatCarteFalse(): void
    {
        $commandeDetail = ($this->createCommandeDetailInscription())->setType('format');

        $this->assertIsBool($commandeDetail->isFormatCarte());
        $this->assertFalse($commandeDetail->isFormatCarte());
    }

    /**
     * @covers \App\Entity\Uca\CommandeDetail::isFormatCarte
     */
    public function testIsFormatCarteTrue(): void
    {
        $commandeDetail = ($this->createCommandeDetailInscription())->setType('format')->setFormatActivite(new FormatAchatCarte());

        $this->assertIsBool($commandeDetail->isFormatCarte());
        $this->assertTrue($commandeDetail->isFormatCarte());
    }

    /**
     * @covers \App\Entity\Uca\CommandeDetail::appartientAvoir
     */
    public function testAppartientAvoirFalse(): void
    {
        $commandeDetail = $this->createCommandeDetailInscription();

        $this->assertIsBool($commandeDetail->appartientAvoir());
        $this->assertFalse($commandeDetail->appartientAvoir());
    }

    /**
     * @covers \App\Entity\Uca\CommandeDetail::appartientAvoir
     */
    public function testAppartientAvoir(): void
    {
        $commandeDetail = $this->createCommandeDetailInscription();
        $commandeDetail->getCommande()->addAvoirCommandeDetail($commandeDetail);

        $this->assertInstanceOf(Commande::class, $commandeDetail->appartientAvoir());
        $this->assertEquals($commandeDetail->getCommande(), $commandeDetail->appartientAvoir());
    }

    /**
     * @covers \App\Entity\Uca\CommandeDetail::eligibleAvoir
     */
    public function testEligibleAvoir(): void
    {
        $commandeDetail = ($this->createCommandeDetailInscription())->setMontant(10);

        $this->assertInstanceOf(CommandeDetail::class, $commandeDetail->eligibleAvoir());
        $this->assertEquals($commandeDetail, $commandeDetail->eligibleAvoir());
    }

    /**
     * @covers \App\Entity\Uca\CommandeDetail::eligibleAvoir
     */
    public function testEligibleAvoirFalse(): void
    {
        $commandeDetail = $this->createCommandeDetailInscription();
        $commandeDetail->getCommande()->addAvoirCommandeDetail($commandeDetail);

        $this->assertIsBool($commandeDetail->eligibleAvoir());
        $this->assertFalse($commandeDetail->eligibleAvoir());
    }

    /**
     * @covers \App\Entity\Uca\CommandeDetail::traitementPostSuppressionPanier
     */
    public function testTraitementPostSuppressionPanierAutorisationWithReference(): void
    {
        $commandeDetail = ($this->createCommandeDetailInscription())
            ->setType('autorisation')
            ->addLigneCommandeReference($this->createCommandeDetailInscription())
        ;

        $suppression = $commandeDetail->traitementPostSuppressionPanier();

        $this->assertIsBool($suppression);
        $this->assertFalse($suppression);
    }

    /**
     * @covers \App\Entity\Uca\CommandeDetail::traitementPostSuppressionPanier
     */
    public function testTraitementPostSuppressionPanierAutorisationWithoutReference(): void
    {
        $commandeDetail = ($this->createCommandeDetailInscription())
            ->setType('autorisation')
        ;

        $suppression = $commandeDetail->traitementPostSuppressionPanier();

        $this->assertIsBool($suppression);
        $this->assertTrue($suppression);
    }

    /**
     * @covers \App\Entity\Uca\CommandeDetail::traitementPostSuppressionPanier
     */
    public function testTraitementPostSuppressionPanierFormatWithReference(): void
    {
        $commandeDetail = ($this->createCommandeDetailInscription())
            ->setType('format')
            ->addLigneCommandeReference($this->createCommandeDetailInscription())
        ;

        $suppression = $commandeDetail->traitementPostSuppressionPanier();

        $this->assertIsBool($suppression);
        $this->assertFalse($suppression);
    }

    /**
     * @covers \App\Entity\Uca\CommandeDetail::traitementPostSuppressionPanier
     */
    public function testTraitementPostSuppressionPanierFormatWithoutReference(): void
    {
        $commandeDetail = ($this->createCommandeDetailInscription())
            ->setType('format')
        ;

        $suppression = $commandeDetail->traitementPostSuppressionPanier();

        $this->assertIsBool($suppression);
        $this->assertTrue($suppression);
        $this->assertEquals('annule', $commandeDetail->getInscription()->getStatut());
    }

    /**
     * @covers \App\Entity\Uca\CommandeDetail::traitementPostSuppressionPanier
     */
    public function testTraitementPostSuppressionPanierInscriptionWithCommandeLiees(): void
    {
        $commandeDetail = ($this->createCommandeDetailInscription())
            ->setType('inscription')
        ;

        $suppression = $commandeDetail->traitementPostSuppressionPanier();

        $this->assertIsBool($suppression);
        $this->assertTrue($suppression);
        $this->assertEquals('annule', $commandeDetail->getInscription()->getStatut());
    }

    /**
     * @covers \App\Entity\Uca\CommandeDetail::traitementPostSuppressionPanier
     */
    public function testTraitementPostSuppressionPanierInscriptionWithoutCommandeLiees(): void
    {
        $commandeDetailLiee = $this->createCommandeDetailInscription();
        $commandeDetail = ($this->createCommandeDetailInscription())
            ->setType('inscription')
            ->addLigneCommandeLiee($commandeDetailLiee)
        ;

        $suppression = $commandeDetail->traitementPostSuppressionPanier();

        $this->assertIsBool($suppression);
        $this->assertTrue($suppression);
        $this->assertEquals('annule', $commandeDetail->getInscription()->getStatut());
        $this->assertFalse($commandeDetail->getLigneCommandeLiees()->contains($commandeDetailLiee));
    }

    /**
     * @covers \App\Entity\Uca\CommandeDetail::traitementPostGenerationAvoir
     */
    public function testTraitementPostGenerationAvoirInscription(): void
    {
        $commandeDetail = ($this->createCommandeDetailInscription())->setType('inscription');

        $commandeDetail->traitementPostGenerationAvoir();

        $this->assertEquals('annule', $commandeDetail->getInscription()->getStatut());
        $this->assertEmpty($commandeDetail->getInscription()->getAutorisations());
    }

    /**
     * @covers \App\Entity\Uca\CommandeDetail::traitementPostGenerationAvoir
     */
    public function testTraitementPostGenerationAvoirInscriptionAvoir(): void
    {
        $commandeDetail = ($this->createCommandeDetailInscription())
            ->setType('autorisation')
            ->setTypeAutorisation(new TypeAutorisation())
            ->setReferenceAvoir(1234)
        ;

        $commandeDetail->traitementPostGenerationAvoir();

        $this->assertEmpty($commandeDetail->getInscription()->getAutorisations());
        $this->assertEmpty($commandeDetail->getCommande()->getUtilisateur()->getAutorisations());
    }

    /**
     * @covers \App\Entity\Uca\CommandeDetail::traitementPostGenerationAvoir
     */
    public function testTraitementPostGenerationAvoirAchatCarte(): void
    {
        $typeAutorisation = (new TypeAutorisation())->setComportementLibelle('Achat de Carte');
        $format = (new FormatAchatCarte())->setCarte($typeAutorisation);

        $commandeDetail = ($this->createCommandeDetailInscription())
            ->setType('autorisation')
            ->setTypeAutorisation($typeAutorisation)
            ->setReferenceAvoir(1234)
            ->setFormatActivite($format)
        ;
        $commandeDetailInscription = ($this->createCommandeDetailInscription())
            ->setType('inscription')
            ->setCommande($commandeDetail->getCommande())
            ->setFormatActivite($format)
        ;
        $commandeDetailInscription->getInscription()->setFormatActivite($format);
        $commandeDetail->getCommande()->addCommandeDetail($commandeDetailInscription);

        $commandeDetail->traitementPostGenerationAvoir();

        $this->assertEmpty($commandeDetail->getInscription()->getAutorisations());
        $this->assertEmpty($commandeDetail->getCommande()->getUtilisateur()->getAutorisations());
        $this->assertEquals('desinscrit', $commandeDetailInscription->getInscription()->getStatut());
    }

    private function createCommande(): Commande
    {
        return new Commande(static::getContainer()->get(UtilisateurRepository::class)->findOneByUsername('admin'));
    }

    private function createCommandeDetailInscription(): CommandeDetail
    {
        // Création d'un format
        $formatSimple = (new FormatSimple())
            ->setDateDebutEffective(new DateTime())
            ->setDateFinEffective(new DateTime())
        ;

        // Création d'une inscription
        $inscription = (new Inscription(
            $formatSimple,
            new Utilisateur(),
            ['typeInscription' => 'format']
        ));
        $inscription->initAutorisations();

        // Création de l'objet commande détail
        return new CommandeDetail(
            $this->createCommande(),
            'inscription',
            $inscription
        );
    }
}