<?php

namespace App\Entity\Uca;

use App\Repository\FormatActiviteNiveauSportifRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=FormatActiviteNiveauSportifRepository::class)
 * @Gedmo\Loggable
 * @UniqueEntity(fields={"formatActivite","niveauSportif"}, message="niveausportifFormat.uniqueentity")
 */
class FormatActiviteNiveauSportif implements \App\Entity\Uca\Interfaces\JsonSerializable
{
    use \App\Entity\Uca\Traits\JsonSerializable;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=FormatActivite::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $formatActivite;

    /**
     * @ORM\ManyToOne(targetEntity=NiveauSportif::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $niveauSportif;

    /**
     * @ORM\Column(type="text")
     */
    private $detail;

    /**
     * @codeCoverageIgnore
     */
    public function __construct(FormatActivite $formatActivite, NiveauSportif $niveauSportif, ?string $detail = null)
    {
        $this->formatActivite = $formatActivite;
        $this->niveauSportif = $niveauSportif;
        $this->detail = $detail;
    }

    public function jsonSerializeProperties()
    {
        return [
            'formatActivite' => 'formatActivite',
            'niveauSportif' => 'niveauSportif',
            'detail' => 'detail',
            'libelle' => $this->getNiveauSportif()->getLibelle(),
        ];
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
    public function getFormatActivite(): ?FormatActivite
    {
        return $this->formatActivite;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setFormatActivite(?FormatActivite $formatActivite): self
    {
        $this->formatActivite = $formatActivite;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getNiveauSportif(): ?NiveauSportif
    {
        return $this->niveauSportif;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setNiveauSportif(?NiveauSportif $niveauSportif): self
    {
        $this->niveauSportif = $niveauSportif;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDetail(): ?string
    {
        return $this->detail;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setDetail(string $detail): self
    {
        $this->detail = $detail;

        return $this;
    }
}
