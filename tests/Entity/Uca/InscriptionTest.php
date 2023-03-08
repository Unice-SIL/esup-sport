<?php

namespace App\Tests\Entity\Uca;

use App\Entity\Uca\Autorisation;
use App\Entity\Uca\Commande;
use App\Entity\Uca\CommandeDetail;
use App\Entity\Uca\ComportementAutorisation;
use App\Entity\Uca\Creneau;
use App\Entity\Uca\DhtmlxEvenement;
use App\Entity\Uca\DhtmlxSerie;
use App\Entity\Uca\FormatAchatCarte;
use App\Entity\Uca\FormatAvecCreneau;
use App\Entity\Uca\Inscription;
use App\Entity\Uca\Lieu;
use App\Entity\Uca\Reservabilite;
use App\Entity\Uca\Tarif;
use App\Entity\Uca\TypeAutorisation;
use App\Entity\Uca\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 * @coversNothing
 */
class InscriptionTest extends KernelTestCase
{
    /**
     * @var Inscription
     */
    private $inscription;

    /**
     * Fonction qui s'exécute avant chaque test.
     */
    protected function setUp(): void
    {
        $this->em = static::getContainer()->get(EntityManagerInterface::class);

        $this->utilisateur = (new Utilisateur())
            ->setUserName('Inscription')
            ->setEmail('test@test.com')
            ->setPassword('test')
        ;

        $this->comportementAutorisationFormat = (new ComportementAutorisation())
            ->setdescriptionComportement('desc')
            ->setLibelle('libelle comportement')
            ->setCodeComportement('code')
        ;

        $this->typeAutorisationFormat = (new TypeAutorisation())
            ->setComportement(
                $this->comportementAutorisationFormat
            )
            ->setLibelle('')
        ;

        $this->format =
            (new FormatAchatCarte())
                ->setCarte(
                    $this->typeAutorisationFormat
                )

        ;

        $this->comportementAutorisation = (new ComportementAutorisation())
            ->setdescriptionComportement('desc2')
            ->setLibelle('libelle comportement2')
            ->setCodeComportement('code')
        ;

        $this->typeAutorisation = (new TypeAutorisation())
            ->setComportement(
                $this->comportementAutorisation
            )
            ->setLibelle('')
        ;

        $this->inscription = new Inscription($this->format, $this->utilisateur, []);

        $date = new \DateTime();

        $this->formatActivite =
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
        $this->inscription->setFormatActivite($this->formatActivite);

        $this->utilisateur->addInscription($this->inscription);
    }

    /**
     * @covers \App\Entity\Uca\Inscription::__construct
     * @covers \App\Entity\Uca\Inscription::setItem
     */
    public function testConstruct(): void
    {
        $utilisateur1 = (new Utilisateur())
            ->setUserName('Inscription')
            ->setEmail('test@test.com')
            ->setPassword('test')
        ;

        $utilisateur2 = (new Utilisateur())
            ->setUserName('Inscription')
            ->setEmail('test@test.com')
            ->setPassword('test')
        ;

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

        $format1 =
            (new FormatAchatCarte())
                ->setCarte(
                    $typeAutorisationFormat
                )
        ;

        $format2 =
            (new FormatAchatCarte())
                ->setCarte(
                    $typeAutorisationFormat
                )
        ;

        $creneau = new Creneau();

        $date = new \DateTime();
        $serie = new DhtmlxSerie();
        $evenement =
            (new DhtmlxEvenement())
                ->setSerie($serie)
                ->setDependanceSerie(true)
                ->setDescription('evenement Test')
                ->setDateDebut($date)
                ->setDateFin($date)
            ;

        $creneau->setSerie($serie->addEvenement($evenement));

        $creneau->setFormatActivite(
            (new FormatAvecCreneau())
                ->setLibelle('FormatAvecCreneau')
        );

        $reservabilite = new Reservabilite();

        $tarif = new Tarif();
        $autorisation = new TypeAutorisation();
        $date = new \DateTime();
        $serie = new DhtmlxSerie();
        $encadrant = new Utilisateur();
        $evenement1 =
            (new DhtmlxEvenement())
                ->setSerie($serie)
                ->setDependanceSerie(true)
                ->setDescription('evenement Test')
                ->setDateDebut($date)
                ->setDateFin($date)
            ;

        $evenement2 =
            (new DhtmlxEvenement())
                ->setSerie($serie)
                ->setDependanceSerie(true)
                ->setDescription('evenement Test')
                ->setDateDebut(new \DateTime('tomorrow'))
                ->setDateFin(new \DateTime('tomorrow'))
        ;

        $ressource =
            (new Lieu())
                ->setLibelle('lieu')
                ->setTarif(new Tarif())
            ;

        $reservabilite = new Reservabilite();

        $reservabilite->setFormatActivite(
            (new FormatAvecCreneau())
                ->setLibelle('FormatAvecCreneau')
                ->addAutorisation($autorisation)
                ->addEncadrant($encadrant)
        );

        $reservabilite->setEvenement($evenement1);
        $reservabilite->setRessource($ressource);

        $format1->addEncadrant(new Utilisateur());

        $inscription1 = new Inscription($format1, $utilisateur1, []);
        $inscription2 = new Inscription($format2, $utilisateur2, ['typeInscription' => 'format']);

        $this->assertEquals($inscription1->getCommandeDetails(), new \Doctrine\Common\Collections\ArrayCollection());
        $this->assertEquals($inscription2->getAutorisations(), new \Doctrine\Common\Collections\ArrayCollection());
        $this->assertEquals($inscription1->getUtilisateur(), $utilisateur1);
        $this->assertEquals($inscription1->getNomInscrit(), $utilisateur1->getNom());
        $this->assertEquals($inscription1->getPrenomInscrit(), $utilisateur1->getPrenom());
        $this->assertEquals($inscription1->getItem(), $format1);
        $this->assertEquals($inscription1->getLibelle(), $format1->getLibelle());
        $this->assertTrue($inscription1->getDate() instanceof \DateTime);
        $this->assertEquals($inscription1->getStatut(), 'attentepaiement');
        $this->assertTrue($utilisateur1->getInscriptions()->contains($inscription1));

        $inscription1 = new Inscription($creneau, $utilisateur1, []);
        $this->assertEquals($inscription1->getItem(), $creneau);

        $inscription1 = new Inscription($reservabilite, $utilisateur1, ['typeInscription' => 'format']);
        $this->assertEquals($inscription1->getItem(), $reservabilite);
    }

