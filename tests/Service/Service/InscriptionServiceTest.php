<?php

namespace App\Tests\Service\Service;

use App\Entity\Uca\Activite;
use App\Entity\Uca\Commande;
use App\Entity\Uca\CommandeDetail;
use App\Entity\Uca\ComportementAutorisation;
use App\Entity\Uca\Creneau;
use App\Entity\Uca\DhtmlxEvenement;
use App\Entity\Uca\DhtmlxSerie;
use App\Entity\Uca\FormatAchatCarte;
use App\Entity\Uca\FormatAvecCreneau;
use App\Entity\Uca\Inscription;
use App\Entity\Uca\ProfilUtilisateur;
use App\Entity\Uca\StatutUtilisateur;
use App\Entity\Uca\TypeAutorisation;
use App\Entity\Uca\Utilisateur;
use App\Form\InscriptionType;
use App\Service\Service\InscriptionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 * @coversNothing
 */
class InscriptionServiceTest extends WebTestCase
{
    /**
     * @var InscriptionService
     */
    private $inscriptionService;

    protected function setUp(): void
    {
        $client = static::createClient();
        $container = static::getContainer();
        $this->em = $container->get(EntityManagerInterface::class);

        $this->utilisateur = (new Utilisateur())
            ->setPrenom('user')
            ->setNom('name')
            ->setUsername('pseudo')
            ->setEmail('test@test.com')
            ->setPassword('password')
        ;
        $profilUtilisateur = (new ProfilUtilisateur())
            ->setLibelle('test')
            ->setNbMaxInscriptions(1)
            ->setPreinscription(false)
            ->setNbMaxInscriptionsRessource(1)
        ;
        $this->utilisateur->setProfil($profilUtilisateur);

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
        $this->format->setActivite(new Activite());

        $this->inscription = new Inscription($this->format, $this->utilisateur, []);

        $this->utilisateur->addInscription($this->inscription);

        $statut = new StatutUtilisateur();
        $this->utilisateur->setStatut($statut);

        $client->loginUser($this->utilisateur);

        $this->inscriptionService = $container->get(InscriptionService::class);
        $this->inscriptionService->setInscription($this->inscription);
    }

    /**
     * @covers \App\Service\Service\InscriptionService::getFormulaire
     */
    public function testGetFormulaire(): void
    {
        // $form = new InscriptionType();
        // dd($this->inscriptionService->getFormulaire($form));
    }

    /**
     * @covers \App\Service\Service\InscriptionService::getMessagePreInscription
     */
    public function testGetMessagePreInscription(): void
    {
        $arrayAttendu = [
            'itemId' => null,
            'statut' => '0',
            'html' => '<divaria-hidden="true"class="modalz-index-topfade"id="modalInscription"role="dialog"tabindex="-1"><divclass="modal-dialog"role="document"><divclass="modal-content"><divclass="modal-headerd-flexjustify-content-centertext-center"><h2aria-labelledby="modalInscription"class="modal-titlefs-24hide-border-titlemt-5mt-md-0">MODALINSCRIPTION.TITRE.ATTENTEPAIEMENT</h2><buttontype="button"class="close"data-dismiss="modal"aria-label="Close"><divclass="image-presentationimage-remove"aria-hidden="true"></div></button></div><divclass="modal-body"><p>Yourinscriptionhasbeentakenintoaccount.</p></div><divclass="modal-footer"><buttonclass="btnbtn-outline-primary"data-dismiss="modal"type="button">Continueonthewebsite</button><aclass="btnbtn-primary"href="/fr/UcaWeb/MesInscriptions"type="button">Myregistrations</a></div></div></div></div>',
            'maxCreneauAtteint' => false,
        ];
        $array = $this->inscriptionService->getMessagePreInscription();
        $array['html'] = str_replace([' ', "\n"], '', $array['html']);

        $this->assertEquals($arrayAttendu, $array);
    }

