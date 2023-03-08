<?php

namespace App\Tests\Controller\UcaWeb;

use App\Entity\Uca\Activite;
use App\Entity\Uca\ClasseActivite;
use App\Entity\Uca\Creneau;
use App\Entity\Uca\DhtmlxEvenement;
use App\Entity\Uca\Etablissement;
use App\Entity\Uca\FormatAchatCarte;
use App\Entity\Uca\FormatActivite;
use App\Entity\Uca\FormatAvecCreneau;
use App\Entity\Uca\FormatAvecReservation;
use App\Entity\Uca\FormatSimple;
use App\Entity\Uca\Lieu;
use App\Entity\Uca\Reservabilite;
use App\Entity\Uca\Ressource;
use App\Entity\Uca\TypeActivite;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;

/**
 * @internal
 * @coversNothing
 */
class ActiviteControllerTest extends WebTestCase
{
    private $client;

    private $em;

    private $router;

    private $tokens = [];

    private $ids = [];

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->router = static::getContainer()->get(RouterInterface::class);
        $tokenManager = static::getContainer()->get('security.csrf.token_manager');

        $this->tokens['ucabundle_rechercheactivite'] = $tokenManager->getToken('ucabundle_rechercheactivite')->getValue();

        $etablissement = (new Etablissement())
            ->setCode('IUTSD')
            ->setLibelle('IUT de Saint-Dié-des-Vosges')
            ->setAdresse('11 Rue de l\'université')
            ->setCodePostal('88100')
            ->setVille('Saint-Dié-des-Vosges')
        ;
        $etablissement->setImage('test.jpg');
        $this->em->persist($etablissement);

        $typeActivite = (new TypeActivite())
            ->setLibelle('Type Activite Test')
        ;
        $this->em->persist($typeActivite);

        $classeActivite = (new ClasseActivite())
            ->setLibelle('Classe Actvite Test')
            ->setTypeActiviteLibelle('Type Activite Test')
            ->setImage('test.jpg')
            ->setTypeActivite($typeActivite)
        ;
        $this->em->persist($classeActivite);

        $activite = (new Activite())
            ->setLibelle('Activite Test')
            ->setImage('test.jpg')
            ->setDescription('Description Activite Test')
            ->setClasseActiviteLibelle('Classe Activite Test')
            ->setClasseActivite($classeActivite)
        ;
        $this->em->persist($activite);

        $formatSimple = (new FormatSimple());
        $formatSimple->setCapacite(10);
        $formatSimple->setLibelle('FormatSimple');
        $formatSimple->setDescription('FormatSimple');
        $formatSimple->setDateDebutPublication(new \DateTime());
        $formatSimple->setDateFinPublication((new \DateTime())->add(new \DateInterval('P14D')));
        $formatSimple->setDateDebutInscription((new \DateTime())->add(new \DateInterval('P1D')));
        $formatSimple->setDateFinInscription((new \DateTime())->add(new \DateInterval('P7D')));
        $formatSimple->setDateDebutEffective((new \DateTime())->add(new \DateInterval('P5D')));
        $formatSimple->setDateFinEffective((new \DateTime())->add(new \DateInterval('P15D')));
        $formatSimple->setImage('test.jpg');
        $formatSimple->setEstPayant(false);
        $formatSimple->setEstEncadre(false);
        $formatSimple->setActivite($activite);

        $this->em->persist($formatSimple);

        $formatAvecCreneau = (new FormatAvecCreneau());
        $formatAvecCreneau->setCapacite(10);
        $formatAvecCreneau->setLibelle('FormatAvecCreneau');
        $formatAvecCreneau->setDescription('FormatAvecCreneau');
        $formatAvecCreneau->setDateDebutPublication(new \DateTime());
        $formatAvecCreneau->setDateFinPublication((new \DateTime())->add(new \DateInterval('P14D')));
        $formatAvecCreneau->setDateDebutInscription((new \DateTime())->add(new \DateInterval('P1D')));
        $formatAvecCreneau->setDateFinInscription((new \DateTime())->add(new \DateInterval('P7D')));
        $formatAvecCreneau->setDateDebutEffective((new \DateTime())->add(new \DateInterval('P5D')));
        $formatAvecCreneau->setDateFinEffective((new \DateTime())->add(new \DateInterval('P15D')));
        $formatAvecCreneau->setImage('test.jpg');
        $formatAvecCreneau->setEstPayant(false);
        $formatAvecCreneau->setEstEncadre(false);
        $formatAvecCreneau->setActivite($activite);

