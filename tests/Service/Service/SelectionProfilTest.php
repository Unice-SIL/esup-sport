<?php

namespace App\Tests\Service\Service;

use App\Service\Service\SelectionProfil;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\Translator;

/**
 * @internal
 * @coversNothing
 */
class SelectionProfilTest extends WebTestCase
{
    /**
     * @var SelectionProfil
     */
    private $selectionProfil;

    protected function setUp(): void
    {
        $container = static::getContainer();

        $translator = new Translator('fr');
        $dir = dirname(__DIR__, 3);
        $router = $container->get(RouterInterface::class);

        $this->selectionProfil = new SelectionProfil($dir, $router, $translator);
    }

    /**
     * @covers \App\Service\Service\SelectionProfil::getFileDatas
     */
    public function testGetFileDatas(): void
    {
        $this->assertEquals($this->selectionProfil->getFileDatas(), [
            0 => [
                'titre' => "trans('security.login.selectionprofil.uca.titre')",
                'route' => 'UcaWeb_ShibLogin',
                'shibboleth' => 1,
                'items' => [
                    0 => [
                        'titre' => "Université Côte d'Azur",
                        'shibboleth' => 1,
                    ],
                    1 => [
                        'titre' => 'Villa Arson',
                        'shibboleth' => 0,
                    ],
                    2 => [
                        'titre' => 'ERACM',
                        'shibboleth' => 0,
                    ],
                    3 => [
                        'titre' => 'CIRM',
                        'shibboleth' => 0,
                    ],
                    4 => [
                        'titre' => 'CNRS',
                        'shibboleth' => 0,
                    ],
                    5 => [
                        'titre' => 'OCA',
                        'shibboleth' => 0,
                    ],
                    6 => [
                        'titre' => 'INRA',
                        'shibboleth' => 0,
                    ],
                    7 => [
                        'titre' => 'INRIA',
                        'shibboleth' => 0,
                    ],
                    8 => [
                        'titre' => 'INSERM',
                        'shibboleth' => 0,
                    ],
                    9 => [
                        'titre' => 'IRD',
                        'shibboleth' => 0,
                    ],
                ],
            ],
            1 => [
                'titre' => "trans('security.login.selectionprofil.autres.titre')",
                'route' => 'security_login',
                'shibboleth' => 0,
                'items' => [
                    0 => [
                        'titre' => 'Retraité',
                        'shibboleth' => 0,
                    ],
                    1 => [
                        'titre' => 'Alumnis',
                        'shibboleth' => 0,
                    ],
                    2 => [
                        'titre' => "Membre d'honneur",
                        'shibboleth' => 0,
                    ],
                ],
            ],
        ]);
    }

    /**
     * @covers \App\Service\Service\SelectionProfil::getUrl
     */
    public function testGetUrl(): void
    {
        $this->assertEquals('/fr/UcaWeb/ShibLogin', $this->selectionProfil->getUrl(
            [
                'titre' => "Université Côte d'Azur",
                'shibboleth' => 1,
            ],
        ));
    }

    /**
     * @covers \App\Service\Service\SelectionProfil::getTitre
     */
    public function testGetTitre(): void
    {
        $this->assertEquals($this->selectionProfil->getTitre(
            [
                'titre' => 'Retraité',
                'shibboleth' => 0,
            ],
            ['Retraité' => 'Retired']
        ), 'Retired');
    }
}
