<?php

namespace UcaBundle\Service\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use UcaBundle\Entity\Commande;
use UcaBundle\Entity\CommandeDetail;
use UcaBundle\Entity\Inscription;
use UcaBundle\Service\Common\MailService;
use UcaBundle\Service\Common\Previsualisation;

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

    public function controleDejaInscrit($item)
    {
        if ($this->user->hasInscription($item)) {
            $result['itemId'] = $item->getId();
            $result['statut'] = '-1';
            $result['html'] = $this->twig->render('@Uca/UcaWeb/Inscription/Modal.Error.html.twig', array("title" => "modal.error", "message" => "modal.error.dejainscrit"));
        } else {
            $result['statut'] = '0';
        }
        return $result;
    }

    public function controleMaxInscriptionCreneau($item, $type)
    {
        if ($type == "UcaBundle:Creneau" && $this->user->isMaxInscription()) {
            $result['itemId'] = $item->getId();
            $result['statut'] = '-1';
            $result['html'] = $this->twig->render('@Uca/UcaWeb/Inscription/Modal.Error.html.twig', array("title" => "modal.error", "message" => "modal.error.maxInscription"));
        } else {
            $result['statut'] = '0';
        }
        return $result;
    }

    public function controleMaxCapacite($item, $type){
        $result['statut'] = "0";
        if ($type == "UcaBundle:Creneau"){
            if ($item->isFull()) {
                $result['itemId'] = $item->getId();
                $result['statut'] = '-1';
                $result['html'] = $this->twig->render('@Uca/UcaWeb/Inscription/Modal.Error.html.twig', array("title" => "modal.error", "message" => "modal.error.maxcapacite"));
            }
        }

        return $result;    
    }

    public function controleDateInscription($item, $type){
        $result['statut'] = '0';

        if (!$item->dateInscriptionValid()) {
            $result['itemId'] = $this->inscription->getItem()->getId();
            $result['statut'] = '-1';
            $result['html'] = $this->twig->render('@Uca/UcaWeb/Inscription/Modal.Error.html.twig', array("title" => "modal.error", "message" => "modal.error.dateinscription"));
        }
        return $result;        
    }

    public function controlePrevisualisation(){
        $result['statut'] = '0';

        if(Previsualisation::$IS_ACTIVE){
            $result['statut'] = '-1';
            $result['html'] = $this->twig->render('@Uca/UcaWeb/Inscription/Modal.Error.html.twig', array("title" => "modal.error", "message" => "modal.error.previsualisation"));
        }
        
        return $result;
    }

    public function controleMontantItem($user){
        $result['statut'] = '0';

        if($this->inscription->getFormatActivite()->getArticleMontant($user) < 0){
            $result['statut'] = '-1';
            $result['html'] = $this->twig->render('@Uca/UcaWeb/Inscription/Modal.Error.html.twig', array("title" => "modal.error", "message" => "modal.error.montant"));
            return $result;            
        }
        return $result;

    }

    public function controleMontantAutorisations($user){
        $result['statut'] = '0';
        
        foreach($this->inscription->getAutorisations() as $key => $autorisation){
            if($autorisation->getTypeAutorisation()->getArticleMontant($user) < 0 ){
                $result['statut'] = '-1';
                $result['html'] = $this->twig->render('@Uca/UcaWeb/Inscription/Modal.Error.html.twig', array("title" => "modal.error", "message" => "modal.error.montant"));
                return $result;
            }
        }
        return $result;
    }
    
    public function getFormulaire($form)
    {
        $twigConfig['item'] = $this->inscription;
        $twigConfig['form'] = $form->createView();
        $html = $this->twig->render('@Uca/UcaWeb/Inscription/Modal.Formulaire.html.twig', $twigConfig);
        return ["itemId" => $this->inscription->getItem()->getId(), "statut" => "1", "html" => $html];
    }

    public function getMessagePreInscription()
    {
        $twigConfig['item'] = $this->inscription;
        $html = $this->twig->render('@Uca/UcaWeb/Inscription/Modal.ValidationInscription.html.twig', $twigConfig);
        return ["itemId" => $this->inscription->getItem()->getId(), "statut" => "0", "html" => $html];
    }

    public function ajoutPanier()
    {
        $panier = $this->inscription->getUtilisateur()->getPanier();
        $articles = [];
        $articleInscription = new CommandeDetail($panier, 'inscription', $this->inscription);
        array_push($articles, $articleInscription);
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

        $this->em->persist($this->inscription);
        $this->em->persist($panier);
        return $articles;
    }

    public function getComfirmationPanier($articles)
    {
        $twigConfig['articles'] = $articles;
        $html =  $this->twig->render('@Uca/UcaWeb/Inscription/Modal.ValidationPanier.html.twig', $twigConfig);
        return ["itemId" => $this->inscription->getItem()->getId(), "statut" => "0", "html" => $html];
    }

    public function envoyerMailInscriptionNecessitantValidation(){
        if (in_array($this->inscription->getStatut(), ['attentevalidationencadrant', 'attentevalidationgestionnaire'])) {
            $this->mailer->sendMailWithTemplate(
                'Inscription',
                $this->inscription->getUtilisateur()->getEmail(),
                '@Uca/Email/Inscription/InscriptionAvecValidation.html.twig',
                ['inscription' => $this->inscription]
            );

            $destinataires = array();
            if ($this->inscription->getStatut() == 'attentevalidationencadrant') {
                $listUser = $this->inscription->getItem()->getEncadrants();
                foreach ($listUser as $user) {
                    $destinataires[$user->getEmail()] = ucfirst($user->getPrenom()) . ' ' . ucfirst($user->getNom());
                }
            } else if($this->inscription->getStatut() == 'attentevalidationgestionnaire'){
                $listUser = $this->em->getRepository('UcaBundle:Utilisateur')->findByRole('ROLE_GESTIONNAIRE_VALIDEUR_INSCRIPTION');
                foreach ($listUser as $user) {
                    $destinataires[$user['email']] = ucfirst($user['prenom']) . ' ' . ucfirst($user['nom']);
                }
            }
            
            $this->mailer->sendMailWithTemplate(
                'Demande d\'inscription',
                $destinataires,
                '@Uca/Email/Inscription/InscriptionDemandeValidation.html.twig',
                ['inscription' => $this->inscription]
            );
        } else if($this->inscription->getStatut() == 'attenteajoutpanier'){
            $this->mailer->sendMailWithTemplate(
                'Demande d\'inscription validée',
                $this->inscription->getUtilisateur()->getEmail(),
                '@Uca/Email/Inscription/InscriptionValidee.html.twig',
                ['inscription' => $this->inscription]
            );
        } else if($this->inscription->getStatut() == 'annule'){
            $this->mailer->sendMailWithTemplate(
                'Demande d\'inscription refusée',
                $this->inscription->getUtilisateur()->getEmail(),
                '@Uca/Email/Inscription/InscriptionRefusee.html.twig',
                ['inscription' => $this->inscription]
            );
        }
    }
}
