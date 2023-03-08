<?php

namespace App\Tests\Entity\Uca;

use App\Entity\Uca\ShnuHighlight;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @internal
 * @coversNothing
 */
class ShnuHighlightTest extends WebTestCase
{
    /**
     * @covers \App\Entity\Uca\ShnuHighlight::setImageFile
     */
    public function testSetImageFile(): void
    {
        $file = new File(dirname(__DIR__, 2).'/fixtures/test.pdf');
        $highlight = (new ShnuHighlight())->setImageFile($file);

        $this->assertInstanceOf(File::class, $highlight->getImageFile());
        $this->assertEquals($file, $highlight->getImageFile());
        $this->assertInstanceOf(DateTime::class, $highlight->getUpdatedAt());
    }

    /**
     * @covers \App\Entity\Uca\ShnuHighlight::getImageUrl
     */
    public function testGetImageUrlMiniature(): void
    {
        $highlight = (new ShnuHighlight())->setMiniature('https://google.com');

        $this->assertIsString($highlight->getImageUrl());
        $this->assertEquals('https://google.com', $highlight->getImageUrl());
    }

    /**
     * @covers \App\Entity\Uca\ShnuHighlight::getImageUrl
     */
    public function testGetImageUrlImage(): void
    {
        $highlight = (new ShnuHighlight())->setImage('https://google.com');

        $this->assertIsString($highlight->getImageUrl());
        $this->assertEquals('https://google.com', $highlight->getImageUrl());
    }

    /**
     * @covers \App\Entity\Uca\ShnuHighlight::getImageUrl
     */
    public function testGetImageUrlEmpty(): void
    {
        $highlight = new ShnuHighlight();

        $this->assertIsString($highlight->getImageUrl());
        $this->assertEmpty($highlight->getImageUrl());
        $this->assertEquals('', $highlight->getImageUrl());
    }

    /**
     * @covers \App\Entity\Uca\ShnuHighlight::setVideo
     */
    public function testSetVideoYoutube(): void
    {
        $highlight = (new ShnuHighlight())->setVideo('https://www.youtube.com/watch?v=kuKb3VfcTWE');

        $this->assertIsString($highlight->getLecteurVideo());
        $this->assertNotEmpty($highlight->getLecteurVideo());
        $this->assertEquals('youtube', $highlight->getLecteurVideo());
        $this->assertIsString($highlight->getVideo());
        $this->assertNotEmpty($highlight->getVideo());
        $this->assertEquals('https://www.youtube.com/embed/kuKb3VfcTWE', $highlight->getVideo());
        $this->assertIsString($highlight->getMiniature());
        $this->assertNotEmpty($highlight->getMiniature());
        $this->assertEquals('http://img.youtube.com/vi/kuKb3VfcTWE/hqdefault.jpg', $highlight->getMiniature());
    }

    /**
     * @covers \App\Entity\Uca\ShnuHighlight::setVideo
     */
    public function testSetVideoDailymotion(): void
    {
        $highlight = (new ShnuHighlight())->setVideo('https://www.dailymotion.com/video/x2gee6l');

        $this->assertIsString($highlight->getLecteurVideo());
        $this->assertNotEmpty($highlight->getLecteurVideo());
        $this->assertEquals('dailymotion', $highlight->getLecteurVideo());
        $this->assertIsString($highlight->getVideo());
        $this->assertNotEmpty($highlight->getVideo());
        $this->assertEquals('https://dailymotion.com/embed/video/x2gee6l', $highlight->getVideo());
        $this->assertIsString($highlight->getMiniature());
        $this->assertNotEmpty($highlight->getMiniature());
        $this->assertEquals('https://www.dailymotion.com/thumbnail/video/x2gee6l', $highlight->getMiniature());
    }

    /**
     * @covers \App\Entity\Uca\ShnuHighlight::setVideo
     */
    public function testSetVideoVimeo(): void
    {
        $highlight = (new ShnuHighlight())->setVideo('https://vimeo.com/277297974');

        $this->assertIsString($highlight->getLecteurVideo());
        $this->assertNotEmpty($highlight->getLecteurVideo());
        $this->assertEquals('vimeo', $highlight->getLecteurVideo());
        $this->assertIsString($highlight->getVideo());
        $this->assertNotEmpty($highlight->getVideo());
        $this->assertEquals('https://player.vimeo.com/video/277297974', $highlight->getVideo());
        $this->assertIsString($highlight->getMiniature());
        $this->assertNotEmpty($highlight->getMiniature());
    }

