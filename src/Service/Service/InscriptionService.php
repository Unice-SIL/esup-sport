<?php

/*
 * classe - Inscription
 *
 * Service gérant la logique des inscriptions
*/

namespace App\Service\Service;

use App\Entity\Uca\Commande;
use App\Entity\Uca\CommandeDetail;
use App\Entity\Uca\Inscription;
use App\Entity\Uca\Utilisateur;
use App\Repository\CommandeDetailRepository;
use App\Repository\EntityRepository;
use App\Repository\UtilisateurRepository;
use App\Service\Common\MailService;
use App\Service\Common\Parametrage;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Security;
use Twig\Environment;

class InscriptionService
{
    private $em;
    private $twig;
    private $user;
    private $inscription;
    private $mailer;
    private $utilisateurRepository;
    private $request;
    private $router;

    /**
     * @codeCoverageIgnore
     */
    public function __construct(EntityManagerInterface $em, Environment $twig, Security $security, MailService $mailer, 
    UtilisateurRepository $utilisateurRepository, RequestStack $request, RouterInterface $router)
    {
        $this->em = $em;
        $this->twig = $twig;
        $this->user = $security->getUser();
        $this->mailer = $mailer;
        $this->utilisateurRepository = $utilisateurRepository;
        $this->request = $request;
        $this->router = $router;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setInscription(Inscription $inscription)
    {
        $this->inscription = $inscription;
    }

    public function getFormulaire($form)
    {
        $twigConfig['item'] = $this->inscription;
        $twigConfig['form'] = $form->createView();
        $html = $this->twig->render('UcaBundle/UcaWeb/Inscription/Modal.Formulaire.html.twig', $twigConfig);

        return ['itemId' => $this->inscription->getItem()->getId(), 'statut' => '1', 'html' => $html];
    }

    public function getMessagePreInscription()
    {
        $twigConfig['item'] = $this->inscription;
        $maxCreneauAtteint = $this->user->nbCreneauMaximumAtteint();
        $html = $this->twig->render('UcaBundle/UcaWeb/Inscription/Modal.ValidationInscription.html.twig', $twigConfig);

        return [
            'itemId' => $this->inscription->getItem()->getId(),
            'statut' => '0',
            'html' => $html,
            'maxCreneauAtteint' => $maxCreneauAtteint,
        ];
    }

    public function ajoutPanier($confirmation = false)
    {
        $user = $this->inscription->getUtilisateur();
        $panier = $user->getPanier();
        $item = $this->inscription->getItem();
        $articles = [];
        $articleInscription = new CommandeDetail($panier, 'inscription', $this->inscription);
        array_push($articles, $articleInscription);
        if (in_array($this->inscription->getItemType(), ['Creneau', 'Reservabilite'])) {
            $inscriptionFormat = $user->getInscriptionsByCriteria([
                ['formatActivite', 'eq', $item->getFormatActivite()],
                ['creneau', 'eq', null],
                ['reservabilite', 'eq', null],
                ['statut', 'notIn', ['annule', 'desinscrit', 'ancienneinscription', 'desinscriptionadministrative']],
            ])->first();
            if (empty($inscriptionFormat)) {
                $inscriptionFormat = new Inscription($item->getFormatActivite(), $user, ['typeInscription' => 'format']);
                $articleInscriptionFormat = false;
                $inscriptionFormatValide = false;
            } elseif ('valide' != $inscriptionFormat->getStatut()) {
                $articleInscriptionFormat = $panier->getCommandeDetails()->matching(EntityRepository::criteriaBy([['inscription', 'eq', $inscriptionFormat]]))->first();
                $inscriptionFormatValide = false;
            } else {
                $inscriptionFormatValide = true;
            }
            if (!$inscriptionFormatValide) {
                if (!empty($articleInscriptionFormat)) {
                    $articleInscriptionFormat->addLigneCommandeReference($articleInscription);
                    $articleInscription->addLigneCommandeLiee($articleInscriptionFormat);
                } else {
                    $articleInscriptionFormat = new CommandeDetail($panier, 'format', $inscriptionFormat, $articleInscription);
                    array_push($articles, $articleInscriptionFormat);
                }
            }
        }
        foreach ($this->inscription->getAutorisationsByComportement(['carte', 'cotisation'], 'invalide')->getIterator() as $autorisation) {
            $article = $panier->getCommandeDetails()->matching(EntityRepository::criteriaBy([['typeAutorisation', 'eq', $autorisation->getTypeAutorisation()]]))->first();
            if (!empty($article)) {
                $article->addLigneCommandeReference($articleInscription);
                $articleInscription->addLigneCommandeLiee($article);
            } else {
                $article = new CommandeDetail($panier, 'autorisation', $autorisation->getTypeAutorisation(), $articleInscription);
            }
            array_push($articles, $article);
        }
        $panier->changeStatut('panier');

        if (!$confirmation) {
            $this->em->persist($this->inscription);
            $this->em->persist($panier);
        }

        return $articles;
    }

    public function getConfirmationPanier($articles)
    {
        $maxCreneauAtteint = $this->user->nbCreneauMaximumAtteint();
        $twigConfig['articles'] = $articles;
        $html = $this->twig->render('UcaBundle/UcaWeb/Inscription/Modal.ValidationPanier.html.twig', $twigConfig);

        return [
            'itemId' => $this->inscription->getItem()->getId(),
            'statut' => '0',
            'html' => $html,
            'maxCreneauAtteint' => $maxCreneauAtteint,
        ];
    }

    public function envoyerMailInscriptionNecessitantValidation()
    {
        if (in_array($this->inscription->getStatut(), ['attentevalidationencadrant', 'attentevalidationgestionnaire'])) {
            $listeEncadrants = '';
            if($this->inscription->getStatut() == 'attentevalidationencadrant'){
                foreach($this->inscription->getItem()->getEncadrants() as $encadrant){
                    $listeEncadrants .= '</br><span>'.
                    $encadrant->getPrenom().' '.$encadrant->getNom().' '.($encadrant->getEmail())
                    .'</span>';
                }
            }
            $this->mailer->sendMailWithTemplate(
                null,
                $this->inscription->getUtilisateur()->getEmail(),
                'InscriptionAvecValidation',
                ['inscription' => $this->twig->render('UcaBundle/Datatables/Column/InscriptionDataColumn.html.twig', ['row' => $this->inscription]), 
                'date' => $this->inscription->getDate()->format(date("d/m/Y à H:i")), 'listeEncadrants' => $listeEncadrants]
            );

            $destinataires = [];
            if ('attentevalidationencadrant' == $this->inscription->getStatut()) {
                $statut = 'qu\'encadrant.';
                $listUser = $this->inscription->getItem()->getEncadrants();
                foreach ($listUser as $user) {
                    $destinataires[] = $user->getEmail();
                }
            } elseif ('attentevalidationgestionnaire' == $this->inscription->getStatut()) {
                $statut = 'que gestionnaire.';
                $listUser = $this->utilisateurRepository->findByRole('ROLE_GESTIONNAIRE_VALIDEUR_INSCRIPTION');
                foreach ($listUser as $user) {
                    $destinataires[] = $user['email'];
                }
            }
            $lienInscription = $this->router->generate('UcaWeb_InscriptionAValiderVoir', ['id' => $this->inscription->getId()]);
            $this->mailer->sendMailWithTemplate(
                null,
                $destinataires,
                'InscriptionDemandeValidation',
                ['date' => $this->inscription->getDate()->format(date("d/m/Y à H:i")), 'inscription' => $this->twig->render('UcaBundle/Datatables/Column/InscriptionDataColumn.html.twig', ['row' => $this->inscription]),
                'prenom' => $this->inscription->getUtilisateur()->getPrenom(),'nom' => $this->inscription->getUtilisateur()->getNom(),'mail' => $this->inscription->getUtilisateur()->getEmail(),
                'lienInscription' => $this->request->getCurrentRequest()->getSchemeAndHttpHost().$lienInscription,
                'statut' => $statut]
            );
        } elseif ('attenteajoutpanier' == $this->inscription->getStatut()) {
            $lienMesInscriptions = $this->router->generate('UcaWeb_MesInscriptions');
            $this->mailer->sendMailWithTemplate(
                null,
                $this->inscription->getUtilisateur()->getEmail(),
                'InscriptionValidee',
                ['date' => $this->inscription->getDate()->format(date("d/m/Y à H:i")), 'inscription' => $this->twig->render('UcaBundle/Datatables/Column/InscriptionDataColumn.html.twig', ['row' => $this->inscription]),
                'lienInscription' => $this->request->getCurrentRequest()->getSchemeAndHttpHost().$lienMesInscriptions,
                'timerPanierApresValidation' => Parametrage::getTimerPanierApresValidation() ,'timerPanier' => Parametrage::getTimerPanier()]
            );
        } elseif ('annule' == $this->inscription->getStatut()) {
            $this->mailer->sendMailWithTemplate(
                null,
                $this->inscription->getUtilisateur()->getEmail(),
                'InscriptionRefusee',
                ['date' => $this->inscription->getDate()->format(date("d/m/Y à H:i")), 'inscription' => $this->twig->render('UcaBundle/Datatables/Column/InscriptionDataColumn.html.twig', ['row' => $this->inscription]),
                'commentaireAnnulation' => $this->inscription->getCommentaireAnnulation() , 'motifAnnulation' => $this->inscription->getMotifAnnulation()]
            );
        }
    }

    public function mailDesinscription()
    {
        $this->mailer->sendMailWithTemplate(
            null,
            $this->inscription->getUtilisateur()->getEmail(),
            'Desinscription',
            ['inscription' => $this->twig->render('UcaBundle/Datatables/Column/InscriptionDataColumn.html.twig', ['row' => $this->inscription])]
        );
    }

    public function setPartenaires($partenaires): void
    {
        if (null !== $partenaires && sizeof($partenaires) > 0) {
            $utilisateurRepository = $this->em->getRepository(Utilisateur::class);
            $this->inscription->setListeEmailPartenaires(implode('|', $partenaires));
            foreach ($partenaires as $partenaire) {
                $utilisateur = $utilisateurRepository->findOneByEmail($partenaire);
                $this->mailer->sendMailWithTemplate(
                    null,
                    $partenaire,
                    'InscriptionPartenaire',
                    [
                    'inscription' => $this->twig->render('UcaBundle/Datatables/Column/InscriptionDataColumn.html.twig', ['row' => $this->inscription]),
                    'prenom' => $this->inscription->getUtilisateur()->getPrenom(), 'nom' => $this->inscription->getUtilisateur()->getNom(),
                    'formatActivite' => $this->inscription->getFormatActivite()->getLibelle(), 'dateDebut' => $this->inscription->getReservabilite()->getEvenement()->getDateDebut()->format(date("H:i")), 
                    'dateFin' => $this->inscription->getReservabilite()->getEvenement()->getDateFin()->format(date("H:i")),
                    'etablissement' => $this->inscription->getReservabilite()->getRessource()->getEtablissementLibelle(), 'ressource' => $this->inscription->getReservabilite()->getRessource()->getLibelle(), 
                    'evenement' => $this->inscription->getReservabilite()->getEvenement()->getDateDebut()->format(date("d/m/Y")),
                    'lienInscription' => $this->request->getCurrentRequest()->getSchemeAndHttpHost().$this->router->generate('UcaWeb_InscriptionAvecPartenaire', ['id' => $this->inscription->getId()])]
                );
            }
            $this->em->flush();
        }
    }

    /**
     * Fonction qui permet de cloner une inscription
     * Utiliser dans le cadre des inscriptions avec partenaires.
     */
    public function cloneInscription(Inscription $inscription, Utilisateur $utilisateur): void
    {
        $newInscription = clone $inscription;
        $newInscription->setUtilisateur($utilisateur)
            ->setEstPartenaire($inscription->getId())
            ->setStatut('attentepaiement')
            ->setNomInscrit($utilisateur->getNom())
            ->setPrenomInscrit($utilisateur->getPrenom())
            ->setListeEmailPartenaires(null)
            ->setDate(new DateTime())
        ;

        $commandeDetails = $this->em->getRepository(CommandeDetail::class)->findByInscription($inscription->getId());
        if ($commandeDetails) {
            $newCommande = clone $commandeDetails[0]->getCommande();
            $newCommande->setInscriptionAvecPartenaires(true)
                ->setStatut('panier')
                ->setUtilisateur($utilisateur)
                ->setNom($utilisateur->getNom())
                ->setPrenom($utilisateur->getPrenom())
                ->setDatePanier(new DateTime())
                ->setDateCommande(null)
                ->setDatePaiement(null)
                ->setMoyenPaiement(null)
                ->setTypePaiement(null)
                ->setNumeroCommande(null)
                ->setNumeroRecu(null)
                ->setUtilisateurEncaisseur(null)
                ->setPrenomEncaisseur(null)
                ->setNomEncaisseur(null)
                ->setCreditUtilise(0)
                ->setNumeroCheque(null)
            ;
            foreach ($commandeDetails as $commandeDetail) {
                $newCommandeDetail = clone $commandeDetail;
                $newCommandeDetail->setInscription($newInscription)
                    ->setCommande($newCommande)
                    ->setDateAjoutPanier(new DateTime())
                ;
                $newCommande->addCommandeDetail($newCommandeDetail);
                $this->em->persist($newCommandeDetail);
            }
            $this->em->persist($newCommande);
        }
        $this->em->persist($newInscription);

        $this->em->flush();
    }

    /**
     * Fonction qui permet de mettre à jour le statut des inscriptions partenaires dans le cas ou on supprimer/annule une commande/inscription.
     */
    public function updateStatutInscriptionsPartenaire(Inscription $inscription): void
    {
        // Si l'initiateur annule son inscription, toutes les inscriptions partenaires sont annulées
        if ($inscription->getListeEmailPartenaires()) {
            $commandePartenaires = $this->em->getRepository(Commande::class)->findAssociatedCommandesPartenaireByInscription($inscription->getId());
            foreach ($commandePartenaires as $commandePartenaire) {
                $commandePartenaire->changeStatut('annule', ['motifAnnulation' => 'annulationpartenaire', 'commentaireAnnulation' => null, 'em' => $this->em]);
                $this->mailer->sendMailWithTemplate(
                    null,
                    $commandePartenaire->getUtilisateur()->getEmail(),
                    'DesinscriptionPartenaire',
                    ['inscription' => $this->twig->render('UcaBundle/Datatables/Column/InscriptionDataColumn.html.twig', ['row' => $inscription])]
                );
            }
        } elseif ($inscription->getEstPartenaire()) { // sinon on remet les inscriptions au statut attentepartenaire
            $inscriptionsPartenaire = $this->em->getRepository(Inscription::class)->findAssociatedInscriptionsPartenaire($inscription);
            foreach ($inscriptionsPartenaire as $inscriptionPartenaire) {
                $inscriptionPartenaire->setStatut('attentepartenaire');
            }
        }
    }
}