        $this->em->persist($formatAvecCreneau);

        $creneau = (new Creneau())
            ->setCapacite(10)
            ->setFormatActivite($formatAvecCreneau)
        ;
        $this->em->persist($creneau);

        $formatAchatCarte = (new FormatAchatCarte());
        $formatAchatCarte->setCapacite(10);
        $formatAchatCarte->setLibelle('FormatAchatCarte');
        $formatAchatCarte->setDescription('FormatAchatCarte');
        $formatAchatCarte->setDateDebutPublication(new \DateTime());
        $formatAchatCarte->setDateFinPublication((new \DateTime())->add(new \DateInterval('P14D')));
        $formatAchatCarte->setDateDebutInscription((new \DateTime())->add(new \DateInterval('P1D')));
        $formatAchatCarte->setDateFinInscription((new \DateTime())->add(new \DateInterval('P7D')));
        $formatAchatCarte->setDateDebutEffective((new \DateTime())->add(new \DateInterval('P5D')));
        $formatAchatCarte->setDateFinEffective((new \DateTime())->add(new \DateInterval('P15D')));
        $formatAchatCarte->setImage('test.jpg');
        $formatAchatCarte->setEstPayant(false);
        $formatAchatCarte->setEstEncadre(false);
        $formatAchatCarte->setActivite($activite);

        $this->em->persist($formatAchatCarte);

        $ressourceLieu = (new Lieu());
        $ressourceLieu->setLibelle('Ressource Lieu Test');
        $ressourceLieu->setImage('test.jpg');
        $ressourceLieu->setNbPartenaires(1);
        $ressourceLieu->setNbPartenairesMax(5);
        $this->em->persist($ressourceLieu);

        $formatAvecReservation = (new FormatAvecReservation());
        $formatAvecReservation->setCapacite(10);
        $formatAvecReservation->setLibelle('FormatAvecReservation');
        $formatAvecReservation->setDescription('FormatAvecReservation');
        $formatAvecReservation->setDateDebutPublication(new \DateTime());
        $formatAvecReservation->setDateFinPublication((new \DateTime())->add(new \DateInterval('P14D')));
        $formatAvecReservation->setDateDebutInscription((new \DateTime())->add(new \DateInterval('P1D')));
        $formatAvecReservation->setDateFinInscription((new \DateTime())->add(new \DateInterval('P7D')));
        $formatAvecReservation->setDateDebutEffective((new \DateTime())->add(new \DateInterval('P5D')));
        $formatAvecReservation->setDateFinEffective((new \DateTime())->add(new \DateInterval('P15D')));
        $formatAvecReservation->setImage('test.jpg');
        $formatAvecReservation->setEstPayant(false);
        $formatAvecReservation->setEstEncadre(false);
        $formatAvecReservation->setActivite($activite);
        $formatAvecReservation->addRessource($ressourceLieu);

        $this->em->persist($formatAvecReservation);

        $evenement1 = (new DhtmlxEvenement());
        $evenement1->setDateDebut(new \DateTime());
        $evenement1->setDateFin((new \DateTime())->add(new \DateInterval('P1D')));

        $this->em->persist($evenement1);

        $evenement2 = (new DhtmlxEvenement());
        $evenement2->setDateDebut((new \DateTime())->add(new \DateInterval('P2D')));
        $evenement2->setDateFin((new \DateTime())->add(new \DateInterval('P9D')));

        $this->em->persist($evenement2);

