<?php

/*
 * classe - Inscription
 *
 * Service gérant la logique des inscriptions
*/

namespace App\Service\Service;

use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Common\Collections\Criteria;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Entity\Uca\Commande;
use App\Entity\Uca\CommandeDetail;
use App\Entity\Uca\Inscription;
use App\Entity\Uca\Utilisateur;
use App\Repository\CommandeDetailRepository;
use App\Repository\EntityRepository;
use App\Repository\UtilisateurRepository;
use App\Service\Common\MailService;
use Twig\Environment;

class InscriptionService
{
    private $em;
    private $twig;
    private $user;
    private $template;
    private $inscription;
    private $mailer;
    private $utilisateurRepository;
    private $commandeDetailRepository;

    public function __construct(EntityManagerInterface $em, Environment $twig, TokenStorageInterface $tokenStorage, MailService $mailer, UtilisateurRepository $utilisateurRepository, CommandeDetailRepository $commandeDetailRepository)
    {
        $this->em = $em;
        $this->twig = $twig;
        $this->user = $tokenStorage->getToken()->getUser();
        $this->mailer = $mailer;
        $this->utilisateurRepository = $utilisateurRepository;
        $this->commandeDetailRepository = $commandeDetailRepository;
    }

    public function setTemplate($template)
    {
        $this->template = $template;
    }

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
            'maxCreneauAtteint' => $maxCreneauAtteint, ];
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
            $article = $this->commandeDetailRepository->findOneBy(['commande' => $panier, 'typeAutorisation' => $autorisation->getTypeAutorisation()]);
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

    public function getComfirmationPanier($articles)
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
            $this->mailer->sendMailWithTemplate(
                'Inscription',
                $this->inscription->getUtilisateur()->getEmail(),
                'UcaBundle/Email/Inscription/InscriptionAvecValidation.html.twig',
                ['inscription' => $this->inscription]
            );

            $destinataires = [];
            if ('attentevalidationencadrant' == $this->inscription->getStatut()) {
                $listUser = $this->inscription->getItem()->getEncadrants();
                foreach ($listUser as $user) {
                    $destinataires[$user->getEmail()] = ucfirst($user->getPrenom()).' '.ucfirst($user->getNom());
                }
            } elseif ('attentevalidationgestionnaire' == $this->inscription->getStatut()) {
                $listUser = $this->utilisateurRepository->findByRole('ROLE_GESTIONNAIRE_VALIDEUR_INSCRIPTION');
                foreach ($listUser as $user) {
                    $destinataires[$user['email']] = ucfirst($user['prenom']).' '.ucfirst($user['nom']);
                }
            }

            $this->mailer->sendMailWithTemplate(
                'Demande d\'inscription',
                $destinataires,
                'UcaBundle/Email/Inscription/InscriptionDemandeValidation.html.twig',
                ['inscription' => $this->inscription]
            );
        } elseif ('attenteajoutpanier' == $this->inscription->getStatut()) {
            $this->mailer->sendMailWithTemplate(
                'Demande d\'inscription validée',
                $this->inscription->getUtilisateur()->getEmail(),
                'UcaBundle/Email/Inscription/InscriptionValidee.html.twig',
                ['inscription' => $this->inscription]
            );
        } elseif ('annule' == $this->inscription->getStatut()) {
            $this->mailer->sendMailWithTemplate(
                'Demande d\'inscription refusée',
                $this->inscription->getUtilisateur()->getEmail(),
                'UcaBundle/Email/Inscription/InscriptionRefusee.html.twig',
                ['inscription' => $this->inscription]
            );
        }
    }

    public function mailDesinscription()
    {
        $this->mailer->sendMailWithTemplate(
            'Désinscription',
            $this->inscription->getUtilisateur()->getEmail(),
            'UcaBundle/Email/Inscription/Desinscription.html.twig',
            ['inscription' => $this->inscription]
        );
    }


    public function setPartenaires($partenaires): void {
        if ($partenaires !== null && sizeof($partenaires) > 0) {            
            $utilisateurRepository = $this->em->getRepository(Utilisateur::class);
            $this->inscription->setListeEmailPartenaires(implode('|', $partenaires));
            foreach ($partenaires as $partenaire) {
                $utilisateur = $utilisateurRepository->findOneByEmail($partenaire);
                $this->mailer->sendMailWithTemplate(
                    'Inscription avec partenaire',
                    $partenaire,
                    'UcaBundle/Email/Inscription/InscriptionPartenaire.html.twig',
                    ['inscription' => $this->inscription, 'utilisateur' => $utilisateur]
                );
            }
            $this->em->flush();
        }
    }

    /**
     * Fonction qui permet de cloner une inscription
     * Utiliser dans le cadre des inscriptions avec partenaires
     *
     * @param Inscription $inscription
     * @param Utilisateur $utilisateur
     * @return void
     */
    public function cloneInscription(Inscription $inscription, Utilisateur $utilisateur): void {
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
     * Fonction qui permet de mettre à jour le statut des inscriptions partenaires dans le cas ou on supprimer/annule une commande/inscription
     *
     * @param Inscription $inscription
     * @return void
     */
    public function updateStatutInscriptionsPartenaire(Inscription $inscription): void {
        // Si l'initiateur annule son inscription, toutes les inscriptions partenaires sont annulées
        if ($inscription->getListeEmailPartenaires()) {
            $commandePartenaires = $this->em->getRepository(Commande::class)->findAssociatedCommandesPartenaireByInscription($inscription->getId());
            foreach ($commandePartenaires as $commandePartenaire) {
                $commandePartenaire->changeStatut('annule', ['motifAnnulation' => 'annulationpartenaire', 'commentaireAnnulation' => null, 'em' => $this->em]);
                $this->mailer->sendMailWithTemplate(
                    'Désinscription partenaire',
                    $commandePartenaire->getUtilisateur()->getEmail(),
                    'UcaBundle/Email/Inscription/DesinscriptionPartenaire.html.twig',
                    ['commande' => $commandePartenaire, 'inscription' => $inscription]
                );
            }
        } elseif ($inscription->getEstPartenaire()) { //sinon on remet les inscriptions au statut attentepartenaire
            $inscriptionsPartenaire = $this->em->getRepository(Inscription::class)->findAssociatedInscriptionsPartenaire($inscription);
            foreach ($inscriptionsPartenaire as $inscriptionPartenaire) {
                $inscriptionPartenaire->setStatut('attentepartenaire');
            }
        }
    }
}
