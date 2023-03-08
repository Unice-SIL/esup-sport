<?php

namespace App\Tests\Controller\UcaWeb;

use App\Entity\Uca\Activite;
use App\Entity\Uca\Autorisation;
use App\Entity\Uca\ClasseActivite;
use App\Entity\Uca\Commande;
use App\Entity\Uca\CommandeDetail;
use App\Entity\Uca\ComportementAutorisation;
use App\Entity\Uca\Creneau;
use App\Entity\Uca\CreneauProfilUtilisateur;
use App\Entity\Uca\DhtmlxEvenement;
use App\Entity\Uca\DhtmlxSerie;
use App\Entity\Uca\Etablissement;
use App\Entity\Uca\FormatActivite;
use App\Entity\Uca\FormatActiviteProfilUtilisateur;
use App\Entity\Uca\FormatAvecCreneau;
use App\Entity\Uca\FormatAvecReservation;
use App\Entity\Uca\Inscription;
use App\Entity\Uca\Lieu;
use App\Entity\Uca\MontantTarifProfilUtilisateur;
use App\Entity\Uca\ProfilUtilisateur;
use App\Entity\Uca\Reservabilite;
use App\Entity\Uca\ReservabiliteProfilUtilisateur;
use App\Entity\Uca\Ressource;
use App\Entity\Uca\Tarif;
use App\Entity\Uca\TypeActivite;
use App\Entity\Uca\TypeAutorisation;
use App\Entity\Uca\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\RouterInterface;

/**
 * @internal
 *
 * @coversNothing
 */
class InscriptionControllerTest extends WebTestCase
{
    private $client;

    private $router;

    private $em;

    private $ids = [];

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->router = static::getContainer()->get(RouterInterface::class);
        $this->em = static::getContainer()->get(EntityManagerInterface::class);

        $profil = (new ProfilUtilisateur())
            ->setLibelle('test')
            ->setNbMaxInscriptions(100)
            ->setNbMaxInscriptionsRessource(100)
            ->setPreinscription(true)
        ;
        $this->em->persist($profil);

        $user = (new Utilisateur())
            ->setEmail('userr@test.fr')
            ->setUsername('user')
            ->setPassword('password')
            ->setCgvAcceptees(true)
            ->setEnabled(true)
            ->setRoles([])
            ->setProfil($profil)
        ;
        $this->em->persist($user);

        $userCGVFalse = (new Utilisateur())
            ->setEmail('userr@test.fr')
            ->setUsername('user')
            ->setPassword('password')
            ->setCgvAcceptees(false)
            ->setEnabled(true)
            ->setRoles([])
            ->setProfil($profil)
        ;
        $this->em->persist($userCGVFalse);

        $typeActivite = (new TypeActivite())
            ->setLibelle('type activite')
        ;
        $this->em->persist($typeActivite);

        $classeActivite = (new ClasseActivite())
            ->setLibelle('classe activite')
            ->setTypeActivite($typeActivite)
            ->setImage('')
        ;
        $this->em->persist($classeActivite);
        $typeActivite->addClasseActivite($classeActivite);

        $activite = (new Activite())
            ->setLibelle('activite')
            ->setClasseActivite($classeActivite)
            ->setImage('')
            ->setDescription('test')
        ;
        $this->em->persist($activite);
        $classeActivite->addActivite($activite);

        $formatActiviteAvecCreneauJustificatif = (new FormatAvecCreneau())
            ->setActivite($activite)
            ->setLibelle('FormatAvecCreneau')
            ->setCapacite(1)
            ->setDescription('test')
            ->setDateDebutEffective(new \DateTime())
            ->setDateDebutInscription(new \DateTime())
            ->setDateDebutPublication(new \DateTime())
            ->setDateFinEffective((new \DateTime())->add(new \DateInterval('P1D')))
            ->setDateFinInscription((new \DateTime())->add(new \DateInterval('P1D')))
            ->setDateFinPublication((new \DateTime())->add(new \DateInterval('P1D')))
            ->setImage('test')
            ->setStatut(1)
            ->setTarifLibelle('Tarif')
            ->setListeLieux('[]')
            ->setListeAutorisations('[]')
            ->setListeNiveauxSportifs('[]')
            ->setListeProfils('[]')
            ->setListeEncadrants('[]')
            ->setPromouvoir(false)
            ->setEstPayant(false)
            ->setEstEncadre(true)
        ;
        $this->em->persist($formatActiviteAvecCreneauJustificatif);
        $activite->addFormatsActivite($formatActiviteAvecCreneauJustificatif);

