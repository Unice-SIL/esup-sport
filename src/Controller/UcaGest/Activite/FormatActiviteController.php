<?php

/*
 * Classe - FormatActiviteController
 *
 * Gestion du CRUD pour les formats d'activités
 * Gestion des trois formats
*/

namespace App\Controller\UcaGest\Activite;

use App\Entity\Uca\Activite;
use App\Entity\Uca\Appel;
use App\Entity\Uca\DhtmlxEvenement;
use App\Entity\Uca\FormatAchatCarte;
use App\Entity\Uca\FormatActivite;
use App\Entity\Uca\FormatActiviteNiveauSportif;
use App\Entity\Uca\FormatActiviteProfilUtilisateur;
use App\Entity\Uca\FormatAvecCreneau;
use App\Entity\Uca\FormatAvecReservation;
use App\Entity\Uca\FormatSimple;
use App\Entity\Uca\Utilisateur;
use App\Form\EvenementType;
use App\Form\PlanningMailType;
use App\Repository\NiveauSportifRepository;
use App\Repository\ProfilUtilisateurRepository;
use App\Service\Common\FlashBag;
use App\Service\Common\Tools;
use App\Service\Service\InscriptionService;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("UcaGest/Activite/{idActivite}/FormatActivite", requirements={"idActivite"="\d+"})
 * @Security("is_granted('ROLE_ADMIN')")
 */
class FormatActiviteController extends AbstractController
{
    /**
     * @Route("/Voir/{id}", name="UcaGest_FormatActiviteVoir", methods={"GET"})
     * @Isgranted("ROLE_GESTION_FORMAT_ACTIVITE_LECTURE")
     *
     * @param mixed $idActivite
     */
    public function voirAction(Request $request, $idActivite, FormatActivite $item)
    {
        $twigConfig['item'] = $item;
        $twigConfig['type'] = 'FormatActivite';
        $twigConfig['role'] = 'admin';
        $array_class = explode('\\', get_class($item));
        $twigConfig['format'] = end($array_class);

        if (($item instanceof FormatAvecCreneau) && $this->isGranted('ROLE_GESTION_FORMAT_ACTIVITE_ECRITURE')) {
            $twigConfig['Scheduler'] = true;
        } elseif ($item instanceof FormatSimple || $item instanceof FormatAchatCarte || $item instanceof FormatAvecReservation) {
            $twigConfig['Scheduler'] = false;
        } else {
            $twigConfig['Scheduler'] = false;
        }

        return $this->render('UcaBundle/UcaGest/Activite/FormatActivite/Voir.html.twig', $twigConfig);
    }

    public function redirectAfterCommit($form, $formatActivite)
    {
        if ($form->get('previsualiser')->isClicked()) {
            return $this->redirectToRoute('UcaWeb_FormatActiviteDetail', [
                'idCa' => $formatActivite->getActivite()->getClasseActivite()->getId(),
                'idA' => $formatActivite->getActivite()->getId(),
                'id' => $formatActivite->getId(),
                'previsualisation' => 'on',
                'urlRetourPrevisualisation' => $this->generateUrl('UcaGest_FormatActiviteModifier', [
                    'idActivite' => $formatActivite->getActivite()->getId(),
                    'id' => $formatActivite->getId(),
                    'previsualisation' => 'off',
                ]),
            ]);
        }

        return $this->redirectToRoute(
            'UcaGest_FormatActiviteVoir',
            [
                'idActivite' => $formatActivite->getActivite()->getId(),
                'id' => $formatActivite->getId(),
            ]
        );
    }