        $reservabilite1 = (new Reservabilite());
        $reservabilite1->setCapacite(10);
        $reservabilite1->setEvenement($evenement1);
        $reservabilite1->setRessource($ressourceLieu);

        $this->em->persist($reservabilite1);

        $reservabilite2 = (new Reservabilite());
        $reservabilite2->setCapacite(10);
        $reservabilite2->setEvenement($evenement2);
        $reservabilite2->setRessource($ressourceLieu);

        $this->em->persist($reservabilite2);

        $this->em->flush();

        $this->ids['etablissement'] = $etablissement->getId();
        $this->ids['typeActivite'] = $typeActivite->getId();
        $this->ids['classeActivite'] = $classeActivite->getId();
        $this->ids['activite'] = $activite->getId();
        $this->ids['formatSimple'] = $formatSimple->getId();
        $this->ids['formatAvecCreneau'] = $formatAvecCreneau->getId();
        $this->ids['creneau'] = $creneau->getId();
        $this->ids['formatAchatCarte'] = $formatAchatCarte->getId();
        $this->ids['ressourceLieu'] = $ressourceLieu->getId();
        $this->ids['formatAvecReservation'] = $formatAvecReservation->getId();
        $this->ids['evenement1'] = $evenement1->getId();
        $this->ids['evenement2'] = $evenement2->getId();
        $this->ids['reservabilite1'] = $reservabilite1->getId();
        $this->ids['reservabilite2'] = $reservabilite2->getId();
    }

    public function accessDataProvider()
    {
        return [
            [null, 'GET', 'UcaWeb_ClasseActiviteLister', Response::HTTP_OK],
            [null, 'POST', 'UcaWeb_ClasseActiviteLister', Response::HTTP_OK, [], ['ucabundle_rechercheactivite' => []]],
            [null, 'POST', 'UcaWeb_ClasseActiviteLister', Response::HTTP_OK, [], ['ucabundle_rechercheactivite' => ['activite' => 'id_activite', 'etablissement' => 'id_etablissement', '_token' => 'token']]],
            [null, 'GET', 'UcaWeb_ActiviteLister', Response::HTTP_OK, ['id' => 'id_classe_activite']],
            [null, 'GET', 'UcaWeb_ActiviteLister', Response::HTTP_FOUND, ['id' => 'id_classe_activite_inconnu']],
            [null, 'GET', 'UcaWeb_FormatActiviteLister', Response::HTTP_OK, ['idCa' => 'id_classe_activite', 'id' => 'id_activite']],
            [null, 'GET', 'UcaWeb_FormatActiviteLister', Response::HTTP_FOUND, ['idCa' => 'id_classe_activite', 'id' => 'id_activite_inconnu']],
            [null, 'GET', 'UcaWeb_FormatActiviteDetail', Response::HTTP_OK, ['idCa' => 'id_classe_activite', 'idA' => 'id_activite', 'id' => 'id_format_simple']],
            [null, 'GET', 'UcaWeb_FormatActiviteDetail', Response::HTTP_OK, ['idCa' => 'id_classe_activite', 'idA' => 'id_activite', 'id' => 'id_format_creneau']],
            [null, 'GET', 'UcaWeb_FormatActiviteDetail', Response::HTTP_OK, ['idCa' => 'id_classe_activite', 'idA' => 'id_activite', 'id' => 'id_format_carte']],
            [null, 'GET', 'UcaWeb_FormatActiviteDetail', Response::HTTP_OK, ['idCa' => 'id_classe_activite', 'idA' => 'id_activite', 'id' => 'id_format_reservation']],
            [null, 'GET', 'UcaWeb_FormatActiviteReservationDetailRessource', Response::HTTP_OK, ['idCa' => 'id_classe_activite', 'idA' => 'id_activite', 'id' => 'id_format_reservation', 'idRessource' => 'id_ressource_lieu']],
            [null, 'GET', 'UcaWeb_FormatActiviteReservationDetailAnneeSemaine', Response::HTTP_OK, ['idCa' => 'id_classe_activite', 'idA' => 'id_activite', 'id' => 'id_format_reservation', 'idRessource' => 'id_ressource_lieu', 'year_week' => 'year_week']],
            [null, 'GET', 'UcaWeb_FormatActiviteReservationDetailAnneeSemaineJour', Response::HTTP_OK, ['idCa' => 'id_classe_activite', 'idA' => 'id_activite', 'id' => 'id_format_reservation', 'idRessource' => 'id_ressource_lieu', 'year_week' => 'year_week', 'day_week' => 1]],
        ];
    }

    /**
     * @dataProvider accessDataProvider
     *
     * @covers \App\Controller\UcaWeb\ActiviteController::ActiviteListerAction
     * @covers \App\Controller\UcaWeb\ActiviteController::ClasseActiviteListerAction
     * @covers \App\Controller\UcaWeb\ActiviteController::findNextDate
     * @covers \App\Controller\UcaWeb\ActiviteController::findPreviousDate
     * @covers \App\Controller\UcaWeb\ActiviteController::FormatActiviteAchatCarte
     * @covers \App\Controller\UcaWeb\ActiviteController::FormatActiviteAvecCreneau
     * @covers \App\Controller\UcaWeb\ActiviteController::FormatActiviteAvecReservation
     * @covers \App\Controller\UcaWeb\ActiviteController::FormatActiviteAvecReservationDetailsRessource
     * @covers \App\Controller\UcaWeb\ActiviteController::FormatActiviteDetailAction
     * @covers \App\Controller\UcaWeb\ActiviteController::FormatActiviteListerAction
     * @covers \App\Controller\UcaWeb\ActiviteController::FormatActiviteSimple
     * @covers \App\Controller\UcaWeb\ActiviteController::formatAvecReservationVoirRessource
     *
     * @param mixed $userEmail
     * @param mixed $method
     * @param mixed $routeName
     * @param mixed $httpResponse
     * @param mixed $urlParameters
     * @param mixed $body
     * @param mixed $ajax
     */
    public function testAccesRoutes($userEmail, $method, $routeName, $httpResponse, $urlParameters = [], $body = [], $ajax = false): void
    {
        if (null != $userEmail) {
            $userTest = static::getContainer()->get(UtilisateurRepository::class)->findOneByEmail($userEmail);
            $this->client->loginUser($userTest, 'app');
        }
        $date = new \DateTime();
        $route = $this->router->generate($routeName, $urlParameters);
        $route = str_replace('id_activite_inconnu', 0, $route);
        $route = str_replace('id_activite', $this->ids['activite'], $route);
        $route = str_replace('id_classe_activite_inconnu', 0, $route);
        $route = str_replace('id_classe_activite', $this->ids['classeActivite'], $route);
        $route = str_replace('id_format_simple', $this->ids['formatSimple'], $route);
        $route = str_replace('id_format_creneau', $this->ids['formatAvecCreneau'], $route);
        $route = str_replace('id_format_carte', $this->ids['formatAchatCarte'], $route);
        $route = str_replace('id_format_reservation', $this->ids['formatAvecReservation'], $route);
        $route = str_replace('id_ressource_lieu', $this->ids['ressourceLieu'], $route);
        $route = str_replace('year_week', $date->format('Y').'_'.$date->format('W'), $route);

        if (!empty($body)) {
            if (isset($body['ucabundle_rechercheactivite']['_token'])) {
                $body['ucabundle_rechercheactivite']['_token'] = $this->tokens['ucabundle_rechercheactivite'];
            }
            if (isset($body['ucabundle_rechercheactivite']['activite'])) {
                $body['ucabundle_rechercheactivite']['activite'] = $this->ids['activite'];
            }
            if (isset($body['ucabundle_rechercheactivite']['etablissement'])) {
                $body['ucabundle_rechercheactivite']['etablissement'] = $this->ids['etablissement'];
            }
        }

        if ($ajax) {
            $this->client->xmlHttpRequest($method, $route, $body);
        } else {
            $this->client->request($method, $route, $body);
        }
        $this->assertResponseStatusCodeSame($httpResponse);
    }
}
