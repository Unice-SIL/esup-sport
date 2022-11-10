<?php

/*
 * Interface - Article:
 *
 * Permet d'implémenter les méhtodes nécessaire à la gestion des commandes.
*/

namespace App\Entity\Uca\Interfaces;

interface Article
{
    //public function getInscriptions();
    public function getArticleAutorisations();

    public function getArticleDateDebut();

    public function getArticleDateFin();

    public function getArticleDescription();

    public function getArticleLibelle();

    public function getArticleMontant($user);

    public function getArticleTva($user);

    public function getArticleType();

    public function getCapacite();

    public function getEncadrants();

    public function getTarif();

    public function isFull($usr, $format);
}
