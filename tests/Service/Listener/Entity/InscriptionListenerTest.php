<?php

namespace App\Tests\Service\Listener\Entity;

use App\Entity\Uca\ComportementAutorisation;
use App\Entity\Uca\FormatAchatCarte;
use App\Entity\Uca\FormatActiviteProfilUtilisateur;
use App\Entity\Uca\FormatAvecCreneau;
use App\Entity\Uca\Inscription;
use App\Entity\Uca\ProfilUtilisateur;
use App\Entity\Uca\TypeAutorisation;
use App\Entity\Uca\Utilisateur;
use App\Service\Listener\Entity\InscriptionListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 * @coversNothing
 */
class InscriptionListenerTest extends WebTestCase
{
    /**
     * @var InscriptionListener
     */
    private $inscriptionListener;

    protected function setUp(): void
    {
        $this->utilisateur = (new Utilisateur())
            ->setUserName('Inscription')
            ->setEmail('test@test.com')
            ->setPassword('test')
        ;

        $this->comportementAutorisationFormat = (new ComportementAutorisation())
            ->setdescriptionComportement('desc')
            ->setLibelle('libelle comportement')
            ->setCodeComportement('code')
        ;

        $this->typeAutorisationFormat = (new TypeAutorisation())
            ->setComportement(
                $this->comportementAutorisationFormat
            )
            ->setLibelle('')
        ;

        $this->format =
            (new FormatAchatCarte())
                ->setCarte(
                    $this->typeAutorisationFormat
                )

        ;

        $this->comportementAutorisation = (new ComportementAutorisation())
            ->setdescriptionComportement('desc2')
            ->setLibelle('libelle comportement2')
            ->setCodeComportement('code')
        ;

        $this->typeAutorisation = (new TypeAutorisation())
            ->setComportement(
                $this->comportementAutorisation
            )
            ->setLibelle('')
        ;

        $this->inscription = new Inscription($this->format, $this->utilisateur, []);

        $date = new \DateTime();

        $this->formatActivite =
            (new FormatAvecCreneau())
                ->setLibelle('FormatAvecCreneau')
                ->addAutorisation($this->typeAutorisation)
                ->setCapacite(10)
                ->setDescription('')
                ->setDateDebutPublication($date)
                ->setDateFinPublication($date)
                ->setDateDebutInscription($date)
                ->setDateFinInscription($date)
                ->setDateDebutEffective($date)
                ->setDateFinEffective($date)
                ->setImage('')
                ->setEstPayant(false)
                ->setEstEncadre(false)
        ;
        $this->inscription->setFormatActivite($this->formatActivite);

        $this->profil = (new ProfilUtilisateur())
            ->addUtilisateur($this->utilisateur)
        ;
        $this->utilisateur->setProfil($this->profil);

        $this->formatActivite->addProfilsUtilisateur(new FormatActiviteProfilUtilisateur($this->formatActivite, $this->profil, 1));

        $this->inscriptionListener = new InscriptionListener(
            $this->inscription
        );
    }

    /**
     * @covers \App\Service\Listener\Entity\InscriptionListener::prePersist
     */
    public function testPrePersist(): void
    {
        $container = static::getContainer();

        $this->assertEquals($this->inscription->getFormatActivite()->getProfilsUtilisateurs()->first()->getNbInscrits(), 0);

        $this->inscriptionListener->prePersist($this->inscription, new LifecycleEventArgs($this->inscription, $container->get(EntityManagerInterface::class)));

        $this->assertEquals($this->inscription->getFormatActivite()->getProfilsUtilisateurs()->first()->getNbInscrits(), 1);
    }
}
