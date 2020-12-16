<?php

/*
 * classe -  ExtractionInscription
 *
 * Service gérant la logique de l'extraction des inscriptions
*/

namespace UcaBundle\Service\Service;

use Doctrine\ORM\EntityManagerInterface;
use UcaBundle\Entity;
use UcaBundle\Entity\FormatAvecCreneau;
use UcaBundle\Service\Common\Fn;

class ExtractionInscriptionService
{
    private $em;
    private $default;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->default = ['traduction.tous' => 0];
    }

    public function getTypesActivite()
    {
        $choicesList = $this->default;
        foreach ($this->em->getRepository(Entity\TypeActivite::class)->findAll() as $typeActivite) {
            $choicesList[$typeActivite->getLibelle()] = $typeActivite->getId();
        }

        return ['choicesList' => $choicesList];
    }

    public function getClassesActivite($typesActivite = [])
    {
        $choicesList = $this->default;
        foreach ($this->em->getRepository(Entity\ClasseActivite::class)->findAll() as $classeActivite) {
            $choicesList[$classeActivite->getLibelle()] = $classeActivite->getId();
            $typesActivite[$classeActivite->getId()] = $classeActivite->getTypeACtivite()->getId();
        }

        return ['choicesList' => $choicesList, 'typeActivite' => $typesActivite];
    }

    public function getActivite($classesActivite = [], $typesActivite = [])
    {
        $choicesList = $this->default;
        foreach ($this->em->getRepository(Entity\Activite::class)->findAll() as $activite) {
            $choicesList[$activite->getLibelle()] = $activite->getId();
            $classesActivite[$activite->getId()] = $activite->getClasseActivite()->getId();
            $typesActivite[$activite->getId()] = $activite->getClasseActivite()->getTypeACtivite()->getId();
        }

        return ['choicesList' => $choicesList, 'classeActivite' => $classesActivite, 'typeActivite' => $typesActivite];
    }

    public function getFormatActivite($activite = [], $hasCreneau = [])
    {
        $choicesList = $this->default;
        foreach ($this->em->getRepository(Entity\FormatActivite::class)->findAll() as $formatActivite) {
            $choicesList[$formatActivite->getLibelle()] = $formatActivite->getId();
            $activite[$formatActivite->getId()] = $formatActivite->getActivite()->getId();
            $hasCreneau[$formatActivite->getId()] = ($formatActivite instanceof FormatAvecCreneau) ? 'true' : 'false';
        }

        return ['choicesList' => $choicesList, 'activite' => $activite, 'hasCreneau' => $hasCreneau];
    }

    public function getCreneau($formatActivite = [])
    {
        $choicesList = $this->default;
        foreach ($this->em->getRepository(Entity\FormatActivite::class)->findAll() as $format) {
            if ($format instanceof FormatAvecCreneau) {
                $choicesList[$format->getLibelle()] = 'allCreneaux_'.$format->getId();
            }
        }

        foreach ($this->em->getRepository(Entity\Creneau::class)->findCreneauBySerie() as $data) {
            $libelle = $data['libelle']
                .' ['.Fn::intlDateFormat($data['dateDebut'], 'cccc')
                .' '.$data['dateDebut']->format('H:i')
                .' - '.$data['dateFin']->format('H:i').']';
            $choicesList[$libelle] = $data['serieId'];
            $formatActivite[$data['serieId']] = $data['formatId'];
        }

        return ['choicesList' => $choicesList, 'formatActivite' => $formatActivite];
    }

    public function getEntcadrant()
    {
        $choicesList = $this->default;
        foreach ($this->em->getRepository(Entity\Groupe::class)->findByLibelle('Encadrant')[0]->getUtilisateurs() as $encadrant) {
            $choicesList[$encadrant->getPrenom().' '.$encadrant->getNom()] = $encadrant->getId();
        }

        return ['choicesList' => $choicesList];
    }

    public function getEtablissement()
    {
        $choicesList = $this->default;
        foreach ($this->em->getRepository(Entity\Etablissement::class)->findAll() as $etablissement) {
            $choicesList[$etablissement->getLibelle()] = $etablissement->getId();
        }

        return ['choicesList' => $choicesList];
    }

    public function getLieu($etablissement = [])
    {
        $choicesList = $this->default;
        foreach ($this->em->getRepository(Entity\Ressource::class)->findAllLieu() as $lieu) {
            $choicesList[$lieu->getLibelle()] = $lieu->getId();
            $etablissement[$lieu->getId()] = $lieu->getEtablissement()->getId();
        }

        return ['choicesList' => $choicesList, 'etablissement' => $etablissement];
    }

    public function getOptionsInscription()
    {
        return [
            'typeActivite' => $this->getTypesActivite(),
            'classeActivite' => $this->getClassesActivite(),
            'listeActivite' => $this->getActivite(),
            'listeFormatActivite' => $this->getFormatActivite(),
            'listeCreneau' => $this->getCreneau(),
            'listeEncadrant' => $this->getEntcadrant(),
            'listeEtablissement' => $this->getEtablissement(),
            'listeLieu' => $this->getLieu(),
        ];
    }
}
