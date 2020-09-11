<?php

/*
 * Classe - CreateUserFromBDDStatistiqueCommand
 *
 * Commadne (exécution en console)
 * Requêtes à la base de données permettant l'alimentation des données statistiques
*/

namespace StatistiqueBundle\Command;

use DateTime;
use Exception;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UcaBundle\Entity\ProfilUtilisateur;
use UcaBundle\Entity\Utilisateur;

class CreateUserFromBDDStatistiqueCommand extends ContainerAwareCommand
{
    protected static $defaultName = 'uca:create:user';

    protected function configure()
    {
        $this->setDescription('Créer des utilisateurs à partir de la table utilisateurs de la bdd_statistique et établit un lien entre les deux');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $emStat = $this->getContainer()->get('doctrine')->getManager('statistique');
        $em = $this->getContainer()->get('doctrine')->getManager();
        $listeUtilisateurs = $emStat->getRepository('StatistiqueBundle:DataUtilisateur')->findAll();
        $prenom = [
            'Kate', 'Gatien', 'Mario', 'Nolann', 'Pierre-Antoine', 'Eline', 'Mélinda', 'Nadir', 'Stéphanie', 'Marlon',
            'Sebastien', 'Yvan', 'Loïs', 'Charlie', 'Kimberly', 'Virginie', 'Ugo', 'Tomas', 'Charlène', 'Dario',
        ];

        $nom = [
            'Martin', 'Bernard', 'Thomas', 'Petit', 'Robert', 'Richard', 'Durand', 'Dubois', 'Moreau', 'Laurent',
            'Simon', 'Michel', 'Lefebvre', 'Leroy', 'Roux', 'David', 'Bertrand', 'Morel', 'Fournier', 'Girard',
        ];
        $size = 20;
        $profilEtudiant = $em->getRepository(ProfilUtilisateur::class)->find(4); //étudiant
        $profilPersonnel = $em->getRepository(ProfilUtilisateur::class)->find(8);
        $mail = '@statistique.fr';
        $password = '$2y$13$ae0/x9Oqd2xq6tkwoXW4y.0ftJfpIn7rOw1YKBVjYEr.8x.75Hlrq';
        $adresse = '28 Avenue de Valrose';
        $cp = '06108';
        $ville = 'Nice';
        $cmptSuccess = 0;
        $cmptFail = 0;

        $i = 0;

        foreach ($listeUtilisateurs as $utilisateur) {
            try {
                $username = $utilisateur->getCodEtu();
                $user = new Utilisateur();
                $rand = rand(0, $size - 1);
                $user->setPrenom($prenom[$rand]);
                $rand = rand(0, $size - 1);
                $user->setNom($nom[$rand]);
                $user->setUsername($username);
                $user->setUsernameCanonical($username);
                $user->setEmail($username.$mail);
                $user->setEmailCanonical($username.$mail);
                if ($utilisateur->getEstMembrePersonnel()) {
                    $user->setProfil($profilPersonnel);
                } else {
                    $user->setProfil($profilEtudiant);
                }
                $user->setPassword($password);
                $user->setSexe($utilisateur->getSexe());
                $user->setAdresse($adresse);
                $user->setCodePostal($cp);
                $user->setVille($ville);

                //La date fournit est une chaine au format mm/yyyy
                $dateToConvert = $utilisateur->getDateNaissance();
                $dateToConvert = explode('/', $dateToConvert);
                $date = strtotime($dateToConvert[0].'/'.'01/'.$dateToConvert[1]);
                $newDate = date('Y-m-d', $date);

                $user->setDateNaissance(new DateTime($newDate));

                $em->persist($user);
                ++$cmptSuccess;
            } catch (Exception $e) {
                $output->writeln($e->getMessage());
                ++$cmptFail;
            }
        }

        $em->flush();

        $output->writeln($cmptSuccess.' utilisateur(s) importé(s).');
        $output->writeln($cmptFail.' échec(s) d\'import');
    }
}
