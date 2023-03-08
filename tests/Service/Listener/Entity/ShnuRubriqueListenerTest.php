<?php

namespace App\Tests\Service\Listener\Entity;

use App\Entity\Uca\ShnuRubrique;
use App\Service\Listener\Entity\ShnuRubriqueListener;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 * @coversNothing
 */
class ShnuRubriqueListenerTest extends WebTestCase
{
    /**
     * @var ShnuRubriqueListener
     */
    private $ShnuRListener;

    private $ordre;

    protected function setUp(): void
    {
        $this->ShnuRListener = new ShnuRubriqueListener();

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $this->ordre = $em->getRepository(ShnuRubrique::class)->max('ordre');
    }

    /**
     * @covers \App\Service\Listener\Entity\ShnuRubriqueListener::preFlush
     */
    public function testPreFlush(): void
    {
        $container = static::getContainer();
        $em = $container->get(EntityManagerInterface::class);

        for ($nbreRubrique = $this->ordre + 1; $nbreRubrique < $this->ordre + 10; ++$nbreRubrique) {
            $rubrique = new ShnuRubrique();
            $rubrique
                ->setTitre('Rubrique '.$nbreRubrique)
            ;

            $event = new PreFlushEventArgs($em);
            $this->assertEquals($rubrique->getOrdre(), null);
            $this->ShnuRListener->preFlush($rubrique, $event);
            $this->assertEquals($rubrique->getOrdre(), $nbreRubrique);

            $em->persist($rubrique);
            $em->flush();
        }
    }
}
