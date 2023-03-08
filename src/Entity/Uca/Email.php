<?php

/*
 * Classe - Email:
 *
 * Entité correspondant aux Emails
 * Ces éléments sont organisables.
*/

namespace App\Entity\Uca;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;
use App\Annotations\CKEditor;
/**
 * @ORM\Table(name="email")
 * @ORM\Entity(repositoryClass="App\Repository\EmailRepository")
 * @Gedmo\Loggable
 */
class Email
{
    //region Propriétés

    /**
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @ORM\Column(type="text", nullable=false) 
     * @Assert\NotNull(message="activite.classeactivite.notnull") 
     * @Gedmo\Versioned
     * 
     * @CKEditor
     */
    private $corps;

    /** @ORM\Column(type="string", length=255, nullable=false)
     * @Assert\NotNull(message="activite.classeactivite.notnull") 
     */
    private $subject;

     /** @ORM\Column(type="string", length=255, nullable=false)
     * @Assert\NotNull(message="activite.classeactivite.notnull") 
     */
    private $nom;

    //endregion

    //region Méthodes

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCorps(): ?string
    {
        return $this->corps;
    }

    public function setCorps(string $corps): self
    {
        $this->corps = $corps;

        return $this;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(string $subject): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

}