    /**
     * @Security("is_granted('ROLE_GESTION_FORMAT_ACTIVITE_ECRITURE') and is_granted('ROLE_GESTION_ACTIVITE_ECRITURE')")
     * @Route("/Ajouter", name="UcaGest_FormatActiviteAjouter")
     *
     * @param mixed $idActivite
     */
    public function ajouterAction(Request $request, $idActivite, FlashBag $flashBag, Tools $tools, EntityManagerInterface $em, ProfilUtilisateurRepository $profilUtilisateurRepository, NiveauSportifRepository $niveauSportifRepo)
    {
        $format = $request->get('format');
        if (FormatActivite::formatIsValid($format)) {
            $className = $tools->getClassName($format);
            $item = new $className();
            $item->setEstEncadre(false);
            $item->setEstPayant(false);
            $today = new \DateTime();
            $today->setTime(0, 0);
            $item->setDateDebutPublication($today);
            $item->setDateFinPublication($today);
            $item->setDateDebutEffective($today);
            $item->setDateFinEffective($today);
            $item->setDateDebutInscription($today);
            $item->setDateFinInscription($today);
            $activite = $em->getRepository(Activite::class)->find($idActivite);
            $item->setActivite($activite);
            $item->setImage($activite->getImage());
            $item->setLibelle($activite->getLibelle());
            // if (!$request->isMethod('POST')) {
            // Liste profils pour le collectionType
            $tousProfils = $profilUtilisateurRepository->findAll();
            foreach ($tousProfils as $profil) {
                $formatProfil = new FormatActiviteProfilUtilisateur($item, $profil, 0);
                $item->addProfilsUtilisateur($formatProfil);
            }
            $niveaux = $niveauSportifRepo->findAll();
            foreach ($niveaux as $niveau) {
                $formatNiveau = new FormatActiviteNiveauSportif($item, $niveau);
                $item->addNiveauxSportif($formatNiveau);
            }
            // }
            $typeClassName = $tools->getClassName($format, 'FormType');
        } else {
            throw new \Exception("Format <{$format}> d'activité non valide");
        }

        $form = $this->createForm($typeClassName, $item);
        $form->handleRequest($request);
        if ($request->isMethod('POST')) {
            $profilsExistants = [];
            $niveauxExistants = [];
            foreach ($form->getData()->getProfilsUtilisateurs() as $formatProfil) {
                $profilsExistants[] = $formatProfil->getProfilUtilisateur()->getLibelle();
            }
            foreach ($form->getData()->getNiveauxSportifs() as $formatNiveau) {
                $niveauxExistants[] = $formatNiveau->getNiveauSportif()->getLibelle();
            }
            $twigConfig['profilsExistants'] = $profilsExistants;
            $twigConfig['niveauxExistants'] = $niveauxExistants;

            if ($form->isValid()) {
                $item->verifieCoherenceDonnees();
                $em->persist($item);
                $em->flush();
                $flashBag->addActionFlashBag($item, 'Ajouter');

                return $this->redirectAfterCommit($form, $item);
            }
        }
        $twigConfig['FormatClassName'] = $className;
        $twigConfig['tousProfils'] = $tousProfils;
        $twigConfig['tousNiveaux'] = $niveaux;
        $twigConfig['item'] = $item;
        $twigConfig['form'] = $form->createView();

        return $this->render('UcaBundle/UcaGest/Activite/FormatActivite/Formulaire.html.twig', $twigConfig);
    }

