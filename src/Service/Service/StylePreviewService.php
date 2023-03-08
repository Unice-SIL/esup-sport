<?php

namespace App\Service\Service;

use App\Entity\Uca\Activite;
use App\Entity\Uca\ClasseActivite;
use App\Entity\Uca\Commande;
use App\Entity\Uca\CommandeDetail;
use App\Entity\Uca\ComportementAutorisation;
use App\Entity\Uca\Creneau;
use App\Entity\Uca\CreneauProfilUtilisateur;
use App\Entity\Uca\DhtmlxEvenement;
use App\Entity\Uca\DhtmlxSerie;
use App\Entity\Uca\Etablissement;
use App\Entity\Uca\FormatActiviteProfilUtilisateur;
use App\Entity\Uca\FormatActiviteNiveauSportif;
use App\Entity\Uca\FormatAvecCreneau;
use App\Entity\Uca\FormatSimple;
use App\Entity\Uca\Inscription;
use App\Entity\Uca\Lieu;
use App\Entity\Uca\MontantTarifProfilUtilisateur;
use App\Entity\Uca\NiveauSportif;
use App\Entity\Uca\ProfilUtilisateur;
use App\Entity\Uca\Tarif;
use App\Entity\Uca\TypeActivite;
use App\Entity\Uca\TypeAutorisation;
use App\Entity\Uca\Utilisateur;
use Doctrine\Common\Collections\Criteria;

/**
 * Service qui permet de gérer les données statique et fictive de la preview de style
 */

class StylePreviewService
{
    private $encadrant;

    private $niveauxSportif;

    private $tarif;

    private $lieu;

    private $comportementAutorisation;

    private $typeAutorisation;

    private $etablissements;

    private $baseEvent;

    private $typeActivite;

    private $classeActivite;

    private $activite;

    private $formatSimple;

    private $formatAvecCreneau;

    private $baseCreneau;

    private $series;

    private $articles;


