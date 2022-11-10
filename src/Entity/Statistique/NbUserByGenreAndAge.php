<?php

/*
 * Classe - NbUserByGenreAndAge:
 *
 * Données sur le nombre d'utilisateur actifs par genre et age
*/

namespace App\Entity\Statistique;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="nb_user_by_genre_and_age")
 * @ORM\Entity(repositoryClass="App\Repository\NbUserByGenreAndAgeRepository")
 */
class NbUserByGenreAndAge
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /** @ORM\Column(name="age", type="string", length=255, nullable=true)*/
    private $age;

    /** @ORM\Column(name="genre", type="string", length=255)*/
    private $genre;

    /** @ORM\Column(name="nombre_user", type="integer", length=11) */
    private $nombreUser;

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set age.
     *
     * @param string $age
     *
     * @return NbUserByElement
     */
    public function setAge($age)
    {
        $this->age = $age;

        return $this;
    }

    /**
     * Get age.
     *
     * @return string
     */
    public function getAge()
    {
        return $this->age;
    }

    /**
     * Set genre.
     *
     * @param string $genre
     *
     * @return NbUserByElement
     */
    public function setGenre($genre)
    {
        $this->genre = $genre;

        return $this;
    }

    /**
     * Get genre.
     *
     * @return string
     */
    public function getGenre()
    {
        return $this->genre;
    }

    /**
     * Set nombreUser.
     *
     * @param mixed $nombreUser
     *
     * @return NbUserByElement
     */
    public function setNombreUser($nombreUser)
    {
        $this->nombreUser = $nombreUser;

        return $this;
    }

    /**
     * Get nombreUser.
     *
     * @return \int
     */
    public function getNombreUser()
    {
        return $this->nombreUser;
    }
}
