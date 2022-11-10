<?php

namespace App\Tests\Service\Service;

use App\Entity\Uca\Activite;
use App\Entity\Uca\Autorisation;
use App\Entity\Uca\ClasseActivite;
use App\Entity\Uca\Commande;
use App\Entity\Uca\CommandeDetail;
use App\Entity\Uca\ComportementAutorisation;
use App\Entity\Uca\Creneau;
use App\Entity\Uca\DhtmlxEvenement;
use App\Entity\Uca\DhtmlxSerie;
use App\Entity\Uca\Etablissement;
use App\Entity\Uca\FormatAchatCarte;
use App\Entity\Uca\FormatAvecCreneau;
use App\Entity\Uca\FormatAvecReservation;
use App\Entity\Uca\FormatSimple;
use App\Entity\Uca\Groupe;
use App\Entity\Uca\Inscription;
use App\Entity\Uca\Lieu;
use App\Entity\Uca\ProfilUtilisateur;
use App\Entity\Uca\Reservabilite;
use App\Entity\Uca\StatutUtilisateur;
use App\Entity\Uca\TypeActivite;
use App\Entity\Uca\TypeAutorisation;
use App\Entity\Uca\Utilisateur;
use App\Form\InscriptionType;
use App\Repository\ComportementAutorisationRepository;
use App\Repository\GroupeRepository;
use App\Repository\UtilisateurRepository;
use App\Service\Service\InscriptionService;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use App\Repository\ProfilUtilisateurRepository;

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

        // -- Construction des donnees pour l'ajout au panier
        $this->typeActivite = (new TypeActivite())
            ->setLibelle('Type tests')
        ;
        $this->classeActivite = (new ClasseActivite())
            ->setLibelle('Classe tests')
            ->setTypeActivite($this->typeActivite)
            ->setImage('vide')
        ;
        $this->activite = (new Activite())
            ->setLibelle(('Activite tests'))
            ->setClasseActivite($this->classeActivite)
            ->setImage('vide')
            ->setDescription('Test')
        ;
        $comportementCase = static::getContainer()->get(ComportementAutorisationRepository::class)->findOneByCodeComportement('case');
        $this->caseACocher = (new TypeAutorisation())
            ->setLibelle('Case test')
            ->setComportement($comportementCase)
        ;
        $comportementCotisation = static::getContainer()->get(ComportementAutorisationRepository::class)->findOneByCodeComportement('cotisation');
        $this->cotisation = (new TypeAutorisation())
            ->setLibelle('Cotisation test')
            ->setComportement($comportementCotisation)
        ;
        // Format simple - Evenement
        $this->formatSimple = (new FormatSimple())
            ->setLibelle('Format simple test')
            ->setDateDebutEffective(new DateTime())
            ->setActivite($this->activite)
        ;
        // Format achat de carte
        $comportementCarte = static::getContainer()->get(ComportementAutorisationRepository::class)->findOneByCodeComportement('carte');
        $this->carte = (new TypeAutorisation())
            ->setLibelle('Carte test')
            ->setComportement($comportementCarte)
        ;
        $this->formatAchatCarte = (new FormatAchatCarte())
            ->setCarte($this->carte)
            ->setLibelle('Format achat carte test')
            ->setDateDebutEffective(new DateTime())
            ->setActivite($this->activite)
        ;
        // Format avec reservation
        $this->eventForReservabilite = (new DhtmlxEvenement())
            ->setDescription('Test')
            ->setDateDebut(new DateTime())
            ->setDateFin((new DateTime())->add(new DateInterval('PT2H')))
        ;
        $this->reservabilite = (new Reservabilite())
            ->setEvenement($this->eventForReservabilite)
            ->setCapacite(5)
        ;
        $this->etablissement = (new Etablissement())
            ->setLibelle('Etablissement test')
            ->setCode('TEST')
            ->setAdresse('TEST')
            ->setCodePostal('CP')
            ->setVille('Ville')
        ;
        $this->etablissement->setImage('vide');
        $this->ressource = (new Lieu())
            ->setLibelle('Lieu test')
            ->addReservabilite($this->reservabilite)
            ->setEtablissement($this->etablissement)
            ->setImage('vide')
            ->setNbPartenaires(1)
            ->setNbPartenairesMax(2)
            ->setDescription('Test')
        ;
        $this->reservabilite->setRessource($this->ressource);
        $this->formatAvecReservation = (new FormatAvecReservation())
            ->addRessource($this->ressource)
            ->setLibelle('Format avec reservation test')
            ->setDateDebutEffective(new DateTime())
            ->setDateDebutPublication(new DateTime())
            ->setDateDebutInscription(new DateTime())
            ->setDateFinPublication((new DateTime())->add(new DateInterval('PT2H')))
            ->setDateFinInscription((new DateTime())->add(new DateInterval('PT2H')))
            ->setDateFinEffective((new DateTime())->add(new DateInterval('PT2H')))
            ->setActivite($this->activite)
            ->setCapacite(5)
            ->setDescription('Test')
            ->setImage('vide')
            ->setEstPayant(false)
            ->setEstEncadre(false)
        ;

        // Format avec creneau
        $eventForCreneau = (new DhtmlxEvenement())
            ->setDateDebut(new DateTime())
            ->setDateFin((new DateTime())->add(new DateInterval('PT2H')))
        ;
        $serieForCreneau = (new DhtmlxSerie())
            ->setDateDebut(new DateTime())
            ->setDateFin((new DateTime())->add(new DateInterval('PT2H')))
            ->addEvenement($eventForCreneau)
        ;
        $this->creneau = (new Creneau())
            ->setSerie($serieForCreneau)
        ;
        $this->formatAvecCreneau = (new FormatAvecCreneau())
            ->addCreneaux($this->creneau)
            ->setLibelle('Format avec creneau test')
            ->setDateDebutEffective(new DateTime())
            ->setActivite($this->activite)
        ;
        $this->creneau->setFormatActivite($this->formatAvecCreneau);

        // Utilisateur encadrant
        $profilUtilisateurEncadrant = (new ProfilUtilisateur())
            ->setLibelle('test_encadrant')
            ->setNbMaxInscriptions(1)
            ->setPreinscription(false)
            ->setNbMaxInscriptionsRessource(1)
        ;
        $this->utilisateurEncadrant = (new Utilisateur())
            ->setPrenom('user encadrant')
            ->setNom('name encadrant')
            ->setUsername('pseudo')
            ->setEmail('test_encadrant@test.com')
            ->setPassword('password')
            ->setProfil($profilUtilisateurEncadrant)
        ;

        // Utilisateur gestionnaire
        $profilUtilisateurGestionnaire = (new ProfilUtilisateur())
            ->setLibelle('test_gestionnaire')
            ->setNbMaxInscriptions(1)
            ->setPreinscription(false)
            ->setNbMaxInscriptionsRessource(1)
        ;
        $this->utilisateurGestionnaire = (new Utilisateur())
            ->setPrenom('user gestionnaire')
            ->setNom('name gestionnaire')
            ->setUsername('pseudo')
            ->setEmail('test_gestionnaire@test.com')
            ->setPassword('password')
            ->setProfil($profilUtilisateurGestionnaire)
        ;
        $this->goupeGestionnaire = (new Groupe('Test group gestionnaire', ['ROLE_GESTIONNAIRE_VALIDEUR_INSCRIPTION']))
            ->addUtilisateur($this->utilisateurGestionnaire)
            ->setLibelle('Test group gestionnaire')
        ;
        $this->utilisateurGestionnaire->addGroup($this->goupeGestionnaire);
        $this->em->persist($this->goupeGestionnaire);
        $this->em->persist($this->utilisateurGestionnaire);
        $this->em->flush();
    }

    protected function tearDown(): void
    {
        $this->em->clear();

        $container = static::getContainer();
        $this->em->remove($container->get(UtilisateurRepository::class)->find($this->utilisateurGestionnaire->getId()));
        $this->em->remove($container->get(GroupeRepository::class)->find($this->goupeGestionnaire->getId()));
        $this->em->remove($container->get(ProfilUtilisateurRepository::class)->find($this->utilisateurGestionnaire->getProfil()->getId()));
        $this->em->flush();

        static::ensureKernelShutdown();
    }

    /**
     * @covers \App\Service\Service\InscriptionService::getFormulaire
     */
    public function testGetFormulaire(): void
    {
        $container = static::getContainer();
        $form = $container->get('form.factory')->create(InscriptionType::class, $this->inscription, []);
        $resultat = $this->inscriptionService->getFormulaire($form);

        $this->assertEquals($resultat['itemId'], $this->inscription->getItem()->getId());
        $this->assertEquals($resultat['statut'], '1');
        $this->assertNotNull($resultat['html']);
    }

    /**
     * @covers \App\Service\Service\InscriptionService::getMessagePreInscription
     */
    public function testGetMessagePreInscription(): void
    {
        $arrayAttendu = [
            'itemId' => null,
            'statut' => '0',
            'maxCreneauAtteint' => false,
        ];
        $array = $this->inscriptionService->getMessagePreInscription();

        $this->assertEquals($arrayAttendu['itemId'], $array['itemId']);
        $this->assertEquals($arrayAttendu['statut'], $array['statut']);
        $this->assertEquals($arrayAttendu['maxCreneauAtteint'], $array['maxCreneauAtteint']);
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
     * Data provider pour la confirmation ou non.
     */
    public function confirmationDataProvider()
    {
        return [
            // Inscription a un evenement sans autorisation
            ['formatSimple', [], null, true, false, ['inscription']],
            ['formatSimple', [], null, false, true, ['inscription']],
            // Inscription a un evenement avec case a cocher
            ['formatSimple', ['case' => false], null, true, false, ['inscription']],
            ['formatSimple', ['case' => false], null, false, true, ['inscription']],
            // Inscription a un evenement avec carte
            ['formatSimple', ['carte' => false], null, true, false, ['inscription', 'autorisation']],
            ['formatSimple', ['carte' => false], null, false, true, ['inscription', 'autorisation']],
            // Inscription a un evenement avec une carte que l'utilisateur a deja
            ['formatSimple', ['carte' => true], null, true, false, ['inscription']],
            ['formatSimple', ['carte' => true], null, false, true, ['inscription']],
            // Inscription a un evenement avec cotisation
            ['formatSimple', ['cotisation' => false], null, true, false, ['inscription', 'autorisation']],
            ['formatSimple', ['cotisation' => false], null, false, true, ['inscription', 'autorisation']],
            // Inscription a un evenement avec une cotisation que l'utilisateur a deja
            ['formatSimple', ['cotisation' => true], null, true, false, ['inscription']],
            ['formatSimple', ['cotisation' => true], null, false, true, ['inscription']],

            // Inscription a un format avec achat de carte
            ['formatAchatCarte', ['carte' => true], null, true, false, ['inscription', 'autorisation']],
            ['formatAchatCarte', ['carte' => true], null, false, true, ['inscription', 'autorisation']],

            // Inscription a un format avec reservation de ressource sans inscription au format
            ['formatAvecReservation', [], null, true, false, ['inscription', 'format']],
            ['formatAvecReservation', [], null, false, true, ['inscription', 'format']],
            // Inscription à une reservation de ressource - Il existe une inscription au format qui n'est pas encore valide
            ['formatAvecReservation', [], 'annule', true, false, ['inscription', 'format']],
            ['formatAvecReservation', [], 'annule', false, true, ['inscription', 'format']],
            // Inscription à une reservation de ressource - Il existe une inscription au format valide
            ['formatAvecReservation', [], 'valide', true, false, ['inscription']],
            ['formatAvecReservation', [], 'valide', false, true, ['inscription']],

            // Inscription à un cours - Il n'existe pas une inscription au format
            ['formatAvecCreneau', [], null, true, false, ['inscription', 'format']],
            ['formatAvecCreneau', [], null, false, true, ['inscription', 'format']],
            // Inscription à un cours - Il existe une inscription au format qui n'est pas encore valide
            ['formatAvecCreneau', [], 'annule', true, false, ['inscription', 'format']],
            ['formatAvecCreneau', [], 'annule', false, true, ['inscription', 'format']],
            // Inscription à un cours - Il existe une inscription au format valide
            ['formatAvecCreneau', [], 'valide', true, false, ['inscription']],
            ['formatAvecCreneau', [], 'valide', false, true, ['inscription']],
            // Inscription à un cours - Il existe une inscription au format en attente de paiement
            ['formatAvecCreneau', [], 'attentepaiement', true, false, ['inscription']],
            ['formatAvecCreneau', [], 'attentepaiement', false, true, ['inscription']],
            // Inscription à un cours - Il existe une inscription au format en attente de paiement
            ['formatAvecCreneau', ['carte' => false], 'attentepaiement', true, false, ['inscription', 'autorisation']],
            ['formatAvecCreneau', ['carte' => false], 'attentepaiement', false, true, ['inscription', 'autorisation']],
        ];
    }

    /**
     * Fonction qui permet de creer l'inscription pour le test.
     *
     * @param mixed      $typeFormat            - Type de format a ajouter au panier
     * @param mixed      $autorisations         - Liste des autorisations au format "type"=>boleen (permettant de savoir si une autorisation doit etre ajoute a l'utilisateur)
     * @param null|mixed $etatInscriptionFormat - Etat de l'inscription au format si l'utilisateur doit avoir une inscription
     */
    public function createInscription($typeFormat, $autorisations, $etatInscriptionFormat)
    {
        // Gestion du choix du format
        $article = null;
        $format = null;

        switch ($typeFormat) {
            case 'formatSimple':
                $article = $this->formatSimple;

                break;

            case 'formatAchatCarte':
                $article = $this->formatAchatCarte;

                break;

            case 'formatAvecReservation':
                $article = $this->reservabilite;
                $format = $this->formatAvecReservation;

                break;

            case 'formatAvecCreneau':
                $article = $this->creneau;
                $format = $this->formatAvecCreneau;

                break;
        }

        // Creation de l'inscription
        $this->inscription = new Inscription($article, $this->utilisateur, ['format' => $format]);

        $panier = $this->utilisateur->getPanier();

        // Ajout de l'inscription au format dans les inscriptions de l'utilisateur
        if (null != $etatInscriptionFormat && null != $format) {
            $inscriptionFormat = new Inscription($format, $this->utilisateur, ['format' => null]);
            $inscriptionFormat->setStatut($etatInscriptionFormat);
            $this->utilisateur->addInscription($inscriptionFormat);

            if ('attentepaiement' == $etatInscriptionFormat) {
                $commandeArticle = new CommandeDetail($panier, 'inscription', $this->inscription);
                $commandeDetailFormat = new CommandeDetail($panier, 'format', $inscriptionFormat, $commandeArticle);
                $panier->addCommandeDetail($commandeDetailFormat);
                $panier->changeStatut('panier');
                $this->utilisateur->addCommande($panier);
            }
        }

        // Ajout des autorisations
        foreach ($autorisations as $autorisationType => $ajout) {
            // Recuperation du type d'autorisation
            $typeAutorisation = null;

            switch ($autorisationType) {
                case 'case':
                    $typeAutorisation = $this->caseACocher;

                    break;

                case 'cotisation':
                    $typeAutorisation = $this->cotisation;

                    break;

                case 'carte':
                    $typeAutorisation = $this->carte;

                    break;
            }

            // Ajout du typ d'autoriation a l'utilisateur
            if ($ajout) {
                $this->utilisateur->addAutorisation($typeAutorisation);
            }
            // Creation de l'autorisation + ajout a l'inscription
            $autorisation = new Autorisation($this->inscription, $typeAutorisation);
            $this->inscription->addAutorisation($autorisation);

            if ('attentepaiement' == $etatInscriptionFormat) {
                $panier = $this->utilisateur->getPanier();
                $commandeArticle = new CommandeDetail($panier, 'inscription', $this->inscription);
                $commandeDetailAutorisation = new CommandeDetail($panier, 'autorisation', $typeAutorisation, $commandeArticle);
                $panier->addCommandeDetail($commandeDetailAutorisation);
            }
        }

        // Modification de l'inscription dans le service
        $this->inscriptionService->setInscription($this->inscription);
    }

    /**
     * @dataProvider confirmationDataProvider
     *
     * @covers \App\Service\Service\InscriptionService::ajoutPanier
     *
     * @param mixed      $typeFormat            - Type de format a ajouter au panier
     * @param mixed      $autorisations         - Liste des autorisations au format "type"=>boleen (permettant de savoir si une autorisation doit etre ajoute a l'utilisateur)
     * @param null|mixed $etatInscriptionFormat - Etat de l'inscription au format si l'utilisateur doit avoir une inscription
     * @param mixed      $confirmation          - Parametre pour l'ajout au panier
     * @param mixed      $enregistrerEnBD       - Booleen permettant de savoir si les donnees doivent etre enregistrees ou non
     * @param mixed      $expectedResultat      - Liste de resultats attendus type des lignes de commande detail
     * @param mixed      $etatInscriptionFormat
     */
    public function testAjoutPanier($typeFormat, $autorisations, $etatInscriptionFormat, $confirmation, $enregistrerEnBD, $expectedResultat): void
    {
        $this->createInscription($typeFormat, $autorisations, $etatInscriptionFormat);
        $resultat = $this->inscriptionService->ajoutPanier($confirmation);

        $this->assertCount(count($expectedResultat), $resultat);
        $this->assertEquals($this->em->contains($this->inscription), $enregistrerEnBD);
        for ($index = 0; $index < count($expectedResultat); ++$index) {
            $commandeDetail = $resultat[$index];
            $this->assertInstanceOf(CommandeDetail::class, $commandeDetail);
            $this->assertEquals($commandeDetail->getType(), $expectedResultat[$index]);
            $this->assertEquals($this->em->contains($commandeDetail), $enregistrerEnBD);
        }
    }

    /**
     * Data provider pour le testEnvoyerMailInscriptionNecessitantValidation.
     */
    public function envoyerMailInscriptionNecessitantValidationDataProvider()
    {
        return [
            ['attentevalidationencadrant', [[['Subject', 'eq', '[UCA] Inscription'], ['To', 'eq', 'test@test.com']], [['Subject', 'eq', '[UCA] Demande d\'inscription'], ['To', 'eq', 'test_encadrant@test.com']]]],
            ['attentevalidationgestionnaire', [[['Subject', 'eq', '[UCA] Inscription'], ['To', 'eq', 'test@test.com']], [['Subject', 'eq', '[UCA] Demande d\'inscription'], ['To', 'like', 'test_gestionnaire@test.com']]]],
            ['attenteajoutpanier', [[['Subject', 'eq', '[UCA] Demande d\'inscription validée'], ['To', 'eq', 'test@test.com']]]],
            ['annule', [[['Subject', 'eq', '[UCA] Demande d\'inscription refusée'], ['To', 'eq', 'test@test.com']]]],
            ['valide', []],
        ];
    }

    /**
     * @dataProvider envoyerMailInscriptionNecessitantValidationDataProvider
     *
     * @covers \App\Service\Service\InscriptionService::envoyerMailInscriptionNecessitantValidation
     *
     * @param mixed $statut
     * @param mixed $resultatAttendu
     */
    public function testEnvoyerMailInscriptionNecessitantValidation($statut, $resultatAttendu)
    {
        $this->formatSimple->addEncadrant($this->utilisateurEncadrant);
        $this->inscription = new Inscription($this->formatSimple, $this->utilisateur, ['format' => null]);
        $this->inscription->setstatut($statut);
        $this->inscriptionService->setInscription($this->inscription);

        $this->inscriptionService->envoyerMailInscriptionNecessitantValidation();
        $this->assertEmailCount(count($resultatAttendu));

        for ($index = 0; $index < count($resultatAttendu); ++$index) {
            $email = $this->getMailerMessage($index);
            $resultatMail = $resultatAttendu[$index];
            foreach ($resultatMail as $res) {
                $headerName = $res[0];
                $type = $res[1];
                $headerExpectedValue = $res[2];

                $this->assertEmailHasHeader($email, $headerName);

                switch ($type) {
                    case 'eq':
                        $this->assertEmailHeaderSame($email, $headerName, $headerExpectedValue);

                        break;

                    case 'like':
                        $headerValue = $email->getHeaders()->get($headerName)->getBodyAsString();
                        $this->assertMatchesRegularExpression('/'.preg_quote($headerExpectedValue).'/', $headerValue);

                        break;
                }
            }
        }
    }

    /**
     * @covers \App\Service\Service\InscriptionService::mailDesinscription
     */
    public function testMailDesinscription()
    {
        $this->formatSimple->addEncadrant($this->utilisateurEncadrant);
        $this->inscription = new Inscription($this->formatSimple, $this->utilisateur, ['format' => null]);
        $this->inscriptionService->setInscription($this->inscription);

        $this->inscriptionService->mailDesinscription();
        $this->assertEmailCount(1);

        $email = $this->getMailerMessage(0);
        $this->assertEmailHeaderSame($email, 'Subject', '[UCA] Désinscription');
        $this->assertEmailHeaderSame($email, 'To', 'test@test.com');
    }

    /**
     * @covers \App\Service\Service\InscriptionService::setPartenaires
     */
    public function testSetPartenaires()
    {
        $this->inscription = new Inscription($this->reservabilite, $this->utilisateur, ['format' => $this->formatAvecReservation]);
        $this->inscriptionService->setInscription($this->inscription);

        $this->inscriptionService->setPartenaires(['user-1@test.fr', 'user-2@test.fr']);
        $this->assertEmailCount(2);

        $email = $this->getMailerMessage(0);
        $this->assertEmailHeaderSame($email, 'Subject', '[UCA] Inscription avec partenaire');
        $this->assertEmailHeaderSame($email, 'To', 'user-1@test.fr');
        $email = $this->getMailerMessage(1);
        $this->assertEmailHeaderSame($email, 'Subject', '[UCA] Inscription avec partenaire');
        $this->assertEmailHeaderSame($email, 'To', 'user-2@test.fr');
    }

    /**
     * @covers \App\Service\Service\InscriptionService::cloneInscription
     */
    public function testCloneInscription()
    {
        $profilUtilisateur = (new ProfilUtilisateur())
            ->setLibelle('test')
            ->setNbMaxInscriptions(1)
            ->setPreinscription(false)
            ->setNbMaxInscriptionsRessource(1)
        ;
        $user1 = (new Utilisateur())
            ->setPrenom('user number 1')
            ->setNom('name 1')
            ->setUsername('pseudo')
            ->setEmail('test_1@test.com')
            ->setPassword('password')
            ->setProfil($profilUtilisateur)
        ;
        $user2 = (new Utilisateur())
            ->setPrenom('user number_2')
            ->setNom('name number_2')
            ->setUsername('pseudo')
            ->setEmail('test_number_2@test.com')
            ->setPassword('password')
            ->setProfil($profilUtilisateur)
        ;

        $inscriptionReservabilite = new Inscription($this->reservabilite, $user1, ['format' => $this->formatAvecReservation]);
        $inscriptionFormat = new Inscription($this->formatAvecReservation, $user1, ['format' => null]);

        $commande = new Commande($user1);
        $commandeArticle = new CommandeDetail($commande, 'inscription', $inscriptionReservabilite);
        $commandeDetailFormat = new CommandeDetail($commande, 'format', $inscriptionFormat, $commandeArticle);
        $commande->addCommandeDetail($commandeArticle);
        $commande->addCommandeDetail($commandeDetailFormat);
        $commande->changeStatut('valide');
        $user1->addCommande($commande);

        $this->em->persist($user1);
        $this->em->persist($user2);
        $this->em->persist($this->typeActivite);
        $this->em->persist($this->classeActivite);
        $this->em->persist($this->activite);
        $this->em->persist($this->etablissement);
        $this->em->persist($this->ressource);
        $this->em->persist($this->reservabilite);
        $this->em->persist($this->formatAvecReservation);
        $this->em->persist($this->eventForReservabilite);
        $this->em->persist($inscriptionReservabilite);
        $this->em->persist($inscriptionFormat);
        $this->em->persist($commande);
        $this->em->persist($commandeArticle);
        $this->em->persist($commandeDetailFormat);
        $this->em->flush();

        $this->inscriptionService->setInscription($inscriptionReservabilite);
        $this->inscriptionService->cloneInscription($inscriptionReservabilite, $user2);

        $inscriptionUser2 = $this->em->getRepository(Inscription::class)->findBy(
            [],
            ['id' => 'desc'],
            1,
            0
        )[0];
        $this->assertEquals($inscriptionReservabilite->getId(), $inscriptionUser2->getEstPartenaire());
        $this->assertEquals('attentepaiement', $inscriptionUser2->getStatut());
        $this->assertEquals('name number_2', $inscriptionUser2->getNomInscrit());
        $this->assertEquals('user number_2', $inscriptionUser2->getPrenomInscrit());
        $commandeUser2 = $this->em->getRepository(CommandeDetail::class)->findByInscription($inscriptionUser2->getId())[0]->getCommande();
        $this->assertEquals('panier', $commandeUser2->getStatut());
        $this->assertEquals($user2->getId(), $commandeUser2->getUtilisateur()->getId());
        $commandeDetails = $this->em->getRepository(CommandeDetail::class)->findByCommande($commandeUser2);
        $this->assertCount(1, $commandeDetails);

        $this->em->remove($user1);
        $this->em->remove($user2);
        $this->em->remove($this->typeActivite);
        $this->em->remove($this->classeActivite);
        $this->em->remove($this->activite);
        $this->em->remove($this->etablissement);
        $this->em->remove($this->ressource);
        $this->em->remove($this->reservabilite);
        $this->em->remove($this->formatAvecReservation);
        $this->em->remove($this->eventForReservabilite);
        $this->em->remove($inscriptionReservabilite);
        $this->em->remove($inscriptionFormat);
        $this->em->remove($commande);
        $this->em->remove($commandeArticle);
        $this->em->remove($commandeDetailFormat);

        $this->em->remove($inscriptionUser2);
        $this->em->remove($commandeUser2);
        foreach ($commandeDetails as $cmdDetails) {
            $this->em->remove($cmdDetails);
        }

        $this->em->remove($profilUtilisateur);
        $this->em->flush();
    }

    /**
     * @covers \App\Service\Service\InscriptionService::updateStatutInscriptionsPartenaire
     */
    public function testUpdateStatutInscriptionsPartenaireAvecListePartenaires()
    {
        $profilUtilisateur = (new ProfilUtilisateur())
            ->setLibelle('test')
            ->setNbMaxInscriptions(1)
            ->setPreinscription(false)
            ->setNbMaxInscriptionsRessource(1)
        ;
        $user1 = (new Utilisateur())
            ->setPrenom('user number 1')
            ->setNom('name 1')
            ->setUsername('pseudo')
            ->setEmail('test_1@test.com')
            ->setPassword('password')
            ->setProfil($profilUtilisateur)
        ;
        $user2 = (new Utilisateur())
            ->setPrenom('user number_2')
            ->setNom('name number_2')
            ->setUsername('pseudo')
            ->setEmail('test_number_2@test.com')
            ->setPassword('password')
            ->setProfil($profilUtilisateur)
        ;

        $inscriptionReservabilite = new Inscription($this->reservabilite, $user1, ['format' => $this->formatAvecReservation]);
        $inscriptionReservabilite->setListeEmailPartenaires('test_number_2@test.com');
        $inscriptionFormat = new Inscription($this->formatAvecReservation, $user1, ['format' => null]);

        $commande = new Commande($user1);
        $commandeArticle = new CommandeDetail($commande, 'inscription', $inscriptionReservabilite);
        $commandeDetailFormat = new CommandeDetail($commande, 'format', $inscriptionFormat, $commandeArticle);
        $commande->addCommandeDetail($commandeArticle);
        $commande->addCommandeDetail($commandeDetailFormat);
        $commande->changeStatut('valide');
        $user1->addCommande($commande);

        $this->em->persist($user1);
        $this->em->persist($user2);
        $this->em->persist($this->typeActivite);
        $this->em->persist($this->classeActivite);
        $this->em->persist($this->activite);
        $this->em->persist($this->etablissement);
        $this->em->persist($this->ressource);
        $this->em->persist($this->reservabilite);
        $this->em->persist($this->formatAvecReservation);
        $this->em->persist($this->eventForReservabilite);
        $this->em->persist($inscriptionReservabilite);
        $this->em->persist($inscriptionFormat);
        $this->em->persist($commande);
        $this->em->persist($commandeArticle);
        $this->em->persist($commandeDetailFormat);
        $this->em->flush();

        $this->inscriptionService->setInscription($inscriptionReservabilite);
        $this->inscriptionService->cloneInscription($inscriptionReservabilite, $user2);

        $inscriptionUser2 = $this->em->getRepository(Inscription::class)->findBy(
            [],
            ['id' => 'desc'],
            1,
            0
        )[0];

        $this->inscriptionService->updateStatutInscriptionsPartenaire($inscriptionReservabilite);
        $commandeUser2 = $this->em->getRepository(CommandeDetail::class)->findByInscription($inscriptionUser2->getId())[0]->getCommande();
        $this->assertEquals('annule', $commandeUser2->getStatut());
        $this->assertNotNull($commandeUser2->getDateAnnulation());
        $this->assertEquals('annulationpartenaire', $inscriptionUser2->getMotifAnnulation());
        $this->assertEquals('annule', $inscriptionUser2->getStatut());

        $this->assertEmailCount(1);
        $email = $this->getMailerMessage(0);
        $this->assertEmailHeaderSame($email, 'Subject', '[UCA] Désinscription partenaire');
        $this->assertEmailHeaderSame($email, 'To', 'test_number_2@test.com');

        $commandeDetails = $this->em->getRepository(CommandeDetail::class)->findByCommande($commandeUser2);

        $this->em->remove($user1);
        $this->em->remove($user2);
        $this->em->remove($this->typeActivite);
        $this->em->remove($this->classeActivite);
        $this->em->remove($this->activite);
        $this->em->remove($this->etablissement);
        $this->em->remove($this->ressource);
        $this->em->remove($this->reservabilite);
        $this->em->remove($this->formatAvecReservation);
        $this->em->remove($this->eventForReservabilite);
        $this->em->remove($inscriptionReservabilite);
        $this->em->remove($inscriptionFormat);
        $this->em->remove($commande);
        $this->em->remove($commandeArticle);
        $this->em->remove($commandeDetailFormat);

        $this->em->remove($inscriptionUser2);
        $this->em->remove($commandeUser2);
        foreach ($commandeDetails as $cmdDetails) {
            $this->em->remove($cmdDetails);
        }

        $this->em->remove($profilUtilisateur);
        $this->em->flush();
    }

    /**
     * @covers \App\Service\Service\InscriptionService::updateStatutInscriptionsPartenaire
     */
    public function testUpdateStatutInscriptionsPartenaireAvecPartenaireParent()
    {
        $profilUtilisateur = (new ProfilUtilisateur())
            ->setLibelle('test')
            ->setNbMaxInscriptions(1)
            ->setPreinscription(false)
            ->setNbMaxInscriptionsRessource(1)
        ;
        $user1 = (new Utilisateur())
            ->setPrenom('user number 1')
            ->setNom('name 1')
            ->setUsername('pseudo')
            ->setEmail('test_1@test.com')
            ->setPassword('password')
            ->setProfil($profilUtilisateur)
        ;
        $user2 = (new Utilisateur())
            ->setPrenom('user number_2')
            ->setNom('name number_2')
            ->setUsername('pseudo')
            ->setEmail('test_number_2@test.com')
            ->setPassword('password')
            ->setProfil($profilUtilisateur)
        ;

        $inscriptionReservabilite = new Inscription($this->reservabilite, $user1, ['format' => $this->formatAvecReservation]);
        $inscriptionReservabilite->setListeEmailPartenaires('test_number_2@test.com');
        $inscriptionReservabilite->setStatut('valide');
        $inscriptionFormat = new Inscription($this->formatAvecReservation, $user1, ['format' => null]);

        $commande = new Commande($user1);
        $commandeArticle = new CommandeDetail($commande, 'inscription', $inscriptionReservabilite);
        $commandeDetailFormat = new CommandeDetail($commande, 'format', $inscriptionFormat, $commandeArticle);
        $commande->addCommandeDetail($commandeArticle);
        $commande->addCommandeDetail($commandeDetailFormat);
        $commande->changeStatut('valide');
        $user1->addCommande($commande);

        $this->em->persist($user1);
        $this->em->persist($user2);
        $this->em->persist($this->typeActivite);
        $this->em->persist($this->classeActivite);
        $this->em->persist($this->activite);
        $this->em->persist($this->etablissement);
        $this->em->persist($this->ressource);
        $this->em->persist($this->reservabilite);
        $this->em->persist($this->formatAvecReservation);
        $this->em->persist($this->eventForReservabilite);
        $this->em->persist($inscriptionReservabilite);
        $this->em->persist($inscriptionFormat);
        $this->em->persist($commande);
        $this->em->persist($commandeArticle);
        $this->em->persist($commandeDetailFormat);
        $this->em->flush();

        $this->inscriptionService->setInscription($inscriptionReservabilite);
        $this->inscriptionService->cloneInscription($inscriptionReservabilite, $user2);

        $inscriptionUser2 = $this->em->getRepository(Inscription::class)->findBy(
            [],
            ['id' => 'desc'],
            1,
            0
        )[0];
        $commandeUser2 = $this->em->getRepository(CommandeDetail::class)->findByInscription($inscriptionUser2->getId())[0]->getCommande();
        $commandeDetails = $this->em->getRepository(CommandeDetail::class)->findByCommande($commandeUser2);

        $this->inscriptionService->updateStatutInscriptionsPartenaire($inscriptionUser2);
        $this->assertEquals('attentepartenaire', $inscriptionReservabilite->getStatut());

        $this->em->remove($user1);
        $this->em->remove($user2);
        $this->em->remove($this->typeActivite);
        $this->em->remove($this->classeActivite);
        $this->em->remove($this->activite);
        $this->em->remove($this->etablissement);
        $this->em->remove($this->ressource);
        $this->em->remove($this->reservabilite);
        $this->em->remove($this->formatAvecReservation);
        $this->em->remove($this->eventForReservabilite);
        $this->em->remove($inscriptionReservabilite);
        $this->em->remove($inscriptionFormat);
        $this->em->remove($commande);
        $this->em->remove($commandeArticle);
        $this->em->remove($commandeDetailFormat);

        $this->em->remove($inscriptionUser2);
        $this->em->remove($commandeUser2);
        foreach ($commandeDetails as $cmdDetails) {
            $this->em->remove($cmdDetails);
        }

        $this->em->remove($profilUtilisateur);
        $this->em->flush();
    }
}