        $formatActiviteAvecCreneauEncadre = (new FormatAvecCreneau())
            ->setActivite($activite)
            ->setLibelle('FormatAvecCreneau')
            ->setCapacite(1)
            ->setDescription('test')
            ->setDateDebutEffective(new \DateTime())
            ->setDateDebutInscription(new \DateTime())
            ->setDateDebutPublication(new \DateTime())
            ->setDateFinEffective((new \DateTime())->add(new \DateInterval('P1D')))
            ->setDateFinInscription((new \DateTime())->add(new \DateInterval('P1D')))
            ->setDateFinPublication((new \DateTime())->add(new \DateInterval('P1D')))
            ->setImage('test')
            ->setStatut(1)
            ->setTarifLibelle('Tarif')
            ->setListeLieux('[]')
            ->setListeAutorisations('[]')
            ->setListeNiveauxSportifs('[]')
            ->setListeProfils('[]')
            ->setListeEncadrants('[]')
            ->setPromouvoir(false)
            ->setEstPayant(false)
            ->setEstEncadre(true)
        ;
        $this->em->persist($formatActiviteAvecCreneauEncadre);
        $activite->addFormatsActivite($formatActiviteAvecCreneauEncadre);

        $formatActiviteAvecCreneauNonEncadre = (new FormatAvecCreneau())
            ->setActivite($activite)
            ->setLibelle('FormatAvecCreneau')
            ->setCapacite(1)
            ->setDescription('test')
            ->setDateDebutEffective(new \DateTime())
            ->setDateDebutInscription(new \DateTime())
            ->setDateDebutPublication(new \DateTime())
            ->setDateFinEffective((new \DateTime())->add(new \DateInterval('P1D')))
            ->setDateFinInscription((new \DateTime())->add(new \DateInterval('P1D')))
            ->setDateFinPublication((new \DateTime())->add(new \DateInterval('P1D')))
            ->setImage('test')
            ->setStatut(1)
            ->setTarifLibelle('Tarif')
            ->setListeLieux('[]')
            ->setListeAutorisations('[]')
            ->setListeNiveauxSportifs('[]')
            ->setListeProfils('[]')
            ->setListeEncadrants('[]')
            ->setPromouvoir(false)
            ->setEstPayant(false)
            ->setEstEncadre(false)
        ;
        $this->em->persist($formatActiviteAvecCreneauNonEncadre);
        $activite->addFormatsActivite($formatActiviteAvecCreneauNonEncadre);

        $formatActiviteAvecReservationNonEncadre = (new FormatAvecReservation())
            ->setActivite($activite)
            ->setLibelle('FormatAvecCreneau')
            ->setCapacite(1)
            ->setDescription('test')
            ->setDateDebutEffective(new \DateTime())
            ->setDateDebutInscription(new \DateTime())
            ->setDateDebutPublication(new \DateTime())
            ->setDateFinEffective((new \DateTime())->add(new \DateInterval('P1D')))
            ->setDateFinInscription((new \DateTime())->add(new \DateInterval('P1D')))
            ->setDateFinPublication((new \DateTime())->add(new \DateInterval('P1D')))
            ->setImage('test')
            ->setStatut(1)
            ->setTarifLibelle('Tarif')
            ->setListeLieux('[]')
            ->setListeAutorisations('[]')
            ->setListeNiveauxSportifs('[]')
            ->setListeProfils('[]')
            ->setListeEncadrants('[]')
            ->setPromouvoir(false)
            ->setEstPayant(false)
            ->setEstEncadre(false)
        ;
        $this->em->persist($formatActiviteAvecReservationNonEncadre);
        $activite->addFormatsActivite($formatActiviteAvecReservationNonEncadre);

