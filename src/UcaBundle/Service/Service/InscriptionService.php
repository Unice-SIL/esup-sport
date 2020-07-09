<?php

namespace UcaBundle\Service\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use UcaBundle\Entity\CommandeDetail;
use UcaBundle\Entity\Inscription;
use UcaBundle\Repository\EntityRepository;
use UcaBundle\Service\Common\MailService;

class InscriptionService
{
    private $em;
    private $twig;
    private $user;
    private $template;
    private $inscription;
    private $mailer;

    public function __construct(EntityManagerInterface $em, EngineInterface $twig, TokenStorageInterface $tokenStorage, MailService $mailer)
    {
        $this->em = $em;
        $this->twig = $twig;
        $this->user = $tokenStorage->getToken()->getUser();
        $this->mailer = $mailer;
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
        $html = $this->twig->render('@Uca/UcaWeb/Inscription/Modal.Formulaire.html.twig', $twigConfig);

        return ['itemId' => $this->inscription->getItem()->getId(), 'statut' => '1', 'html' => $html];
    }

    public function getMessagePreInscription()
    {
        $twigConfig['item'] = $this->inscription;
        $maxCreneauAtteint = $this->user->nbCreneauMaximumAtteint();
        $html = $this->twig->render('@Uca/UcaWeb/Inscription/Modal.ValidationInscription.html.twig', $twigConfig);

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
        if (in_array($this->inscription->getItemType(), ['UcaBundle:Creneau', 'UcaBundle:Reservabilite'])) {
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
            $article = $this->em->getRepository('UcaBundle:CommandeDetail')->findOneBy(['commande' => $panier, 'typeAutorisation' => $autorisation->getTypeAutorisation()]);
            if (!empty($article)) {
                $article->addLigneCommandeReference($articleInscription);
                $articleInscription->addLigneCommandeLiee($article);
            } else {
                $article = new CommandeDetail($panier, 'autorisation', $autorisation->getTypeAutorisation(), $articleInscription);
            }
            array_push($articles, $article);
        }

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
        $html = $this->twig->render('@Uca/UcaWeb/Inscription/Modal.ValidationPanier.html.twig', $twigConfig);

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
                '@Uca/Email/Inscription/InscriptionAvecValidation.html.twig',
                ['inscription' => $this->inscription]
            );

            $destinataires = [];
            if ('attentevalidationencadrant' == $this->inscription->getStatut()) {
                $listUser = $this->inscription->getItem()->getEncadrants();
                foreach ($listUser as $user) {
                    $destinataires[$user->getEmail()] = ucfirst($user->getPrenom()).' '.ucfirst($user->getNom());
                }
            } elseif ('attentevalidationgestionnaire' == $this->inscription->getStatut()) {
                $listUser = $this->em->getRepository('UcaBundle:Utilisateur')->findByRole('ROLE_GESTIONNAIRE_VALIDEUR_INSCRIPTION');
                foreach ($listUser as $user) {
                    $destinataires[$user['email']] = ucfirst($user['prenom']).' '.ucfirst($user['nom']);
                }
            }

            $this->mailer->sendMailWithTemplate(
                'Demande d\'inscription',
                $destinataires,
                '@Uca/Email/Inscription/InscriptionDemandeValidation.html.twig',
                ['inscription' => $this->inscription]
            );
        } elseif ('attenteajoutpanier' == $this->inscription->getStatut()) {
            $this->mailer->sendMailWithTemplate(
                'Demande d\'inscription validée',
                $this->inscription->getUtilisateur()->getEmail(),
                '@Uca/Email/Inscription/InscriptionValidee.html.twig',
                ['inscription' => $this->inscription]
            );
        } elseif ('annule' == $this->inscription->getStatut()) {
            $this->mailer->sendMailWithTemplate(
                'Demande d\'inscription refusée',
                $this->inscription->getUtilisateur()->getEmail(),
                '@Uca/Email/Inscription/InscriptionRefusee.html.twig',
                ['inscription' => $this->inscription]
            );
        }
    }

    public function mailDesinscription()
    {
        $this->mailer->sendMailWithTemplate(
            'Désinscription',
            $this->inscription->getUtilisateur()->getEmail(),
            '@Uca/Email/Inscription/Desinscription.html.twig',
            ['inscription' => $this->inscription]
        );
    }
}