    /**
     * @Route("/Modifier/{id}", name="UcaGest_FormatActiviteModifier", methods={"GET", "POST"})
     * @Isgranted("ROLE_GESTION_FORMAT_ACTIVITE_ECRITURE")
     *
     * @param mixed $idActivite
     */
    public function modifierAction(Request $request, $idActivite, FormatActivite $item, FlashBag $flashBag, Tools $tools, EntityManagerInterface $em, ProfilUtilisateurRepository $profilUtilisateurRepository, NiveauSportifRepository $niveauSportifRepository)
    {
        $item->updateListeProfils();
        $tousProfils = $profilUtilisateurRepository->findAll();
        $tousNiveaux = $niveauSportifRepository->findAll();
        $profilsExistants = explode(', ', $item->getListeProfils());
        $niveauxExistants = explode(', ', $item->getListeNiveauxSportifs());

        $className = get_class($item);
        $path = explode('\\', $className);
        $typeClassName = $tools->getClassName(array_pop($path), 'FormType');
        $form = $this->createForm($typeClassName, $item); // ['transformer' => null]); //, ['sub_class' => get_class($item)]);

        $form->handleRequest($request);
        if ($request->isMethod('POST')) {
            $profilsExistants = [];
            $niveauxExistants = [];
            $tabProfil = [];
            $tabNiveau = [];
            foreach ($form->getData()->getProfilsUtilisateurs() as $formatProfil) {
                $tabProfil[$formatProfil->getProfilUtilisateur()->getId()] = $formatProfil->getProfilUtilisateur()->getLibelle();
                ksort($tabProfil);
            }
            foreach ($tabProfil as $profil) {
                $profilsExistants[] = $profil;
            }
            foreach ($form->getData()->getNiveauxSportifs() as $formatNiveau) {
                $tabNiveau[$formatNiveau->getNiveauSportif()->getId()] = $formatNiveau->getNiveauSportif()->getLibelle();
                ksort($tabNiveau);
            }
            foreach ($tabNiveau as $niveau) {
                $niveauxExistants[] = $niveau;
            }

            if ($form->isValid()) {
                $item->verifieCoherenceDonnees();
                $em->flush();
                $flashBag->addActionFlashBag($item, 'Modifier');

                return $this->redirectAfterCommit($form, $item);
            }
        }
        $twigConfig['FormatClassName'] = $className;
        $twigConfig['item'] = $item;
        $twigConfig['profilsExistants'] = $profilsExistants;
        $twigConfig['niveauxExistants'] = $niveauxExistants;
        $twigConfig['tousProfils'] = $tousProfils;
        $twigConfig['tousNiveaux'] = $tousNiveaux;
        $twigConfig['form'] = $form->createView();

        return $this->render('UcaBundle/UcaGest/Activite/FormatActivite/Formulaire.html.twig', $twigConfig);
    }

    /**
     * @Security("is_granted('ROLE_GESTION_FORMAT_ACTIVITE_ECRITURE') and is_granted('ROLE_GESTION_ACTIVITE_ECRITURE')")
     * @Route("/Supprimer/{id}", name="UcaGest_FormatActiviteSupprimer")
     *
     * @param mixed $idActivite
     */
    public function supprimerAction(Request $request, $idActivite, FormatActivite $formatActivite, FlashBag $flashBag, InscriptionService $inscriptionService, EntityManagerInterface $em)
    {
        if (!$formatActivite->getInscriptions()->isEmpty()) {
            foreach ($formatActivite->getInscriptions() as $inscription) {
                if (in_array($inscription->getStatut(), ['valide'])) { // voir quels statuts doivent empêcher la suppression
                    $flashBag->addActionErrorFlashBag($formatActivite, 'Supprimer');

                    return $this->redirectToRoute('UcaGest_ActiviteVoir', ['id' => $idActivite]);
                }
                $inscriptionService->updateStatutInscriptionsPartenaire($inscription);
                $inscription->setStatut('ancienneinscription');
                $formatActivite->removeInscription($inscription);
                $inscription->setFormatActivite(null);
            }
        }
        if ($formatActivite instanceof FormatAvecCreneau && !$formatActivite->getCreneaux()->isEmpty()) {
            $flashBag->addActionErrorFlashBag($formatActivite, 'Supprimer');

            return $this->redirectToRoute('UcaGest_ActiviteVoir', ['id' => $idActivite]);
        }
        $em->remove($formatActivite);
        $em->flush();
        $flashBag->addActionFlashBag($formatActivite, 'Supprimer');

        return $this->redirectToRoute('UcaGest_ActiviteVoir', ['id' => $idActivite]);
    }