    /**
     * @covers \App\Entity\Uca\Inscription::jsonSerializeProperties
     */
    public function testJsonSerializeProperties(): void
    {
        $arrayProperties = $this->inscription->jsonSerializeProperties();
        $properties = [];

        $this->assertTrue($arrayProperties == $properties);
    }

    /**
     * @covers \App\Entity\Uca\Inscription::getItem
     */
    public function testGetItem(): void
    {
        $this->assertEquals($this->formatActivite, $this->inscription->getItem());

        $reservabilite = new Reservabilite();
        $this->inscription->setReservabilite($reservabilite);

        $this->assertEquals($reservabilite, $this->inscription->getItem());

        $creneau = new Creneau();
        $this->inscription->setCreneau($creneau);

        $this->assertEquals($creneau, $this->inscription->getItem());
    }

    /**
     * @covers \App\Entity\Uca\Inscription::getAutorisationTypes
     */
    public function testGetAutorisationTypes(): void
    {
        $this->assertEquals($this->inscription->getAutorisationTypes()->getIterator()[0], $this->typeAutorisation);
    }

    /**
     * @covers \App\Entity\Uca\Inscription::getItemColumn
     */
    public function testGetItemColumn(): void
    {
        $this->assertEquals('formatActivite', $this->inscription->getItemColumn($this->formatActivite));

        $reservabilite = new Reservabilite();
        $this->assertEquals('reservabilite', $this->inscription->getItemColumn($reservabilite));

        $creneau = new Creneau();
        $this->assertEquals('creneau', $this->inscription->getItemColumn($creneau));
    }

    /**
     * @covers \App\Entity\Uca\Inscription::getItemId
     */
    public function testGetItemId(): void
    {
        $this->assertEquals($this->formatActivite->getId(), $this->inscription->getItemId());

        $reservabilite = new Reservabilite();
        $this->inscription->setReservabilite($reservabilite);

        $this->assertEquals($reservabilite->getId(), $this->inscription->getItemId());

        $creneau = new Creneau();
        $this->inscription->setCreneau($creneau);

        $this->assertEquals($creneau->getId(), $this->inscription->getItemId());
    }

    /**
     * @covers \App\Entity\Uca\Inscription::getItemType
     */
    public function testGetItemType(): void
    {
        $this->assertEquals('FormatActivite', $this->inscription->getItemType());

        $reservabilite = new Reservabilite();
        $this->inscription->setReservabilite($reservabilite);

        $this->assertEquals('Reservabilite', $this->inscription->getItemType());

        $creneau = new Creneau();
        $this->inscription->setCreneau($creneau);

        $this->assertEquals('Creneau', $this->inscription->getItemType());
    }