    /**
     * @covers \App\Service\Service\InscriptionService::getComfirmationPanier
     */
    public function testGetComfirmationPanier(): void
    {
        $commande = new Commande($this->utilisateur);
        $commandeDetail = new CommandeDetail($commande, 'inscription', $this->inscription);

        $arrayAttendu = [
            'itemId' => null,
            'statut' => '0',
            'html' => '<divaria-hidden="true"class="modalz-index-topfade"id="modalInscription"role="dialog"tabindex="-1"><divclass="modal-dialog"role="document"><divclass="modal-content"><divclass="modal-headerd-flexjustify-content-centertext-center"><h2aria-labelledby="modalInscription"class="modal-titlefs-24hide-border-titlemt-5mt-md-0">ADDTOTHEBASKET</h2><buttontype="button"class="close"data-dismiss="modal"aria-label="Close"><divclass="image-presentationimage-remove"aria-hidden="true"></div></button></div><divclass="modal-body"><div><h3></h3><p></p><p>0&nbsp;€</p></div></div><divclass="modal-footer"><buttonclass="btnbtn-outline-primary"data-dismiss="modal"type="button">Continueonthewebsite</button><aclass="btnbtn-primary"href="/fr/UcaWeb/Panier"type="button">Seebasket</a></div></div></div></div>',
            'maxCreneauAtteint' => false,
        ];
        $array = $this->inscriptionService->getComfirmationPanier([$commandeDetail]);
        $array['html'] = str_replace([' ', "\n"], '', $array['html']);

        $this->assertEquals($arrayAttendu, $array);
    }

    /**
     * @covers \App\Service\Service\InscriptionService::mailDesinscription
     */
    public function testMailDesinscription(): void
    {
        // dd($this->inscriptionService->mailDesinscription());
    }

    /**
     * @covers \App\Service\Service\InscriptionService::ajoutPanier
     */
    public function testAjoutPanier(): void
    {
        $commande = new Commande($this->utilisateur);
        $this->utilisateur->addCommande($commande);
        // dd($this->utilisateur->getCommandes());
        // dump($this->utilisateur->getPanier());
        // dd($this->inscriptionService->ajoutPanier()[0]->getCommande()->getStatut());
        // dd($this->inscriptionService->ajoutPanier());
        $this->assertEquals($this->inscriptionService->ajoutPanier()[0]->getCommande(), $this->utilisateur->getPanier());
        $creneau = new Creneau();

        $date = new \Datetime();
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

        $formatAvecCreneau = (new FormatAvecCreneau())
            ->setLibelle('FormatAvecCreneau')
        ;
        $creneau->setFormatActivite($formatAvecCreneau);

        $this->inscription->setCreneau($creneau);

        $this->assertEquals($this->inscriptionService->ajoutPanier()[1]->getCommande(), $this->utilisateur->getPanier());

        // $comportementAutorisationFormat = (new ComportementAutorisation())
        //     ->setdescriptionComportement('desc')
        //     ->setLibelle('libelle comportement')
        //     ->setCodeComportement('code')
        // ;

        // $typeAutorisationFormat = (new TypeAutorisation())
        //     ->setComportement(
        //         $comportementAutorisationFormat
        //     )
        //     ->setLibelle('')
        // ;

        // $format2 =
        //     (new FormatAchatCarte())
        //         ->setCarte(
        //             $typeAutorisationFormat
        //         )
        // ;
        // $activite = new Activite();
        // $format2->setActivite($activite);

        // $inscription2 = new Inscription($format2, $this->utilisateur, []);
        // $this->em->persist($inscription2);
        // $this->em->persist($format2);
        // $this->em->persist($typeAutorisationFormat);
        // $this->em->persist($comportementAutorisationFormat);
        // $this->em->persist($creneau);
        // $this->em->persist($evenement);
        // $this->em->persist($serie);
        // $this->em->persist($formatAvecCreneau);
        // $this->em->persist($activite);
        // $this->em->flush();

        // $this->utilisateur->addInscription($inscription2);

        // // dd(count($this->inscriptionService->ajoutPanier()));
        // // dd($this->inscriptionService->ajoutPanier());

        // $this->assertEquals($this->inscriptionService->ajoutPanier()[0]->getCommande(), $this->utilisateur->getPanier());

        // $inscription2->setStatut('valide');
        // $this->em->persist($inscription2);
        // $this->em->persist($format2);
        // $this->em->persist($typeAutorisationFormat);
        // $this->em->persist($comportementAutorisationFormat);
        // $this->em->persist($creneau);
        // $this->em->persist($evenement);
        // $this->em->persist($serie);
        // $this->em->persist($formatAvecCreneau);
        // $this->em->persist($activite);
        // $this->em->flush();

        // $this->assertEquals($this->inscriptionService->ajoutPanier()[0]->getCommande(), $this->utilisateur->getPanier());
        // $this->em->remove($inscription2);
        // $this->em->remove($format2);
        // $this->em->remove($typeAutorisationFormat);
        // $this->em->remove($comportementAutorisationFormat);
        // $this->em->remove($creneau);
        // $this->em->remove($evenement);
        // $this->em->remove($serie);
        // $this->em->remove($formatAvecCreneau);
        // $this->em->remove($activite);
        // $this->em->flush();
    }
}
