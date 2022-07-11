<?php

namespace App\Tests\Service\Service;

use App\Entity\Uca\ComportementAutorisation;
use App\Entity\Uca\Creneau;
use App\Entity\Uca\CreneauProfilUtilisateur;
use App\Entity\Uca\DhtmlxEvenement;
use App\Entity\Uca\DhtmlxSerie;
use App\Entity\Uca\Etablissement;
use App\Entity\Uca\FormatAvecCreneau;
use App\Entity\Uca\Lieu;
use App\Entity\Uca\ProfilUtilisateur;
use App\Entity\Uca\Reservabilite;
use App\Entity\Uca\TypeAutorisation;
use App\Entity\Uca\Utilisateur;
use App\Repository\UtilisateurRepository;
use App\Service\Service\CalendrierService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * @internal
 * @coversNothing
 */
class CalendrierServiceTest extends WebTestCase
{
    /**
     * @var CalendrierService
     */
    private $calendrierService;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $container = static::getContainer();

        $this->em = $container->get(EntityManagerInterface::class);
        $this->calendrierService = $container->get(CalendrierService::class);
    }

    /**
     * @covers \App\Service\Service\CalendrierService::createPlanning
     */
    public function testCreatePlanning(): void
    {
        $container = static::getContainer();
        $date = new \DateTime('2022-01-01');

        $comportementAutorisation = (new ComportementAutorisation())
            ->setdescriptionComportement('desc2')
            ->setLibelle('libelle comportement2')
            ->setCodeComportement('code')
        ;

        $typeAutorisation = (new TypeAutorisation())
            ->setComportement(
                $comportementAutorisation
            )
            ->setLibelle('')
        ;
        $formatActivite =
            (new FormatAvecCreneau())
                ->setLibelle('FormatAvecCreneau')
                ->addAutorisation($typeAutorisation)
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
        $creneau = (new Creneau())
            ->setFormatActivite($formatActivite)
            ->setCapacite(10)
        ;

        $this->em->persist($formatActivite);
        $this->em->persist($creneau);
        $this->em->persist($typeAutorisation);
        $this->em->persist($comportementAutorisation);
        $this->em->flush();

        $formatCreneau1 = ['typeFormat' => 'FormatAvecCreneau', 'itemId' => $formatActivite->getId(), 'typeVisualisation' => 'mois', 'widthWindow' => 1000, 'currentDate' => date_format(new \DateTime('2022-01-01'), 'd/m/Y'), 'idRessource' => 1];
        $html = $this->calendrierService->createPlanning($formatCreneau1);

        $this->em->remove($formatActivite);
        $this->em->remove($creneau);
        $this->em->remove($typeAutorisation);
        $this->em->remove($comportementAutorisation);
        $this->em->flush();

        $this->assertEquals($html->getContent(), '{"content":"\u003Cdiv class=\u0022container-style mb-5\u0022 id=\u0022planning\u0022\u003E\n\t\u003Cdiv id=\u0022load\u0022\u003E\n\t\t\u003Cimg src=\u0022\/build\/images\/load.gif\u0022 id=\u0022load-img\u0022\u003E\n\t\u003C\/div\u003E\n\t\t\t\u003Cdiv class=\u0022row d-flex\u0022\u003E\n\t\t\t\u003Cdiv class=\u0022col-2 d-flex\u0022\u003E\n\t\t\t\t\u003Ca class=\u0022cursor-pointer color-blanc\u0022 onclick=\u0022_uca.calendrier.changePeriode(false, 4)\u0022\u003E\n\t\t\t\t\t\u003Ci class=\u0022fas fa-caret-square-left\u0022\u003E\u003C\/i\u003E\n\t\t\t\t\u003C\/a\u003E\n\t\t\t\t\u003Ca class=\u0022cursor-pointer color-blanc ml-1\u0022 onclick=\u0022_uca.calendrier.changePeriode(true, 4)\u0022\u003E\n\t\t\t\t\t\u003Ci class=\u0022fas fa-caret-square-right\u0022\u003E\u003C\/i\u003E\n\t\t\t\t\u003C\/a\u003E\n\t\t\t\u003C\/div\u003E\n\n\t\t\t\u003Ch3 class=\u0022col-8 text-center color-blanc\u0022\u003EJanuary\n\t\t\t\t2022\u003C\/h3\u003E\n\n\t\t\t\u003Cdiv class=\u0022col-2\u0022\u003E\n\t\t\t\t\t\t\t\t\t\t\t\t\t\u003Ca class=\u0022text-uppercase cursor-pointer mr-2 font-weight-bold color-blanc\u0022 onclick=\u0022_uca.calendrier.changeTypeVisualisation(\u0027semaine\u0027)\u0022\u003ESemaine\u003C\/a\u003E\n\t\t\t\t\t\t\t\t\u003Ca class=\u0022text-uppercase cursor-pointer font-weight-bold color-blanc\u0022 onclick=\u0022_uca.calendrier.changeTypeVisualisation(\u0027jour\u0027)\u0022\u003EJour\u003C\/a\u003E\n\t\t\t\u003C\/div\u003E\n\t\t\u003C\/div\u003E\n\n\t\t\u003Cdiv class=\u0022row\u0022\u003E\n\t\t\t\n\t\t\t\t\t\t\t\u003Cdiv class=\u0022col-sm bg-dark-gray font-weight-bold calendar-entete \u0022\u003E\n\t\t\t\t\t\t\t\t\t\tSATURDAY\u003Cbr\/\u003E\n\t\t\t\t\t\t\t\t\t\u003C\/div\u003E\n\t\t\t\t\t\t\t\u003Cdiv class=\u0022col-sm bg-dark-gray font-weight-bold calendar-entete \u0022\u003E\n\t\t\t\t\t\t\t\t\t\tSUNDAY\u003Cbr\/\u003E\n\t\t\t\t\t\t\t\t\t\u003C\/div\u003E\n\t\t\t\t\t\t\t\u003Cdiv class=\u0022col-sm bg-dark-gray font-weight-bold calendar-entete \u0022\u003E\n\t\t\t\t\t\t\t\t\t\tMONDAY\u003Cbr\/\u003E\n\t\t\t\t\t\t\t\t\t\u003C\/div\u003E\n\t\t\t\t\t\t\t\u003Cdiv class=\u0022col-sm bg-dark-gray font-weight-bold calendar-entete \u0022\u003E\n\t\t\t\t\t\t\t\t\t\tTUESDAY\u003Cbr\/\u003E\n\t\t\t\t\t\t\t\t\t\u003C\/div\u003E\n\t\t\t\t\t\u003C\/div\u003E\n\n\t\t\u003Cdiv class=\u0027row\u0027\u003E\n\t\t\t\t\t\u003C\/div\u003E\n\t\u003C\/div\u003E\n\u003Cscript type=\u0027text\/javascript\u0027\u003E\n\t\u0027use_strict\u0027;\n  var itemId = \u0022'.$formatCreneau1['itemId'].'\u0022;\n  var typeVisualisation = \u0022mois\u0022;\n  var currentDate = \u002201\/01\/2022\u0022;\n  var typeFormat = \u0022FormatAvecCreneau\u0022;\n  var idRessource = \u00221\u0022;\n  $(function(){\n\t  _uca.calendrier.listenClickBtnGetModalDetailCreneau()\n  });\n\u003C\/script\u003E\n"}');

        $this->em->persist($formatActivite);
        $this->em->persist($creneau);
        $this->em->persist($typeAutorisation);
        $this->em->persist($comportementAutorisation);
        $this->em->flush();

        $formatCreneau2 = ['typeFormat' => 'FormatAvecCreneau', 'itemId' => $formatActivite->getId(), 'typeVisualisation' => 'mois', 'widthWindow' => 579, 'currentDate' => date_format(new \DateTime('2022-01-01'), 'd/m/Y'), 'idRessource' => 1];
        $html = $this->calendrierService->createPlanning($formatCreneau2);

        $this->em->remove($formatActivite);
        $this->em->remove($creneau);
        $this->em->remove($typeAutorisation);
        $this->em->remove($comportementAutorisation);
        $this->em->flush();

        // dd($html->getContent());
        $this->assertEquals($html->getContent(), '{"content":"\n\u003Cdiv id=\u0022planning-mobile\u0022 class=\u0022container-style\u0022\u003E\n\t\u003Cdiv id=\u0022load\u0022\u003E\n\t\t\u003Cimg src=\u0022\/build\/images\/load.gif\u0022 id=\u0022load-img\u0022\u003E\n\t\u003C\/div\u003E\n\n\t\u003Cdiv class=\u0022row d-flex flex-row justify-content-between align-items-center mb-3\u0022 id=\u0022top\u0022\u003E\n\t\t\u003Cdiv class=\u0022invisible\u0022\u003E\n\t\t\t\t\t\u003C\/div\u003E\n\n\t\t\u003Cdiv class=\u0022d-flex justify-content-center align-items-center\u0022\u003E\n\t\t\t\u003Ca class=\u0022cursor-pointer color-blanc fa-rotate-180 mr-3 d-flex\u0022 onclick=\u0022_uca.calendrier.changePeriode(false, null)\u0022\u003E\n\t\t\t\t\u003Ci class=\u0022fas fa-play\u0022\u003E\u003C\/i\u003E\n\t\t\t\u003C\/a\u003E\n\t\t\t\u003Ch2 class=\u0022text-center text-uppercase color-blanc\u0022\u003E\n\t\t\t\tJanuary\n\t\t\t\t2022\n\t\t\t\u003C\/h2\u003E\n\t\t\t\u003Ca class=\u0022cursor-pointer color-blanc ml-3 d-flex\u0022 onclick=\u0022_uca.calendrier.changePeriode(true, null)\u0022\u003E\n\t\t\t\t\u003Ci class=\u0022fas fa-play\u0022\u003E\u003C\/i\u003E\n\t\t\t\u003C\/a\u003E\n\t\t\u003C\/div\u003E\n\n\t\t\u003Cdiv class=\u0022\u0022\u003E\n\t\t\t\t\t\u003C\/div\u003E\n\t\u003C\/div\u003E\n\n\t\u003C\/div\u003E\n\n\u003Cscript type=\u0027text\/javascript\u0027\u003E\n\t\u0027use_strict\u0027;\n  var itemId = \u0022'.$formatCreneau2['itemId'].'\u0022;\n  var typeVisualisation = \u0022mois\u0022;\n  var currentDate = \u002201\/01\/2022\u0022;\n  var typeFormat = \u0022FormatAvecCreneau\u0022;\n  var idRessource = \u00221\u0022;\n  $(function(){\n\t  _uca.calendrier.listenClickBtnGetModalDetailCreneau()\n  });\n\u003C\/script\u003E\n"}');

        $reservabilite = new Reservabilite();
        $reservabilite->setCapacite(10);
        $reservabilite->setFormatActivite($formatActivite);

        $this->em->persist($reservabilite);
        $this->em->persist($formatActivite);
        $this->em->persist($typeAutorisation);
        $this->em->persist($comportementAutorisation);
        $this->em->flush();

        $formatAvecReservation = ['typeFormat' => 'FormatAvecReservation', 'itemId' => $formatActivite->getId(), 'typeVisualisation' => 'mois', 'widthWindow' => 579, 'currentDate' => date_format(new \DateTime('2022-01-01'), 'd/m/Y'), 'idRessource' => 1];
        $html = $this->calendrierService->createPlanning($formatAvecReservation);

        $this->em->remove($reservabilite);
        $this->em->remove($formatActivite);
        $this->em->remove($typeAutorisation);
        $this->em->remove($comportementAutorisation);
        $this->em->flush();

        $this->assertEquals($html->getContent(), '{"content":"\n\u003Cdiv id=\u0022planning-mobile\u0022 class=\u0022container-style\u0022\u003E\n\t\u003Cdiv id=\u0022load\u0022\u003E\n\t\t\u003Cimg src=\u0022\/build\/images\/load.gif\u0022 id=\u0022load-img\u0022\u003E\n\t\u003C\/div\u003E\n\n\t\u003Cdiv class=\u0022row d-flex flex-row justify-content-between align-items-center mb-3\u0022 id=\u0022top\u0022\u003E\n\t\t\u003Cdiv class=\u0022invisible\u0022\u003E\n\t\t\t\t\t\u003C\/div\u003E\n\n\t\t\u003Cdiv class=\u0022d-flex justify-content-center align-items-center\u0022\u003E\n\t\t\t\u003Ca class=\u0022cursor-pointer color-blanc fa-rotate-180 mr-3 d-flex\u0022 onclick=\u0022_uca.calendrier.changePeriode(false, null)\u0022\u003E\n\t\t\t\t\u003Ci class=\u0022fas fa-play\u0022\u003E\u003C\/i\u003E\n\t\t\t\u003C\/a\u003E\n\t\t\t\u003Ch2 class=\u0022text-center text-uppercase color-blanc\u0022\u003E\n\t\t\t\tJanuary\n\t\t\t\t2022\n\t\t\t\u003C\/h2\u003E\n\t\t\t\u003Ca class=\u0022cursor-pointer color-blanc ml-3 d-flex\u0022 onclick=\u0022_uca.calendrier.changePeriode(true, null)\u0022\u003E\n\t\t\t\t\u003Ci class=\u0022fas fa-play\u0022\u003E\u003C\/i\u003E\n\t\t\t\u003C\/a\u003E\n\t\t\u003C\/div\u003E\n\n\t\t\u003Cdiv class=\u0022\u0022\u003E\n\t\t\t\t\t\u003C\/div\u003E\n\t\u003C\/div\u003E\n\n\t\u003C\/div\u003E\n\n\u003Cscript type=\u0027text\/javascript\u0027\u003E\n\t\u0027use_strict\u0027;\n  var itemId = \u0022'.$formatAvecReservation['itemId'].'\u0022;\n  var typeVisualisation = \u0022mois\u0022;\n  var currentDate = \u002201\/01\/2022\u0022;\n  var typeFormat = \u0022FormatAvecReservation\u0022;\n  var idRessource = \u00221\u0022;\n  $(function(){\n\t  _uca.calendrier.listenClickBtnGetModalDetailCreneau()\n  });\n\u003C\/script\u003E\n"}');

        $format = ['typeFormat' => '', 'itemId' => $formatActivite->getId(), 'typeVisualisation' => 'mois', 'widthWindow' => 579, 'currentDate' => date_format(new \DateTime('2022-01-01'), 'd/m/Y'), 'idRessource' => 1];
        $html = $this->calendrierService->createPlanning($format);

        $this->assertEquals($html, new JsonResponse([]));
    }

    /**
     * @covers \App\Service\Service\CalendrierService::getModalDetailCreneau
     */
    public function testGetModalDetailCreneau(): void
    {
        $container = static::getContainer();

        $utilisateur = (new Utilisateur())
            ->setPrenom('user')
            ->setNom('name')
            ->setUsername('pseudo')
            ->setEmail('test@test.com')
            ->setPassword('password')
        ;

        $this->em->persist($utilisateur);
        $this->em->flush();

        // $userTest = $container->get(UtilisateurRepository::class)->find($utilisateur->getId());
        // dump($utilisateur);
        $this->client->loginUser($utilisateur);

        $profilUtilisateur = (new ProfilUtilisateur())
            ->setLibelle('test')
            ->setNbMaxInscriptions(1)
            ->setPreinscription(false)
            ->setNbMaxInscriptionsRessource(1)
        ;
        $utilisateur->setProfil($profilUtilisateur);

        $date = new \DateTime('2022-07-28');

        $comportementAutorisation = (new ComportementAutorisation())
            ->setdescriptionComportement('desc2')
            ->setLibelle('libelle comportement2')
            ->setCodeComportement('code')
        ;

        $typeAutorisation = (new TypeAutorisation())
            ->setComportement(
                $comportementAutorisation
            )
            ->setLibelle('')
        ;
        $formatActivite =
            (new FormatAvecCreneau())
                ->setLibelle('FormatAvecCreneau')
                ->addAutorisation($typeAutorisation)
                ->setCapacite(10)
                ->setDescription('')
                ->setDateDebutPublication($date)
                ->setDateFinPublication($date)
                ->setDateDebutInscription($date)
                ->setDateFinInscription($date)
                ->setDateDebutEffective($date)
                ->setDateFinEffective($date)
                ->setImage('image')
                ->setEstPayant(false)
                ->setEstEncadre(false)
        ;
        $etablissement = (new Etablissement())
            ->setCode('0000')
            ->setLibelle('etablissement')
            ->setAdresse('adresse')
            ->setCodePostal('00000')
            ->setVille('ville')
        ;
        $etablissement->setImage('image');
        $ressource =
            (new Lieu())
                ->setLibelle('lieu')
                ->setEtablissement($etablissement)
                ->setImage('image')
                ->setNbPartenaires(1)
                ->setNbPartenairesMax(10)
            ;

        $reservabilite = new Reservabilite();
        $reservabilite->setCapacite(10);
        $reservabilite->setFormatActivite($formatActivite);
        $reservabilite->setRessource($ressource);

        $serie = (new DhtmlxSerie())
            ->setReservabilite($reservabilite)
            ->setDateDebut($date)
            ->setDateFin($date)
        ;
        $event = (new DhtmlxEvenement())
            ->setSerie($serie)
            ->setDescription('evenement Test')
            ->setDateDebut($date)
            ->setDateFin($date)
        ;

        $reservabilite->setEvenement($event);

        $creneau = (new Creneau())
            ->setFormatActivite($formatActivite)
            ->setCapacite(10)
        ;

        $creneauProfil = (new CreneauProfilUtilisateur($creneau, $profilUtilisateur, 1))
        ;
        $creneau->addProfilsUtilisateur($creneauProfil);

        $serie->setCreneau($creneau);

        $utilisateur->addCreneaux($creneau);

        $this->em->persist($formatActivite);
        $this->em->persist($typeAutorisation);
        $this->em->persist($comportementAutorisation);
        $this->em->persist($reservabilite);
        $this->em->persist($event);
        $this->em->persist($serie);
        $this->em->persist($ressource);
        $this->em->persist($etablissement);
        $this->em->persist($creneau);
        $this->em->persist($creneauProfil);
        $this->em->flush();

        $res1 = $this->calendrierService->getModalDetailCreneau($event, 'FormatAvecReservation', $formatActivite->getId());
        $res2 = $this->calendrierService->getModalDetailCreneau($event, 'FormatAvecCreneau', $formatActivite->getId());

        $htmlAttendu1 = '{"html":"\n\u003Cdivclass=\u0022modalfade\u0022id=\u0022content_popover_\u0022tabindex=\u0022-1\u0022role=\u0022dialog\u0022aria-hidden=\u0022true\u0022\u003E\n\u003Cdivclass=\u0022modal-dialog\u0022role=\u0022document\u0022\u003E\n\u003Cdivclass=\u0022modal-content\u0022\u003E\n\u003Cdivclass=\u0022modal-header\u0022\u003E\n\u003Cbuttontype=\u0022button\u0022class=\u0022close\u0022data-dismiss=\u0022modal\u0022aria-label=\u0022Close\u0022\u003E\n\u003Cdivclass=\u0022image-presentationimage-remove\u0022aria-hidden=\u0022true\u0022\u003E\u003C\/div\u003E\n\u003C\/button\u003E\n\u003C\/div\u003E\n\u003Cdivclass=\u0022modal-body\u0022\u003E\n\u003Cdivclass=\u0022info-container\u0022\u003E\n\u003Cdivclass=\u0022image-presentation-container\u0022\u003E\n\u003Cdivclass=\u0022image-presentationimage-clock\u0022\u003E\u003C\/div\u003E\n\u003C\/div\u003E\n\u003Cdivclass=\u0022d-gridfont-tall\u0022\u003E\n\u003Cspan\u003E28\/07\/2022\u003C\/span\u003E\n\u003Cspan\u003E00:00-00:00\u003C\/span\u003E\n\u003C\/div\u003E\n\u003C\/div\u003E\n\n\n\u003Cdivclass=\u0022info-container\u0022\u003E\n\u003Cdivclass=\u0022image-presentation-container\u0022\u003E\n\u003Cdivclass=\u0022image-presentationimage-localization\u0022\u003E\u003C\/div\u003E\n\u003C\/div\u003E\n\u003Cspanclass=\u0022font-tall\u0022\u003E\nNoplacedefined\n\u003C\/span\u003E\n\u003C\/div\u003E\n\n\u003Cdivclass=\u0022info-container\u0022\u003E\n\u003Cdivclass=\u0022image-presentation-container\u0022\u003E\n\u003Cdivclass=\u0022image-presentationimage-shopping\u0022\u003E\u003C\/div\u003E\n\u003C\/div\u003E\n\u003Cdivclass=\u0022d-grid\u0022\u003E\n\u003Cspanclass=\u0022font-talllabel\u0022\u003EPrice:\u003C\/span\u003E\n\u003C\/div\u003E\n\u003C\/div\u003E\n\n\n\n\u003C\/div\u003E\n\u003Cdivclass=\u0022modal-footer\u0022\u003E\n\u003Cbuttontype=\u0022button\u0022class=\u0022btnback\u0022data-dismiss=\u0022modal\u0022\u003EBack\u003C\/button\u003E\n\u003C!--cgvnonacceptees--\u003E\n\u003Cpid=\u0022indisponible-cgvnonacceptees\u0022class=\u0022m-0fs-14fw-500text-uppercasecolor-taupe-graytext-centerdisplay_desktop\u0022\u003E\nUNAVAILABLE\n\u003Cspanclass=\u0022d-inline-block\u0022tabindex=\u00220\u0022data-toggle=\u0022tooltip\u0022title=\u0022Pleaseacceptgeneraltermsandconditionsofsaletoregistertoanyactivity.\u0022aria-label=\u0022Pleaseacceptgeneraltermsandconditionsofsaletoregistertoanyactivity.\u0022\u003E\n\u003Ciclass=\u0022fasfa-question-circle\u0022\u003E\u003C\/i\u003E\n\u003C\/span\u003E\n\u003C\/p\u003E\n\u003Cpid=\u0022indisponible-cgvnonacceptees_mobile\u0022class=\u0022m-0fs-14fw-500color-taupe-graytext-centerdisplay_responsive\u0022\u003E\n\u003Ciclass=\u0022fasfa-question-circle\u0022\u003E\u003C\/i\u003EPleaseacceptgeneraltermsandconditionsofsaletoregistertoanyactivity.\n\u003C\/p\u003E\n\u003C\/div\u003E\n\u003C\/div\u003E\n\u003C\/div\u003E\n\u003C\/div\u003E"}';

        $html1 = str_replace([' '], '', substr($res2->getContent(), 0, 83).substr($res2->getContent(), 83 + 4));
        $this->assertEquals($html1, $htmlAttendu1);

        $htmlAttendu2 = '{"html":"\n\u003Cdivclass=\u0022modalfade\u0022id=\u0022content_popover_\u0022tabindex=\u0022-1\u0022role=\u0022dialog\u0022aria-hidden=\u0022true\u0022\u003E\n\u003Cdivclass=\u0022modal-dialog\u0022role=\u0022document\u0022\u003E\n\u003Cdivclass=\u0022modal-content\u0022\u003E\n\u003Cdivclass=\u0022modal-header\u0022\u003E\n\u003Cbuttontype=\u0022button\u0022class=\u0022close\u0022data-dismiss=\u0022modal\u0022aria-label=\u0022Close\u0022\u003E\n\u003Cdivclass=\u0022image-presentationimage-remove\u0022aria-hidden=\u0022true\u0022\u003E\u003C\/div\u003E\n\u003C\/button\u003E\n\u003C\/div\u003E\n\u003Cdivclass=\u0022modal-body\u0022\u003E\n\u003Cdivclass=\u0022info-container\u0022\u003E\n\u003Cdivclass=\u0022image-presentation-container\u0022\u003E\n\u003Cdivclass=\u0022image-presentationimage-clock\u0022\u003E\u003C\/div\u003E\n\u003C\/div\u003E\n\u003Cdivclass=\u0022d-gridfont-tall\u0022\u003E\n\u003Cspan\u003E28\/07\/2022\u003C\/span\u003E\n\u003Cspan\u003E00:00-00:00\u003C\/span\u003E\n\u003C\/div\u003E\n\u003C\/div\u003E\n\n\n\u003Cdivclass=\u0022info-container\u0022\u003E\n\u003Cdivclass=\u0022image-presentation-container\u0022\u003E\n\u003Cdivclass=\u0022image-presentationimage-localization\u0022\u003E\u003C\/div\u003E\n\u003C\/div\u003E\n\u003Cspanclass=\u0022font-tall\u0022\u003E\nNoplacedefined\n\u003C\/span\u003E\n\u003C\/div\u003E\n\n\u003Cdivclass=\u0022info-container\u0022\u003E\n\u003Cdivclass=\u0022image-presentation-container\u0022\u003E\n\u003Cdivclass=\u0022image-presentationimage-shopping\u0022\u003E\u003C\/div\u003E\n\u003C\/div\u003E\n\u003Cdivclass=\u0022d-grid\u0022\u003E\n\u003Cspanclass=\u0022font-talllabel\u0022\u003EPrice:\u003C\/span\u003E\n\u003C\/div\u003E\n\u003C\/div\u003E\n\n\n\n\u003C\/div\u003E\n\u003Cdivclass=\u0022modal-footer\u0022\u003E\n\u003Cbuttontype=\u0022button\u0022class=\u0022btnback\u0022data-dismiss=\u0022modal\u0022\u003EBack\u003C\/button\u003E\n\u003C!--cgvnonacceptees--\u003E\n\u003Cpid=\u0022indisponible-cgvnonacceptees\u0022class=\u0022m-0fs-14fw-500text-uppercasecolor-taupe-graytext-centerdisplay_desktop\u0022\u003E\nUNAVAILABLE\n\u003Cspanclass=\u0022d-inline-block\u0022tabindex=\u00220\u0022data-toggle=\u0022tooltip\u0022title=\u0022Pleaseacceptgeneraltermsandconditionsofsaletoregistertoanyactivity.\u0022aria-label=\u0022Pleaseacceptgeneraltermsandconditionsofsaletoregistertoanyactivity.\u0022\u003E\n\u003Ciclass=\u0022fasfa-question-circle\u0022\u003E\u003C\/i\u003E\n\u003C\/span\u003E\n\u003C\/p\u003E\n\u003Cpid=\u0022indisponible-cgvnonacceptees_mobile\u0022class=\u0022m-0fs-14fw-500color-taupe-graytext-centerdisplay_responsive\u0022\u003E\n\u003Ciclass=\u0022fasfa-question-circle\u0022\u003E\u003C\/i\u003EPleaseacceptgeneraltermsandconditionsofsaletoregistertoanyactivity.\n\u003C\/p\u003E\n\u003C\/div\u003E\n\u003C\/div\u003E\n\u003C\/div\u003E\n\u003C\/div\u003E"}';

        $html2 = str_replace([' '], '', substr($res2->getContent(), 0, 83).substr($res2->getContent(), 83 + 4));
        $this->assertEquals($html2, $htmlAttendu2);

        $this->em->remove($formatActivite);
        $this->em->remove($typeAutorisation);
        $this->em->remove($comportementAutorisation);
        $this->em->remove($reservabilite);
        $this->em->remove($event);
        $this->em->remove($serie);
        $this->em->remove($ressource);
        $this->em->remove($etablissement);
        $this->em->remove($creneau);
        $this->em->remove($utilisateur);
        $this->em->remove($creneauProfil);
        $this->em->flush();
    }

    /**
     * @covers \App\Service\Service\CalendrierService::createMonthPlanning
     */
    public function testCreateMonthPlanning(): void
    {
        $container = static::getContainer();

        $date = new \DateTime('2022-01-01');

        $comportementAutorisation = (new ComportementAutorisation())
            ->setdescriptionComportement('desc2')
            ->setLibelle('libelle comportement2')
            ->setCodeComportement('code')
        ;

        $typeAutorisation = (new TypeAutorisation())
            ->setComportement(
                $comportementAutorisation
            )
            ->setLibelle('')
        ;
        $formatActivite =
            (new FormatAvecCreneau())
                ->setLibelle('FormatAvecCreneau')
                ->addAutorisation($typeAutorisation)
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
        $creneau = (new Creneau())
            ->setFormatActivite($formatActivite)
            ->setCapacite(10)
        ;
        $serie = (new DhtmlxSerie())
            ->setCreneau($creneau)
            ->setDateDebut($date)
            ->setDateFin($date)
        ;
        $event = (new DhtmlxEvenement())
            ->setSerie($serie)
            ->setDescription('evenement Test')
            ->setDateDebut($date)
            ->setDateFin($date)
        ;

        $this->em->persist($formatActivite);
        $this->em->persist($serie);
        $this->em->persist($event);
        $this->em->persist($creneau);
        $this->em->persist($typeAutorisation);
        $this->em->persist($comportementAutorisation);
        $this->em->flush();

        $format = ['typeFormat' => 'FormatAvecCreneau', 'itemId' => $formatActivite->getId(), 'typeVisualisation' => 'mois', 'widthWindow' => 1000, 'currentDate' => date_format($date, 'd/m/Y'), 'idRessource' => 1];
        $twigConfig['itemId'] = $format['itemId'];
        $twigConfig['typeVisualisation'] = $format['typeVisualisation'];
        $twigConfig['currentDate'] = \DateTime::createFromFormat('d/m/Y', $format['currentDate'])->setTime(0, 0);
        $twigConfig['typeFormat'] = $format['typeFormat'];
        $twigConfig['idRessource'] = $format['idRessource'];
        $twigConfig['widthWindow'] = $format['widthWindow'];
        $twigConfig['formatActivite'] = $formatActivite;

        $html = $this->calendrierService->createMonthPlanning($formatActivite, $format, $twigConfig);

        $l = ['Semaine', 'Jour', 'MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY', 'SUNDAY', '27', '28', '29', '30', '31', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31', '01', '02', '03', '04', '05', '06'];

        $crawler = new Crawler($html);
        for ($i = 0; $i < $crawler->filter('[class*=font-weight-bold]')->count(); ++$i) {
            $this->assertEquals($crawler->filter('[class*=font-weight-bold]')->eq($i)->text(), $l[$i]);
        }
        $scriptText = $crawler->filter('script')->text();
        $scriptText = explode(';', $scriptText);

        $l1 = explode(' ', $scriptText[1]);
        $this->assertTrue('itemId' == $l1[2] && $l1[4] == '"'.$format['itemId'].'"');

        $l2 = explode(' ', $scriptText[2]);
        $this->assertTrue('typeVisualisation' == $l2[2] && '"mois"' == $l2[4]);

        $l3 = explode(' ', $scriptText[3]);
        $this->assertTrue('currentDate' == $l3[2] && '"01/01/2022"' == $l3[4]);

        $l4 = explode(' ', $scriptText[4]);
        $this->assertTrue('typeFormat' == $l4[2] && '"FormatAvecCreneau"' == $l4[4]);

        $l5 = explode(' ', $scriptText[5]);
        $this->assertTrue('idRessource' == $l5[2] && '"1"' == $l5[4]);

        $date = new \DateTime('2022-10-01');

        $format['currentDate'] = date_format($date, 'd/m/Y');
        $twigConfig['currentDate'] = \DateTime::createFromFormat('d/m/Y', $format['currentDate'])->setTime(0, 0);

        $html = $this->calendrierService->createMonthPlanning($formatActivite, $format, $twigConfig);

        $l = ['Semaine', 'Jour', 'MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY', 'SUNDAY', '26', '27', '28', '29', '30', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31', '01', '02', '03', '04', '05', '06'];

        $crawler = new Crawler($html);
        for ($i = 0; $i < $crawler->filter('[class*=font-weight-bold]')->count(); ++$i) {
            $this->assertEquals($crawler->filter('[class*=font-weight-bold]')->eq($i)->text(), $l[$i]);
        }
        $scriptText = $crawler->filter('script')->text();
        $scriptText = explode(';', $scriptText);

        $l1 = explode(' ', $scriptText[1]);
        $this->assertTrue('itemId' == $l1[2] && $l1[4] == '"'.$format['itemId'].'"');

        $l2 = explode(' ', $scriptText[2]);
        $this->assertTrue('typeVisualisation' == $l2[2] && '"mois"' == $l2[4]);

        $l3 = explode(' ', $scriptText[3]);
        $this->assertTrue('currentDate' == $l3[2] && '"01/10/2022"' == $l3[4]);

        $l4 = explode(' ', $scriptText[4]);
        $this->assertTrue('typeFormat' == $l4[2] && '"FormatAvecCreneau"' == $l4[4]);

        $l5 = explode(' ', $scriptText[5]);
        $this->assertTrue('idRessource' == $l5[2] && '"1"' == $l5[4]);

        $event->setDateDebut(new \DateTime('2020-12-27'));
        $event->setDateFin(new \DateTime('2021-01-10'));
        $date = $date = new \DateTime('2021-01-01');

        $format['currentDate'] = date_format($date, 'd/m/Y');
        $twigConfig['currentDate'] = \DateTime::createFromFormat('d/m/Y', $format['currentDate'])->setTime(0, 0);
        $formatActivite
            ->setDateDebutPublication($event->getDateDebut())
            ->setDateFinPublication($event->getDateFin())
            ->setDateDebutInscription($event->getDateDebut())
            ->setDateFinInscription($event->getDateFin())
            ->setDateDebutEffective($event->getDateDebut())
            ->setDateFinEffective($event->getDateFin())
        ;
        $this->em->persist($event);
        $this->em->persist($formatActivite);
        $this->em->flush();

        $html = $this->calendrierService->createMonthPlanning($formatActivite, $format, $twigConfig);

        $l = ['Semaine', 'Jour', 'MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY', 'SUNDAY', '28', '29', '30', '31', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31'];

        $crawler = new Crawler($html);
        for ($i = 0; $i < $crawler->filter('[class*=font-weight-bold]')->count(); ++$i) {
            $this->assertEquals($crawler->filter('[class*=font-weight-bold]')->eq($i)->text(), $l[$i]);
        }
        $scriptText = $crawler->filter('script')->text();
        $scriptText = explode(';', $scriptText);

        $l1 = explode(' ', $scriptText[1]);
        $this->assertTrue('itemId' == $l1[2] && $l1[4] == '"'.$format['itemId'].'"');

        $l2 = explode(' ', $scriptText[2]);
        $this->assertTrue('typeVisualisation' == $l2[2] && '"mois"' == $l2[4]);

        $l3 = explode(' ', $scriptText[3]);
        $this->assertTrue('currentDate' == $l3[2] && '"01/01/2021"' == $l3[4]);

        $l4 = explode(' ', $scriptText[4]);
        $this->assertTrue('typeFormat' == $l4[2] && '"FormatAvecCreneau"' == $l4[4]);

        $l5 = explode(' ', $scriptText[5]);
        $this->assertTrue('idRessource' == $l5[2] && '"1"' == $l5[4]);

        $event->setDateDebut(new \DateTime('2021-01-01'));
        $event->setDateFin(new \DateTime('2021-01-10'));
        $date = new \DateTime('2021-01-01');

        $format['currentDate'] = date_format($date, 'd/m/Y');
        $twigConfig['currentDate'] = \DateTime::createFromFormat('d/m/Y', $format['currentDate'])->setTime(0, 0);
        $formatActivite
            ->setDateDebutPublication($event->getDateDebut())
            ->setDateFinPublication($event->getDateFin())
            ->setDateDebutInscription($event->getDateDebut())
            ->setDateFinInscription($event->getDateFin())
            ->setDateDebutEffective($event->getDateDebut())
            ->setDateFinEffective($event->getDateFin())
        ;
        $this->em->persist($event);
        $this->em->persist($formatActivite);
        $this->em->flush();

        $html = $this->calendrierService->createMonthPlanning($formatActivite, $format, $twigConfig);

        $l = ['Semaine', 'Jour', 'MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY', 'SUNDAY', '28', '29', '30', '31', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31'];

        $crawler = new Crawler($html);
        for ($i = 0; $i < $crawler->filter('[class*=font-weight-bold]')->count(); ++$i) {
            $this->assertEquals($crawler->filter('[class*=font-weight-bold]')->eq($i)->text(), $l[$i]);
        }
        $scriptText = $crawler->filter('script')->text();
        $scriptText = explode(';', $scriptText);

        $l1 = explode(' ', $scriptText[1]);
        $this->assertTrue('itemId' == $l1[2] && $l1[4] == '"'.$format['itemId'].'"');

        $l2 = explode(' ', $scriptText[2]);
        $this->assertTrue('typeVisualisation' == $l2[2] && '"mois"' == $l2[4]);

        $l3 = explode(' ', $scriptText[3]);
        $this->assertTrue('currentDate' == $l3[2] && '"01/01/2021"' == $l3[4]);

        $l4 = explode(' ', $scriptText[4]);
        $this->assertTrue('typeFormat' == $l4[2] && '"FormatAvecCreneau"' == $l4[4]);

        $l5 = explode(' ', $scriptText[5]);
        $this->assertTrue('idRessource' == $l5[2] && '"1"' == $l5[4]);

        $event->setDateDebut(new \DateTime('2021-01-02'));
        $event->setDateFin(new \DateTime('2021-01-10'));
        $date = new \DateTime('2021-01-02');

        $format['currentDate'] = date_format($date, 'd/m/Y');
        $twigConfig['currentDate'] = \DateTime::createFromFormat('d/m/Y', $format['currentDate'])->setTime(0, 0);
        $formatActivite
            ->setDateDebutPublication($event->getDateDebut())
            ->setDateFinPublication($event->getDateFin())
            ->setDateDebutInscription($event->getDateDebut())
            ->setDateFinInscription($event->getDateFin())
            ->setDateDebutEffective($event->getDateDebut())
            ->setDateFinEffective($event->getDateFin())
        ;
        $this->em->persist($event);
        $this->em->persist($formatActivite);
        $this->em->flush();

        $html = $this->calendrierService->createMonthPlanning($formatActivite, $format, $twigConfig);

        $l = ['Semaine', 'Jour', 'MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY', 'SUNDAY', '28', '29', '30', '31', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31'];

        $crawler = new Crawler($html);
        for ($i = 0; $i < $crawler->filter('[class*=font-weight-bold]')->count(); ++$i) {
            $this->assertEquals($crawler->filter('[class*=font-weight-bold]')->eq($i)->text(), $l[$i]);
        }
        $scriptText = $crawler->filter('script')->text();
        $scriptText = explode(';', $scriptText);

        $l1 = explode(' ', $scriptText[1]);
        $this->assertTrue('itemId' == $l1[2] && $l1[4] == '"'.$format['itemId'].'"');

        $l2 = explode(' ', $scriptText[2]);
        $this->assertTrue('typeVisualisation' == $l2[2] && '"mois"' == $l2[4]);

        $l3 = explode(' ', $scriptText[3]);
        $this->assertTrue('currentDate' == $l3[2] && '"02/01/2021"' == $l3[4]);

        $l4 = explode(' ', $scriptText[4]);
        $this->assertTrue('typeFormat' == $l4[2] && '"FormatAvecCreneau"' == $l4[4]);

        $l5 = explode(' ', $scriptText[5]);
        $this->assertTrue('idRessource' == $l5[2] && '"1"' == $l5[4]);

        $event->setDateDebut(new \DateTime('2022-11-31'));
        $event->setDateFin(new \DateTime('2022-12-10'));
        $date = new \DateTime('2022-12-02');

        $format['currentDate'] = date_format($date, 'd/m/Y');
        $twigConfig['currentDate'] = \DateTime::createFromFormat('d/m/Y', $format['currentDate'])->setTime(0, 0);
        $formatActivite
            ->setDateDebutPublication($event->getDateDebut())
            ->setDateFinPublication($event->getDateFin())
            ->setDateDebutInscription($event->getDateDebut())
            ->setDateFinInscription($event->getDateFin())
            ->setDateDebutEffective($event->getDateDebut())
            ->setDateFinEffective($event->getDateFin())
        ;
        $this->em->persist($event);
        $this->em->persist($formatActivite);
        $this->em->flush();

        $html = $this->calendrierService->createMonthPlanning($formatActivite, $format, $twigConfig);

        $l = ['Semaine', 'Jour', 'MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY', 'SUNDAY', '28', '29', '30', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31', '01', '02', '03', '04', '05', '06', '07', '08'];

        $crawler = new Crawler($html);
        for ($i = 0; $i < $crawler->filter('[class*=font-weight-bold]')->count(); ++$i) {
            $this->assertEquals($crawler->filter('[class*=font-weight-bold]')->eq($i)->text(), $l[$i]);
        }
        $scriptText = $crawler->filter('script')->text();
        $scriptText = explode(';', $scriptText);

        $l1 = explode(' ', $scriptText[1]);
        $this->assertTrue('itemId' == $l1[2] && $l1[4] == '"'.$format['itemId'].'"');

        $l2 = explode(' ', $scriptText[2]);
        $this->assertTrue('typeVisualisation' == $l2[2] && '"mois"' == $l2[4]);

        $l3 = explode(' ', $scriptText[3]);
        $this->assertTrue('currentDate' == $l3[2] && '"02/12/2022"' == $l3[4]);

        $l4 = explode(' ', $scriptText[4]);
        $this->assertTrue('typeFormat' == $l4[2] && '"FormatAvecCreneau"' == $l4[4]);

        $l5 = explode(' ', $scriptText[5]);
        $this->assertTrue('idRessource' == $l5[2] && '"1"' == $l5[4]);

        $event->setDateDebut(new \DateTime('2010-12-26'));
        $event->setDateFin(new \DateTime('2011-01-10'));
        $date = $date = new \DateTime('2011-01-08');

        $format['currentDate'] = date_format($date, 'd/m/Y');
        $twigConfig['currentDate'] = \DateTime::createFromFormat('d/m/Y', $format['currentDate'])->setTime(0, 0);
        $formatActivite
            ->setDateDebutPublication($event->getDateDebut())
            ->setDateFinPublication($event->getDateFin())
            ->setDateDebutInscription($event->getDateDebut())
            ->setDateFinInscription($event->getDateFin())
            ->setDateDebutEffective($event->getDateDebut())
            ->setDateFinEffective($event->getDateFin())
        ;
        $this->em->persist($event);
        $this->em->persist($formatActivite);
        $this->em->flush();

        $html = $this->calendrierService->createMonthPlanning($formatActivite, $format, $twigConfig);

        $l = ['Semaine', 'Jour', 'MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY', 'SUNDAY', '27', '28', '29', '30', '31', '01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23', '24', '25', '26', '27', '28', '29', '30', '31', '01', '02', '03', '04', '05', '06'];

        $crawler = new Crawler($html);
        for ($i = 0; $i < $crawler->filter('[class*=font-weight-bold]')->count(); ++$i) {
            $this->assertEquals($crawler->filter('[class*=font-weight-bold]')->eq($i)->text(), $l[$i]);
        }
        $scriptText = $crawler->filter('script')->text();
        $scriptText = explode(';', $scriptText);

        $l1 = explode(' ', $scriptText[1]);
        $this->assertTrue('itemId' == $l1[2] && $l1[4] == '"'.$format['itemId'].'"');

        $l2 = explode(' ', $scriptText[2]);
        $this->assertTrue('typeVisualisation' == $l2[2] && '"mois"' == $l2[4]);

        $l3 = explode(' ', $scriptText[3]);
        $this->assertTrue('currentDate' == $l3[2] && '"08/01/2011"' == $l3[4]);

        $l4 = explode(' ', $scriptText[4]);
        $this->assertTrue('typeFormat' == $l4[2] && '"FormatAvecCreneau"' == $l4[4]);

        $l5 = explode(' ', $scriptText[5]);
        $this->assertTrue('idRessource' == $l5[2] && '"1"' == $l5[4]);

        $this->em->remove($formatActivite);
        $this->em->remove($serie);
        $this->em->remove($event);
        $this->em->remove($creneau);
        $this->em->remove($typeAutorisation);
        $this->em->remove($comportementAutorisation);
        $this->em->flush();
    }

    /**
     * @covers \App\Service\Service\CalendrierService::createWeekPlanning
     * @covers \App\Service\Service\CalendrierService::getEvents
     */
    public function testCreateWeekPlanning(): void
    {
        $container = static::getContainer();

        $date = new \DateTime('2022-01-01');

        $comportementAutorisation = (new ComportementAutorisation())
            ->setdescriptionComportement('desc2')
            ->setLibelle('libelle comportement2')
            ->setCodeComportement('code')
        ;

        $typeAutorisation = (new TypeAutorisation())
            ->setComportement(
                $comportementAutorisation
            )
            ->setLibelle('')
        ;
        $formatActivite =
            (new FormatAvecCreneau())
                ->setLibelle('FormatAvecCreneau')
                ->addAutorisation($typeAutorisation)
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

        $etablissement = (new Etablissement())
            ->setCode('0000')
            ->setLibelle('etablissement')
            ->setAdresse('adresse')
            ->setCodePostal('00000')
            ->setVille('ville')
        ;
        $etablissement->setImage('image');
        $ressource =
            (new Lieu())
                ->setLibelle('lieu')
                ->setImage('image')
                ->setNbPartenaires(2)
                ->setNbPartenairesMax(10)
            ;
        $creneau = (new Creneau())
            ->setFormatActivite($formatActivite)
            ->setCapacite(10)
        ;

        $reservabilite = new Reservabilite();
        $reservabilite->setCapacite(10);
        $reservabilite->setFormatActivite($formatActivite);

        $serie = (new DhtmlxSerie())
            ->setCreneau($creneau)
            ->setDateDebut($date)
            ->setDateFin($date)
            ->setReservabilite($reservabilite)
        ;
        $event = (new DhtmlxEvenement())
            ->setSerie($serie)
            ->setDescription('evenement Test')
        ;
        $event->setDateDebut($date);
        $event->setDateFin($date);
        $event->setReservabilite($reservabilite);

        $reservabilite->setEvenement($event);
        $creneau->setLieu($ressource);
        $reservabilite->setRessource($ressource);

        $this->em->persist($formatActivite);
        $this->em->persist($serie);
        $this->em->persist($event);
        $this->em->persist($creneau);
        $this->em->persist($typeAutorisation);
        $this->em->persist($comportementAutorisation);
        $this->em->persist($etablissement);
        $this->em->persist($ressource);
        $this->em->persist($reservabilite);
        $this->em->flush();

        $format = ['typeFormat' => 'FormatAvecCreneau', 'itemId' => $formatActivite->getId(), 'typeVisualisation' => 'mois', 'widthWindow' => 1426, 'currentDate' => date_format($date, 'd/m/Y'), 'idRessource' => $ressource->getId()];
        $twigConfig['itemId'] = $format['itemId'];
        $twigConfig['typeVisualisation'] = $format['typeVisualisation'];
        $twigConfig['currentDate'] = \DateTime::createFromFormat('d/m/Y', $format['currentDate'])->setTime(0, 0);
        $twigConfig['typeFormat'] = $format['typeFormat'];
        $twigConfig['idRessource'] = $format['idRessource'];
        $twigConfig['widthWindow'] = $format['widthWindow'];
        $twigConfig['formatActivite'] = $formatActivite;

        $html = $this->calendrierService->createWeekPlanning($formatActivite, $format, $twigConfig);

        $l = ['Mois', 'Semaine', 'Jour', 'MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY', 'SUNDAY'];

        $crawler = new Crawler($html);
        for ($i = 0; $i < $crawler->filter('[class*=font-weight-bold]')->count(); ++$i) {
            $this->assertEquals($crawler->filter('[class*=font-weight-bold]')->eq($i)->text(), $l[$i]);
        }
        $scriptText = $crawler->filter('script')->text();
        $scriptText = explode(';', $scriptText);

        $l1 = explode(' ', $scriptText[1]);
        $this->assertTrue('itemId' == $l1[2] && $l1[4] == '"'.$format['itemId'].'"');

        $l2 = explode(' ', $scriptText[2]);
        $this->assertTrue('typeVisualisation' == $l2[2] && '"mois"' == $l2[4]);

        $l3 = explode(' ', $scriptText[3]);
        $this->assertTrue('currentDate' == $l3[2] && '"01/01/2022"' == $l3[4]);

        $l4 = explode(' ', $scriptText[4]);
        $this->assertTrue('typeFormat' == $l4[2] && '"FormatAvecCreneau"' == $l4[4]);

        $l5 = explode(' ', $scriptText[5]);
        $this->assertTrue('idRessource' == $l5[2] && '"'.$ressource->getId().'"' == $l5[4]);

        $ressource =
            (new Lieu())
                ->setLibelle('lieu')
                ->setEtablissement($etablissement)
                ->setImage('image')
                ->setNbPartenaires(1)
                ->setNbPartenairesMax(10)
            ;
        $creneau->setLieu($ressource);

        $this->em->persist($creneau);
        $this->em->persist($ressource);
        $this->em->flush();

        $format['widthWindow'] = 1000;
        $format['idRessource'] = $ressource->getId();
        $twigConfig['idRessource'] = $format['idRessource'];

        $html = $this->calendrierService->createWeekPlanning($formatActivite, $format, $twigConfig);

        $l = ['Mois', 'Semaine', 'Jour', 'SATURDAY', 'SUNDAY', 'MONDAY', 'TUESDAY'];

        $crawler = new Crawler($html);
        for ($i = 0; $i < $crawler->filter('[class*=font-weight-bold]')->count(); ++$i) {
            $this->assertEquals($crawler->filter('[class*=font-weight-bold]')->eq($i)->text(), $l[$i]);
        }
        $scriptText = $crawler->filter('script')->text();
        $scriptText = explode(';', $scriptText);

        $l1 = explode(' ', $scriptText[1]);
        $this->assertTrue('itemId' == $l1[2] && $l1[4] == '"'.$format['itemId'].'"');

        $l2 = explode(' ', $scriptText[2]);
        $this->assertTrue('typeVisualisation' == $l2[2] && '"mois"' == $l2[4]);

        $l3 = explode(' ', $scriptText[3]);
        $this->assertTrue('currentDate' == $l3[2] && '"01/01/2022"' == $l3[4]);

        $l4 = explode(' ', $scriptText[4]);
        $this->assertTrue('typeFormat' == $l4[2] && '"FormatAvecCreneau"' == $l4[4]);

        $l5 = explode(' ', $scriptText[5]);
        $this->assertTrue('idRessource' == $l5[2] && '"'.$ressource->getId().'"' == $l5[4]);

        $format['widthWindow'] = 1300;

        $html = $this->calendrierService->createWeekPlanning($formatActivite, $format, $twigConfig);

        $l = ['Mois', 'Semaine', 'Jour', 'SATURDAY', 'SUNDAY', 'MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY'];

        $crawler = new Crawler($html);
        for ($i = 0; $i < $crawler->filter('[class*=font-weight-bold]')->count(); ++$i) {
            $this->assertEquals($crawler->filter('[class*=font-weight-bold]')->eq($i)->text(), $l[$i]);
        }
        $scriptText = $crawler->filter('script')->text();
        $scriptText = explode(';', $scriptText);

        $l1 = explode(' ', $scriptText[1]);
        $this->assertTrue('itemId' == $l1[2] && $l1[4] == '"'.$format['itemId'].'"');

        $l2 = explode(' ', $scriptText[2]);
        $this->assertTrue('typeVisualisation' == $l2[2] && '"mois"' == $l2[4]);

        $l3 = explode(' ', $scriptText[3]);
        $this->assertTrue('currentDate' == $l3[2] && '"01/01/2022"' == $l3[4]);

        $l4 = explode(' ', $scriptText[4]);
        $this->assertTrue('typeFormat' == $l4[2] && '"FormatAvecCreneau"' == $l4[4]);

        $l5 = explode(' ', $scriptText[5]);
        $this->assertTrue('idRessource' == $l5[2] && '"'.$ressource->getId().'"' == $l5[4]);

        $format['widthWindow'] = 1200;

        $html = $this->calendrierService->createWeekPlanning($formatActivite, $format, $twigConfig);

        $l = ['Mois', 'Semaine', 'Jour', 'SATURDAY', 'SUNDAY', 'MONDAY', 'TUESDAY', 'WEDNESDAY'];

        $crawler = new Crawler($html);
        for ($i = 0; $i < $crawler->filter('[class*=font-weight-bold]')->count(); ++$i) {
            $this->assertEquals($crawler->filter('[class*=font-weight-bold]')->eq($i)->text(), $l[$i]);
        }
        $scriptText = $crawler->filter('script')->text();
        $scriptText = explode(';', $scriptText);

        $l1 = explode(' ', $scriptText[1]);
        $this->assertTrue('itemId' == $l1[2] && $l1[4] == '"'.$format['itemId'].'"');

        $l2 = explode(' ', $scriptText[2]);
        $this->assertTrue('typeVisualisation' == $l2[2] && '"mois"' == $l2[4]);

        $l3 = explode(' ', $scriptText[3]);
        $this->assertTrue('currentDate' == $l3[2] && '"01/01/2022"' == $l3[4]);

        $l4 = explode(' ', $scriptText[4]);
        $this->assertTrue('typeFormat' == $l4[2] && '"FormatAvecCreneau"' == $l4[4]);

        $l5 = explode(' ', $scriptText[5]);
        $this->assertTrue('idRessource' == $l5[2] && '"'.$ressource->getId().'"' == $l5[4]);

        $format['widthWindow'] = 800;

        $html = $this->calendrierService->createWeekPlanning($formatActivite, $format, $twigConfig);

        $l = ['Mois', 'Semaine', 'Jour', 'SATURDAY', 'SUNDAY', 'MONDAY'];

        $crawler = new Crawler($html);
        for ($i = 0; $i < $crawler->filter('[class*=font-weight-bold]')->count(); ++$i) {
            $this->assertEquals($crawler->filter('[class*=font-weight-bold]')->eq($i)->text(), $l[$i]);
        }
        $scriptText = $crawler->filter('script')->text();
        $scriptText = explode(';', $scriptText);

        $l1 = explode(' ', $scriptText[1]);
        $this->assertTrue('itemId' == $l1[2] && $l1[4] == '"'.$format['itemId'].'"');

        $l2 = explode(' ', $scriptText[2]);
        $this->assertTrue('typeVisualisation' == $l2[2] && '"mois"' == $l2[4]);

        $l3 = explode(' ', $scriptText[3]);
        $this->assertTrue('currentDate' == $l3[2] && '"01/01/2022"' == $l3[4]);

        $l4 = explode(' ', $scriptText[4]);
        $this->assertTrue('typeFormat' == $l4[2] && '"FormatAvecCreneau"' == $l4[4]);

        $l5 = explode(' ', $scriptText[5]);
        $this->assertTrue('idRessource' == $l5[2] && '"'.$ressource->getId().'"' == $l5[4]);

        $format['widthWindow'] = 700;

        $html = $this->calendrierService->createWeekPlanning($formatActivite, $format, $twigConfig);

        $l = ['Mois', 'Semaine', 'Jour', 'SATURDAY', 'SUNDAY'];

        $crawler = new Crawler($html);
        for ($i = 0; $i < $crawler->filter('[class*=font-weight-bold]')->count(); ++$i) {
            $this->assertEquals($crawler->filter('[class*=font-weight-bold]')->eq($i)->text(), $l[$i]);
        }
        $scriptText = $crawler->filter('script')->text();
        $scriptText = explode(';', $scriptText);

        $l1 = explode(' ', $scriptText[1]);
        $this->assertTrue('itemId' == $l1[2] && $l1[4] == '"'.$format['itemId'].'"');

        $l2 = explode(' ', $scriptText[2]);
        $this->assertTrue('typeVisualisation' == $l2[2] && '"mois"' == $l2[4]);

        $l3 = explode(' ', $scriptText[3]);
        $this->assertTrue('currentDate' == $l3[2] && '"01/01/2022"' == $l3[4]);

        $l4 = explode(' ', $scriptText[4]);
        $this->assertTrue('typeFormat' == $l4[2] && '"FormatAvecCreneau"' == $l4[4]);

        $l5 = explode(' ', $scriptText[5]);
        $this->assertTrue('idRessource' == $l5[2] && '"'.$ressource->getId().'"' == $l5[4]);

        $format['widthWindow'] = 500;

        $html = $this->calendrierService->createWeekPlanning($formatActivite, $format, $twigConfig);

        $l = ['Mois', 'Semaine', 'Jour', 'SATURDAY'];

        $crawler = new Crawler($html);
        for ($i = 0; $i < $crawler->filter('[class*=font-weight-bold]')->count(); ++$i) {
            $this->assertEquals($crawler->filter('[class*=font-weight-bold]')->eq($i)->text(), $l[$i]);
        }
        $scriptText = $crawler->filter('script')->text();
        $scriptText = explode(';', $scriptText);

        $l1 = explode(' ', $scriptText[1]);
        $this->assertTrue('itemId' == $l1[2] && $l1[4] == '"'.$format['itemId'].'"');

        $l2 = explode(' ', $scriptText[2]);
        $this->assertTrue('typeVisualisation' == $l2[2] && '"mois"' == $l2[4]);

        $l3 = explode(' ', $scriptText[3]);
        $this->assertTrue('currentDate' == $l3[2] && '"01/01/2022"' == $l3[4]);

        $l4 = explode(' ', $scriptText[4]);
        $this->assertTrue('typeFormat' == $l4[2] && '"FormatAvecCreneau"' == $l4[4]);

        $l5 = explode(' ', $scriptText[5]);
        $this->assertTrue('idRessource' == $l5[2] && '"'.$ressource->getId().'"' == $l5[4]);

        $format['typeFormat'] = 'FormatAvecReservation';
        $format['idRessource'] = $ressource->getId();

        $twigConfig['typeFormat'] = $format['typeFormat'];
        $twigConfig['itemId'] = $format['itemId'];

        $reservabilite->setRessource($ressource);

        $this->em->persist($reservabilite);
        $this->em->flush();

        $html = $this->calendrierService->createWeekPlanning($formatActivite, $format, $twigConfig);

        $l = ['Mois', 'Semaine', 'Jour', 'SATURDAY'];

        $crawler = new Crawler($html);
        for ($i = 0; $i < $crawler->filter('[class*=font-weight-bold]')->count(); ++$i) {
            $this->assertEquals($crawler->filter('[class*=font-weight-bold]')->eq($i)->text(), $l[$i]);
        }
        $scriptText = $crawler->filter('script')->text();
        $scriptText = explode(';', $scriptText);

        $l1 = explode(' ', $scriptText[1]);
        $this->assertTrue('itemId' == $l1[2] && $l1[4] == '"'.$format['itemId'].'"');

        $l2 = explode(' ', $scriptText[2]);
        $this->assertTrue('typeVisualisation' == $l2[2] && '"mois"' == $l2[4]);

        $l3 = explode(' ', $scriptText[3]);
        $this->assertTrue('currentDate' == $l3[2] && '"01/01/2022"' == $l3[4]);

        $l4 = explode(' ', $scriptText[4]);
        $this->assertTrue('typeFormat' == $l4[2] && '"FormatAvecReservation"' == $l4[4]);

        $l5 = explode(' ', $scriptText[5]);
        $this->assertTrue('idRessource' == $l5[2] && '"'.$ressource->getId().'"' == $l5[4]);

        $event->setSerie(null);

        $this->em->persist($event);
        $this->em->flush();

        $html = $this->calendrierService->createWeekPlanning($formatActivite, $format, $twigConfig);

        $l = ['Mois', 'Semaine', 'Jour', 'SATURDAY'];

        $crawler = new Crawler($html);
        for ($i = 0; $i < $crawler->filter('[class*=font-weight-bold]')->count(); ++$i) {
            $this->assertEquals($crawler->filter('[class*=font-weight-bold]')->eq($i)->text(), $l[$i]);
        }
        $scriptText = $crawler->filter('script')->text();
        $scriptText = explode(';', $scriptText);

        $l1 = explode(' ', $scriptText[1]);
        $this->assertTrue('itemId' == $l1[2] && $l1[4] == '"'.$format['itemId'].'"');

        $l2 = explode(' ', $scriptText[2]);
        $this->assertTrue('typeVisualisation' == $l2[2] && '"mois"' == $l2[4]);

        $l3 = explode(' ', $scriptText[3]);
        $this->assertTrue('currentDate' == $l3[2] && '"01/01/2022"' == $l3[4]);

        $l4 = explode(' ', $scriptText[4]);
        $this->assertTrue('typeFormat' == $l4[2] && '"FormatAvecReservation"' == $l4[4]);

        $l5 = explode(' ', $scriptText[5]);
        $this->assertTrue('idRessource' == $l5[2] && '"'.$ressource->getId().'"' == $l5[4]);

        $this->em->remove($formatActivite);
        $this->em->remove($serie);
        $this->em->remove($event);
        $this->em->remove($creneau);
        $this->em->remove($typeAutorisation);
        $this->em->remove($comportementAutorisation);
        $this->em->remove($etablissement);
        $this->em->remove($ressource);
        $this->em->remove($reservabilite);
        $this->em->flush();
    }

    /**
     * @covers \App\Service\Service\CalendrierService::createDayPlanning
     */
    public function testCreateDayPlanning(): void
    {
        $container = static::getContainer();

        $date = new \DateTime('2022-01-01');

        $comportementAutorisation = (new ComportementAutorisation())
            ->setdescriptionComportement('desc2')
            ->setLibelle('libelle comportement2')
            ->setCodeComportement('code')
        ;

        $typeAutorisation = (new TypeAutorisation())
            ->setComportement(
                $comportementAutorisation
            )
            ->setLibelle('')
        ;
        $formatActivite =
            (new FormatAvecCreneau())
                ->setLibelle('FormatAvecCreneau')
                ->addAutorisation($typeAutorisation)
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

        $etablissement = (new Etablissement())
            ->setCode('0000')
            ->setLibelle('etablissement')
            ->setAdresse('adresse')
            ->setCodePostal('00000')
            ->setVille('ville')
        ;
        $etablissement->setImage('image');
        $ressource =
            (new Lieu())
                ->setLibelle('lieu')
                ->setImage('image')
                ->setNbPartenaires(2)
                ->setNbPartenairesMax(10)
            ;
        $creneau = (new Creneau())
            ->setFormatActivite($formatActivite)
            ->setCapacite(10)
        ;

        $reservabilite = new Reservabilite();
        $reservabilite->setCapacite(10);
        $reservabilite->setFormatActivite($formatActivite);

        $serie = (new DhtmlxSerie())
            ->setCreneau($creneau)
            ->setDateDebut($date)
            ->setDateFin($date)
            ->setReservabilite($reservabilite)
        ;
        $event = (new DhtmlxEvenement())
            ->setSerie($serie)
            ->setDescription('evenement Test')
        ;
        $event->setDateDebut($date);
        $event->setDateFin($date);
        $event->setReservabilite($reservabilite);

        $reservabilite->setEvenement($event);
        $creneau->setLieu($ressource);
        $reservabilite->setRessource($ressource);

        $this->em->persist($formatActivite);
        $this->em->persist($serie);
        $this->em->persist($event);
        $this->em->persist($creneau);
        $this->em->persist($typeAutorisation);
        $this->em->persist($comportementAutorisation);
        $this->em->persist($etablissement);
        $this->em->persist($ressource);
        $this->em->persist($reservabilite);
        $this->em->flush();

        $format = ['typeFormat' => 'FormatAvecCreneau', 'itemId' => $formatActivite->getId(), 'typeVisualisation' => 'mois', 'widthWindow' => 1426, 'currentDate' => date_format($date, 'd/m/Y'), 'idRessource' => $ressource->getId()];
        $twigConfig['itemId'] = $format['itemId'];
        $twigConfig['typeVisualisation'] = $format['typeVisualisation'];
        $twigConfig['currentDate'] = \DateTime::createFromFormat('d/m/Y', $format['currentDate'])->setTime(0, 0);
        $twigConfig['typeFormat'] = $format['typeFormat'];
        $twigConfig['idRessource'] = $format['idRessource'];
        $twigConfig['widthWindow'] = $format['widthWindow'];
        $twigConfig['formatActivite'] = $formatActivite;

        $html = $this->calendrierService->createDayPlanning($formatActivite, $format, $twigConfig);

        $l = ['Mois', 'Semaine', 'Jour', 'Mois', 'Semaine', 'Jour'];

        $crawler = new Crawler($html);
        for ($i = 0; $i < $crawler->filter('[class*=font-weight-bold]')->count(); ++$i) {
            $this->assertEquals($crawler->filter('[class*=font-weight-bold]')->eq($i)->text(), $l[$i]);
        }
        $scriptText = $crawler->filter('script')->text();
        $scriptText = explode(';', $scriptText);

        $l1 = explode(' ', $scriptText[1]);
        $this->assertTrue('itemId' == $l1[2] && $l1[4] == '"'.$format['itemId'].'"');

        $l2 = explode(' ', $scriptText[2]);
        $this->assertTrue('typeVisualisation' == $l2[2] && '"mois"' == $l2[4]);

        $l3 = explode(' ', $scriptText[3]);
        $this->assertTrue('currentDate' == $l3[2] && '"01/01/2022"' == $l3[4]);

        $l4 = explode(' ', $scriptText[4]);
        $this->assertTrue('typeFormat' == $l4[2] && '"FormatAvecCreneau"' == $l4[4]);

        $l5 = explode(' ', $scriptText[5]);
        $this->assertTrue('idRessource' == $l5[2] && '"'.$ressource->getId().'"' == $l5[4]);

        $format['typeFormat'] = 'FormatAvecReservation';
        $format['idRessource'] = $ressource->getId();

        $twigConfig['typeFormat'] = $format['typeFormat'];
        $twigConfig['itemId'] = $format['itemId'];

        $reservabilite->setRessource($ressource);

        $this->em->persist($reservabilite);
        $this->em->flush();

        $html = $this->calendrierService->createDayPlanning($formatActivite, $format, $twigConfig);

        $l = ['Mois', 'Semaine', 'Jour', 'Mois', 'Semaine', 'Jour'];

        $crawler = new Crawler($html);
        for ($i = 0; $i < $crawler->filter('[class*=font-weight-bold]')->count(); ++$i) {
            $this->assertEquals($crawler->filter('[class*=font-weight-bold]')->eq($i)->text(), $l[$i]);
        }
        $scriptText = $crawler->filter('script')->text();
        $scriptText = explode(';', $scriptText);

        $l1 = explode(' ', $scriptText[1]);
        $this->assertTrue('itemId' == $l1[2] && $l1[4] == '"'.$format['itemId'].'"');

        $l2 = explode(' ', $scriptText[2]);
        $this->assertTrue('typeVisualisation' == $l2[2] && '"mois"' == $l2[4]);

        $l3 = explode(' ', $scriptText[3]);
        $this->assertTrue('currentDate' == $l3[2] && '"01/01/2022"' == $l3[4]);

        $l4 = explode(' ', $scriptText[4]);
        $this->assertTrue('typeFormat' == $l4[2] && '"FormatAvecReservation"' == $l4[4]);

        $l5 = explode(' ', $scriptText[5]);
        $this->assertTrue('idRessource' == $l5[2] && '"'.$ressource->getId().'"' == $l5[4]);

        $this->em->remove($formatActivite);
        $this->em->remove($serie);
        $this->em->remove($event);
        $this->em->remove($creneau);
        $this->em->remove($typeAutorisation);
        $this->em->remove($comportementAutorisation);
        $this->em->remove($etablissement);
        $this->em->remove($ressource);
        $this->em->remove($reservabilite);
        $this->em->flush();
    }
}
