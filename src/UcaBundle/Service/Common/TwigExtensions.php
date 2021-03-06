<?php

/*
 * classe - TwigExtensions
 *
 * Service d'extention de twig
*/

namespace UcaBundle\Service\Common;

use Twig\TwigTest;
use Twig\TwigFilter;
use Twig\TwigFunction;
use UcaBundle\Entity\Lieu;
use UcaBundle\Entity\Commande;
use UcaBundle\Entity\FormatSimple;
use UcaBundle\Entity\Etablissement;
use UcaBundle\Entity\Reservabilite;
use UcaBundle\Entity\CommandeDetail;
use UcaBundle\Entity\FormatActivite;
use Twig\Extension\AbstractExtension;
use UcaBundle\Entity\FormatAchatCarte;
use UcaBundle\Entity\FormatAvecCreneau;
use UcaBundle\Entity\FormatAvecReservation;

class TwigExtensions extends AbstractExtension
{
    public function __construct(\Doctrine\ORM\EntityManagerInterface $em, \Symfony\Component\HttpFoundation\RequestStack $requestStack)
    {
        $this->em = $em;
        $this->requestStack = $requestStack;
    }

    public function getFunctions()
    {
        return [
            new TwigFunction('emplacement', [$this, 'getTexte']),
            new TwigFunction('emplacementImageFond', [$this, 'getImageFond']),
            new TwigFunction('isPrevisualisation', [$this, 'isPrevisualisation']),
            new TwigFunction('parametrage', [$this, 'getParametrage']),
            new TwigFunction('serverPathToWeb', [$this, 'serverPathToWeb']),
            new TwigFunction('urlRetourPrevisualisation', [$this, 'urlRetourPrevisualisation']),
            new TwigFunction('isValideAutorisation', [$this, 'getValiditeAutorisation']),
            new TwigFunction('isCarte', [$this, 'isCarte']),
            new TwigFunction('getInformationCarte', [$this, 'getInformationCarte']),
            new TwigFunction('var_dump', [$this, 'varDump']),
            new TwigFunction('getInformationCarteByCommandeId', [$this, 'getInformationCarteByCommandeId']),
            new TwigFunction('getAdresseComplete', [$this, 'getAdresseComplete']),
            new TwigFunction('instanceOf', [$this, 'instanceOf'])
        ];
    }

    public function getFilters()
    {
        return [
            new TwigFilter('dateFormat', [$this, 'dateFormat']),
            new TwigFilter('telephone', [$this, 'telephoneFilter']),
        ];
    }

    public function getTests()
    {
        return [
            new TwigTest('formatType', [$this, 'isFormatType'])
        ];
    }

    /**
     * Fonction qui cr??er un filtre pour twig afin de mettre en forme un num??ro de t??l??phone.
     *
     * @param [type] $numeroTelephone
     */
    public function telephoneFilter($numeroTelephone)
    {
        if (null == $numeroTelephone || 0 == strlen($numeroTelephone)) {
            return $numeroTelephone;
        }

        $indexSeparateurInPhoneNumber = 2;
        if (12 == strlen($numeroTelephone)) {
            $indexSeparateurInPhoneNumber = 4;
        }

        return substr($numeroTelephone, 0, $indexSeparateurInPhoneNumber).' '
            .substr($numeroTelephone, $indexSeparateurInPhoneNumber, 2).' '
            .substr($numeroTelephone, $indexSeparateurInPhoneNumber + 2, 2).' '
            .substr($numeroTelephone, $indexSeparateurInPhoneNumber + 4, 2).' '
            .substr($numeroTelephone, $indexSeparateurInPhoneNumber + 6, 2).' '
            .substr($numeroTelephone, $indexSeparateurInPhoneNumber + 8, 2);
    }

    /**
     * Fonction qui permet de cr??er une fonction twig qui retourne un texte enregistr?? en base pour une cl?? donn??e.
     *
     * @param [type] $name
     * @param string $type
     */
    public function getTexte($name, $type = 'text')
    {
        $emplacement = $this->em->getRepository('UcaBundle:Texte')->findOneByEmplacement($name);
        if (is_null($emplacement)) {
            return '';
        }
        switch ($type) {
            case 'title':
                $text = $emplacement->getTitre();

                break;
            case 'mobile':
                $text = $emplacement->getMobile();

                break;
            case 'texte_mobile':
                $text = $emplacement->getTexteMobile();

                break;
            case 'text':
            default:
                $text = $emplacement->getTexte();

                break;
        }

        return $text;
    }

    /**
     * Fonction qui retourne une image de fond enregistr??e en base pour une cl?? donn??e.
     *
     * @param [type] $name
     */
    public function getImageFond($name)
    {
        return $this->em->getRepository('UcaBundle:ImageFond')->findOneByEmplacement($name);
    }

    /**
     * Fonction qui retourne le contenue de la table param??trage.
     */
    public function getParametrage()
    {
        return Parametrage::get();
    }

    /**
     * Fonction qui retourne une date au format souhait??.
     *
     * @param [type] $date
     * @param [type] $format
     */
    public function dateFormat($date, $format = null)
    {
        return Fn::intlDateFormat($date, $format);
    }

    /**
     * Fonction qui retourne un le boolean Is_Active du mode pr??visualisation.
     *
     * @return bool
     */
    public function isPrevisualisation()
    {
        return Previsualisation::$IS_ACTIVE;
    }

