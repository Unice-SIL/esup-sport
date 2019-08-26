<?php

namespace UcaBundle\Entity\Interfaces;

interface Article
{
    //public function getInscriptions();
    public function getArticleAutorisations();
    public function getArticleDescription();
    public function getArticleLibelle();
    public function getArticleMontant($user);
    public function getArticleTva($user);
    public function getCapacite();
    public function getEncadrants();
    public function getTarif();
    public function isDisponible($user);
    public function isFull();
    public function userIsInscrit($user);
}
