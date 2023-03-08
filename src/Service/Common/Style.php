<?php

namespace App\Service\Common;

use App\Repository\StyleRepository;
use SSNepenthe\ColorUtils as ColorUtils;

class Style
{
    private $preview = false;
    private $styleRepo;

    /**
     * @codeCoverageIgnore
     */
    public function __construct(StyleRepository $styleRepo)
    {
        $this->styleRepo = $styleRepo;
    }

    public function saveStyle(): self
    {
        $previewed = $this->styleRepo->findOneBy(['preview' => true]);
        $style = $this->styleRepo->findOneBy(['preview' => false]);
        $style->update($previewed);
        $this->styleRepo->add($style, true);

        return $this;
    }

    public function getStyle()
    {
        return $this->styleRepo->findOneBy(['preview' => $this->preview]);
    }

    public function getNavbarColors()
    {
        return [
            'background' => $this->getStyle()->getNavbarBackgroundColor(),
            'foreground' => $this->getStyle()->getNavbarForegroundColor(),
        ];
    }

    public function getPrimaryColor()
    {
        return ColorUtils\color($this->getStyle()->getPrimaryColor())->getHsl();
    }

    public function getPrimaryColorHover()
    {
        $color = $this->getPrimaryColor();
        $lightness = $color->getLightness() + $this->getStyle()->getPrimaryHover() * 100;
        return $color->with(['lightness' => $lightness]);
    }

    public function getPrimaryColorShadow()
    {
        $color = $this->getPrimaryColor();
        $lightness = $color->getLightness() + $this->getStyle()->getPrimaryShadow() * 100;
        return $color->with(['lightness' => $lightness]);
    }

    public function getPrimaryColorWithAlpha(float $alpha)
    {
        return $this->getPrimaryColor()->with(['alpha' => $alpha]);
    }

    public function getPrimaryColorHoverWithAlpha(float $alpha)
    {
        return $this->getPrimaryColorHover()->with(['alpha' => $alpha]);
    }

    public function getPrimaryColorShadowWithAlpha(float $alpha)
    {
        return $this->getPrimaryColorShadow()->with(['alpha' => $alpha]);
    }

    public function getSuccessColor()
    {
        return ColorUtils\color($this->getStyle()->getSuccessColor())->getHsl();
    }

    public function getSuccessColorHover()
    {
        $color = $this->getSuccessColor();
        $lightness = $color->getLightness() + $this->getStyle()->getSuccessHover() * 100;
        return $color->with(['lightness' => $lightness]);
    }

    public function getSuccessColorShadow()
    {
        $color = $this->getSuccessColor();
        $lightness = $color->getLightness() + $this->getStyle()->getSuccessShadow() * 100;
        return $color->with(['lightness' => $lightness]);
    }

    public function getSuccessColorWithAlpha(float $alpha)
    {
        return $this->getSuccessColor()->with(['alpha' => $alpha]);
    }

    public function getSuccessColorHoverWithAlpha(float $alpha)
    {
        return $this->getSuccessColorHover()->with(['alpha' => $alpha]);
    }

    public function getSuccessColorShadowWithAlpha(float $alpha)
    {
        return $this->getSuccessColorShadow()->with(['alpha' => $alpha]);
    }

    public function getWarningColor()
    {
        return ColorUtils\color($this->getStyle()->getWarningColor())->getHsl();
    }

    public function getWarningColorHover()
    {
        $color = $this->getWarningColor();
        $lightness = $color->getLightness() + $this->getStyle()->getWarningHover() * 100;
        return $color->with(['lightness' => $lightness]);
    }

    public function getWarningColorShadow()
    {
        $color = $this->getWarningColor();
        $lightness = $color->getLightness() + $this->getStyle()->getWarningShadow() * 100;
        return $color->with(['lightness' => $lightness]);
    }

    public function getWarningColorWithAlpha(float $alpha)
    {
        return $this->getWarningColor()->with(['alpha' => $alpha]);
    }

    public function getWarningColorHoverWithAlpha(float $alpha)
    {
        return $this->getWarningColorHover()->with(['alpha' => $alpha]);
    }

    public function getWarningColorShadowWithAlpha(float $alpha)
    {
        return $this->getWarningColorShadow()->with(['alpha' => $alpha]);
    }

    public function getDangerColor()
    {
        return ColorUtils\color($this->getStyle()->getDangerColor())->getHsl();
    }

    public function getDangerColorHover()
    {
        $color = $this->getDangerColor();
        $lightness = $color->getLightness() + $this->getStyle()->getDangerHover() * 100;
        return $color->with(['lightness' => $lightness]);
    }

    public function getDangerColorShadow()
    {
        $color = $this->getDangerColor();
        $lightness = $color->getLightness() + $this->getStyle()->getDangerShadow() * 100;
        return $color->with(['lightness' => $lightness]);
    }

    public function getDangerColorWithAlpha(float $alpha)
    {
        return $this->getDangerColor()->with(['alpha' => $alpha]);
    }

    public function getDangerColorHoverWithAlpha(float $alpha)
    {
        return $this->getDangerColorHover()->with(['alpha' => $alpha]);
    }

    public function getDangerColorShadowWithAlpha(float $alpha)
    {
        return $this->getDangerColorShadow()->with(['alpha' => $alpha]);
    }


    public function getSecondaryColor()
    {
        return ColorUtils\color($this->getStyle()->getSecondaryColor())->getHsl();
    }

    public function getSecondaryColorHover()
    {
        $color = $this->getSecondaryColor();
        $lightness = $color->getLightness() + $this->getStyle()->getSecondaryHover() * 100;
        return $color->with(['lightness' => $lightness]);
    }

    public function getSecondaryColorShadow()
    {
        $color = $this->getSecondaryColor();
        $lightness = $color->getLightness() + $this->getStyle()->getSecondaryShadow() * 100;
        return $color->with(['lightness' => $lightness]);
    }

    public function getSecondaryColorWithAlpha(float $alpha)
    {
        return $this->getSecondaryColor()->with(['alpha' => $alpha]);
    }

    public function getSecondaryColorHoverWithAlpha(float $alpha)
    {
        return $this->getSecondaryColorHover()->with(['alpha' => $alpha]);
    }

    public function getSecondaryColorShadowWithAlpha(float $alpha)
    {
        return $this->getSecondaryColorShadow()->with(['alpha' => $alpha]);
    }

    /**
     * @codeCoverageIgnore
     */
    public function isPreview(): bool
    {
        return $this->preview;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setPreview(bool $preview): self
    {
        $this->preview = $preview;

        return $this;
    }
}
