<?php

namespace UcaBundle\Controller\Api;

use UcaBundle\Entity\Creneau;
use UcaBundle\Entity\DhtmlxDate;
use UcaBundle\Entity\DhtmlxEvenement;
use UcaBundle\Entity\DhtmlxSerie;
use UcaBundle\Entity\FormatActivite;
use UcaBundle\Entity\ProfilUtilisateur;
use UcaBundle\Entity\Reservabilite;
use UcaBundle\Entity\Ressource;
use UcaBundle\Entity\Tarif;
use UcaBundle\Entity\Utilisateur;
use UcaBundle\Entity\NiveauSportif;
use UcaBundle\Entity\Lieu;

class DhtmlxCommand
{
    private $em;
    private $data;
    private $action;
    private $item;
    private $commands;

    public function __construct($em, $data, $parent = null)
    {
        $this->em = $em;
        $this->data = $data;
        $this->commands = [];
        if (in_array($data['action'], ['insert', 'update', 'delete'])) {
            $this->action = $data['action'];
        } else {
            throw new \Exception("L'action " . $data['action'] . " n'est pas valide !");
        }
        $this->initItemAndCommands($parent);
    }

    public function getItem()
    {
        return $this->item;
    }

    public function execute()
    {

        if ($this->action == 'delete') {
            $this->em->remove($this->item);
        } else {
            $this->item->setDateDebut(new \DateTime($this->data['dateDebut']));
            $this->item->setDateFin(new \DateTime($this->data['dateFin']));
            if (get_class($this->item) == 'UcaBundle\Entity\DhtmlxEvenement') {
                $this->item->setDescription($this->data['text']);
                $this->item->setDependanceSerie(isset($this->data['dependanceSerie']) && $this->data['dependanceSerie'] == 'true');
            } elseif (get_class($this->item) == 'UcaBundle\Entity\DhtmlxSerie') {
                if (isset($this->data['recurrence'])) {
                    $this->recurrence = $this->data['recurrence'];
                }
                $this->dateFinSerie = new \DateTime($this->data['dateFinSerie']);
            }
            if ($this->isCreneauEvent() && isset($this->data['capacite'])) {
                $this->getReferenceItem()->setCapacite($this->data['capacite']);
            }
            $this->updateTarif();
            $this->updateLieu();
            $this->updateProfils();
            $this->updateNiveauSportif();
            $this->updateEncadrants();
            $this->em->persist($this->item);
        }
        foreach ($this->commands as $command) {
            $command->execute();
        }
    }

    public function getResult()
    {
        $res = $this->item->jsonSerialize();
        if ($this->action == 'delete') {
            $res['id'] = null;
        }
        if (!empty($res['evenements'])) {
            $res['enfants'] = $res['evenements'];
        }
        return $res;
    }

    private function initItemAndCommands($parent)
    {
        if ($this->action == 'insert' && isset($this->data['enfants'])) {
            $this->item = new DhtmlxSerie($this->data);
            $this->createReference();
        } elseif ($this->action == 'insert' && !isset($this->data['enfants'])) {
            $this->item = new DhtmlxEvenement($this->data);
            if (!empty($parent)) {
                $this->item->setSerie($parent->getItem());
                $parent->getItem()->addEvenement($this->item);
            }
            $this->createReference();
        } else {
            $this->item = $this->em->getRepository(DhtmlxDate::class)->find($this->data['id']);
        }

        if (isset($this->data['enfants'])) {
            foreach ($this->data['enfants'] as $enfant) {
                array_push($this->commands, new DhtmlxCommand($this->em, $enfant, $this));
            }
        }
        $this->item->oldId = $this->data['id'];
        $this->item->action = $this->data['action'];
    }

    private function isCreneauEvent()
    {
        return isset($this->data['enfants']) && (
            (isset($this->data['evenementType']) && $this->data['evenementType'] == 'creneau')
            || isset($this->data['creneau'])
            || (isset($this->data['reference_class']) && $this->data['reference_class'] == 'UcaBundle\Entity\FormatAvecCreneau'));
    }

