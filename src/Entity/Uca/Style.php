<?php

namespace App\Entity\Uca;

use App\Repository\StyleRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=StyleRepository::class)
 * @Gedmo\Loggable
 */
class Style
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=7)
     * @Gedmo\Versioned
     * @Assert\NotBlank(
     *  message="style.color.notblank"
     * )
     * @Assert\CssColor(
     *  formats={Assert\CssColor::HEX_LONG},
     *  message="style.color.notvalid"
     * )
     */
    private $primaryColor;

    /**
     * @ORM\Column(type="boolean")
     */
    private $preview;

    /**
     * @ORM\Column(type="float")
     * @Gedmo\Versioned
     * @Assert\NotNull(
     *  message="style.hover.notnull"
     * )
     * @Assert\Range(
     *      min=-1,
     *      max=1,
     *      notInRangeMessage="style.hover.notinrange"
     * )
     */
    private $primaryHover;

    /**
     * @ORM\Column(type="float")
     * @Gedmo\Versioned
     * @Assert\NotNull(
     *  message="style.shadow.notnull"
     * )
     * @Assert\Range(
     *      min=-1,
     *      max=1,
     *      notInRangeMessage="style.shadow.notinrange"
     * )
     */
    private $primaryShadow;

    /**
     * @ORM\Column(type="string", length=7)
     * @Gedmo\Versioned
     * @Assert\NotBlank(
     *  message="style.color.notblank"
     * )
     * @Assert\CssColor(
     *  formats={Assert\CssColor::HEX_LONG},
     *  message="style.color.notvalid"
     * )
     */
    private $secondaryColor;

    /**
     * @ORM\Column(type="float")
     * @Gedmo\Versioned
     * @Assert\NotNull(
     *  message="style.hover.notnull"
     * )
     * @Assert\Range(
     *      min=-1,
     *      max=1,
     *      notInRangeMessage="style.hover.notinrange"
     * )
     */
    private $secondaryHover;

    /**
     * @ORM\Column(type="float")
     * @Gedmo\Versioned
     * @Assert\NotNull(
     *  message="style.shadow.notnull"
     * )
     * @Assert\Range(
     *      min=-1,
     *      max=1,
     *      notInRangeMessage="style.shadow.notinrange"
     * )
     */
    private $secondaryShadow;

    /**
     * @ORM\Column(type="string", length=7)
     * @Gedmo\Versioned
     * @Assert\NotBlank(
     *  message="style.color.notblank"
     * )
     * @Assert\CssColor(
     *  formats={Assert\CssColor::HEX_LONG},
     *  message="style.color.notvalid"
     * )
     */
    private $successColor;

    /**
     * @ORM\Column(type="float")
     * @Gedmo\Versioned
     * @Assert\NotNull(
     *  message="style.hover.notnull"
     * )
     * @Assert\Range(
     *      min=-1,
     *      max=1,
     *      notInRangeMessage="style.hover.notinrange"
     * )
     */
    private $successHover;

    /**
     * @ORM\Column(type="float")
     * @Gedmo\Versioned
     * @Assert\NotNull(
     *  message="style.shadow.notnull"
     * )
     * @Assert\Range(
     *      min=-1,
     *      max=1,
     *      notInRangeMessage="style.shadow.notinrange"
     * )
     */
    private $successShadow;

    /**
     * @ORM\Column(type="string", length=7)
     * @Gedmo\Versioned
     * @Assert\NotBlank(
     *  message="style.color.notblank"
     * )
     * @Assert\CssColor(
     *  formats={Assert\CssColor::HEX_LONG},
     *  message="style.color.notvalid"
     * )
     */
    private $warningColor;

    /**
     * @ORM\Column(type="float")
     * @Gedmo\Versioned
     * @Assert\NotNull(
     *  message="style.hover.notnull"
     * )
     * @Assert\Range(
     *      min=-1,
     *      max=1,
     *      notInRangeMessage="style.hover.notinrange"
     * )
     */
    private $warningHover;

    /**
     * @ORM\Column(type="float")
     * @Gedmo\Versioned
     * @Assert\NotNull(
     *  message="style.shadow.notnull"
     * )
     * @Assert\Range(
     *      min=-1,
     *      max=1,
     *      notInRangeMessage="style.shadow.notinrange"
     * )
     */
    private $warningShadow;

    /**
     * @ORM\Column(type="string", length=7)
     * @Gedmo\Versioned
     * @Assert\NotBlank(
     *  message="style.color.notblank"
     * )
     * @Assert\CssColor(
     *  formats={Assert\CssColor::HEX_LONG},
     *  message="style.color.notvalid"
     * )
     */
    private $dangerColor;

    /**
     * @ORM\Column(type="float")
     * @Gedmo\Versioned
     * @Assert\NotNull(
     *  message="style.hover.notnull"
     * )
     * @Assert\Range(
     *      min=-1,
     *      max=1,
     *      notInRangeMessage="style.hover.notinrange"
     * )
     */
    private $dangerHover;

    /**
     * @ORM\Column(type="float")
     * @Gedmo\Versioned
     * @Assert\NotNull(
     *  message="style.shadow.notnull"
     * )
     * @Assert\Range(
     *      min=-1,
     *      max=1,
     *      notInRangeMessage="style.shadow.notinrange"
     * )
     */
    private $dangerShadow;

    /**
     * @ORM\Column(type="string", length=7)
     * @Gedmo\Versioned
     * @Assert\NotBlank(
     *  message="style.color.notblank"
     * )
     * @Assert\CssColor(
     *  formats={Assert\CssColor::HEX_LONG},
     *  message="style.color.notvalid"
     * )
     */
    private $navbarBackgroundColor;

    /**
     * @ORM\Column(type="string", length=7)
     * @Gedmo\Versioned
     * @Assert\NotBlank(
     *  message="style.color.notblank"
     * )
     * @Assert\CssColor(
     *  formats={Assert\CssColor::HEX_LONG},
     *  message="style.color.notvalid"
     * )
     */
    private $navbarForegroundColor;

    public function update(self $data): self
    {
        $this->primaryColor = $data->getPrimaryColor();
        $this->primaryHover = $data->getPrimaryHover();
        $this->primaryShadow = $data->getPrimaryShadow();
        $this->secondaryColor = $data->getSecondaryColor();
        $this->secondaryHover = $data->getSecondaryHover();
        $this->secondaryShadow = $data->getSecondaryShadow();
        $this->successColor = $data->getSuccessColor();
        $this->successHover = $data->getSuccessHover();
        $this->successShadow = $data->getSuccessShadow();
        $this->warningColor = $data->getWarningColor();
        $this->warningHover = $data->getWarningHover();
        $this->warningShadow = $data->getWarningShadow();
        $this->dangerColor = $data->getDangerColor();
        $this->dangerHover = $data->getDangerHover();
        $this->dangerShadow = $data->getDangerShadow();
        $this->navbarBackgroundColor = $data->getNavbarBackgroundColor();
        $this->navbarForegroundColor = $data->getNavbarForegroundColor();

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getPrimaryColor(): ?string
    {
        return $this->primaryColor;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setPrimaryColor(?string $primaryColor): self
    {
        $this->primaryColor = $primaryColor;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function isPreview(): ?bool
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

    /**
     * @codeCoverageIgnore
     */
    public function getPrimaryHover(): ?float
    {
        return $this->primaryHover;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setPrimaryHover(?float $primaryHover): self
    {
        $this->primaryHover = $primaryHover;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getPrimaryShadow(): ?float
    {
        return $this->primaryShadow;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setPrimaryShadow(?float $primaryShadow): self
    {
        $this->primaryShadow = $primaryShadow;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getSecondaryColor(): ?string
    {
        return $this->secondaryColor;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setSecondaryColor(?string $secondaryColor): self
    {
        $this->secondaryColor = $secondaryColor;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getSecondaryHover(): ?float
    {
        return $this->secondaryHover;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setSecondaryHover(?float $secondaryHover): self
    {
        $this->secondaryHover = $secondaryHover;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getSecondaryShadow(): ?float
    {
        return $this->secondaryShadow;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setSecondaryShadow(?float $secondaryShadow): self
    {
        $this->secondaryShadow = $secondaryShadow;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getNavbarBackgroundColor(): ?string
    {
        return $this->navbarBackgroundColor;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setNavbarBackgroundColor(?string $navbarBackgroundColor): self
    {
        $this->navbarBackgroundColor = $navbarBackgroundColor;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getNavbarForegroundColor(): ?string
    {
        return $this->navbarForegroundColor;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setNavbarForegroundColor(?string $navbarForeroundColor): self
    {
        $this->navbarForegroundColor = $navbarForeroundColor;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getSuccessColor()
    {
        return $this->successColor;
    }

    /**
     * @codeCoverageIgnore
     *
     * @return  self
     */
    public function setSuccessColor($successColor)
    {
        $this->successColor = $successColor;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getSuccessHover()
    {
        return $this->successHover;
    }

    /**
     * @codeCoverageIgnore
     *
     * @return  self
     */
    public function setSuccessHover($successHover)
    {
        $this->successHover = $successHover;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getSuccessShadow()
    {
        return $this->successShadow;
    }

    /**
     * @codeCoverageIgnore
     *
     * @return  self
     */
    public function setSuccessShadow($successShadow)
    {
        $this->successShadow = $successShadow;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getWarningShadow()
    {
        return $this->warningShadow;
    }

    /**
     * @codeCoverageIgnore
     *
     * @return  self
     */
    public function setWarningShadow($warningShadow)
    {
        $this->warningShadow = $warningShadow;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getWarningHover()
    {
        return $this->warningHover;
    }

    /**
     * @codeCoverageIgnore
     *
     * @return  self
     */
    public function setWarningHover($warningHover)
    {
        $this->warningHover = $warningHover;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getWarningColor()
    {
        return $this->warningColor;
    }

    /**
     * @codeCoverageIgnore
     *
     * @return  self
     */
    public function setWarningColor($warningColor)
    {
        $this->warningColor = $warningColor;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDangerColor()
    {
        return $this->dangerColor;
    }

    /**
     * @codeCoverageIgnore
     *
     * @return  self
     */
    public function setDangerColor($dangerColor)
    {
        $this->dangerColor = $dangerColor;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDangerHover()
    {
        return $this->dangerHover;
    }

    /**
     * @codeCoverageIgnore
     *
     * @return  self
     */
    public function setDangerHover($dangerHover)
    {
        $this->dangerHover = $dangerHover;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDangerShadow()
    {
        return $this->dangerShadow;
    }

    /**
     * @codeCoverageIgnore
     *
     * @return  self
     */
    public function setDangerShadow($dangerShadow)
    {
        $this->dangerShadow = $dangerShadow;

        return $this;
    }
}
