<?php

namespace App\Service\Securite\Voter;

use App\Entity\Uca\Inscription;
use App\Entity\Uca\Utilisateur;
use App\Repository\InscriptionRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class InscriptionVoter extends Voter
{
    public const INSCRIPTION_PARTENAIRE = 'inscriptionPartenaire';

    private $inscriptionRepository;

    public function __construct(InscriptionRepository $inscriptionRepository)
    {
        $this->inscriptionRepository = $inscriptionRepository;
    }

    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, [self::INSCRIPTION_PARTENAIRE]) || !$subject instanceof Inscription) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();

        if (!$user instanceof Utilisateur) {
            return false;
        }

        if (self::INSCRIPTION_PARTENAIRE === $attribute) {
            return $this->isInscriptionPartenaireAuthorized($subject, $user);
        }

        throw new \LogicException('This code should not be reached');
    }

    /**
     * Fonction qui permet de savoir si un utilisateur est autorisé à cloner l'inscription.
     */
    private function isInscriptionPartenaireAuthorized(Inscription $inscription, Utilisateur $utilisateur): bool
    {
        // Si l'inscription ne nécessite pas de partenaire ou si l'utilisateur n'est pas dans la liste des partenaires ou si l'inscription n'a pas le bon statut
        if (null === $inscription->getListeEmailPartenaires() || !in_array($utilisateur->getEmail(), explode('|', $inscription->getListeEmailPartenaires())) || in_array($inscription->getStatut(), Inscription::STATUT_INVALIDE)) {
            return false;
        }

        // Il faut que l'utilisateur n'est pas d'inscription partenaire valide
        return 0 === $this->inscriptionRepository->getNbInscriptionPartenaireValide($inscription->getId(), $utilisateur->getId());
    }
}