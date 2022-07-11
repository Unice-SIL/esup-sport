<?php

/*
 * classe - TwigExtensions
 *
 * Service d'extention de twig
*/

namespace App\Service\Common;

use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;
use App\Entity\Uca\Lieu;
use App\Entity\Uca\Commande;
use App\Entity\Uca\FormatSimple;
use App\Entity\Uca\Etablissement;
use App\Entity\Uca\CommandeDetail;
use App\Entity\Uca\FormatAchatCarte;
use App\Entity\Uca\FormatActivite;
use App\Entity\Uca\FormatAvecCreneau;
use App\Entity\Uca\FormatAvecReservation;
use App\Entity\Uca\Reservabilite;
use App\Repository\ImageFondRepository;
use App\Repository\TexteRepository;
use Twig\Extension\AbstractExtension;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class TwigExtensions extends AbstractExtension
{
    private $texteRepository;
    private $imageFondRepository;

    public function __construct(EntityManagerInterface $em, RequestStack $requestStack, TexteRepository $texteRepository, ImageFondRepository $imageFondRepository)
    {
        $this->em = $em;
        $this->requestStack = $requestStack;
        $this->texteRepository = $texteRepository;
        $this->imageFondRepository = $imageFondRepository;
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
     * Fonction qui créer un filtre pour twig afin de mettre en forme un numéro de téléphone.
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
     * Fonction qui permet de créer une fonction twig qui retourne un texte enregistré en base pour une clé donnée.
     *
     * @param [type] $name
     * @param string $type
     */
    public function getTexte($name, $type = 'text')
    {
        $emplacement = $this->texteRepository->findByEmplacementName($name);
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
     * Fonction qui retourne une image de fond enregistrée en base pour une clé donnée.
     *
     * @param [type] $name
     */
    public function getImageFond($name)
    {
        return $this->imageFondRepository->findByEmplacementName($name);
    }

    /**
     * Fonction qui retourne le contenue de la table paramétrage.
     */
    public function getParametrage()
    {
        return Parametrage::get();
    }

    /**
     * Fonction qui retourne une date au format souhaité.
     *
     * @param [type] $date
     * @param [type] $format
     */
    public function dateFormat($date, $format = null)
    {
        return Fctn::intlDateFormat($date, $format);
    }

    /**
     * Fonction qui retourne un le boolean Is_Active du mode prévisualisation.
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
     * Fonction qui retourne l'url de retour de prévisualisation.
     */
    public function urlRetourPrevisualisation()
    {
        return Previsualisation::$BACK_URL;
    }

    /**
     * Fonction qui retourne la validité d'une autorisation.
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
     * Fonction qui permet de récupérer les informations d'une autorisation.
     *
     * @param [type] $cmdDetailId
     */
    public function getInformationCarte($cmdDetailId)
    {
        $cmdDetail = $this->em->getRepository(CommandeDetail::class)->find($cmdDetailId);
        $texte = $cmdDetail->getTypeAutorisation()->getLibelle();
        $cmdDetail->getNumeroCarte() ? $texte .= ' -  N°'.$cmdDetail->getNumeroCarte() : null;

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
     * Fonction qui permet de savoir si une commande avait une carte et si elle a été retirée
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
     * Fonction qui permet de récupérer l'adresse complète d'un Lieu ou un Etablissement
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
     * Fonction twig qui étend le test instanceof de php
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