    /**
     * @Route("/{idFormat}/more/{id}",name="UcaGest_PlanningMore")
     * @Route("/{idFormat}/more/",name="UcaGest_PlanningMore_NoId")
     *
     * @param mixed $idActivite
     * @param mixed $idEvent
     * @param mixed $idFormat
     */
    public function voirPlusAction(Request $request, $idActivite, $idFormat, ?DhtmlxEvenement $dhtmlxEvenement, EntityManagerInterface $em)
    {
        if (null == $dhtmlxEvenement) {
            return $this->redirectToRoute('UcaGest_FormatActiviteVoir', [
                'idActivite' => $idActivite,
                'id' => $idFormat,
            ]);
        }

        $twigConfig = [];
        $inscriptions = [];
        $eventName = '';
        if (null != $dhtmlxEvenement->getSerie()) {
            if (null != $dhtmlxEvenement->getSerie()->getCreneau()) {
                $inscriptions = $dhtmlxEvenement->getSerie()->getCreneau()->getAllInscriptions();
                $eventName = $dhtmlxEvenement->getSerie()->getCreneau()->getFormatActivite()->getActivite()->getLibelle();

                if (!$this->getUser()->isEncadrantEvenement($dhtmlxEvenement)) {
                    if (!$this->isGranted('ROLE_GESTION_FORMAT_ACTIVITE_ECRITURE') && empty($em->getRepository(Inscription::class)->findBy(['creneau' => $dhtmlxEvenement->getSerie()->getCreneau(), 'utilisateur' => $this->getUser()->getId()]))) {
                        return $this->redirectToRoute('UcaWeb_MonPlanning');
                    }
                }
            }
        }
        if ($dhtmlxEvenement->getFormatSimple()) {
            $inscriptions = $dhtmlxEvenement->getFormatSimple()->getAllInscriptions();
            $eventName = $dhtmlxEvenement->getFormatSimple()->getActivite()->getLibelle();
        }

        if (null !== ($reservabilite = $dhtmlxEvenement->getReservabilite()) || (null !== $dhtmlxEvenement->getSerie() && null !== ($reservabilite = $dhtmlxEvenement->getSerie()->getReservabilite()))) {
            $eventName = $reservabilite->getRessource()->getLibelle();
            $inscriptions = $reservabilite->getInscriptions();
        }

        $destinataires = [];
        $existingAppel = $em->getRepository(Utilisateur::class)->findUtilisateurByEvenement($dhtmlxEvenement->getId());
        foreach ($inscriptions as $key => $inscription) {
            if (!in_array($inscription->getUtilisateur(), $existingAppel)) {
                $appel = new Appel();
                $appel->setUtilisateur($inscription->getUtilisateur());
                $appel->setDhtmlxEvenement($dhtmlxEvenement);
                $dhtmlxEvenement->addAppel($appel);
            }
            $user = $inscription->getUtilisateur();
            $key = ucfirst($user->getPrenom()).' '.ucfirst($user->getNom());
            $destinataires[$key] = $user->getEmail();
        }
        $form = $this->get('form.factory')->create(EvenementType::class, $dhtmlxEvenement);
        $formMail = $this->get('form.factory')->create(PlanningMailType::class, null, ['liste_destinataires' => $destinataires]);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->persist($dhtmlxEvenement);
            $em->flush();
        }
        $twigConfig['evenement'] = $dhtmlxEvenement;
        $twigConfig['eventName'] = $eventName;
        $twigConfig['isEncadrant'] = $this->getUser()->isEncadrantEvenement($dhtmlxEvenement);
        $twigConfig['inscriptions'] = $inscriptions;
        $twigConfig['form'] = $form->createView();
        $twigConfig['formMail'] = $formMail->createView();
        $twigConfig['item'] = $dhtmlxEvenement;

        return $this->render('UcaBundle/UcaWeb/Utilisateur/More.html.twig', $twigConfig);
    }
}
