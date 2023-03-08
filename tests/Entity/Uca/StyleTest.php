<?php

namespace App\Tests\Entity\Uca;

use App\Entity\Uca\Style;
use PHPUnit\Framework\TestCase;

/**
 * @coversNothing
 */
class StyleTest extends TestCase
{
    /**
     * @covers App\Entity\Uca\Style::update
     */
    public function testUpdate(): void
    {
        $styleOne = (new Style())
            ->setPreview(true)
            ->setPrimaryColor("#46aed8")
            ->setPrimaryHover(0.8)
            ->setPrimaryShadow(0.2)
            ->setSecondaryColor("#424242")
            ->setSecondaryHover(0.2)
            ->setSecondaryShadow(0.8)
            ->setSuccessColor("#242400")
            ->setSuccessHover(0.7)
            ->setSuccessShadow(0.3)
            ->setWarningColor("#242000")
            ->setWarningHover(0.3)
            ->setWarningShadow(0.7)
            ->setDangerColor("#002000")
            ->setDangerHover(0.4)
            ->setDangerShadow(0.6)
            ->setNavbarBackgroundColor("#1a1a1a")
            ->setNavbarForegroundColor("#aaaaaa")
        ;

        $styleTwo = (new Style())
            ->setPreview(false)
            ->setPrimaryColor('#ffffff')
            ->setPrimaryHover(0)
            ->setPrimaryShadow(1)
            ->setSecondaryColor('#000000')
            ->setSecondaryHover(1)
            ->setSecondaryShadow(0)
            ->setSuccessColor("#040400")
            ->setSuccessHover(0.6)
            ->setSuccessShadow(0.4)
            ->setWarningColor("#040000")
            ->setWarningHover(0.4)
            ->setWarningShadow(0.6)
            ->setDangerColor("#004000")
            ->setDangerHover(0.3)
            ->setDangerShadow(0.7)
            ->setNavbarBackgroundColor("#a1a1a1")
            ->setNavbarForegroundColor("#cccccc")
        ;

        $styleTwo->update($styleOne);

        $this->assertFalse($styleTwo->isPreview());
        $this->assertEquals($styleOne->getPrimaryColor(), $styleTwo->getPrimaryColor());
        $this->assertEquals($styleOne->getPrimaryHover(), $styleTwo->getPrimaryHover());
        $this->assertEquals($styleOne->getPrimaryShadow(), $styleTwo->getPrimaryShadow());
        $this->assertEquals($styleOne->getSecondaryColor(), $styleTwo->getSecondaryColor());
        $this->assertEquals($styleOne->getSecondaryHover(), $styleTwo->getSecondaryHover());
        $this->assertEquals($styleOne->getSecondaryShadow(), $styleTwo->getSecondaryShadow());
        $this->assertEquals($styleOne->getNavbarBackgroundColor(), $styleTwo->getNavbarBackgroundColor());
        $this->assertEquals($styleOne->getNavbarForegroundColor(), $styleTwo->getNavbarForegroundColor());
    }
}
