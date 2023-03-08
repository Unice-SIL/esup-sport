<?php

namespace App\Tests\Controller\UcaGest\Parametrage;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use App\Entity\Uca\Email;
use App\Entity\Uca\Groupe;
use App\Entity\Uca\Utilisateur;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\RouterInterface;

class EmailControllerTest extends WebTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Client
     */
    private $client;

    /**
     * @var \App\Repository\Uca\EmailRepository
     */
    private $emailRepository;


    /**
     * @var RouterInterface
     */
    private $router;

    private $ids = [];

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get(EntityManagerInterface::class);
        $this->router = static::getContainer()->get(RouterInterface::class);
        $this->emailRepository = $this->em->getRepository(Email::class);

        $groupe_email = (new Groupe(
            'test_controleur_email',
            [
                'ROLE_GESTION_PARAMETRAGE',
            ]
        ))
            ->setLibelle('test_controleur_email');
        $this->em->persist($groupe_email);
        $user_email = (new Utilisateur())
        ->setEmail('user_gestionnaire@test.fr')
        ->setUsername('user_gestionnaire')
        ->setPassword('password')
        ->setCgvAcceptees(true)
        ->setEnabled(true)
        ->setRoles([])
        ->addGroup($groupe_email);

        $this->em->persist($user_email);
        $this->em->flush();
        $this->ids['groupe_email'] = $groupe_email->getId();
        $this->ids['user_email'] = $user_email->getId();
    }

    public function testListerEmails()
    {
        $this->client->loginUser($this->em->getRepository(Utilisateur::class)->find($this->ids['user_email']), 'app');
        $email = $this->emailRepository->findOneBy([]);
        $route = $this->router->generate('UcaGest_EmailModifier', ['id' => $email->getId()]);
        $this->client->request('GET', $route);
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function testModifierEmail()
    {
        $this->client->loginUser($this->em->getRepository(Utilisateur::class)->find($this->ids['user_email']), 'app');
        $email = $this->emailRepository->findOneBy([]);
        $route = $this->router->generate('UcaGest_EmailModifier', ['id' => $email->getId()]);
        $this->client->request('GET', $route);
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $csrfToken = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('ucabundle_password_forgotten');
        $crawler = $this->client->request(
            'POST',
            $this->router->generate('UcaGest_EmailModifier', ['id' => $email->getId()]),
            ['ucabundle_email' => ['subject' => 'Test objet',  'corps' => 'Test contenu','_token' => $csrfToken->getValue(), 'save' => '']],
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }
}
