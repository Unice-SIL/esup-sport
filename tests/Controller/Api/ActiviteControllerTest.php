<?php

namespace App\Tests\Controller\Api;

use DateTime;
use App\Entity\Uca\Lieu;
use App\Entity\Uca\Appel;
use App\Entity\Uca\Creneau;
use App\Entity\Uca\Commande;
use App\Entity\Uca\DhtmlxSerie;
use App\Entity\Uca\Inscription;
use App\Entity\Uca\Autorisation;
use App\Entity\Uca\Reservabilite;
use App\Entity\Uca\CommandeDetail;
use App\Entity\Uca\DhtmlxEvenement;
use App\Entity\Uca\TypeAutorisation;
use App\Entity\Uca\ProfilUtilisateur;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Uca\FormatAvecReservation;
use App\Repository\UtilisateurRepository;
use App\Entity\Uca\ComportementAutorisation;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\File\File;
use App\Entity\Uca\ReservabiliteProfilUtilisateur;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 * @coversNothing
 */
class ActiviteControllerTest extends WebTestCase
{
    /**
     * @covers \App\Controller\Api\ActiviteController::DataAction
     */
    public function testDataAction(): void
    {
        $client = static::createClient();
        $router = static::getContainer()->get(RouterInterface::class);

        $routeParams = [
            'data' => [
                'typeFormat' => '',
                'itemId' => '',
                'typeVisualisation' => '',
                'currentDate' => '',
                'idRessource' => '',
                'widthWindow' => '',
            ],
        ];

        $client->request('POST', $router->generate('api_activite_creneau'), $routeParams);

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);
    }

    /**
     * @covers \App\Controller\Api\ActiviteController::DetailCreneau
     */
    public function testDetailCreneau(): void
    {
        $client = static::createClient();
        $router = static::getContainer()->get(RouterInterface::class);
        $user = static::getContainer()->get(UtilisateurRepository::class)->findOneByUsername('admin');
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $dhtmlxEvenement = new DhtmlxEvenement();
        $imageTest = new File(__DIR__ . '../../../fixtures/vtt.jpg');

        // DxhtmlEvent > Reservabilite > Ressource > FormatAvecReservation
        $formatAvecReservation = (new FormatAvecReservation())
            ->setLibelle('format simple')
            ->setDescription('format simple - description')
            ->setDateDebutPublication((new DateTime('now'))->modify('+1 day'))
            ->setDateFinPublication((new DateTime('now'))->modify('+10 day'))
            ->setDateDebutInscription((new DateTime('now')))
            ->setDateFinInscription((new DateTime('now'))->modify('+10 day'))
            ->setImageFile($imageTest)
            ->setImage($imageTest->getRealPath())
            ->setCapacite(48)
            ->setEstPayant(false)
            ->setEstEncadre(false)
            ->setDateDebutEffective(new DateTime())
            ->setDateFinEffective(new DateTime());

        $dhtmlxEvenement->setDescription("desctiption");
        $datetimeNow = new DateTime('now');
        $dhtmlxEvenement->setDateDebut($datetimeNow);
        $dhtmlxEvenement->setDateFin($datetimeNow->modify('+7 day'));

        // DhtmlxEvenement > Appel
        $Appel = new Appel();
        $Appel->setUtilisateur($user)
            ->setPresent(true)
            ->setDhtmlxEvenement($dhtmlxEvenement);
        $dhtmlxEvenement->addAppel($Appel);

        // DhtmlxEvenement > Reservabilite
        $Reservabilite = new Reservabilite();
        // DhtmlxEvenement > Inscription
        $inscription = new Inscription($formatAvecReservation, $user, ['typeInscription' => 'format']);
        // DhtmlxEvenement > Inscription > FormatActivite
        $inscription->setLibelle('Inscription libelle');
        // DhtmlxEvenement > Inscription > Creneau
        $creneau = new Creneau();
        $creneau->setCapacite(144);

        $inscription->setCreneau($creneau);
        $inscription->setReservabilite($Reservabilite);
        $inscription->setUtilisateur($user);
        $inscription->setDate(new DateTime('now'));
        $inscription->setStatut('valide');

        // DhtmlxEvenement > Inscription > Autorisation
        $comportement = (new ComportementAutorisation())
            ->setLibelle('Comportement autorisation')
            ->setCodeComportement('case');
        $typeAutorisation = (new TypeAutorisation())
            ->setLibelle('Type autorisation lib')
            ->setComportement($comportement)
            ->setInformationsComplementaires('Infos complémentaires');

        $autorisation = new Autorisation(
            $inscription,
            $typeAutorisation
        );
        $inscription->addAutorisation($autorisation);
        $inscription->addEncadrant($user);

        $Commande = new Commande(static::getContainer()->get(UtilisateurRepository::class)->findOneByUsername('admin'));
        // DhtmlxEvenement > Inscription > CommandeDetail
        $commandeDetail = new CommandeDetail(
            $Commande,
            'inscription',
            $inscription
        );
        $commandeDetail->setLibelle('commande detail lib')
            ->setDateDebut((new DateTime('now')))
            ->setDateFin((new DateTime('now'))->modify('+15 day'));

        $inscription->addCommandeDetail($commandeDetail);
        $inscription->setUtilisateurDesinscription($user);

        $Reservabilite->addInscription($inscription);

        // DhtmlxEvenement > Inscription > Ressource
        $ressource = (new Lieu())
            ->setLibelle('Lieu')
            ->setNbPartenaires(1)
            ->setImageFile($imageTest)
            ->setImage($imageTest->getRealPath())
            ->addFormatResa($formatAvecReservation)
            ->setNbPartenairesMax(1);
        $Reservabilite->setRessource($ressource);

        // DhtmlxEvenement > Inscription > DhtmlxSerie
        $DhtmlxSerie_inscription =  (new DhtmlxSerie())
            ->setDateDebut((new DateTime('now')))
            ->setDateFin((new DateTime('now'))->modify('+15 day'));
        $Reservabilite->setSerie($DhtmlxSerie_inscription);

        // DhtmlxEvenement > Inscription > DhtmlxEvenement
        $Reservabilite->setEvenement($dhtmlxEvenement);

        $profilUtilisateur = (new ProfilUtilisateur())
            ->setNbMaxInscriptions(44)
            ->setNbMaxInscriptionsRessource(2)
            ->setPreinscription(true)
            ->setLibelle('Profil utilisateur');
        $Reservabilite->setCapacite(33);

        $ReservabiliteProfilUtilisateur = new ReservabiliteProfilUtilisateur(
            $Reservabilite,
            $profilUtilisateur,
            10
        );
        $Reservabilite->addProfilsUtilisateur($ReservabiliteProfilUtilisateur);

        $dhtmlxEvenement->setReservabilite($Reservabilite);

        // DhtmlxEvenement > DhtmlxSerie
        $DhtmlxSerie = (new DhtmlxSerie())
            ->setDateDebut((new DateTime('now')))
            ->setDateFin((new DateTime('now'))->modify('+15 day'));
        $dhtmlxEvenement->setSerie($DhtmlxSerie);

        $em->persist($ressource);
        $em->persist($Appel);
        $em->persist($DhtmlxSerie_inscription);
        $em->persist($DhtmlxSerie);
        $em->persist($inscription);
        $em->persist($creneau);
        $em->persist($Commande);
        $em->persist($commandeDetail);
        $em->persist($profilUtilisateur);
        $em->persist($ReservabiliteProfilUtilisateur);
        $em->persist($Reservabilite);
        $em->persist($comportement);
        $em->persist($typeAutorisation);
        $em->persist($autorisation);
        $em->persist($formatAvecReservation);
        $em->persist($dhtmlxEvenement);
        $em->flush();

        $routeParams = [
            'id' => $dhtmlxEvenement->getId(),
            'typeFormat' => 'FormatAvecReservation',
            'idFormat' => $dhtmlxEvenement->getReservabilite()->getRessource()->getFormatResa()[0]->getId(),
        ];
        $client->request('GET', $router->generate('api_detail_creneau', $routeParams));

        $queryIdNeeded = 'content_popover_' . $dhtmlxEvenement->getId();
        $htmlResponse = json_decode($client->getResponse()->getContent(), true)["html"];
        $this->assertStringContainsString($queryIdNeeded, $htmlResponse);
        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);

        $em->remove($ressource);
        $em->remove($Appel);
        $em->remove($DhtmlxSerie_inscription);
        $em->remove($DhtmlxSerie);
        $em->remove($inscription);
        $em->remove($creneau);
        $em->remove($Commande);
        $em->remove($commandeDetail);
        $em->remove($profilUtilisateur);
        $em->remove($ReservabiliteProfilUtilisateur);
        $em->remove($Reservabilite);
        $em->remove($comportement);
        $em->remove($typeAutorisation);
        $em->remove($autorisation);
        $em->remove($formatAvecReservation);
        $em->remove($dhtmlxEvenement);
        $em->flush();
    }
}
