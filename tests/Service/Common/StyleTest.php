<?php

namespace App\Tests\Service\Common;

use App\Entity\Uca\Style as StyleEntity;
use App\Service\Common\Style as StyleService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @coversNothing
 */
class StyleTest extends KernelTestCase
{
    private $service;

    protected function setUp(): void
    {
        $this->service = self::getContainer()->get(StyleService::class);
    }

    /**
     * @covers App\Service\Common\Style::saveStyle
     */
    public function testSaveStyle(): void
    {
        $response = $this->service->saveStyle();
        $this->assertInstanceOf(StyleService::class, $response);
    }

    /**
     * @covers App\Service\Common\Style::getStyle
     */
    public function testGetStyle(): void
    {
        $style = $this->service->getStyle();
        $this->assertInstanceOf(StyleEntity::class, $style);
        $this->assertFalse($style->isPreview());
    }

    /**
     * @covers App\Service\Common\Style::getStyle
     */
    public function testGetStylePreview(): void
    {
        $this->service->setPreview(true);
        $style = $this->service->getStyle();
        $this->assertInstanceOf(StyleEntity::class, $style);
        $this->assertTrue($style->isPreview());
    }

    /**
     * @covers App\Service\Common\Style::getDangerColor
     */
    public function testGetDangerColor(): void
    {
        $color = $this->service->getDangerColor();

        $this->assertStringStartsWith('hsl(', $color);
    }

    /**
     * @covers App\Service\Common\Style::getDangerColorHover
     */
    public function testGetDangerColorHover(): void
    {
        $color = $this->service->getDangerColorHover();

        $this->assertStringStartsWith('hsl(', $color);
    }

    /**
     * @covers App\Service\Common\Style::getDangerColorShadow
     */
    public function testGetDangerColorShadow(): void
    {
        $color = $this->service->getSuccessColorShadow();

        $this->assertStringStartsWith('hsl(', $color);
    }

    /**
     * @covers App\Service\Common\Style::getDangerColorWithAlpha
     */
    public function testGetDangerColorWithAlpha(): void
    {
        $color = $this->service->getDangerColorWithAlpha(0.5);

        $this->assertStringStartsWith('hsla(', $color);
        $this->assertStringEndsWith(', 0.5)', $color);
    }

    /**
     * @covers App\Service\Common\Style::getDangerColorHoverWithAlpha
     */
    public function testGetDangerColorHoverWithAlpha(): void
    {
        $color = $this->service->getDangerColorHoverWithAlpha(0.5);

        $this->assertStringStartsWith('hsla(', $color);
        $this->assertStringEndsWith(', 0.5)', $color);
    }

    /**
     * @covers App\Service\Common\Style::getDangerColorShadowWithAlpha
     */
    public function testGetDangerColorShadowWithAlpha(): void
    {
        $color = $this->service->getDangerColorShadowWithAlpha(0.5);

        $this->assertStringStartsWith('hsla(', $color);
        $this->assertStringEndsWith(', 0.5)', $color);
    }

    /**
     * @covers App\Service\Common\Style::getWarningColor
     */
    public function testGetWarningColor(): void
    {
        $color = $this->service->getSuccessColor();

        $this->assertStringStartsWith('hsl(', $color);
    }

    /**
     * @covers App\Service\Common\Style::getWarningColorHover
     */
    public function testGetWarningColorHover(): void
    {
        $color = $this->service->getWarningColorHover();

        $this->assertStringStartsWith('hsl(', $color);
    }

    /**
     * @covers App\Service\Common\Style::getWarningColorShadow
     */
    public function testGetWarningColorShadow(): void
    {
        $color = $this->service->getWarningColorShadow();

        $this->assertStringStartsWith('hsl(', $color);
    }

    /**
     * @covers App\Service\Common\Style::getWarningColorWithAlpha
     */
    public function testGetWarningColorWithAlpha(): void
    {
        $color = $this->service->getWarningColorWithAlpha(0.5);

        $this->assertStringStartsWith('hsla(', $color);
        $this->assertStringEndsWith(', 0.5)', $color);
    }

    /**
     * @covers App\Service\Common\Style::getWarningColorHoverWithAlpha
     */
    public function testGetWarningColorHoverWithAlpha(): void
    {
        $color = $this->service->getWarningColorHoverWithAlpha(0.5);

        $this->assertStringStartsWith('hsla(', $color);
        $this->assertStringEndsWith(', 0.5)', $color);
    }

    /**
     * @covers App\Service\Common\Style::getWarningColorShadowWithAlpha
     */
    public function testGetWarningColorShadowWithAlpha(): void
    {
        $color = $this->service->getWarningColorShadowWithAlpha(0.5);

        $this->assertStringStartsWith('hsla(', $color);
        $this->assertStringEndsWith(', 0.5)', $color);
    }

    /**
     * @covers App\Service\Common\Style::getSuccessColor
     */
    public function testGetSuccessColor(): void
    {
        $color = $this->service->getSuccessColor();

        $this->assertStringStartsWith('hsl(', $color);
    }

    /**
     * @covers App\Service\Common\Style::getSuccessColorHover
     */
    public function testGetSuccessColorHover(): void
    {
        $color = $this->service->getSuccessColorHover();

        $this->assertStringStartsWith('hsl(', $color);
    }

    /**
     * @covers App\Service\Common\Style::getSuccessColorShadow
     */
    public function testGetSuccessColorShadow(): void
    {
        $color = $this->service->getSuccessColorShadow();

        $this->assertStringStartsWith('hsl(', $color);
    }