    /**
     * @covers \App\Entity\Uca\Inscription::getAutorisationsByComportement
     */
    public function testGetAutorisationsByComportement(): void
    {
        $this->assertEquals($this->typeAutorisation->getComportement()->getCodeComportement(), $this->inscription->getAutorisationsByComportement(['code'])[0]->getCodeComportement());
        $this->assertEquals($this->typeAutorisation->getComportement()->getCodeComportement(), $this->inscription->getAutorisationsByComportement(['code', 'non'], 'invalide')[0]->getCodeComportement());
    }

    /**
     * @covers \App\Entity\Uca\Inscription::hasCodeComportementByStatut
     */
    public function testHasCodeComportementByStatut(): void
    {
        $this->assertTrue($this->inscription->hasCodeComportementByStatut(['code', 'non'], 'invalide'));
    }

    /**
     * @covers \App\Entity\Uca\Inscription::setStatut
     */
    public function testSetStatut(): void
    {
        $this->inscription->setStatut('annule', ['motifAnnulation' => 'motif annulation', 'commentaireAnnulation' => 'commentaire annulation']);
        $this->assertEquals($this->inscription->getMotifAnnulation(), 'motif annulation');
        $this->assertEquals($this->inscription->getCommentaireAnnulation(), 'commentaire annulation');
        $this->assertEquals($this->inscription->getStatut(), 'annule');

        $this->inscription->setStatut('valide');
        $this->assertEquals($this->inscription->getStatut(), 'valide');
    }

    /**
     * @covers \App\Entity\Uca\Inscription::seDesinscrire
     */
    public function testSeDesinscrire(): void
    {
        $date = new \DateTime();

        $creneau = (new Creneau())
            ->setFormatActivite($this->inscription->getFormatActivite())
            ->setCapacite(10)
        ;
        $this->inscription->setCreneau($creneau);
        $comportementAutorisation = (new ComportementAutorisation())
            ->setdescriptionComportement('desc2')
            ->setLibelle('libelle comportement2')
            ->setCodeComportement('comp')
        ;

        $typeAutorisationFormat = (new TypeAutorisation())
            ->setComportement(
                $comportementAutorisation
            )
            ->setLibelle('')
        ;

        $format = (new FormatAchatCarte())
            ->setCarte(
                $typeAutorisationFormat
            )
            ->setCapacite(10)
            ->setLibelle('')
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

        $tmp_inscription = (new Inscription($format, $this->utilisateur, []))->setStatut('valide');
        $tmp_inscription->setFormatActivite($this->formatActivite);

        $this->persistObjects([$creneau, $this->comportementAutorisation, $this->typeAutorisation, $this->comportementAutorisationFormat, $this->typeAutorisationFormat, $this->formatActivite, $this->inscription, $this->utilisateur, $comportementAutorisation, $typeAutorisationFormat, $format, $tmp_inscription]);

        $tmp_inscription->setStatut('inscrit');
        $this->inscription->seDesinscrire($this->utilisateur, true);

        $this->assertEquals('ancienneinsciption', $tmp_inscription->getStatut());

        $tmp_inscription->setStatut('inscrit');
        $this->inscription->seDesinscrire($this->utilisateur);

        $this->assertEquals('desinscrit', $tmp_inscription->getStatut());

        $tmp_inscription->setStatut('inscrit');
        $tmp_inscription->setCreneau($creneau);
        $this->inscription->seDesinscrire($this->utilisateur, true);

        $this->assertEquals('inscrit', $tmp_inscription->getStatut());

        $this->removeObjects([$creneau, $this->comportementAutorisation, $this->typeAutorisation, $this->comportementAutorisationFormat, $this->typeAutorisationFormat, $this->formatActivite, $this->inscription, $this->utilisateur, $comportementAutorisation, $typeAutorisationFormat, $format, $tmp_inscription]);
    }

    /**
     * @covers \App\Entity\Uca\Inscription::updateStatut
     */
    public function testUpdateStatut(): void
    {
        $typeAutorisation1 = (new TypeAutorisation())
            ->setComportement((new ComportementAutorisation())
            ->setCodeComportement('justificatif'))
        ;

        $typeAutorisation2 = (new TypeAutorisation())
            ->setComportement((new ComportementAutorisation())
            ->setCodeComportement('validationencadrant'))
        ;

        $typeAutorisation3 = (new TypeAutorisation())
            ->setComportement((new ComportementAutorisation())
            ->setCodeComportement('validationgestionnaire'))
        ;

        $autorisation1 = new Autorisation($this->inscription, $typeAutorisation1);
        $autorisation2 = new Autorisation($this->inscription, $typeAutorisation2);
        $autorisation3 = new Autorisation($this->inscription, $typeAutorisation3);

        $this->inscription->updateStatut();
        $this->assertEquals($this->inscription->getStatut(), 'attentepaiement');

        $this->inscription->addAutorisation($autorisation3);
        $this->inscription->updateStatut();
        $this->assertEquals($this->inscription->getStatut(), 'attentevalidationgestionnaire');

        $this->inscription->getAutorisations()[1]->setValideParGestionnaire(true);
        $this->inscription->updateStatut();
        $this->assertEquals($this->inscription->getStatut(), 'attenteajoutpanier');
        $this->inscription->addAutorisation($autorisation2);
        $this->inscription->updateStatut();
        $this->assertEquals($this->inscription->getStatut(), 'attentevalidationencadrant');

        $this->inscription->addAutorisation($autorisation1);
        $this->inscription->updateStatut();
        $this->assertEquals($this->inscription->getStatut(), 'initialise');
    }

