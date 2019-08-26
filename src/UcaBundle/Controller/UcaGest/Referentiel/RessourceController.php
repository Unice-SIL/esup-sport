<?php

namespace UcaBundle\Controller\UcaGest\Referentiel;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use UcaBundle\Datatables\RessourceDatatable;
use League\Csv\Reader;
use UcaBundle\Entity\Ressource;
use UcaBundle\Entity\Etablissement;
use UcaBundle\Entity\Lieu;
use UcaBundle\Entity\Materiel;
use UcaBundle\Entity\ReferentielImmobilier;
use UcaBundle\Entity\Fichier;
use UcaBundle\Form\FichierType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Liip\ImagineBundle\Config\Filter\Argument\Size;
use Doctrine\DBAL\Driver\PDOException;
use Doctrine\DBAL\Driver\PDOConnection as PDO;

/** 
 * @Route("UcaGest/Ressource") 
 * @Security("has_role('ROLE_ADMIN')")
*/
class RessourceController extends Controller
{
    /**
     * @Route("/", name="UcaGest_RessourceLister")
     * @Isgranted("ROLE_GESTION_RESSOURCE_LECTURE")
    */
    public function listerAction(Request $request)
    {   
        $em = $this->getDoctrine()->getManager();
        $referentiel = new Fichier($em);
        $form = $this->get('form.factory')->create(FichierType::class,$referentiel);
        $form->handleRequest($request);        
        if ($form->isSubmitted() && $form->isValid() && $request->isMethod('POST')) {
            // on supprime le fichier référentiel existant
            $this->supprimerFichierImmobilier();
            // on force le code REFIMMO pour identifier qu'il s'agit du fichier référentiel immobilier
            $referentiel->setCode('REFIMMO');
            $em->persist($referentiel);
            $em->flush();
            // mise à jour du référentiel
            $this->majReferentielImmobilier();
            return $this->redirectToRoute('UcaGest_RessourceLister');
        }

        $isAjax = $request->isXmlHttpRequest();
        $datatable = $this->get('sg_datatables.factory')->create(RessourceDatatable::class);
        $datatable->buildDatatable();
        $twigConfig['datatable'] = $datatable;

        if ($isAjax) {
            $responseService = $this->get('sg_datatables.response');
            $responseService->setDatatable($datatable);
            $dtQueryBuilder = $responseService->getDatatableQueryBuilder();
            $qb = $dtQueryBuilder->getQb();
            return $responseService->getResponse();
        }
        
        $twigConfig['form'] = $form->createView();
        $twigConfig['codeListe'] = 'Ressource';
        // Bouton Ajouter
        $usr = $this->container->get('security.token_storage')->getToken()->getUser();
        if (!$usr->hasRole('ROLE_GESTION_RESSOURCE_ECRITURE')) $twigConfig['noAddButton'] = true;
        return $this->render('@Uca/UcaGest/Referentiel/Ressource/Datatable.html.twig', $twigConfig);
    }