        $etablissement = (new Etablissement())
            ->setCode('IUTSD')
            ->setLibelle('IUT de Saint-Dié-des-Vosges')
            ->setAdresse('11 Rue de l\'université')
            ->setCodePostal('88100')
            ->setVille('Saint-Dié-des-Vosges')
        ;
        $etablissement->setImage('test.jpg');
        $this->em->persist($etablissement);

        $ressourceLieu = (new Lieu());
        $ressourceLieu->setLibelle('Ressource Lieu Test');
        $ressourceLieu->setImage('test.jpg');
        $ressourceLieu->setNbPartenaires(1);
        $ressourceLieu->setNbPartenairesMax(5);
        $ressourceLieu->setEtablissement($etablissement);
        $this->em->persist($ressourceLieu);

        $formatActiviteAvecReservationNonEncadre->addRessource($ressourceLieu);

        $evenement = new DhtmlxEvenement();
        $evenement->setDateDebut(new \DateTime());
        $evenement->setDateFin((new \DateTime())->add(new \DateInterval('P1D')));
        $this->em->persist($evenement);

        $reservabilite1 = new Reservabilite();
        $reservabilite1->setRessource($ressourceLieu);
        $reservabilite1->setFormatActivite($formatActiviteAvecReservationNonEncadre);
        $reservabilite1->setEvenement($evenement);
        $reservabilite1->setCapacite(100);
        $this->em->persist($reservabilite1);

        $reservabilite2 = new Reservabilite();
        $reservabilite2->setRessource($ressourceLieu);
        $reservabilite2->setFormatActivite($formatActiviteAvecReservationNonEncadre);
        $reservabilite2->setEvenement($evenement);
        $reservabilite2->setCapacite(100);
        $this->em->persist($reservabilite2);

        $formatActiviteNonEncadreProfil = new FormatActiviteProfilUtilisateur($formatActiviteAvecCreneauNonEncadre, $profil, 100);
        $this->em->persist($formatActiviteNonEncadreProfil);

        $formatActiviteEncadreProfil = new FormatActiviteProfilUtilisateur($formatActiviteAvecCreneauEncadre, $profil, 100);
        $this->em->persist($formatActiviteEncadreProfil);

        $formatActiviteJustificatifProfil = new FormatActiviteProfilUtilisateur($formatActiviteAvecCreneauJustificatif, $profil, 100);
        $this->em->persist($formatActiviteJustificatifProfil);

        $formatActiviteReservationProfil = new FormatActiviteProfilUtilisateur($formatActiviteAvecReservationNonEncadre, $profil, 100);
        $this->em->persist($formatActiviteReservationProfil);

        $formatActiviteAvecCreneauNonEncadre->addProfilsUtilisateur($formatActiviteNonEncadreProfil);
        $formatActiviteAvecCreneauEncadre->addProfilsUtilisateur($formatActiviteEncadreProfil);
        $formatActiviteAvecCreneauJustificatif->addProfilsUtilisateur($formatActiviteJustificatifProfil);
        $formatActiviteAvecReservationNonEncadre->addProfilsUtilisateur($formatActiviteReservationProfil);

        $reservabiliteProfil = new ReservabiliteProfilUtilisateur($reservabilite1, $profil, 100);
        $this->em->persist($reservabiliteProfil);

        $reservabilite1->addProfilsUtilisateur($reservabiliteProfil);

        $serie = (new DhtmlxSerie())
            ->setDateDebut(new \DateTime())
            ->setDateFin((new \DateTime())->add(new \DateInterval('P1D')))
        ;
        $serie->addEvenement($evenement);
        $evenement->setSerie($serie)
            ->setDependanceSerie(true);
        $this->em->persist($serie);

        $creneauNonEncadre = (new Creneau())
            ->setCapacite(1)
            ->setFormatActivite($formatActiviteAvecCreneauNonEncadre)
            ->setSerie($serie)
        ;
        $this->em->persist($creneauNonEncadre);

        $creneauEncadre = (new Creneau())
            ->setCapacite(1)
            ->setFormatActivite($formatActiviteAvecCreneauEncadre)
            ->setSerie($serie)
        ;
        $this->em->persist($creneauEncadre);

        $creneauJustificatif = (new Creneau())
            ->setCapacite(1)
            ->setFormatActivite($formatActiviteAvecCreneauJustificatif)
            ->setSerie($serie)
        ;
        $this->em->persist($creneauJustificatif);