    /**
     * @covers \App\Entity\Uca\Inscription::initAutorisations
     */
    public function testInitAutorisations(): void
    {
        $this->inscription->initAutorisations();

        $this->assertEquals($this->inscription->getAutorisations()[0]->getTypeAutorisation(), $this->typeAutorisation);
    }

    /**
     * @covers \App\Entity\Uca\Inscription::removeAllAutorisations
     */
    public function testRemoveAllAutorisations(): void
    {
        $this->inscription->removeAllAutorisations();

        $this->assertEquals($this->inscription->getAutorisations()[0], null);
    }

    /**
     * @covers \App\Entity\Uca\Inscription::removeAllCommandeDetails
     */
    public function testRemoveAllCommandeDetails(): void
    {
        $commande = new Commande($this->inscription->getUtilisateur());
        $commandeDetail = new CommandeDetail($commande, 'inscription', $this->inscription);
        $this->inscription->addCommandeDetail($commandeDetail);

        $this->assertEquals($this->inscription->getCommandeDetails()[0], $commandeDetail);

        $this->inscription->removeAllCommandeDetails();

        $this->assertEquals($this->inscription->getCommandeDetails()[10], null);
    }

    /**
     * @covers \App\Entity\Uca\Inscription::getFirstCommande
     */
    public function testGetFirstCommande(): void
    {
        $this->assertEquals($this->inscription->getFirstCommande(), null);

        $commande = new Commande($this->inscription->getUtilisateur());
        $commandeDetail = new CommandeDetail($commande, 'inscription', $this->inscription);
        $this->inscription->addCommandeDetail($commandeDetail);

        $this->assertEquals($this->inscription->getFirstCommande(), $commande);
    }

    /**
     * Data provider pour le formulaire de creation.
     */
    public function estAnnulableDataProvider()
    {
        return [
            ['panier', false],
            ['apayer', false],
            ['termine', false],
            ['annule', true],
            ['annule', false, 'valide'],
            ['factureAnnulee', true],
            ['factureAnnulee', false, 'valide'],
            ['avoir', true],
            ['avoir', false, 'valide'],
        ];
    }

    /**
     * @dataProvider estAnnulableDataProvider
     *
     * @covers \App\Entity\Uca\Inscription::estAnnulable
     *
     * @param mixed $statutCmd
     * @param mixed $resultat
     * @param mixed $statutInscription
     */
    public function testEstAnnulable($statutCmd, $resultat, $statutInscription = 'annule'): void
    {
        $this->inscription->setStatut($statutInscription);

        $cmd1 = (new Commande($this->utilisateur))
            ->setStatut('annule')
        ;
        $cmdDetail1 = new CommandeDetail($cmd1, 'inscription', $this->inscription);
        $this->setProperty($cmdDetail1, 1, 'id');
        $this->inscription->addCommandeDetail($cmdDetail1);

        $cmd2 = (new Commande($this->utilisateur))
            ->setStatut($statutCmd)
        ;
        $cmdDetail2 = new CommandeDetail($cmd2, 'inscription', $this->inscription);
        $this->setProperty($cmdDetail2, 2, 'id');
        $this->inscription->addCommandeDetail($cmdDetail2);

        $annulable = $this->inscription->estAnnulable($cmdDetail1);
        $this->assertEquals($annulable, $resultat);
    }

    private function setProperty($entity, $value, $propertyName = 'id')
    {
        $class = new ReflectionClass($entity);
        $property = $class->getProperty($propertyName);
        $property->setAccessible(true);

        $property->setValue($entity, $value);
    }

    private function persistObjects($objects)
    {
        foreach ($objects as $event) {
            $this->em->persist($event);
        }
        $this->em->flush();

        return $objects;
    }

    private function removeObjects($objects)
    {
        foreach ($objects as $event) {
            $this->em->remove($event);
        }
        $this->em->flush();

        return $objects;
    }
}
