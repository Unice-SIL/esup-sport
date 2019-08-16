<?php

namespace UcaBundle\Controller\UcaGest\Activite;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use UcaBundle\Entity\Activite;
use UcaBundle\Entity\FormatAchatCarte;
use UcaBundle\Entity\FormatActivite;
use UcaBundle\Entity\FormatAvecCreneau;
use UcaBundle\Entity\FormatAvecReservation;
use UcaBundle\Entity\FormatSimple;
use UcaBundle\Entity\ProfilUtilisateur;
use UcaBundle\Entity\NiveauSportif;

/** 
 * @Route("UcaGest/Activite/{idActivite}/FormatActivite", requirements={"idActivite"="\d+"}) 
 * @Security("has_role('ROLE_ADMIN')")
 */
class FormatActiviteController extends Controller
{

    /**
     * @Route("/Voir/{id}", name="FormatActiviteVoir")
     * @Method("GET")
     * @Isgranted("ROLE_GESTION_FORMAT_ACTIVITE_LECTURE")
     */
    public function voirAction(Request $request, $idActivite, FormatActivite $item)
    {
        $twigConfig['item'] = $item;
        $twigConfig['type'] = "FormatActivite";
        $twigConfig['role'] = "admin";
        $twigConfig['format'] =  explode('\\', get_class($item))[2];


        if (($item instanceof FormatAvecCreneau) && $this->isGranted('ROLE_GESTION_FORMAT_ACTIVITE_ECRITURE'))
            $twigConfig['Scheduler'] = true;
        elseif ($item instanceof FormatSimple || $item instanceof FormatAchatCarte || $item instanceof FormatAvecReservation)
            $twigConfig['Scheduler'] = false;
        else
            $twigConfig['Scheduler'] = false;
        return $this->render('@Uca/UcaGest/Activite/FormatActivite/Voir.html.twig', $twigConfig);
    }

    /**
     * @Security("is_granted('ROLE_GESTION_FORMAT_ACTIVITE_ECRITURE') and is_granted('ROLE_GESTION_ACTIVITE_ECRITURE')")
     * @Route("/Ajouter", name="FormatActiviteAjouter")
     */
    public function ajouterAction(Request $request, $idActivite)
    {
        $tools = $this->get('uca.tools');
        $em = $this->getDoctrine()->getManager();
        $format = $request->get('format');
        if (FormatActivite::formatIsValid($format)) {
            $className = $tools->getClassName($format);
            $item = new $className();
            $item->setEstEncadre(false);
            $item->setEstPayant(false);
            $item->setActivite($em->getReference(Activite::class, $idActivite));
            $item->setDateDebutEffective(new \Datetime('now'));
            $item->setDateFinEffective(new \Datetime('now'));
            $item->setDateDebutInscription(new \Datetime('now'));
            $item->setDateFinInscription(new \Datetime('now'));
            // DTI - 0001836 Ajout de l'image de l'activité par défaut
            $item->setImage($this->getDoctrine()->getRepository(Activite::class)->findOneById($idActivite)->getImage());
            if (!$request->isMethod('POST')) {
                $profils = $this->getDoctrine()->getRepository(ProfilUtilisateur::class)->findAll();
                foreach ($profils as $profil) {
                    $item->addProfilsUtilisateur($profil);
                }
                $niveaux = $this->getDoctrine()->getRepository(NiveauSportif::class)->findAll();
                foreach ($niveaux as $niveau) {
                    $item->addNiveauxSportif($niveau);
                }
            }
            $typeClassName = $tools->getClassName($format, 'FormType');
        } else {
            throw new \Exception("Format <$format> d'activité non valide");
        }
        $form = $this->get('form.factory')->create($typeClassName, $item);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->persist($item);
            $em->flush();
            $this->get('uca.flashbag')->addActionFlashBag($item, 'Ajouter');
            return $this->redirectToRoute('FormatActiviteVoir', array('idActivite' => $idActivite, 'id' => $item->getId()));
        }
        $twigConfig['FormatClassName'] = $className;
        $twigConfig['item'] = $item;
        $twigConfig['form'] = $form->createView();
        return $this->render('@Uca/UcaGest/Activite/FormatActivite/Formulaire.html.twig', $twigConfig);
    }

    /** 
     * @Route("/Modifier/{id}", name="FormatActiviteModifier")
     * @Method({"GET""POST"})
     * @Isgranted("ROLE_GESTION_FORMAT_ACTIVITE_ECRITURE")
     */
    public function modifierAction(Request $request, $idActivite, FormatActivite $item)
    {
        $tools = $this->get('uca.tools');
        $em = $this->getDoctrine()->getManager();
        $className = get_class($item);
        $path = explode('\\', $className);
        $item_class = $path[2];
        $typeClassName = $tools->getClassName(array_pop($path), 'FormType');

        $form = $this->get('form.factory')->create($typeClassName, $item); //, ['sub_class' => get_class($item)]);
        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->flush();
            $this->get('uca.flashbag')->addActionFlashBag($item, 'Modifier');
            return $this->redirectToRoute('FormatActiviteVoir', [
                'idActivite' => $idActivite,
                'id' => $item->getId()
            ]);
        }
        $twigConfig['FormatClassName'] = $className;
        $twigConfig['item'] = $item;
        $twigConfig['form'] = $form->createView();
        return $this->render('@Uca/UcaGest/Activite/FormatActivite/Formulaire.html.twig', $twigConfig);
    }

    /**
     * @Security("is_granted('ROLE_GESTION_FORMAT_ACTIVITE_ECRITURE') and is_granted('ROLE_GESTION_ACTIVITE_ECRITURE')")
     * @Route("/Supprimer/{id}", name="FormatActiviteSupprimer")
     */
    public function supprimerAction(Request $request, $idActivite, FormatActivite $formatActivite)
    {
        $em = $this->getDoctrine()->getManager();
        if (!$formatActivite->getInscriptions()->isEmpty()) {
            $this->get('uca.flashbag')->addActionErrorFlashBag($formatActivite, 'Supprimer');
            return $this->redirectToRoute("ActiviteVoir", array("id" => $idActivite));
        }
        $em->remove($formatActivite);
        $em->flush();
        $this->get('uca.flashbag')->addActionFlashBag($formatActivite, 'Supprimer');
        return $this->redirectToRoute('ActiviteVoir', array('id' => $idActivite));
    }
}