    public function __construct()
    {
        $this->articles = [];

        $this->typeActivite = (new TypeActivite())
            ->setLibelle('Type Activite Test')
        ;

        $this->classeActivite = (new ClasseActivite())
            ->setLibelle('Classe Actvite Test')
            ->setTypeActiviteLibelle('Type Activite Test')
            ->setImage('test.jpg')
            ->setTypeActivite($this->typeActivite)
        ;

        $this->activite = (new Activite())
            ->setLibelle('Activite Test')
            ->setImage('test.jpg')
            ->setDescription('Description Activite Test')
            ->setClasseActiviteLibelle('Classe Activite Test')
            ->setClasseActivite($this->classeActivite)
        ;

        $this->encadrant = (new Utilisateur())
            ->setNom("Lorem")
            ->setPrenom("Ipsum")
        ;

        $this->niveauxSportif = [
            (new NiveauSportif())->setLibelle("Débutant"),
            (new NiveauSportif())->setLibelle("Intermédiaire"),
            (new NiveauSportif())->setLibelle("Expert"),
        ];

        $this->tarif = (new Tarif())
            ->setLibelle("Lorem Ipsum Price")
        ;

        $this->lieu = (new Lieu())
            ->setLibelle("Salle ATIMIC")
            ->setAdresse("55 Boulevard de Châteaudun")
            ->setCodePostal("45000")
            ->setVille("Orléans")
            ->setAccesPMR(true)
        ;

        $this->etablissements = [
            (new Etablissement())
                ->setLibelle('ATIMIC')
                ->setAdresse('55 Boulevard de Châteaudun')
                ->setCodePostal('45000')
                ->setVille('Orléans')
                ->setTelephone('0238427378')
                ->setEmail('contact@atimic.fr'),
            (new Etablissement())
                ->setLibelle('Acatus Informatique')
                ->setAdresse('55 Boulevard de Châteaudun')
                ->setCodePostal('45000')
                ->setVille('Orléans')
                ->setTelephone('0238427378')
                ->setEmail('acatus-info@acatus.fr')
        ];

        foreach ($this->etablissements as $etablissement) {
            $etablissement->setImage('test.jpg');
        }

        $this->comportementAutorisation = (new ComportementAutorisation())
            ->setLibelle("Carte Preview")
            ->setCodeComportement('carte')
        ;

        $this->typeAutorisation = (new TypeAutorisation())
            ->setLibelle("Carte Lorem Ipsum")
            ->setTarif($this->tarif)
            ->setComportement($this->comportementAutorisation)
        ;



        $this->baseEvent = (new DhtmlxEvenement())
            ->setDependanceSerie(true)
            ->setInformations('
            Lorem ipsum dolor sit amet, consectetur adipiscing elit. Curabitur eget feugiat neque. Curabitur eu magna nibh. Aenean consequat pretium cursus. Phasellus id blandit ligula. Mauris hendrerit gravida velit, id auctor enim posuere ut. Mauris quam odio, placerat vitae arcu eu, hendrerit efficitur mauris. Nulla eu auctor sem. Cras nec ante nec mi ornare sodales. Nam eu iaculis metus. Nullam maximus vulputate arcu, vitae pharetra sapien imperdiet a. Nunc arcu nisl, varius et libero ut, mollis varius felis. Vestibulum consectetur vulputate lacus, placerat convallis nulla volutpat sit amet. Vivamus ut quam consectetur, viverra magna ut, dictum sem. Nulla id suscipit nisi.')
            ->setDescription('Lorem Ipsum Event')
        ;

        $this->formatSimple = (new FormatSimple())
            ->setImage('test.jpg')
            ->setTarif($this->tarif)
            ->setActivite($this->activite)
            ->setCapacite(10)
            ->setLibelle('Event Lorem ipsum')
            ->setDescription('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse condimentum lacus et lorem rutrum, non efficitur purus pharetra. Donec vel ante fermentum, placerat odio at, tincidunt ex. Nam sit amet arcu elit. Phasellus quis nunc finibus, tempus ex at, vestibulum enim. Donec condimentum massa quis nunc pulvinar iaculis. Vivamus vitae congue augue. Fusce nec sapien non tortor consectetur lacinia vitae ac lorem. Cras mollis risus vel diam pharetra volutpat. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse condimentum lacus et lorem rutrum, non efficitur purus pharetra. Donec vel ante fermentum, placerat odio at, tincidunt ex. Nam sit amet arcu elit. Phasellus quis nunc finibus, tempus ex at, vestibulum enim. Donec condimentum massa quis nunc pulvinar iaculis. Vivamus vitae congue augue. Fusce nec sapien non tortor consectetur lacinia vitae ac lorem. Cras mollis risus vel diam pharetra volutpat.')
            ->setDateDebutEffective(new \DateTime())
            ->setDateFinEffective((new \DateTime())->add(new \DateInterval('P1D')))
            ->setDateDebutInscription(new \DateTime())
            ->setDateFinInscription((new \DateTime())->add(new \DateInterval('P1D')))
            ->setDateDebutPublication(new \DateTime())
            ->setDateFinPublication((new \DateTime())->add(new \DateInterval('P1D')))
            ->setEstEncadre(true)
            ->setContactEncadrant(true)
            ->addEncadrant($this->encadrant)
        ;

        $this->formatAvecCreneau = (new FormatAvecCreneau())
            ->setCapacite(10)
            ->setLibelle('Format Lorem ipsum')
            ->setDescription('FormatAvecCreneau Lorem ipsum')
            ->setDateDebutPublication(new \DateTime())
            ->setDateFinPublication((new \DateTime())->add(new \DateInterval('P14D')))
            ->setDateDebutInscription((new \DateTime())->add(new \DateInterval('P1D')))
            ->setDateFinInscription((new \DateTime())->add(new \DateInterval('P7D')))
            ->setDateDebutEffective((new \DateTime())->add(new \DateInterval('P5D')))
            ->setDateFinEffective((new \DateTime())->add(new \DateInterval('P1M1D')))
            ->setImage('test.jpg')
            ->setEstPayant(true)
            ->setTarif($this->tarif)
            ->setEstEncadre(true)
            ->setContactEncadrant(true)
            ->setActivite($this->activite)
            ->addEncadrant($this->encadrant)
            ->addAutorisation($this->typeAutorisation)
        ;
        $this->typeAutorisation->addFormatsActivite($this->formatAvecCreneau);

        foreach ($this->niveauxSportif as $niveauSportif) {
            $this->formatAvecCreneau->addNiveauxSportif(new FormatActiviteNiveauSportif($this->formatAvecCreneau, $niveauSportif, 'Lorem ipsum dolor sit ame'));
            $niveauSportif->addFormatActivite($this->formatAvecCreneau);
        }

        $this->baseCreneau = (new Creneau())
            ->setCapacite(10)
            ->setLieu($this->lieu)
            ->setFormatActivite($this->formatAvecCreneau)
            ->addEncadrant($this->encadrant)
            ->setTarif($this->tarif)
        ;

        $days = ['mon','tue','wed','thu','fri','sat','sun'];

        for ($d = 0; $d < 7; $d++) {
            $serie = $this->createSerie(
                (new \DateTime())->modify('first '.$days[$d].' of this month'),
                (new \DateTime())->modify('last '.$days[$d].' of this month')
            );
            $this->series[] = $serie;
            $this->formatAvecCreneau->addCreneaux($serie->getCreneau());
            for ($i = 1; $i <= 4; $i++) {
                $serie->addEvenement($this->createEvent(
                    $serie,
                    (clone $serie->getDateDebut())->setTime(12, 0)->sub(new \DateInterval('P'.$i.'W')),
                    (clone $serie->getDateDebut())->setTime(14, 0)->sub(new \DateInterval('P'.$i.'W')),
                ));
            }
            for ($i = 0; $i < 4; $i++) {
                $serie->addEvenement($this->createEvent(
                    $serie,
                    (clone $serie->getDateDebut())->setTime(12, 0)->add(new \DateInterval('P'.$i.'W')),
                    (clone $serie->getDateDebut())->setTime(14, 0)->add(new \DateInterval('P'.$i.'W')),
                ));
            }
        }
    }

    public function createSerie(\DateTimeInterface $dateDebut, \DateTimeInterface $dateFin): DhtmlxSerie
    {
        $serie = (new DhtmlxSerie())
            ->setCreneau(clone $this->baseCreneau)
            ->setDateDebut($dateDebut)
            ->setDateFin($dateFin)
        ;
        $serie->getCreneau()->setSerie($serie);
        foreach ($this->niveauxSportif as $niveauSportif) {
            $serie->getCreneau()->addNiveauxSportif($niveauSportif);
            $niveauSportif->addCreneaux($serie->getCreneau());
        }
        return $serie;
    }

    public function getArticles(): array
    {
        return $this->articles;
    }

    public function setUtilisateur(Utilisateur $user): self
    {
        $this->setProfilUtilisateur($user->getProfil());
        $commande = new Commande($user);
        $inscription = new Inscription($this->series[1]->getCreneau(), $user, []);
        $commandeDetailInscription = new CommandeDetail($commande, 'inscription', $inscription);
        $commandeDetailInscription->setMontant('12');
        $this->articles[] = $commandeDetailInscription;
        $inscription = new Inscription($this->formatSimple, $user, []);
        $commandeDetailInscription = new CommandeDetail($commande, 'inscription', $inscription);
        $commandeDetailInscription->setMontant('99');
        $this->articles[] = $commandeDetailInscription;
        return $this;
    }

    private function setProfilUtilisateur(ProfilUtilisateur $profil): void
    {
        $this->tarif->addMontant(new MontantTarifProfilUtilisateur($this->tarif, $profil, 10));
        $this->formatSimple->addProfilsUtilisateur(new FormatActiviteProfilUtilisateur($this->formatSimple, $profil, 10));
        $this->formatAvecCreneau->addProfilsUtilisateur(new FormatActiviteProfilUtilisateur($this->formatAvecCreneau, $profil, 10));
        foreach ($this->series as $serie) {
            $serie->getCreneau()->addProfilsUtilisateur(new CreneauProfilUtilisateur($serie->getCreneau(), $profil, 10));
        }
    }

    public function getEncadrant(): Utilisateur
    {
        return $this->encadrant;
    }

    public function getNiveauxSportifs(): array
    {
        return $this->niveauxSportif;
    }

    public function getTarif(): Tarif
    {
        return $this->tarif;
    }

    public function getLieu(): Lieu
    {
        return $this->lieu;
    }

    public function getComportementAutorisation(): ComportementAutorisation
    {
        return $this->comportementAutorisation;
    }

    public function getTypeAutorisation(): TypeAutorisation
    {
        return  $this->typeAutorisation;
    }

    public function getTypeActivite(): TypeActivite
    {
        return $this->typeActivite;
    }

    public function getClasseActivite(): ClasseActivite
    {
        return $this->classeActivite;
    }

    public function getActivite(): Activite
    {
        return $this->activite;
    }

    public function getEtablissements(): array
    {
        return $this->etablissements;
    }

    public function createEvent(DhtmlxSerie $serie, \DateTimeInterface $debut, \DateTimeInterface $fin, bool $ff = false, bool $bonus = false): DhtmlxEvenement
    {
        $ret = clone $this->baseEvent;
        $ret
            ->setSerie($serie)
            ->setForteFrequence($ff)
            ->setEligibleBonus($bonus)
            ->setDateDebut($debut)
            ->setDateFin($fin)
        ;
        $ret->setId($ret->getDateDebut()->getTimestamp());
        return $ret;
    }

    public function getEvent(int $id): ?DhtmlxEvenement
    {
        $events = [];
        foreach ($this->series as $serie) {
            $events = array_merge($events, $serie->getEvenements()->matching(Criteria::create()->where(Criteria::expr()->eq('id', $id)))->toArray());
        }
        return $events[0] ?? null;
    }

    public function getEvents(\DateTimeInterface $dateDebut, \DateTimeInterface $dateFin, object $item)
    {
        $ret = [];
        $creneaux = $item->getCreneaux();
        foreach ($creneaux as $creneau) {
            $ret = array_merge($ret, $creneau->getSerieEvenements()->matching(Criteria::create()->where(Criteria::expr()->gte('dateFin', $dateDebut))->andWhere(Criteria::expr()->lte('dateDebut', $dateFin))->orderBy(['dateDebut' => Criteria::ASC]))->toArray());
        }
        return $ret;
    }

    public function getFormatSimple(): FormatSimple
    {
        return $this->formatSimple;
    }

    public function getFormatAvecCreneau(): FormatAvecCreneau
    {
        return $this->formatAvecCreneau;
    }

    public function getSeries(): array
    {
        return $this->series;
    }
}
