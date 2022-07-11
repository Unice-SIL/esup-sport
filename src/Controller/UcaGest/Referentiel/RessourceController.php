<?php

/*
 * Classe - RessourceController
 *
 * Gestion du CRUD pour les ressources
*/

namespace App\Controller\UcaGest\Referentiel;

use App\Datatables\RessourceDatatable;
use App\Entity\Uca\Etablissement;
use App\Entity\Uca\Fichier;
use App\Entity\Uca\Lieu;
use App\Entity\Uca\Materiel;
use App\Entity\Uca\ReferentielImmobilier;
use App\Entity\Uca\Ressource;
use App\Form\FichierType;
use App\Repository\ProfilUtilisateurRepository;
use App\Service\Common\FlashBag;
use App\Service\Common\Tools;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use League\Csv\Reader;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sg\DatatablesBundle\Datatable\DatatableFactory;
use Sg\DatatablesBundle\Response\DatatableResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("UcaGest/Ressource")
 * @Security("is_granted('ROLE_ADMIN')")
 */
class RessourceController extends AbstractController
{
    /**
     * @Route("/", name="UcaGest_RessourceLister")
     * @Isgranted("ROLE_GESTION_RESSOURCE_LECTURE")
     */
    public function listerAction(Request $request, DatatableFactory $datatableFactory, DatatableResponse $datatableResponse, FlashBag $flashBag, KernelInterface $kernel, EntityManagerInterface $em)
    {
        $referentiel = new Fichier($em);
        $form = $this->createForm(FichierType::class, $referentiel);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid() && $request->isMethod('POST')) {
            // on supprime le fichier référentiel existant
            $this->supprimerFichierImmobilier($em);
            // on force le code REFIMMO pour identifier qu'il s'agit du fichier référentiel immobilier
            $referentiel->setCode('REFIMMO');
            $em->persist($referentiel);
            $em->flush();
            // mise à jour du référentiel
            $this->majReferentielImmobilier($flashBag, $kernel, $em);

            return $this->redirectToRoute('UcaGest_RessourceLister');
        }

        $isAjax = $request->isXmlHttpRequest();
        $datatable = $datatableFactory->create(RessourceDatatable::class);
        $datatable->buildDatatable();
        $twigConfig['datatable'] = $datatable;

        if ($isAjax) {
            $responseService = $datatableResponse;
            $responseService->setDatatable($datatable);
            $dtQueryBuilder = $responseService->getDatatableQueryBuilder();
            $qb = $dtQueryBuilder->getQb();

            return $responseService->getResponse();
        }

        $twigConfig['form'] = $form->createView();
        $twigConfig['codeListe'] = 'Ressource';
        // Bouton Ajouter
        $usr = $this->getUser();
        if (!$usr->hasRole('ROLE_GESTION_RESSOURCE_ECRITURE')) {
            $twigConfig['noAddButton'] = true;
        }

