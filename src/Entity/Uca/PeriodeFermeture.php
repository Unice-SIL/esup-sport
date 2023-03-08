<?php

namespace App\Entity\Uca;

use App\Repository\PeriodeFermetureRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=PeriodeFermetureRepository::class)
 * @Gedmo\Loggable
 */
class PeriodeFermeture implements Interfaces\JsonSerializable
{
    use Traits\JsonSerializable;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date")
     * @Gedmo\Versioned
     * @Assert\NotBlank(message="periodefermeture.datedeb.notblank")
     */
    private $dateDeb;

    /**
     * @ORM\Column(type="date")
     * @Gedmo\Versioned
     * @Assert\NotBlank(message="periodefermeture.datefin.notblank")
     */
    private $dateFin;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Gedmo\Versioned
     * @Assert\NotBlank(message="periodefermeture.description.notnull")
     */
    private $description;

    public function jsonSerializeProperties()
    {
        return ['dateDeb','dateFin','description'];
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
    public function getDateDeb(): ?\DateTimeInterface
    {
        return $this->dateDeb;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setDateDeb(?\DateTimeInterface $dateDeb): self
    {
        $this->dateDeb = $dateDeb;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setDateFin(?\DateTimeInterface $dateFin): self
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @codeCoverageIgnore
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }
}
