<?php

namespace App\Tests\Service\Securite\Voter;

use App\Entity\Uca\FormatSimple;
use App\Entity\Uca\Inscription;
use App\Entity\Uca\Utilisateur;
use App\Repository\UtilisateurRepository;
use App\Service\Securite\Voter\InscriptionVoter;
use DateTime;
use LogicException;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @internal
 * @coversNothing
 */
class InscriptionVoterTest extends KernelTestCase
{
    /**
     * @var InscriptionVoter
     */
    private $voter;

    protected function setUp(): void
    {
        $this->voter = static::getContainer()->get(InscriptionVoter::class);
    }

    /**
     * @covers \App\Service\Securite\Voter\InscriptionVoter::__construct
     */
    public function testConstruct(): void
    {
        $this->assertInstanceOf(InscriptionVoter::class, $this->voter);
    }

    /**
     * @covers \App\Service\Securite\Voter\InscriptionVoter::supports
     */
    public function testSupports(): void
    {
        $class = new ReflectionClass(InscriptionVoter::class);
        $method = $class->getMethod('supports');
        $method->setAccessible(true);

        $formatSimple = (new FormatSimple())
            ->setDateDebutEffective(new DateTime())
            ->setDateFinEffective(new DateTime())
        ;

        $inscription = (new Inscription(
            $formatSimple,
            new Utilisateur(),
            ['typeInscription' => 'format']
        ));

        $supports = $method->invokeArgs($this->voter, ['inscriptionPartenaire', $inscription]);

        $this->assertTrue($supports);
    }

    /**
     * @covers \App\Service\Securite\Voter\InscriptionVoter::supports
     */
    public function testSupportsBadAttribute(): void
    {
        $class = new ReflectionClass(InscriptionVoter::class);
        $method = $class->getMethod('supports');
        $method->setAccessible(true);

        $formatSimple = (new FormatSimple())
            ->setDateDebutEffective(new DateTime())
            ->setDateFinEffective(new DateTime())
        ;

        $inscription = (new Inscription(
            $formatSimple,
            new Utilisateur(),
            ['typeInscription' => 'format']
        ));

        $supports = $method->invokeArgs($this->voter, ['inscription', $inscription]);

        $this->assertFalse($supports);
    }

    /**
     * @covers \App\Service\Securite\Voter\InscriptionVoter::supports
     */
    public function testSupportsBadSubject(): void
    {
        $class = new ReflectionClass(InscriptionVoter::class);
        $method = $class->getMethod('supports');
        $method->setAccessible(true);

        $supports = $method->invokeArgs($this->voter, ['inscriptionPartenaire', new DateTime()]);

        $this->assertFalse($supports);
    }

    /**
     * @covers \App\Service\Securite\Voter\InscriptionVoter::isInscriptionPartenaireAuthorized
     * @covers \App\Service\Securite\Voter\InscriptionVoter::voteOnAttribute
     */
    public function testVoteOnAttribute(): void
    {
        $class = new ReflectionClass(InscriptionVoter::class);
        $method = $class->getMethod('voteOnAttribute');
        $method->setAccessible(true);

        $formatSimple = (new FormatSimple())
            ->setDateDebutEffective(new DateTime())
            ->setDateFinEffective(new DateTime())
        ;

        $inscription = (new Inscription(
            $formatSimple,
            new Utilisateur(),
            ['typeInscription' => 'format']
        ));

        $user = static::getContainer()->get(UtilisateurRepository::class)->findOneByUsername('admin');
        $token = (new Token())->setUser($user);

        $supports = $method->invokeArgs($this->voter, ['inscriptionPartenaire', $inscription, $token]);

        $this->assertFalse($supports);
    }

    /**
     * @covers \App\Service\Securite\Voter\InscriptionVoter::isInscriptionPartenaireAuthorized
     * @covers \App\Service\Securite\Voter\InscriptionVoter::voteOnAttribute
     */
    public function testVoteOnAttributeAuthorized(): void
    {
        $class = new ReflectionClass(InscriptionVoter::class);
        $method = $class->getMethod('voteOnAttribute');
        $method->setAccessible(true);

        $formatSimple = (new FormatSimple())
            ->setDateDebutEffective(new DateTime())
            ->setDateFinEffective(new DateTime())
        ;

        $inscription = (new Inscription(
            $formatSimple,
            new Utilisateur(),
            ['typeInscription' => 'format']
        ))->setListeEmailPartenaires('admin@uca.fr');

        $user = static::getContainer()->get(UtilisateurRepository::class)->findOneByUsername('admin');
        $token = (new Token())->setUser($user);

        $supports = $method->invokeArgs($this->voter, ['inscriptionPartenaire', $inscription, $token]);

        $this->assertTrue($supports);
    }