        $creneauNonEncadreProfil = new CreneauProfilUtilisateur($creneauNonEncadre, $profil, 100);
        $this->em->persist($creneauNonEncadreProfil);

        $creneauEncadreProfil = new CreneauProfilUtilisateur($creneauEncadre, $profil, 100);
        $this->em->persist($creneauEncadreProfil);

        $creneauJustificatifProfil = new CreneauProfilUtilisateur($creneauJustificatif, $profil, 100);
        $this->em->persist($creneauJustificatifProfil);

        $creneauNonEncadre->addProfilsUtilisateur($creneauNonEncadreProfil);
        $creneauEncadre->addProfilsUtilisateur($creneauEncadreProfil);
        $creneauJustificatif->addProfilsUtilisateur($creneauJustificatifProfil);

        $creneauTarif = new Tarif();
        $creneauTarif->setLibelle('Test Tarif Creneau');
        $creneauTarif->setTva(true);
        $creneauTarif->setPourcentageTVA(20);
        $creneauTarif->setModificationMontants(0);
        $creneauTarif->addCreneaux($creneauNonEncadre);
        $creneauTarif->addCreneaux($creneauEncadre);
        $creneauTarif->addCreneaux($creneauJustificatif);
        $creneauTarif->addFormatsActivite($formatActiviteAvecCreneauNonEncadre);
        $creneauTarif->addFormatsActivite($formatActiviteAvecCreneauEncadre);
        $creneauTarif->addFormatsActivite($formatActiviteAvecCreneauJustificatif);
        $creneauTarif->addFormatsActivite($formatActiviteAvecReservationNonEncadre);
        $creneauTarif->addRessource($ressourceLieu);
        $this->em->persist($creneauTarif);

        $montantProfilUtilisateur = new MontantTarifProfilUtilisateur($creneauTarif, $profil, 10);
        $this->em->persist($montantProfilUtilisateur);

        $creneauTarif->addMontant($montantProfilUtilisateur);

        $ressourceLieu->setTarif($creneauTarif);

        $creneauNonEncadre->setTarif($creneauTarif);
        $creneauEncadre->setTarif($creneauTarif);
        $creneauJustificatif->setTarif($creneauTarif);

        $formatActiviteAvecReservationNonEncadre->setTarif($creneauTarif);

        $typeAutorisationEncadre = new TypeAutorisation();
        $typeAutorisationEncadre->setComportement($this->em->getRepository(ComportementAutorisation::class)->find(5));
        $typeAutorisationEncadre->setComportementLibelle('Test');
        $typeAutorisationEncadre->setLibelle('Test');
        $this->em->persist($typeAutorisationEncadre);

        $typeAutorisationJustificatif = new TypeAutorisation();
        $typeAutorisationJustificatif->setComportement($this->em->getRepository(ComportementAutorisation::class)->find(2));
        $typeAutorisationJustificatif->setComportementLibelle('Test');
        $typeAutorisationJustificatif->setLibelle('Test');
        $this->em->persist($typeAutorisationJustificatif);

        $formatActiviteAvecCreneauEncadre->addAutorisation($typeAutorisationEncadre);

        $formatActiviteAvecCreneauJustificatif->addAutorisation($typeAutorisationJustificatif);

        $inscriptionSansPartenaires = new Inscription($reservabilite2, $user, ['typeInscription' => 'format']);
        $this->em->persist($inscriptionSansPartenaires);

        $inscriptionAvecPartenaires = new Inscription($reservabilite2, $user, ['typeInscription' => 'format']);
        $inscriptionAvecPartenaires->setListeEmailPartenaires('userr@test.fr');
        $this->em->persist($inscriptionAvecPartenaires);

        $this->em->flush();

        $this->ids['classeActivite'] = $classeActivite->getId();
        $this->ids['typeActivite'] = $typeActivite->getId();
        $this->ids['activite'] = $activite->getId();

        $this->ids['inscriptionSansPartenaires'] = $inscriptionSansPartenaires->getId();
        $this->ids['inscriptionAvecPartenaires'] = $inscriptionAvecPartenaires->getId();