    /**
     * @covers \App\Entity\Uca\ShnuHighlight::setVideo
     */
    public function testSetVideoFacebook(): void
    {
        $highlight = (new ShnuHighlight())->setVideo('https://www.facebook.com/tn.developers/videos/1115569582144005');

        $this->assertIsString($highlight->getLecteurVideo());
        $this->assertNotEmpty($highlight->getLecteurVideo());
        $this->assertEquals('facebook', $highlight->getLecteurVideo());
        $this->assertIsString($highlight->getVideo());
        $this->assertNotEmpty($highlight->getVideo());
    }

    /**
     * @covers \App\Entity\Uca\ShnuHighlight::setVideo
     */
    public function testSetVideoInstagram(): void
    {
        $highlight = (new ShnuHighlight())->setVideo('https://www.instagram.com/p/CBDqjTqIpm4');

        $this->assertIsString($highlight->getLecteurVideo());
        $this->assertNotEmpty($highlight->getLecteurVideo());
        $this->assertEquals('instagram', $highlight->getLecteurVideo());
        $this->assertIsString($highlight->getVideo());
        $this->assertNotEmpty($highlight->getVideo());
        $this->assertEquals('http://instagram.com/p/CBDqjTqIpm4/embed', $highlight->getVideo());

        // Il se peut qu'il y ai une erreur sur ce test quand on lance plusieurs fois, il y a une vérification antispam d'instagram
    }

    /**
     * @covers \App\Entity\Uca\ShnuHighlight::setVideo
     */
    public function testSetVideo(): void
    {
        $highlight = (new ShnuHighlight())->setVideo('https://www.google.com');

        $this->assertNull($highlight->getLecteurVideo());
        $this->assertIsString($highlight->getVideo());
        $this->assertNotEmpty($highlight->getVideo());
        $this->assertEquals('https://www.google.com', $highlight->getVideo());
    }

    /**
     * @covers \App\Entity\Uca\ShnuHighlight::getIdVideo
     */
    public function testGetIdVideoYoutube(): void
    {
        $highlight = new ShnuHighlight();
        $idVideo = $highlight->getIdVideo('https://www.youtube.com/watch?v=kuKb3VfcTWE');

        $this->assertIsString($idVideo);
        $this->assertEquals('kuKb3VfcTWE', $idVideo);
    }

    /**
     * @covers \App\Entity\Uca\ShnuHighlight::getIdVideo
     */
    public function testGetIdVideoDailymotion(): void
    {
        $highlight = new ShnuHighlight();
        $idVideo = $highlight->getIdVideo('https://www.dailymotion.com/video/x2gee6l');

        $this->assertIsString($idVideo);
        $this->assertEquals('x2gee6l', $idVideo);
    }

    /**
     * @covers \App\Entity\Uca\ShnuHighlight::getIdVideo
     */
    public function testGetIdVideoVimeo(): void
    {
        $highlight = new ShnuHighlight();
        $idVideo = $highlight->getIdVideo('https://vimeo.com/277297974');

        $this->assertIsString($idVideo);
        $this->assertEquals('277297974', $idVideo);
    }

    /**
     * @covers \App\Entity\Uca\ShnuHighlight::getIdVideo
     */
    public function testGetIdVideoFacebook(): void
    {
        $highlight = new ShnuHighlight();
        $idVideo = $highlight->getIdVideo('https://www.facebook.com/tn.developers/videos/1115569582144005');

        $this->assertIsString($idVideo);
        $this->assertEquals('1115569582144005', $idVideo);
    }

    /**
     * @covers \App\Entity\Uca\ShnuHighlight::getIdVideo
     */
    public function testGetIdVideoInstagram(): void
    {
        $highlight = new ShnuHighlight();
        $idVideo = $highlight->getIdVideo('https://www.instagram.com/p/CBDqjTqIpm4');

        $this->assertIsString($idVideo);
        $this->assertEquals('CBDqjTqIpm4', $idVideo);
    }

    /**
     * @covers \App\Entity\Uca\ShnuHighlight::getDataFacebook
     */
    public function testGetDataFacebook(): void
    {
        $highlight = new ShnuHighlight();
        $datas = $highlight->getDataFacebook('https://www.facebook.com/tn.developers/videos/1115569582144005');

        $this->assertIsArray($datas);
        $this->assertEquals(2, sizeof($datas));
        $this->assertArrayHasKey('urlVideo', $datas);
        $this->assertArrayHasKey('urlThumb', $datas);
    }

    /**
     * @covers \App\Entity\Uca\ShnuHighlight::getDataInstagram
     */
    public function testGetDataInstagram(): void
    {
        $highlight = new ShnuHighlight();
        $datas = $highlight->getDataInstagram('https://www.instagram.com/p/CBDqjTqIpm4');

        $this->assertIsArray($datas);
        $this->assertEquals(1, sizeof($datas));
        $this->assertArrayHasKey('urlVideo', $datas);
    }
}