    /**
     * @covers App\Service\Common\Style::getSuccessColorWithAlpha
     */
    public function testGetSuccessColorWithAlpha(): void
    {
        $color = $this->service->getSuccessColorWithAlpha(0.5);

        $this->assertStringStartsWith('hsla(', $color);
        $this->assertStringEndsWith(', 0.5)', $color);
    }

    /**
     * @covers App\Service\Common\Style::getSuccessColorHoverWithAlpha
     */
    public function testGetSuccessColorHoverWithAlpha(): void
    {
        $color = $this->service->getSuccessColorHoverWithAlpha(0.5);

        $this->assertStringStartsWith('hsla(', $color);
        $this->assertStringEndsWith(', 0.5)', $color);
    }

    /**
     * @covers App\Service\Common\Style::getSuccessColorShadowWithAlpha
     */
    public function testGetSuccessColorShadowWithAlpha(): void
    {
        $color = $this->service->getSuccessColorShadowWithAlpha(0.5);

        $this->assertStringStartsWith('hsla(', $color);
        $this->assertStringEndsWith(', 0.5)', $color);
    }

    /**
     * @covers App\Service\Common\Style::getPrimaryColor
     */
    public function testGetPrimaryColor(): void
    {
        $color = $this->service->getPrimaryColor();

        $this->assertStringStartsWith('hsl(', $color);
    }

    /**
     * @covers App\Service\Common\Style::getPrimaryColorHover
     */
    public function testGetPrimaryColorHover(): void
    {
        $color = $this->service->getPrimaryColorHover();

        $this->assertStringStartsWith('hsl(', $color);
    }

    /**
     * @covers App\Service\Common\Style::getPrimaryColorShadow
     */
    public function testGetPrimaryColorShadow(): void
    {
        $color = $this->service->getPrimaryColorShadow();

        $this->assertStringStartsWith('hsl(', $color);
    }

    /**
     * @covers App\Service\Common\Style::getPrimaryColorWithAlpha
     */
    public function testGetPrimaryColorWithAlpha(): void
    {
        $color = $this->service->getPrimaryColorWithAlpha(0.5);

        $this->assertStringStartsWith('hsla(', $color);
        $this->assertStringEndsWith(', 0.5)', $color);
    }

    /**
     * @covers App\Service\Common\Style::getPrimaryColorHoverWithAlpha
     */
    public function testGetPrimaryColorHoverWithAlpha(): void
    {
        $color = $this->service->getPrimaryColorHoverWithAlpha(0.5);

        $this->assertStringStartsWith('hsla(', $color);
        $this->assertStringEndsWith(', 0.5)', $color);
    }

    /**
     * @covers App\Service\Common\Style::getPrimaryColorShadowWithAlpha
     */
    public function testGetPrimaryColorShadowWithAlpha(): void
    {
        $color = $this->service->getPrimaryColorShadowWithAlpha(0.5);

        $this->assertStringStartsWith('hsla(', $color);
        $this->assertStringEndsWith(', 0.5)', $color);
    }

    /**
     * @covers App\Service\Common\Style::getSecondaryColor
     */
    public function testGetSecondaryColor(): void
    {
        $color = $this->service->getSecondaryColor();

        $this->assertStringStartsWith('hsl(', $color);
    }

    /**
     * @covers App\Service\Common\Style::getSecondaryColorHover
     */
    public function testGetSecondaryColorHover(): void
    {
        $color = $this->service->getSecondaryColorHover();

        $this->assertStringStartsWith('hsl(', $color);
    }

    /**
     * @covers App\Service\Common\Style::getSecondaryColorShadow
     */
    public function testGetSecondaryColorShadow(): void
    {
        $color = $this->service->getSecondaryColorShadow();

        $this->assertStringStartsWith('hsl(', $color);
    }

    /**
     * @covers App\Service\Common\Style::getSecondaryColorWithAlpha
     */
    public function testGetSecondaryColorWithAlpha(): void
    {
        $color = $this->service->getSecondaryColorWithAlpha(0.5);

        $this->assertStringStartsWith('hsla(', $color);
        $this->assertStringEndsWith(', 0.5)', $color);
    }

    /**
     * @covers App\Service\Common\Style::getSecondaryColorHoverWithAlpha
     */
    public function testGetSecondaryColorHoverWithAlpha(): void
    {
        $color = $this->service->getSecondaryColorHoverWithAlpha(0.5);

        $this->assertStringStartsWith('hsla(', $color);
        $this->assertStringEndsWith(', 0.5)', $color);
    }

    /**
     * @covers App\Service\Common\Style::getSecondaryColorShadowWithAlpha
     */
    public function testGetSecondaryColorShadowWithAlpha(): void
    {
        $color = $this->service->getSecondaryColorShadowWithAlpha(0.5);

        $this->assertStringStartsWith('hsla(', $color);
        $this->assertStringEndsWith(', 0.5)', $color);
    }

    public function testGetNavbarColors(): void
    {
        $colors = $this->service->getNavbarColors();

        $this->assertIsArray($colors);
        $this->assertArrayHasKey('background', $colors);
        $this->assertArrayHasKey('foreground', $colors);
        $this->assertMatchesRegularExpression("/#[0-9A-Fa-f]{6}/", $colors["background"]);
        $this->assertMatchesRegularExpression("/#[0-9A-Fa-f]{6}/", $colors["foreground"]);
    }
}