        $this->ids['evenement'] = $evenement->getId();
        $this->ids['reservabilite1'] = $reservabilite1->getId();
        $this->ids['reservabilite2'] = $reservabilite2->getId();
        $this->ids['lieu'] = $ressourceLieu->getId();
        $this->ids['etablissement'] = $etablissement->getId();
        $this->ids['typeAutorisationEncadre'] = $typeAutorisationEncadre->getId();
        $this->ids['typeAutorisationJustificatif'] = $typeAutorisationJustificatif->getId();
        $this->ids['serie'] = $serie->getId();
        $this->ids['creneauNonEncadre'] = $creneauNonEncadre->getId();
        $this->ids['creneauEncadre'] = $creneauEncadre->getId();
        $this->ids['creneauJustificatif'] = $creneauJustificatif->getId();
        $this->ids['creneauNonEncadreProfil'] = $creneauNonEncadreProfil->getId();
        $this->ids['creneauEncadreProfil'] = $creneauEncadreProfil->getId();
        $this->ids['creneauJustificatifProfil'] = $creneauJustificatifProfil->getId();
        $this->ids['reservabiliteProfil'] = $reservabiliteProfil->getId();
        $this->ids['creneauTarif'] = $creneauTarif->getId();
        $this->ids['userCGVFalse'] = $userCGVFalse->getId();
        $this->ids['user'] = $user->getId();
        $this->ids['profil'] = $profil->getId();
        $this->ids['formatActiviteAvecReservationNonEncadre'] = $formatActiviteAvecReservationNonEncadre->getId();
        $this->ids['formatActiviteAvecCreneauNonEncadre'] = $formatActiviteAvecCreneauNonEncadre->getId();
        $this->ids['formatActiviteAvecCreneauEncadre'] = $formatActiviteAvecCreneauEncadre->getId();
        $this->ids['formatActiviteAvecCreneauJustificatif'] = $formatActiviteAvecCreneauJustificatif->getId();
        $this->ids['formatActiviteNonEncadreProfil'] = $formatActiviteNonEncadreProfil->getId();
        $this->ids['formatActiviteEncadreProfil'] = $formatActiviteEncadreProfil->getId();
        $this->ids['formatActiviteJustificatifProfil'] = $formatActiviteJustificatifProfil->getId();
        $this->ids['formatActiviteReservationProfil'] = $formatActiviteReservationProfil->getId();
        $this->ids['montantProfil'] = $montantProfilUtilisateur->getId();
    }

    /**
     * @covers \App\Controller\UcaWeb\InscriptionController::inscriptionAction
     */
    public function testInscriptionCGVNonAcceptees()
    {
        $this->client->loginUser($this->em->getRepository(Utilisateur::class)->find($this->ids['userCGVFalse']), 'app');
        $this->client->xmlHttpRequest('POST', $this->router->generate('UcaWeb_Inscription'), [
            'statut' => 'confirmation',
            'type' => 'Creneau',
            'id' => $this->ids['creneauNonEncadre'],
        ]);
        $response = json_decode($this->client->getResponse()->getContent());
        $this->assertIsObject($response);
        $this->assertObjectHasAttribute('itemId', $response);
        $this->assertEquals($this->ids['creneauNonEncadre'], $response->itemId);
        $this->assertObjectHasAttribute('statut', $response);
        $this->assertEquals('-1', $response->statut);
        $this->assertObjectHasAttribute('html', $response);
        $this->assertIsString($response->html);
    }

    /**
     * @covers \App\Controller\UcaWeb\InscriptionController::inscriptionAction
     */
    public function testConfirmationInscriptionNonEncadre()
    {
        $this->client->loginUser($this->em->getRepository(Utilisateur::class)->find($this->ids['user']), 'app');
        $this->client->xmlHttpRequest('POST', $this->router->generate('UcaWeb_Inscription'), [
            'statut' => 'confirmation',
            'type' => 'Creneau',
            'id' => $this->ids['creneauNonEncadre'],
        ]);
        $response = json_decode($this->client->getResponse()->getContent());
        $this->assertIsString($response);
    }

    /**
     * @covers \App\Controller\UcaWeb\InscriptionController::inscriptionAction
     */
    public function testValidationInscriptionNonEncadre()
    {
        $this->client->loginUser($this->em->getRepository(Utilisateur::class)->find($this->ids['user']), 'app');
        $this->client->xmlHttpRequest('POST', $this->router->generate('UcaWeb_Inscription'), [
            'statut' => 'validation',
            'type' => 'Creneau',
            'id' => $this->ids['creneauNonEncadre'],
        ]);
        $response = json_decode($this->client->getResponse()->getContent());
        $this->assertIsObject($response);
        $this->assertObjectHasAttribute('itemId', $response);
        $this->assertEquals($this->ids['creneauNonEncadre'], $response->itemId);
        $this->assertObjectHasAttribute('statut', $response);
        $this->assertEquals('0', $response->statut);
        $this->assertObjectHasAttribute('html', $response);
        $this->assertIsString($response->html);
        $this->assertObjectHasAttribute('maxCreneauAtteint', $response);
        $this->assertIsBool($response->maxCreneauAtteint);
    }

    /**
     * @covers \App\Controller\UcaWeb\InscriptionController::inscriptionAction
     */
    public function testValidationInscriptionNonEncadreAvecParternairesDoublon()
    {
        $this->client->loginUser($this->em->getRepository(Utilisateur::class)->find($this->ids['user']), 'app');
        $this->client->xmlHttpRequest('POST', $this->router->generate('UcaWeb_Inscription'), [
            'partenaires' => [
                'test@test.fr',
                'test@test.fr',
            ],
            'statut' => 'validation',
            'type' => 'Creneau',
            'id' => $this->ids['creneauNonEncadre'],
        ]);
        $response = json_decode($this->client->getResponse()->getContent());
        $this->assertIsObject($response);
        $this->assertObjectHasAttribute('error', $response);
        $this->assertIsString($response->error);
    }

    /**
     * @covers \App\Controller\UcaWeb\InscriptionController::inscriptionAction
     */
    public function testValidationInscriptionNonEncadreAvecParternairesEmailUser()
    {
        $this->client->loginUser($this->em->getRepository(Utilisateur::class)->find($this->ids['user']), 'app');
        $this->client->xmlHttpRequest('POST', $this->router->generate('UcaWeb_Inscription'), [
            'partenaires' => [
                'test@test.fr',
                'userr@test.fr',
            ],
            'statut' => 'validation',
            'type' => 'Creneau',
            'id' => $this->ids['creneauNonEncadre'],
        ]);
        $response = json_decode($this->client->getResponse()->getContent());
        $this->assertIsObject($response);
        $this->assertObjectHasAttribute('error', $response);
        $this->assertIsString($response->error);
    }

    /**
     * @covers \App\Controller\UcaWeb\InscriptionController::inscriptionAction
     */
    public function testValidationInscriptionNonEncadreAvecPartenaires()
    {
        $this->client->loginUser($this->em->getRepository(Utilisateur::class)->find($this->ids['user']), 'app');
        $this->client->xmlHttpRequest('POST', $this->router->generate('UcaWeb_Inscription'), [
            'partenaires' => ['test@test.fr'],
            'statut' => 'validation',
            'type' => 'Reservabilite',
            'id' => $this->ids['reservabilite1'],
            'idFormat' => $this->ids['formatActiviteAvecReservationNonEncadre'],
        ]);
        $response = json_decode($this->client->getResponse()->getContent());
        $this->assertIsObject($response);
        $this->assertObjectHasAttribute('itemId', $response);
        $this->assertEquals($this->ids['reservabilite1'], $response->itemId);
        $this->assertObjectHasAttribute('statut', $response);
        $this->assertEquals('0', $response->statut);
        $this->assertObjectHasAttribute('html', $response);
        $this->assertIsString($response->html);
        $this->assertObjectHasAttribute('maxCreneauAtteint', $response);
        $this->assertIsBool($response->maxCreneauAtteint);
    }

    /**
     * @covers \App\Controller\UcaWeb\InscriptionController::inscriptionAction
     */
    public function testConfirmationInscriptionEncadre()
    {
        $this->client->loginUser($this->em->getRepository(Utilisateur::class)->find($this->ids['user']), 'app');
        $this->client->xmlHttpRequest('POST', $this->router->generate('UcaWeb_Inscription'), [
            'statut' => 'confirmation',
            'type' => 'Creneau',
            'id' => $this->ids['creneauEncadre'],
        ]);
        $response = json_decode($this->client->getResponse()->getContent());
        $this->assertIsString($response);
    }

    /**
     * @covers \App\Controller\UcaWeb\InscriptionController::inscriptionAction
     */
    public function testValidationInscriptionEncadre()
    {
        $this->client->loginUser($this->em->getRepository(Utilisateur::class)->find($this->ids['user']), 'app');
        $this->client->xmlHttpRequest('POST', $this->router->generate('UcaWeb_Inscription'), [
            'statut' => 'validation',
            'type' => 'Creneau',
            'id' => $this->ids['creneauEncadre'],
        ]);
        $response = json_decode($this->client->getResponse()->getContent());
        $this->assertIsObject($response);
        $this->assertObjectHasAttribute('itemId', $response);
        $this->assertEquals($this->ids['creneauEncadre'], $response->itemId);
        $this->assertObjectHasAttribute('statut', $response);
        $this->assertEquals('0', $response->statut);
        $this->assertObjectHasAttribute('html', $response);
        $this->assertIsString($response->html);
        $this->assertObjectHasAttribute('maxCreneauAtteint', $response);
        $this->assertIsBool($response->maxCreneauAtteint);
    }

    /**
     * @covers \App\Controller\UcaWeb\InscriptionController::inscriptionAction
     */
    public function testConfirmationInscriptionJustificatif()
    {
        $this->client->loginUser($this->em->getRepository(Utilisateur::class)->find($this->ids['user']), 'app');
        $this->client->xmlHttpRequest('POST', $this->router->generate('UcaWeb_Inscription'), [
            'statut' => 'confirmation',
            'type' => 'Creneau',
            'id' => $this->ids['creneauJustificatif'],
        ]);
        $response = json_decode($this->client->getResponse()->getContent());
        $this->assertIsString($response);
    }

    /**
     * @covers \App\Controller\UcaWeb\InscriptionController::inscriptionAction
     */
    public function testValidationInscriptionJustificatif()
    {
        $this->client->loginUser($this->em->getRepository(Utilisateur::class)->find($this->ids['user']), 'app');
        $this->client->xmlHttpRequest('POST', $this->router->generate('UcaWeb_Inscription'), [
            'statut' => 'validation',
            'type' => 'Creneau',
            'id' => $this->ids['creneauJustificatif'],
        ]);
        $response = json_decode($this->client->getResponse()->getContent());
        $this->assertIsObject($response);
        $this->assertObjectHasAttribute('itemId', $response);
        $this->assertEquals($this->ids['creneauJustificatif'], $response->itemId);
        $this->assertObjectHasAttribute('statut', $response);
        $this->assertEquals('1', $response->statut);
        $this->assertObjectHasAttribute('html', $response);
        $this->assertIsString($response->html);
    }

    /**
     * @covers \App\Controller\UcaWeb\InscriptionController::inscriptionAvecPartenaire
     */
    public function testInscriptionAvecPartenairesInscriptionSansPartenaires()
    {
        $this->client->loginUser($this->em->getRepository(Utilisateur::class)->find($this->ids['user']), 'app');
        $this->client->request('GET', $this->router->generate('UcaWeb_InscriptionAvecPartenaire', ['id' => $this->ids['inscriptionSansPartenaires']]));
        $expectedRedirection = $this->router->generate('UcaWeb_Accueil');
        $this->assertResponseRedirects($expectedRedirection);
    }

    /**
     * @covers \App\Controller\UcaWeb\InscriptionController::inscriptionAvecPartenaire
     */
    public function testInscriptionAvecPartenairesInscriptionAvecPartenaires()
    {
        $this->client->loginUser($this->em->getRepository(Utilisateur::class)->find($this->ids['user']), 'app');
        $this->client->request('GET', $this->router->generate('UcaWeb_InscriptionAvecPartenaire', ['id' => $this->ids['inscriptionAvecPartenaires']]));
        $expectedRedirection = $this->router->generate('UcaWeb_Panier');
        $this->assertResponseRedirects($expectedRedirection);
    }
}
