<?php

namespace UcaBundle\DataFixtures\ORM ;

use UcaBundle\Entity;
use Doctrine\Common\DataFixtures\FixtureInterface ;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


class JeuDeTest implements FixtureInterface 
{
    
    private $container;

    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function load(ObjectManager $manager) 
    {
        // 1. Type Activite
        $datas = array('Sport Fr' ,'Culture Fr');
        foreach ($datas as $data) {
            $item = new Entity\TypeActivite ;
            $item->setLibelle($data) ;
            $manager->persist($item) ;
        }
        $manager->flush() ;
        $typeActiviteRepo = $manager->getRepository('UcaBundle:TypeActivite') ;

        // 2. Tarif
        $datas = array('CVEC','Badminton');
        foreach ($datas as $data) {
            $item = new Entity\Tarif ;
            $item->setLibelle($data) ;
            $manager->persist($item) ;
        }
        $manager->flush() ;
        $tarifRepo = $manager->getRepository('UcaBundle:Tarif') ;
    
        // 3. Etablissements
        $datas = array(array('VALROSE','VALROSE'),array('CARLONE','CARLONE'));
        foreach ($datas as $data) {
            $item = new Entity\Etablissement ;
            $item
                ->setCode($data[0])
                ->setLibelle($data[1]) ;
            $manager->persist($item) ;
        }
        $manager->flush() ;
        $etablissementRepo = $manager->getRepository('UcaBundle:Etablissement') ;
        
        // 4. Ressources
        $datas = array(
            array(1,'Cuisine','Cuisine',25,35,40,'Manuel','Lieu') ,
            array(1,'Salle de Bain','Salle de Bain',300,NULL,NULL,'Manuel','Lieu') ,
            array(1,'Salon','Salon',NULL,6,10,'Manuel','Lieu') ,
            array(2,'Terrain de Foot','Terrain de Foot',NULL,NULL,NULL,'Manuel','Materiel') ,
            array(2,'Veranda','Veranda',NULL,NULL,NULL,'Manuel','Materiel') ,
            array(2,'Salle de billard','Salle de billard',NULL,NULL,NULL,'Manuel','Lieu') ,
            array(NULL,'Chambre','Chambre',NULL,NULL,NULL,'Manuel','Materiel') ,
            array(NULL,'Salle à Manger','Salle à Manger',NULL,NULL,NULL,'Manuel','Lieu') ,
            array(NULL,'Toilettes','C\'est pas cher au moins',NULL,NULL,NULL,'Manuel','Materiel') ,
            array(NULL,'Bureau','Bureau',NULL,NULL,NULL,'Manuel','Materiel')
        );
        foreach ($datas as $data) {
            if ($data[7]=='Lieu'){
                $item = new Entity\Lieu;
            } else if ($data[7]=='Materiel') {
                $item = new Entity\Materiel;
            }
            $item
                ->setEtablissement(($data[0] != NULL ? $etablissementRepo->find($data[0]) : NULL))
                ->setLibelle($data[1]) 
                ->setDescription($data[2])
                ->setSuperficie($data[3])
                ->setCapaciteSportifs($data[4])
                ->setCapaciteSpectateurs($data[5])
                ->setSource($data[6]);
            $manager->persist($item) ;
        }
        $manager->flush() ;
        $materielRepo = $manager->getRepository('UcaBundle:Materiel') ;
        $lieuRepo = $manager->getRepository('UcaBundle:Lieu') ;
      
        //5. Classe d'activité
        $datas = array(
            array(1, 'Intérieur'),
            array(1, 'Extérieur'),
            array(1, 'Sport de raquette'),
            array(1, 'Sport collectif'),
            array(1, 'Sport d\'eau'),
            array(1, 'E-sport')
        );
        foreach ($datas as $data) {
            $item = new Entity\ClasseActivite ;
            $item
                ->setTypeActivite($typeActiviteRepo->find($data[0]))
                ->setLibelle($data[1]) ;
            $manager->persist($item) ;
        }
        $manager->flush() ;
        $classeActiviteRepo = $manager->getRepository('UcaBundle:ClasseActivite') ;
        
        // 6. Type Autorisation
        $datas = array(array('Droit sportif',1),array('Certificat médical',NULL),array('Autorisation plongée',NULL), array('Autorisation escalade',NULL));
        foreach ($datas as $data) {
            $item = new Entity\TypeAutorisation ;
            $item
                ->setLibelle($data[0])
                ->settarif(($data[1] != NULL ? $tarifRepo->find($data[1]) : NULL)) ;
            $manager->persist($item) ;
        }
        $manager->flush() ;
        $typeAutorisationRepo=$manager->getRepository('UcaBundle:TypeAutorisation') ;
    
        //7. Profil utilisateurs
        $datas=array('Retraités','Etudiants','Alumnis','Conjoints','Personnels');
        foreach ($datas as $data) {
            $item = new Entity\ProfilUtilisateur ;
            $item->setLibelle($data) ;
            $manager->persist($item) ;
        }
        $manager->flush() ;
        $profilUtilsiateurRepo=$manager->getRepository('UcaBundle:ProfilUtilisateur') ;

        //8. Niveaux Sportifs
        $datas=array('Débutant','Intermédiaire','Expert') ;
        foreach ($datas as $data) {
            $item = new Entity\NiveauSportif ;
            $item->setLibelle($data) ;
            $manager->persist($item) ;
        }
        $manager->flush() ;
        
        //9. Utilisateurs
        $datas=array(
            array(3,'dtinseau','damien.tinseau@acatus.fr',array('a:0:{}'),'damien','tinseau'),
            array(5, 'dgueudre','davy.gueudre@acatus.fr',array('a:1:{i:0;s:10:\"ROLE_ADMIN\";}'),'davy', 'gueudre'),
            array(1, 'lpaumier','laura.paumier@acatus.fr',array('a:0:{}'),'laura', 'paumier'),
            array(2, 'ymaresse','yaelle.maresse@atimic.fr',array('a:0:{}'), 'Yaelle', 'Maresse'),
            array(2, 'pjolivet','pierre.jolivet@atimic.fr',array('a:0:{}'),  'Pierre', 'Jolivet')
        );
        foreach($datas as $data) {
            $item = new Entity\Utilisateur ;
            // Password : Admin123*
            $encodedPass =  '$2y$13$ae0/x9Oqd2xq6tkwoXW4y.0ftJfpIn7rOw1YKBVjYEr.8x.75Hlrq' ;
            $item
                ->setProfil($profilUtilsiateurRepo->find($data[0]))
                ->setUsername($data[1])
                ->setUsernameCanonical($data[1])
                ->setEmail($data[2])
                ->setEmailCanonical($data[2])
                ->setEnabled(true)
                ->setPassword($encodedPass)
                ->setroles($data[3])
                ->setPrenom($data[4])
                ->setNom($data[5]);
            $manager->persist($item) ;
        }
        $manager->flush() ;
        $utilisateurRepo=$manager->getRepository('UcaBundle:Utilisateur') ;

        //10. Groupes
        $datas=array(
            array('Gestionnaire d\'activité', array('a:7:{i:0;s:16:\"GESTION_ACTIVITE\";i:1;s:23:\"GESTION_CLASSE_ACTIVITE\";i:2;s:15:\"GESTION_CRENEAU\";i:3;s:29:\"GESTION_EQUIPEMENT_RESERVABLE\";i:4;s:23:\"GESTION_FORMAT_ACTIVITE\";i:5;s:13:\"GESTION_TARIF\";i:6;s:21:\"GESTION_TYPE_ACTIVITE\";}')),
            array('Gestionnaire financier', array('a:2:{i:0;s:25:\"ACCES_HISTORIQUE_COMMANDE\";i:1;s:13:\"GESTION_TARIF\";}')),
            array('Encadrant', array('a:3:{i:0;s:14:\"ACCES_PLANNING\";i:1;s:24:\"GESTION_FEUILLE_PRESENCE\";i:2;s:29:\"VALIDER_AUTORISATION_SPORTIVE\";}')),
            array('Administrateur', array('ACCES_HISTORIQUE_COMMANDE','ACCES_PLANNING','GESTION_ACTIVITE','GESTION_CLASSE_ACTIVITE','GESTION_CRENEAU','GESTION_EQUIPEMENT_RESERVABLE','GESTION_FEUILLE_PRESENCE','GESTION_FORMAT_ACTIVITE','GESTION_TARIF','GESTION_TYPE_ACTIVITE','GESTION_UTILISATEUR','VALIDER_AUTORISATION_SPORTIVE'))
        );
        foreach($datas as $data) {
            $item = new Entity\Groupe($data[0],$data[1]) ;
            $manager->persist($item) ;
        }
        $manager->flush() ;

        //11. Montants_tarfi
        $datas=array(
            array(20,15,3),
            array(30,15,4),
            array(200,15,6),
            array(60,15,7),
            array(40,15,8),
            array(35,18,3),
            array(25,18,6),
            array(25,18,7),
            array(20,18,8)
        );
        foreach($datas as $data) {
            $item = new Entity\MontantTarifProfilUtilisateur ;
            $item
                ->setmontant($data[0])
                ->setProfil($profilUtilsiateurRepo->find($data[1]))
                ->setTarif($tarifRepo->find($data[2])) ;
            $manager->persist($item) ;
        }
        $manager->flush() ;
        
        //12. Fichiers
        $datas=array(
            '0b8c0d2dd7889388e2b8e62e81fe64769b343dc1.png',
            '51cce86d82d7c283f9dfa8ed66978d71c5648916.png',
            '28133a34fe3c15c3266708089295be66dae75c7d.png',
            '0ede58a242166885a76310766d682456eb9b8995.png' 
        );
        foreach ($datas as $data) {
            $item = new Entity\Fichier;
            $item->setname($data) ;
            $manager->persist($item) ;
        }
        $manager->flush() ;

        //13. Activite
        $datas=array() ;
        $item=new Entity\Activite ;
        $manager->flush() ;
    }
}