    private function isRessourceEvent()
    {
        return !isset($this->data['enfants']) && (
            (isset($this->data['evenementType']) && $this->data['evenementType'] == 'ressource')
            || isset($this->data['reservabilite'])
            || (isset($this->data['reference_class']) && $this->data['reference_class'] == 'UcaBundle\Entity\Lieu')
            || (isset($this->data['reference_class']) && $this->data['reference_class'] == 'UcaBundle\Entity\Materiel'));
    }

    private function getReferenceItem()
    {
        if ($this->isCreneauEvent()) {
            return  $this->item->getCreneau();
        } elseif ($this->isRessourceEvent()) {
            return $this->item->getReservabilite();
        } else {
            return null;
        }
    }

    private function createReference()
    {
        if ($this->isCreneauEvent()) {
            $c = new Creneau();
            $c->setFormatActivite($this->em->getReference(FormatActivite::class, $this->data['reference_id']));
            $this->item->setCreneau($c);
        } elseif ($this->isRessourceEvent()) {
            $r = new Reservabilite();
            $r->setRessource($this->em->getReference(Ressource::class, $this->data['reference_id']));
            $this->item->setReservabilite($r);
        }
    }

    private function updateTarif()
    {
        $item = $this->getReferenceItem();
        if (!empty($item) && $this->isCreneauEvent() && isset($this->data['tarif_id'])) {
            $item->setTarif($this->em->getReference(Tarif::class, $this->data['tarif_id']));
            $this->em->persist($item);
        }
    }

    private function updateLieu()
    {
        $item = $this->getReferenceItem();
        if (!empty($item) && $this->isCreneauEvent() && isset($this->data['lieu_id'])) {
            $item->setLieu($this->em->getReference(Lieu::class, $this->data['lieu_id']));
            $this->em->persist($item);
        }
    }

    private function updateProfils()
    {
        $item = $this->getReferenceItem();
        if (!empty($item) && method_exists($item, 'getProfilsUtilisateurs')) {

            if ($item->getProfilsUtilisateurs() !== null) {
                foreach ($item->getProfilsUtilisateurs() as $key => $profil) {
                    $item->removeProfilsUtilisateur($profil);
                }
            }
            if (isset($this->data['profil_ids']) && $this->data['profil_ids'] !== '') {
                foreach (explode(',', $this->data['profil_ids']) as $key => $profil) {
                    $item->addProfilsUtilisateur($this->em->getReference(ProfilUtilisateur::class, $profil));
                }
            }
        }
    }

    private function updateNiveauSportif()
    {
        $item = $this->getReferenceItem();

        if (!empty($item) && $this->isCreneauEvent()) {

            if ($item->getNiveauxSportifs() !== null) {
                foreach ($item->getNiveauxSportifs() as $key => $niveau) {
                    $item->removeNiveauSportif($niveau);
                }
            }
            if (isset($this->data['niveau_sportif_ids']) && $this->data['niveau_sportif_ids'] !== '') {
                foreach (explode(',', $this->data['niveau_sportif_ids']) as $key => $niveau) {
                    $item->addNiveauSportif($this->em->getReference(NiveauSportif::class, $niveau));
                }
            }
        }
    }

    private function updateEncadrants()
    {
        $item = $this->getReferenceItem();
        if (!empty($item) && $this->isCreneauEvent()) {
            if ($item->getEncadrants() !== null) {
                foreach ($item->getEncadrants() as $key => $encadrant) {
                    $item->removeEncadrant($encadrant);
                }
            }
            if (isset($this->data['encadrant_ids']) && $this->data['encadrant_ids'] !== '') {
                foreach (explode(',', $this->data['encadrant_ids']) as $key => $encadrant) {
                    $item->addEncadrant($this->em->getReference(Utilisateur::class, $encadrant));
                }
            }
        }
    }
}