    /**
     * @Route("/Ajouter", name="UcaGest_RessourceAjouter")
     * @Method({"GET""POST"})
     * @Isgranted("ROLE_GESTION_RESSOURCE_ECRITURE")
     */
    public function ajouterAction(Request $request)
    {
        $tools = $this->get('uca.tools');
        $em = $this->getDoctrine()->getManager();
        $format = $request->get('format');
        if(Ressource::formatIsValid($format)) {
            $className = $tools->getClassName($format);
            $typeClassName = $tools->getClassName($format, 'FormType');
            $item = new $className();
        }
        else {
            throw new \Exception("Format <$format> d'activité non valide");
        }
        $form = $this->get('form.factory')->create($typeClassName, $item);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $item->setSourceReferentiel(false);
            $em->persist($item);
            $em->flush();
            $this->get('uca.flashbag')->addActionFlashBag($item, 'Ajouter');
            return $this->redirectToRoute('UcaGest_RessourceLister');
        }
        $twigConfig['item'] = $item;
        $twigConfig['form'] = $form->createView();
        return $this->render('@Uca/UcaGest/Referentiel/Ressource/' . $format . '/Formulaire.html.twig', $twigConfig);
    }

    /**
     * @Route("/Supprimer/{id}", name="UcaGest_RessourceSupprimer")
     * @Isgranted("ROLE_GESTION_RESSOURCE_ECRITURE")
     */
    public function supprimerAction(Request $request, Ressource $item)
    {
        $em = $this->getDoctrine()->getManager();
        if (!$item->getFormatResa()->isEmpty()) {
            $this->get('uca.flashbag')->addActionErrorFlashBag($item, 'Supprimer');
            return $this->redirectToRoute('UcaGest_RessourceLister');
        }
        $em->remove($item);
        $em->flush();
        $this->get('uca.flashbag')->addActionFlashBag($item, 'Supprimer');
        return $this->redirectToRoute('UcaGest_RessourceLister');
    }

    /**
     * @Route("/Modifier/{id}", name="UcaGest_RessourceModifier")
     * @Method({"GET""POST"})
     * @Isgranted("ROLE_GESTION_RESSOURCE_ECRITURE")
    */
    public function modifierAction(Request $request, Ressource $item)
    {
        $tools = $this->get('uca.tools');
        $em = $this->getDoctrine()->getManager();
        $path = explode('\\', get_class($item)); 
        $item_class = $path[2];
        if ($item instanceof Lieu){
            $typeClassName = $tools->getClassName('Lieu', 'FormType');
        } 
        elseif ($item instanceof Materiel){
            $typeClassName = $tools->getClassName('Materiel', 'FormType');
        } 
        else {
            throw new \Exception("Format d'activité non reconnu. Attendu : 'Lieu' ou 'Materiel'");
        }
        $form = $this->get('form.factory')->create($typeClassName, $item);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->flush();
            $this->get('uca.flashbag')->addActionFlashBag($item, 'Modifier');
            return $this->redirectToRoute('UcaGest_RessourceLister');
        }
        $twigConfig['item'] = $item;
        $twigConfig['form'] = $form->createView();
        return $this->render('@Uca/UcaGest/Referentiel/Ressource/' . $item_class . '/Formulaire.html.twig', $twigConfig);
    }
    
    /**
     * @Route("/Voir/{id}", name="UcaGest_RessourceVoir")
     * @Isgranted("ROLE_GESTION_RESSOURCE_LECTURE")
    */
    public function voirAction(Ressource $item)
    {
        // $reservabilites = $this->getDoctrine()->getRepository(IntervalleDate::class)->findBy(['ressource' => $item]);
        // $twigConfig['reservabilites'] = $this->encoderReservabilites($reservabilites);
        $path = explode('\\', get_class($item))[2]; 
        $twigConfig['item'] = $item;
        $twigConfig['format'] = $path;
        $twigConfig['type'] = "ressource";
        $twigConfig['role'] = "admin";

        return $this->render('@Uca/UcaGest/Referentiel/Ressource/Voir.html.twig', $twigConfig);
    }
    
    // Formatage des données de la table intervalles_dates
    public function encoderReservabilites(Array $datas) {
        $reservabilites = [];
        foreach ($datas as $key=>$value) {
            $reservabilites[$key] =  [
                'id' => $value->getId(),
                'start_date' => $value->getDateDebut()->format('d/m/Y H:i:s'),
                'end_date' => $value->getDateFin()->format('d/m/Y H:i:s'),
                'text' => $value->getRessource()->getLibelle()
            ];
        }
        return json_encode($reservabilites);
    }

    /**
     * @Route("/Maj", name="UcaGest_RessourceMaj")
     * @Isgranted("ROLE_GESTION_RESSOURCE_ECRITURE")
    */
    public function majReferentielImmobilier(){
        $em = $this->getDoctrine()->getManager();
        // Chargement du fichier CSV en base (Dans une table temporaire 'ReferentielImmobilier')
        $this->chargerFichierCsv();
        // Parcours de la table 'ReferentielImmobilier'  
        $salles = $this->getDoctrine()->getRepository(ReferentielImmobilier::class)->findAll();
        foreach($salles as $salle){
            // on recherche le lieu via le code Rus
            $lieu = $this->getDoctrine()->getRepository(Lieu::class)->findOneBy([
                'sourceReferentiel' => true,
                'nomenclatureRus' => $salle->getCodeRus()
            ]);
            if (empty($lieu)) {
                $lieu = new Lieu();
                $this->hydraterLieu($lieu, $salle, true);
                $em->persist($lieu);
                $this->get('uca.flashbag')->addActionFlashBag($lieu, 'Ajouter');
            }
            else {
                $this->hydraterLieu($lieu, $salle, false);
                $this->get('uca.flashbag')->addActionFlashBag($lieu, 'Modifier');
            }
            $em->flush();
        }
        $this->get('uca.flashbag')->addMessageFlashBag('ressource.referentiel.success','success');
        return $this->redirectToRoute('UcaGest_RessourceLister');
    }

    // Permet de renseigner un à partir des informations de la salle issue du référentiel
    public function hydraterLieu($lieu, $salle){
        //$em = $this->getDoctrine()->getManager();
        $lieu->setSourceReferentiel(true);
        $lieu->setLibelle($salle->getLibelle());
        $lieu->setDescription($salle->getDescription());
        $lieu->setNomenclatureRus($salle->getCodeRus());
        $lieu->setSuperficie($salle->getSuperficie());
        $lieu->setCapacite($salle->getCapacite());
        $lieu->setLatitude($salle->getLatitude());
        $lieu->setLongitude($salle->getLongitude());
        // on force un nom d'image par défaut
        $lieu->setImage('no.png');
        $campus = $this->getDoctrine()->getRepository(Etablissement::class)->findOneBy([
            'code' => $salle->getNomCampus()
        ]);
        if (!empty($campus)) {
            $lieu->setEtablissement($campus);
        }
    }

    public function supprimerFichierImmobilier()
    {
        $em = $this->getDoctrine()->getManager();
        $fichiers = $this->getDoctrine()->getRepository(Fichier::class)->findBy(
            ['code' => 'REFIMMO']
        );
        foreach($fichiers as $fichier){
            $em->remove($fichier);
            $em->flush();
        } 
    }
    
    // Chargement du fichier ReferentielImmobilier.csv
    // Dans la table temporaire "referentiel_immobilier"
    // Annule et Remplace
    public function chargerFichierCsv()
    {
        $em = $this->getDoctrine()->getManager();
        $fichier = $this->getDoctrine()->getRepository(Fichier::class)->findOneBy(
            ['code' => 'REFIMMO']
        );
        // Mise en place du lecteur de fichier CSV - Données séparées par ";" - 1 ligne d'entête à ne pas charger
        $reader = Reader::createFromPath($this->get('kernel')->getRootDir() . '/../web/upload/public/fichiers/' . $fichier->getImage());
        $reader->setDelimiter(';');
        $reader->setHeaderOffset(0);
        // Vidage de la table avant chargement du fichier
        $em->createQuery('DELETE from UcaBundle:ReferentielImmobilier')->execute();
        $records = $reader->getRecords();
        // On charge chaque ligne du fichier en base : chaque donnée est identifiée via son nom dans le header
        foreach ($records as $offset => $record) {
           $item = new ReferentielImmobilier();
           $item->setLibelle($record['libelle']);
           //$item->setDescription($record['description']);
           $item->setCodeRus($record['rus']);
           $item->setNomCampus($record['campus']);
           $item->setCapacite($record['capacite_sportifs']);
           $item->setLatitude($record['latitude']);
           $item->setLongitude($record['longitude']);
           $item->setSuperficie(empty($record['superficie']) ? null : $record['superficie']);
           $em->persist($item);
        }
        $em->flush();
        return $this->redirectToRoute('UcaGest_EtablissementLister');
    }
    
    // Suppression de tous les lieux issus du référentiel immobilier (source = 'Auto')
    public function supprimerReferentielImmobilier(){
        $em = $this->getDoctrine()->getManager();
        $lieux = $this->getDoctrine()->getRepository(Lieu::class)->findBy(
            ['sourceReferentiel' => true]
        );
        foreach($lieux as $lieu){
            $em->remove($lieu);
            $em->flush();
        }
    }

    public function uploadRessource(Request $request) { 
        try {
            $file = $request->files()->get('CSV_File');

        } catch(Exception $e) {

        }
    }
}