    /**
     * @covers \App\Service\Securite\Voter\InscriptionVoter::voteOnAttribute
     */
    public function testVoteOnAttributeNoUser(): void
    {
        $class = new ReflectionClass(InscriptionVoter::class);
        $method = $class->getMethod('voteOnAttribute');
        $method->setAccessible(true);

        $token = new Token();

        $supports = $method->invokeArgs($this->voter, ['inscriptionPartenaire', null, $token]);

        $this->assertFalse($supports);
    }

    /**
     * @covers \App\Service\Securite\Voter\InscriptionVoter::voteOnAttribute
     */
    public function testVoteOnAttributeException(): void
    {
        $class = new ReflectionClass(InscriptionVoter::class);
        $method = $class->getMethod('voteOnAttribute');
        $method->setAccessible(true);

        $user = static::getContainer()->get(UtilisateurRepository::class)->findOneByUsername('admin');
        $token = (new Token())->setUser($user);

        $this->expectException(LogicException::class);
        $supports = $method->invokeArgs($this->voter, ['inscription', null, $token]);
    }
}

// Classe qui nous permet de simuler un token de sécurité pour tester le voter
// a voir pour utiliser une autre méthode
class Token implements TokenInterface
{
    private $user;

    /**
     * Returns a string representation of the Token.
     *
     * This is only to be used for debugging purposes.
     *
     * @return string
     */
    public function __toString()
    {
        return '';
    }

    /**
     * Returns all the necessary state of the object for serialization purposes.
     */
    public function __serialize(): array
    {
        return [];
    }

    /**
     * Restores the object state from an array given by __serialize().
     */
    public function __unserialize(array $data): void
    {
    }

    public function getUserIdentifier()
    {
    }

    /**
     * Returns all the necessary state of the object for serialization purposes.
     */
    public function serialize(): ?string
    {
        return null;
    }

    /**
     * Restores the object state from an array given by serialize().
     *
     * @param mixed $serialized
     */
    public function unserialize($serialized)
    {
    }

    /**
     * Returns the user roles.
     *
     * @return string[]
     */
    public function getRoleNames(): array
    {
        return [];
    }

    /**
     * Returns the user credentials.
     *
     * @return mixed
     *
     * @deprecated since Symfony 5.4
     */
    public function getCredentials()
    {
    }

    /**
     * Returns a user representation.
     *
     * @return null|UserInterface
     *
     * @see AbstractToken::setUser()
     */
    public function getUser(): ?Utilisateur
    {
        return $this->user;
    }

    /**
     * Sets the authenticated user in the token.
     *
     * @param UserInterface $user
     *
     * @throws \InvalidArgumentException
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Returns whether the user is authenticated or not.
     *
     * @return bool true if the token has been authenticated, false otherwise
     *
     * @deprecated since Symfony 5.4, return null from "getUser()" instead when a token is not authenticated
     */
    public function isAuthenticated()
    {
        return (bool) $this->user;
    }

    /**
     * Sets the authenticated flag.
     *
     * @deprecated since Symfony 5.4
     */
    public function setAuthenticated(bool $isAuthenticated)
    {
    }

    /**
     * Removes sensitive information from the token.
     */
    public function eraseCredentials()
    {
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return [];
    }

    /**
     * @param array $attributes The token attributes
     */
    public function setAttributes(array $attributes)
    {
    }

    /**
     * @return bool
     */
    public function hasAttribute(string $name)
    {
        return false;
    }

    /**
     * @throws \InvalidArgumentException When attribute doesn't exist for this token
     *
     * @return mixed
     */
    public function getAttribute(string $name)
    {
    }

    /**
     * @param mixed $value The attribute value
     */
    public function setAttribute(string $name, $value)
    {
    }

    /**
     * @return string
     *
     * @deprecated since Symfony 5.3, use getUserIdentifier() instead
     */
    public function getUsername()
    {
        return '';
    }
}