<?php

namespace App\Tests\Entity\Uca\Traits;

use App\Entity\Uca\Activite;
use App\Entity\Uca\ClasseActivite;
use App\Entity\Uca\Creneau;
use App\Entity\Uca\FormatAvecCreneau;
use App\Entity\Uca\NiveauSportif;
use App\Entity\Uca\TypeActivite;
use App\Entity\Uca\Utilisateur;
use App\Repository\ActiviteRepository;
use App\Repository\ClasseActiviteRepository;
use App\Repository\CreneauRepository;
use App\Repository\FormatAvecCreneauRepository;
use App\Repository\NiveauSportifRepository;
use App\Repository\TypeActiviteRepository;
use App\Repository\UtilisateurRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Uca\Traits\JsonSerializable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 * @coversNothing
 */
class JsonSerializableTest extends KernelTestCase
{
    /**
     * @var JsonSerializable
     */
    private $serialiseCaller;


    /**
     * Fonction qui s'exécute avant chaque test.
     */
    protected function setUp(): void
    {
        $container = static::getContainer();
        $em = $container->get(EntityManagerInterface::class);
        // DhtmlxEvenement > Inscription > Autorisation
        $this->niveauSport = (new NiveauSportif())->setLibelle('test_json_serializable1');
        $this->creneau = (new Creneau())
            ->setCapacite(123456789)
            ->addNiveauSportif($this->niveauSport);

        $this->user = (new Utilisateur())
        ->setNom('test_json_serializable')
        ->setPrenom('test_json_serializable')
        ->setUsername('test_json_serializable')
        ->setSexe('M')
        ->setEmail('test_json_serializable@test.fr')
        ->setEnabled(true)
        ->setPassword('test_json_serializable')
        ;

        $this->formatActivite = (new FormatAvecCreneau())
            ->setLibelle('test_json_serializable')
            ->setCapacite(1)
            ->setDescription('test')
            ->setDateDebutEffective(new \DateTime())
            ->setDateDebutInscription(new \DateTime())
            ->setDateDebutPublication(new \DateTime())
            ->setDateFinEffective((new \DateTime())->add(new \DateInterval('P1D')))
            ->setDateFinInscription((new \DateTime())->add(new \DateInterval('P1D')))
            ->setDateFinPublication((new \DateTime())->add(new \DateInterval('P1D')))
            ->setImage('test')
            ->setStatut(1)
            ->setTarifLibelle('Tarif')
            ->setListeLieux('[]')
            ->setListeAutorisations('[]')
            ->setListeNiveauxSportifs('[]')
            ->setListeProfils('[]')
            ->setListeEncadrants('[]')
            ->setPromouvoir(false)
            ->setEstPayant(true)
            ->setEstEncadre(false)
        ;
        $this->typeActivite = (new TypeActivite())
            ->setLibelle('test_json_serializable')
        ;
        $this->classeActivite = (new ClasseActivite())
            ->setLibelle('test_json_serializable')
            ->setTypeActiviteLibelle('test_json_serializable')
            ->setImage('test.jpg')
            ->setTypeActivite($this->typeActivite)
        ;
        $this->activite = (new Activite())
            ->setLibelle('test_json_serializable')
            ->setImage('test.jpg')
            ->setDescription('test_json_serializable')
            ->setClasseActiviteLibelle('test_json_serializable')
            ->setClasseActivite($this->classeActivite)
            ->addFormatsActivite($this->formatActivite);
        ;

        $this->user->serialiseCaller = $this->user;
        $this->activite->serialiseCaller = $this->user;
        $this->classeActivite->serialiseCaller = null;
        $this->creneau->serialiseCaller = $this->creneau;
        $this->niveauSport->serialiseCaller = null;
        $em->persist($this->formatActivite);
        $em->persist($this->typeActivite);
        $em->persist($this->classeActivite);
        $em->persist($this->activite);
        $em->persist($this->user);

        $em->persist($this->niveauSport);
        $em->persist($this->creneau);

        $em->flush();
    }

    /**
     * @covers \App\Entity\Uca\Traits\JsonSerializable::getSerialiseId
     */
    public function testGetSerialiseId()
    {
        $this->assertEquals(get_class($this->user).'#'.$this->user->getId().'#', $this->user->getSerialiseId());
    }

    /**
     * @covers \App\Entity\Uca\Traits\JsonSerializable::hasSerialiseCaller
     */
    public function testHasSerialiseCaller()
    {
        $this->assertFalse($this->classeActivite->hasSerialiseCaller($this->user));
        $this->assertTrue($this->user->hasSerialiseCaller($this->user));

        $this->activite->serialiseCaller->serialiseCaller = $this->creneau;
        $this->assertTrue($this->activite->hasSerialiseCaller($this->creneau));
    }

     /**
     * @covers \App\Entity\Uca\Traits\JsonSerializable::toArray
     */
    public function testToArray()
    {
        // Call the toArray method
        $result = $this->formatActivite->toArray(null);

        // Test date
        $this->assertIsArray($result);
        $this->assertArrayHasKey("libelle", $result);
        $this->assertArrayHasKey("description", $result);
        $this->assertEquals($this->formatActivite->getLibelle(), $result["libelle"]);
        $this->assertEquals($this->formatActivite->getDescription(), $result["description"]);

        // Call the toArray method
        $result = $this->activite->toArray(null);

        // Test du jsonSerializable enfant
        $this->assertIsArray($result);
        $this->assertArrayHasKey("id", $result);
        $this->assertArrayHasKey("libelle", $result);
        $this->assertEquals($this->activite->getLibelle(), $result["libelle"]);
        $this->assertEquals($this->activite->getId(), $result["id"]);
        $this->assertEquals($this->classeActivite->getLibelle(), $result["classeActivite"]["libelle"]);
        $this->assertEquals($this->classeActivite->getId(), $result["classeActivite"]["id"]);

        // Test cascade
        $this->niveauSport2 = (new NiveauSportif())->setLibelle('test_json_serializable2');
        $this->creneau->addNiveauSportif($this->niveauSport2);
        $result = $this->creneau->jsonSerialize(null);
        // Assert that the result is an array with the expected values
        $this->assertIsArray($result);
        $this->assertArrayHasKey("id", $result);
        $this->assertArrayHasKey("capacite", $result);
        $this->assertEquals($this->creneau->getCapacite(), $result["capacite"]);

        foreach ($result['niveauxSportifs'] as $k=>$niv) {
            $this->assertEquals($this->creneau->getNiveauxSportifs()[$k]->getLibelle(), $niv["libelle"]);
        }
        $this->creneau->removeNiveauSportif($this->niveauSport2);
    }

    /**
     * @covers \App\Entity\Uca\Traits\JsonSerializable::jsonSerialize
     */
    public function testJsonSerialize()
    {
        // Call the jsonSerialize method
        $this->niveauSport2 = (new NiveauSportif())->setLibelle('test_json_serializable2');
        $this->creneau->addNiveauSportif($this->niveauSport2);
        $result = $this->creneau->jsonSerialize(null);
        // Assert that the result is an array with the expected values
        $this->assertIsArray($result);
        $this->assertArrayHasKey("id", $result);
        $this->assertArrayHasKey("capacite", $result);
        $this->assertEquals($this->creneau->getCapacite(), $result["capacite"]);

        foreach ($result['niveauxSportifs'] as $k=>$niv) {
            $this->assertEquals($this->creneau->getNiveauxSportifs()[$k]->getLibelle(), $niv["libelle"]);
        }
        $this->creneau->removeNiveauSportif($this->niveauSport2);
    }
}