    /**
     * Fonction qui retourne le chemin.
     */
    public function serverPathToWeb()
    {
        $result = $_SERVER['DOCUMENT_ROOT'];
        if (isset($_SERVER['BASE'])) {
            $result .= $_SERVER['BASE'];
        }
        if ('/' != substr($result, -1)) {
            $result .= '/';
        }

        return $result;
    }

    /**
     * Fonction qui retourne l'url de retour de pr??visualisation.
     */
    public function urlRetourPrevisualisation()
    {
        return Previsualisation::$BACK_URL;
    }

    /**
     * Fonction qui retourne la validit?? d'une autorisation.
     *
     * @param [type] $creneau
     * @param [type] $utilisateur
     */
    public function getValiditeAutorisation($creneau, $utilisateur)
    {
        $autorisations = null;
        if (is_a($creneau, Reservabilite::class)) {
            if ($creneau->getFormatActivite()) {
                $autorisations = $creneau->getAutorisations();
            }
        } else {
            $autorisations = $creneau->getFormatActivite()->getAutorisations();
        }

        if ($autorisations) {
            foreach ($autorisations as $autorisation) {
                if ($utilisateur->getAutorisations()->contains($autorisation)) {
                    $commandeDetail = $this->em->getRepository(CommandeDetail::class)->findCommandeDetailWithAutorisationByUser($utilisateur->getId(), $autorisation->getId());
                    if ($commandeDetail) {
                        if (4 == $autorisation->getComportement()->getId() && $commandeDetail[0]->getDateCarteFinValidite() < $creneau->getSerie()->getEvenements()[0]->getDateDebut()) {
                            return [
                                'valid' => false,
                                'autorisation' => $autorisation->getLibelle(),
                            ];
                        }
                    }
                }
            }
        }

        return ['valid' => true];
    }

    /**
     * Fonction qui permet de savoir si une autorisation est une carte ou autre chose.
     *
     * @param [type] $cmdDetailId
     *
     * @return bool
     */
    public function isCarte($cmdDetailId)
    {
        $cmdDetail = $this->em->getRepository(CommandeDetail::class)->find($cmdDetailId);
        if ($cmdDetail->getTypeAutorisation()) {
            if ($cmdDetail->getTypeAutorisation()->getComportement()) {
                if (4 == $cmdDetail->getTypeAutorisation()->getComportement()->getId()) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Fonction qui permet de r??cup??rer les informations d'une autorisation.
     *
     * @param [type] $cmdDetailId
     */
    public function getInformationCarte($cmdDetailId)
    {
        $cmdDetail = $this->em->getRepository(CommandeDetail::class)->find($cmdDetailId);
        $texte = $cmdDetail->getTypeAutorisation()->getLibelle();
        $cmdDetail->getNumeroCarte() ? $texte .= ' -  N??'.$cmdDetail->getNumeroCarte() : null;

        return $texte;
    }

    /**
     * @param [type] $value
     * @return void
     */
    public function varDump($value): void
    {
        var_dump($value);
    }


    /**
     * Fonction qui permet de savoir si une commande avait une carte et si elle a ??t?? retir??e
     *
     * @param integer $idCommande
     * @return string
     */
    public function getInformationCarteByCommandeId(int $idCommande): string {
        $commande = $this->em->getReference(Commande::class, $idCommande);
        $retour = '';
        foreach($commande->getCommandeDetails() as $commandeDetail) {
            if ($commandeDetail->getTypeAutorisation() && $commandeDetail->getTypeAutorisation()->getComportement() && $commandeDetail->getTypeAutorisation()->getComportement()->getId() == 4) {
                if ($commandeDetail->getEtablissementRetraitCarte()) {
                    return 'common.oui';
                } else {
                    $retour = 'common.non';
                }
            }
        }

        return $retour;
    }

    /**
     * Fonction qui permet de r??cup??rer l'adresse compl??te d'un Lieu ou un Etablissement
     *
     * @param [type] $object
     * @return string
     */
    public function getAdresseComplete($object): string {
        if ($object instanceof Etablissement) {
            return $object->getAdresse().' '.$object->getCodePostal().' - '.$object->getVille();
        } elseif ($object instanceof Lieu && $object->getAdresse()) {
            return $object->getAdresse().' '.$object->getCodePostal().' - '.$object->getVille();
        } elseif ($object instanceof Lieu && ($etablissement = $object->getEtablissement()) !== null) {
            return $etablissement->getAdresse().' '.$etablissement->getCodePostal().' - '.$etablissement->getVille();
        }

        return '';
    }

    /**
     * Fonction twig qui ??tend le test instanceof de php
     *
     * @param [type] $object
     * @param string $instanceName
     * @return boolean
     */
    public function isFormatType(FormatActivite $formatActivite, string $type): bool {
        if ($type == 'FormatAvecCreneau') {
            return $formatActivite instanceof FormatAvecCreneau;
        } elseif ($type == 'FormatAvecReservation') {
            return $formatActivite instanceof FormatAvecReservation;
        } elseif ($type == 'FormatAchatCarte') {
            return $formatActivite instanceof FormatAchatCarte;
        } elseif ($type == 'FormatSimple') {
            return $formatActivite instanceof FormatSimple;
        }

        return false;
    }
}