        return $this->render('UcaBundle/UcaGest/Referentiel/Ressource/Datatable.html.twig', $twigConfig);
    }

    /**
     * @Route("/Ajouter", name="UcaGest_RessourceAjouter", methods={"GET", "POST"})
     * @Isgranted("ROLE_GESTION_RESSOURCE_ECRITURE")
     */
    public function ajouterAction(Request $request, FlashBag $flashBag, Tools $tools, ProfilUtilisateurRepository $profilUtilisateurRepository, EntityManagerInterface $em)
    {
        $format = $request->get('format');

        if (Ressource::formatIsValid($format)) {
            $className = $tools->getClassName($format);
            $typeClassName = $tools->getClassName($format, 'FormType');
            $item = (new $className())->setNbPartenaires(0)->setNbPartenairesMax(0);
        } else {
            throw new \Exception("Format <{$format}> d'activité non valide");
        }
        $tousProfils = $profilUtilisateurRepository->findAll();

        $form = $this->createForm($typeClassName, $item);
        $form->handleRequest($request);

        if ($request->isMethod('POST')) {
            $profilsExistants = [];
            foreach ($form->getData()->getProfilsUtilisateurs() as $formatProfil) {
                $profilsExistants[] = $formatProfil->getProfilUtilisateur()->getLibelle();
            }
            $twigConfig['profilsExistants'] = $profilsExistants;

            if ($form->isValid()) {
                $item->setSourceReferentiel(false);
                $em->persist($item);
                $em->flush();
                $flashBag->addActionFlashBag($item, 'Ajouter');

                return $this->redirectToRoute('UcaGest_RessourceLister');
            }
        }

        $twigConfig['item'] = $item;
        $twigConfig['tousProfils'] = $tousProfils;
        $twigConfig['addAction'] = true;
        $twigConfig['form'] = $form->createView();

        return $this->render('UcaBundle/UcaGest/Referentiel/Ressource/'.$format.'/Formulaire.html.twig', $twigConfig);
    }

    /**
     * @Route("/Supprimer/{id}", name="UcaGest_RessourceSupprimer")
     * @Isgranted("ROLE_GESTION_RESSOURCE_ECRITURE")
     */
    public function supprimerAction(Request $request, Ressource $item, FlashBag $flashBag, EntityManagerInterface $em)
    {
        if (!$item->getFormatResa()->isEmpty()) {
            $flashBag->addActionErrorFlashBag($item, 'Supprimer');

            return $this->redirectToRoute('UcaGest_RessourceLister');
        }
        $em->remove($item);
        $em->flush();
        $flashBag->addActionFlashBag($item, 'Supprimer');

        return $this->redirectToRoute('UcaGest_RessourceLister');
    }

    /**
     * @Route("/Modifier/{id}", name="UcaGest_RessourceModifier", methods={"GET", "POST"})
     * @Isgranted("ROLE_GESTION_RESSOURCE_ECRITURE")
     */
    public function modifierAction(Request $request, Ressource $item, FlashBag $flashBag, Tools $tools, EntityManagerInterface $em, ProfilUtilisateurRepository $profilUtilisateurRepository)
    {
        $path = explode('\\', get_class($item));
        $item_class = $path[3];
        if ($item instanceof Lieu) {
            $typeClassName = $tools->getClassName('Lieu', 'FormType');
        } elseif ($item instanceof Materiel) {
            $typeClassName = $tools->getClassName('Materiel', 'FormType');
        } else {
            throw new \Exception("Format d'activité non reconnu. Attendu : 'Lieu' ou 'Materiel'");
        }

        if ($item instanceof Lieu) {
            $imagesSupplementaires = new ArrayCollection();
            foreach ($item->getImagesSupplementaires() as $imageSupplementaire) {
                $imagesSupplementaires->add($imageSupplementaire);
            }
        }

        $item->updateListeProfils();
        $tousProfils = $profilUtilisateurRepository->findAll();
        $profilsExistants = explode(', ', $item->getListeProfils());

        $form = $this->get('form.factory')->createNamed('editRessourceForm', $typeClassName, $item);

        $form->handleRequest($request);
        if ($request->isMethod('POST')) {
            $profilsExistants = [];
            $tabProfil = [];
            foreach ($form->getData()->getProfilsUtilisateurs() as $formatProfil) {
                $tabProfil[$formatProfil->getProfilUtilisateur()->getId()] = $formatProfil->getProfilUtilisateur()->getLibelle();
                ksort($tabProfil);
            }
            foreach ($tabProfil as $profil) {
                $profilsExistants[] = $profil;
            }

            if ($form->isSubmitted() && $form->isValid()) {
                if ($item instanceof Lieu) {
                    foreach ($imagesSupplementaires as $img) {
                        if (false === $item->getImagesSupplementaires()->contains($img)) {
                            $img->getLieu()->removeImagesSupplementaires($img);
                            $img->setLieu = null;
                        }
                        $em->persist($img);
                    }
                }

                $em->persist($item);
                $em->flush();
                $flashBag->addActionFlashBag($item, 'Modifier');

                return $this->redirectToRoute('UcaGest_RessourceLister');
            }
        }

        $twigConfig['item'] = $item;
        $twigConfig['profilsExistants'] = $profilsExistants;
        $twigConfig['tousProfils'] = $tousProfils;
        $twigConfig['form'] = $form->createView();

        return $this->render('UcaBundle/UcaGest/Referentiel/Ressource/'.$item_class.'/Formulaire.html.twig', $twigConfig);
    }

    /**
     * @Route("/Voir/{id}", name="UcaGest_RessourceVoir")
     * @Isgranted("ROLE_GESTION_RESSOURCE_LECTURE")
     */
    public function voirAction(Ressource $item)
    {
        // $reservabilites = $this->getDoctrine()->getRepository(IntervalleDate::class)->findBy(['ressource' => $item]);
        // $twigConfig['reservabilites'] = $this->encoderReservabilites($reservabilites);
        $path = explode('\\', get_class($item))[3];
        $twigConfig['item'] = $item;
        $twigConfig['format'] = $path;
        $twigConfig['type'] = 'ressource';
        $twigConfig['role'] = 'admin';

        return $this->render('UcaBundle/UcaGest/Referentiel/Ressource/Voir.html.twig', $twigConfig);
    }

    // Formatage des données de la table intervalles_dates
    public function encoderReservabilites(array $datas)
    {
        $reservabilites = [];
        foreach ($datas as $key => $value) {
            $reservabilites[$key] = [
                'id' => $value->getId(),
                'start_date' => $value->getDateDebut()->format('d/m/Y H:i:s'),
                'end_date' => $value->getDateFin()->format('d/m/Y H:i:s'),
                'text' => $value->getRessource()->getLibelle(),
            ];
        }

        return json_encode($reservabilites);
    }

    /**
     * @Route("/Maj", name="UcaGest_RessourceMaj")
     * @Isgranted("ROLE_GESTION_RESSOURCE_ECRITURE")
     */
    public function majReferentielImmobilier(FlashBag $flashBag, KernelInterface $kernel, EntityManagerInterface $em)
    {
        // Chargement du fichier CSV en base (Dans une table temporaire 'ReferentielImmobilier')
        $this->chargerFichierCsv($em, $kernel);
        // Parcours de la table 'ReferentielImmobilier'
        $salles = $em->getRepository(ReferentielImmobilier::class)->findAll();
        foreach ($salles as $salle) {
            // on recherche le lieu via le code Rus
            $lieu = $em->getRepository(Lieu::class)->findOneBy([
                'sourceReferentiel' => true,
                'nomenclatureRus' => $salle->getCodeRus(),
            ]);
            if (empty($lieu)) {
                $lieu = new Lieu();
                $this->hydraterLieu($lieu, $salle, $em, true);
                $em->persist($lieu);
                $flashBag->addActionFlashBag($lieu, 'Ajouter');
            } else {
                $this->hydraterLieu($lieu, $salle, $em, false);
                $flashBag->addActionFlashBag($lieu, 'Modifier');
            }
            $em->flush();
        }
        $flashBag->addMessageFlashBag('ressource.referentiel.success', 'success');

        return $this->redirectToRoute('UcaGest_RessourceLister');
    }

    // Permet de renseigner un à partir des informations de la salle issue du référentiel
    public function hydraterLieu($lieu, $salle, EntityManagerInterface $em)
    {
        $lieu->setSourceReferentiel(true);
        $lieu->setLibelle($salle->getLibelle());
        $lieu->setDescription($salle->getDescription());
        $lieu->setNomenclatureRus($salle->getCodeRus());
        $lieu->setSuperficie($salle->getSuperficie());
        $lieu->setCapaciteAccueil($salle->getCapacite());
        $lieu->setLatitude($salle->getLatitude());
        $lieu->setLongitude($salle->getLongitude());
        // on force un nom d'image par défaut
        $lieu->setImage('no.png');
        $campus = $em->getRepository(Etablissement::class)->findOneBy([
            'code' => $salle->getNomCampus(),
        ]);
        if (!empty($campus)) {
            $lieu->setEtablissement($campus);
        }
    }

    public function supprimerFichierImmobilier(EntityManagerInterface $em)
    {
        $fichiers = $em->getRepository(Fichier::class)->findBy(
            ['code' => 'REFIMMO']
        );
        foreach ($fichiers as $fichier) {
            $em->remove($fichier);
            $em->flush();
        }
    }

    // Chargement du fichier ReferentielImmobilier.csv
    // Dans la table temporaire "referentiel_immobilier"
    // Annule et Remplace
    public function chargerFichierCsv(EntityManagerInterface $em, KernelInterface $kernel)
    {
        $fichier = $em->getRepository(Fichier::class)->findOneBy(
            ['code' => 'REFIMMO']
        );
        // Mise en place du lecteur de fichier CSV - Données séparées par ";" - 1 ligne d'entête à ne pas charger
        $reader = Reader::createFromPath($kernel->getProjectDir().'/public/upload/public/fichiers/'.$fichier->getImage());
        $reader->setDelimiter(';');
        $reader->setHeaderOffset(0);
        // Vidage de la table avant chargement du fichier
        $em->createQuery('DELETE from App\\Entity\\Uca\\ReferentielImmobilier')->execute();
        $records = $reader->getRecords();
        // On charge chaque ligne du fichier en base : chaque donnée est identifiée via son nom dans le header
        foreach ($records as $offset => $record) {
            $record = array_map('utf8_encode', $record);
            $item = new ReferentielImmobilier();
            $item->setLibelle($record['libelle']);
            //$item->setDescription($record['description']);
            $item->setCodeRus($record['rus']);
            $item->setNomCampus($record['campus']);
            $item->setCapacite($record['capacite_sportifs']);
            $item->setLatitude($record['latitude']);
            $item->setLongitude($record['longitude']);
            $item->setSuperficie(empty($record['superficie']) ? null : $record['superficie']);
            $item->setVisiteVirtuelle(empty($record['viste_virtuelle']) ? null : $record['viste_virtuelle']);
            $em->persist($item);
        }
        $em->flush();

        return $this->redirectToRoute('UcaGest_EtablissementLister');
    }

    // Suppression de tous les lieux issus du référentiel immobilier (source = 'Auto')
    public function supprimerReferentielImmobilier(EntityManagerInterface $em)
    {
        $lieux = $em->getRepository(Lieu::class)->findBy(
            ['sourceReferentiel' => true]
        );
        foreach ($lieux as $lieu) {
            $em->remove($lieu);
            $em->flush();
        }
    }

    public function uploadRessource(Request $request)
    {
        try {
            $file = $request->files()->get('CSV_File');
        } catch (\Exception $e) {
        }
    }
}