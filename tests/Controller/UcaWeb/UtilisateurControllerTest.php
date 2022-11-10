<?php

namespace App\Tests\Controller\UcaWeb;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Uca\ProfilUtilisateur;
use App\Entity\Uca\Utilisateur;

/**
 * @internal
 * @coversNothing
 */
class UtilisateurControllerTest extends WebTestCase
{
    private $client;

    private $em;

    private $router;

    private $ids = [];

    protected function setUp(): void
    {
        $this->client = static::createClient();

        $this->router = static::getContainer()->get(RouterInterface::class);

        $this->em = static::getContainer()->get(EntityManagerInterface::class);

        $profilUtilisateur = (new ProfilUtilisateur())
            ->setLibelle("Testeur")
            ->setPreinscription(true)
            ->setNbMaxInscriptions(100)
            ->setNbMaxInscriptionsRessource(100)
        ;
        $this->em->persist($profilUtilisateur);

        $userExists = (new Utilisateur())
            ->setUsername('userExists')
            ->setCgvAcceptees(true)
            ->setPassword('password')
            ->setEmail('userexists@test.fr')
            ->setRoles([])
            ->setProfil($profilUtilisateur)
        ;
        $this->em->persist($userExists);

        $userEncadrant = (new Utilisateur())
            ->setUsername('userEncadrant')
            ->setCgvAcceptees(true)
            ->setPassword('password')
            ->setEmail('userencadrant@test.fr')
            ->setRoles(['ROLE_ENCADRANT'])
            ->setProfil($profilUtilisateur)
        ;
        $this->em->persist($userEncadrant);

        $this->em->flush();

        $this->ids['profil'] = $profilUtilisateur->getId();
        $this->ids['userExists'] = $userExists->getId();
        $this->ids['userEncadrant'] = $userEncadrant->getId();
    }

    protected function tearDown(): void
    {
        $this->em->remove($this->em->getRepository(ProfilUtilisateur::class)->find($this->ids['profil']));
        $this->em->remove($this->em->getRepository(Utilisateur::class)->find($this->ids['userExists']));
        $this->em->remove($this->em->getRepository(Utilisateur::class)->find($this->ids['userEncadrant']));

        $this->em->flush();

        static::ensureKernelShutdown();
    }

    public function dataProviderMonCompte()
    {
        return [
            ['userExists'],
            ['userEncadrant'],
        ];
    }

    /**
     * @dataProvider dataProviderMonCompte
     *
     * @covers App\Controller\UcaWeb\UtilisateurController::voirAction
     * @covers App\Controller\UcaWeb\UtilisateurController::FormatParActivite
     */
    public function testMonCompte($userKey)
    {
        $this->client->loginUser($this->em->getRepository(Utilisateur::class)->find($this->ids[$userKey]));
        $this->client->request('GET', $this->router->generate('UcaWeb_MonCompte'));
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * @covers App\Controller\UcaWeb\UtilisateurController::preInscriptionActionConfirmation
     */
    public function testUcaWebpreInscriptionConfirmation(): void
    {
        $this->client->request('GET', $this->router->generate('UcaWeb_preInscription_confirmation'));
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * @covers App\Controller\UcaWeb\UtilisateurController::confirmationExpireeAction
     */
    public function testUtilisateurConfirmationInvalide(): void
    {
        $this->client->request('GET', $this->router->generate('UtilisateurConfirmationInvalide', ['token' => 'test']));
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * @covers App\Controller\UcaWeb\utilisateurController::preInscriptionAction
     */
    public function testGetPreInscription(): void
    {
        $this->client->request('GET', $this->router->generate('UcaWeb_preInscription'));
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function dataProviderPreInscriptionForm(): array
    {
        $filePdf = new UploadedFile(__DIR__.'/../../fixtures/test.pdf', 'test.pdf');
        $fileTxt = new UploadedFile(__DIR__.'/../../fixtures/test.txt', 'test.txt');

        return [
            // Cas complet
            [['username' => 'user','email' => 'test@test.fr','prenom' => 'Test','nom' => 'test','sexe' => 'M','dateNaissance' => '01/10/2008','adresse' => 'Rue des tests','codePostal' => '45000','ville' => 'Orléans','telephone' => '0238427378','plainPassword' => ['first' => 'Password123!','second' => 'Password123!'],'profil' => 'id_profil'], $filePdf, true],
            // Cas partiels valide
            [['username' => 'user','email' => 'test@test.fr','prenom' => 'Test','nom' => 'test','sexe' => 'M','plainPassword' => ['first' => 'Password123!','second' => 'Password123!'],'profil' => 'id_profil'], $filePdf, true],
            [['username' => 'user','email' => 'test@test.fr','prenom' => 'Test','nom' => 'test','sexe' => 'M','dateNaissance' => '01/10/2008','plainPassword' => ['first' => 'Password123!','second' => 'Password123!'],'profil' => 'id_profil'], $filePdf, true],
            [['username' => 'user','email' => 'test@test.fr','prenom' => 'Test','nom' => 'test','sexe' => 'M','dateNaissance' => '01/10/2008','adresse' => 'Rue des tests','plainPassword' => ['first' => 'Password123!','second' => 'Password123!'],'profil' => 'id_profil'], $filePdf, true],
            [['username' => 'user','email' => 'test@test.fr','prenom' => 'Test','nom' => 'test','sexe' => 'M','dateNaissance' => '01/10/2008','codePostal' => '45000','plainPassword' => ['first' => 'Password123!','second' => 'Password123!'],'profil' => 'id_profil'], $filePdf, true],
            [['username' => 'user','email' => 'test@test.fr','prenom' => 'Test','nom' => 'test','sexe' => 'M','dateNaissance' => '01/10/2008','ville' => 'Orléans','plainPassword' => ['first' => 'Password123!','second' => 'Password123!'],'profil' => 'id_profil'], $filePdf, true],
            [['username' => 'user','email' => 'test@test.fr','prenom' => 'Test','nom' => 'test','sexe' => 'M','dateNaissance' => '01/10/2008','telephone' => '0238427378','plainPassword' => ['first' => 'Password123!','second' => 'Password123!'],'profil' => 'id_profil'], $filePdf, true],
            [['username' => 'user','email' => 'test@test.fr','prenom' => 'Test','nom' => 'test','sexe' => 'M','dateNaissance' => '01/10/2008','adresse' => 'Rue des tests','codePostal' => '45000','plainPassword' => ['first' => 'Password123!','second' => 'Password123!'],'profil' => 'id_profil'], $filePdf, true],
            [['username' => 'user','email' => 'test@test.fr','prenom' => 'Test','nom' => 'test','sexe' => 'M','dateNaissance' => '01/10/2008','adresse' => 'Rue des tests','ville' => 'Orléans','plainPassword' => ['first' => 'Password123!','second' => 'Password123!'],'profil' => 'id_profil'], $filePdf, true],
            [['username' => 'user','email' => 'test@test.fr','prenom' => 'Test','nom' => 'test','sexe' => 'M','dateNaissance' => '01/10/2008','adresse' => 'Rue des tests','telephone' => '0238427378','plainPassword' => ['first' => 'Password123!','second' => 'Password123!'],'profil' => 'id_profil'], $filePdf, true],
            [['username' => 'user','email' => 'test@test.fr','prenom' => 'Test','nom' => 'test','sexe' => 'M','dateNaissance' => '01/10/2008','adresse' => 'Rue des tests','codePostal' => '45000','ville' => 'Orléans','plainPassword' => ['first' => 'Password123!','second' => 'Password123!'],'profil' => 'id_profil'], $filePdf, true],
            [['username' => 'user','email' => 'test@test.fr','prenom' => 'Test','nom' => 'test','sexe' => 'M','adresse' => 'Rue des tests','plainPassword' => ['first' => 'Password123!','second' => 'Password123!'],'profil' => 'id_profil'], $filePdf, true],
            [['username' => 'user','email' => 'test@test.fr','prenom' => 'Test','nom' => 'test','sexe' => 'M','adresse' => 'Rue des tests','codePostal' => '45000','plainPassword' => ['first' => 'Password123!','second' => 'Password123!'],'profil' => 'id_profil'], $filePdf, true],
            [['username' => 'user','email' => 'test@test.fr','prenom' => 'Test','nom' => 'test','sexe' => 'M','adresse' => 'Rue des tests','ville' => 'Orléans','plainPassword' => ['first' => 'Password123!','second' => 'Password123!'],'profil' => 'id_profil'], $filePdf, true],
            [['username' => 'user','email' => 'test@test.fr','prenom' => 'Test','nom' => 'test','sexe' => 'M','adresse' => 'Rue des tests','codePostal' => '45000','ville' => 'Orléans','plainPassword' => ['first' => 'Password123!','second' => 'Password123!'],'profil' => 'id_profil'], $filePdf, true],
            [['username' => 'user','email' => 'test@test.fr','prenom' => 'Test','nom' => 'test','sexe' => 'M','adresse' => 'Rue des tests','telephone' => '0238427378','plainPassword' => ['first' => 'Password123!','second' => 'Password123!'],'profil' => 'id_profil'], $filePdf, true],
            [['username' => 'user','email' => 'test@test.fr','prenom' => 'Test','nom' => 'test','sexe' => 'M','codePostal' => '45000','plainPassword' => ['first' => 'Password123!','second' => 'Password123!'],'profil' => 'id_profil'], $filePdf, true],
            [['username' => 'user','email' => 'test@test.fr','prenom' => 'Test','nom' => 'test','sexe' => 'M','codePostal' => '45000','ville' => 'Orléans','plainPassword' => ['first' => 'Password123!','second' => 'Password123!'],'profil' => 'id_profil'], $filePdf, true],
            [['username' => 'user','email' => 'test@test.fr','prenom' => 'Test','nom' => 'test','sexe' => 'M','codePostal' => '45000','ville' => 'Orléans','telephone' => '0238427378','plainPassword' => ['first' => 'Password123!','second' => 'Password123!'],'profil' => 'id_profil'], $filePdf, true],
            [['username' => 'user','email' => 'test@test.fr','prenom' => 'Test','nom' => 'test','sexe' => 'M','ville' => 'Orléans','plainPassword' => ['first' => 'Password123!','second' => 'Password123!'],'profil' => 'id_profil'], $filePdf, true],
            [['username' => 'user','email' => 'test@test.fr','prenom' => 'Test','nom' => 'test','sexe' => 'M','ville' => 'Orléans','telephone' => '0238427378','plainPassword' => ['first' => 'Password123!','second' => 'Password123!'],'profil' => 'id_profil'], $filePdf, true],
            [['username' => 'user','email' => 'test@test.fr','prenom' => 'Test','nom' => 'test','sexe' => 'M','telephone' => '0238427378','plainPassword' => ['first' => 'Password123!','second' => 'Password123!'],'profil' => 'id_profil'], $filePdf, true],

            // Cas Invalide
            [['username' => 'userExists','email' => 'test@test.fr','prenom' => 'Test','nom' => 'test','sexe' => 'M','dateNaissance' => '01/10/2008','adresse' => 'Rue des tests','codePostal' => '45000','ville' => 'Orléans','telephone' => '0238427378','plainPassword' => ['first' => 'Password123!','second' => 'Password123!'],'profil' => 'id_profil'], $filePdf, false],
            [['username' => 'user','email' => 'test@test.fr','prenom' => 'Test','nom' => 'test','sexe' => 'M','dateNaissance' => '01/10/2008','adresse' => 'Rue des tests','codePostal' => '45','ville' => 'Orléans','telephone' => '0238427378','plainPassword' => ['first' => 'Password123!','second' => 'Password123!'],'profil' => 'id_profil'], $filePdf, false],
            [['username' => 'user','email' => 'test@test.fr','prenom' => 'Test','nom' => 'test','sexe' => 'M','dateNaissance' => '01/10/2008','adresse' => 'Rue des tests','codePostal' => '45000','ville' => 'Orléans','telephone' => '0238427378','plainPassword' => ['first' => 'Password123!','second' => 'Password123!'],'profil' => 'id_profil'], null, false],
            [['username' => 'user','email' => 'test@test.fr','prenom' => 'Test','nom' => 'test','sexe' => 'M','dateNaissance' => '01/10/2008','adresse' => 'Rue des tests','codePostal' => '45000','ville' => 'Orléans','telephone' => '0238427378','plainPassword' => ['first' => 'Password123!','second' => 'Password123!'],'profil' => '0'], $filePdf, false],
            [['username' => 'user','email' => 'test@test.fr','prenom' => 'Test','nom' => 'test','sexe' => 'M','dateNaissance' => '01/13/2008','adresse' => 'Rue des tests','codePostal' => '45000','ville' => 'Orléans','telephone' => '0238427378','plainPassword' => ['first' => 'Password123!','second' => 'Password123!'],'profil' => 'id_profil'], $filePdf, false],
            [['username' => 'user','email' => 'test@test.fr','prenom' => 'Test','nom' => 'test','sexe' => 'M','dateNaissance' => '32/10/2008','adresse' => 'Rue des tests','codePostal' => '45000','ville' => 'Orléans','telephone' => '0238427378','plainPassword' => ['first' => 'Password123!','second' => 'Password123!'],'profil' => 'id_profil'], $filePdf, false],
            [['username' => 'user','email' => 'test@test.fr','prenom' => 'Test','nom' => 'test','sexe' => 'G','dateNaissance' => '01/10/2008','adresse' => 'Rue des tests','codePostal' => '45000','ville' => 'Orléans','telephone' => '0238427378','plainPassword' => ['first' => 'Password123!','second' => 'Password123!'],'profil' => 'id_profil'], $filePdf, false],
            [['username' => 'user','email' => 'test@test.fr','prenom' => 'Test','nom' => 'test','sexe' => 'M','dateNaissance' => '01/10/2008','adresse' => 'Rue des tests','codePostal' => '45000','ville' => 'Orléans','telephone' => '0238427378','plainPassword' => ['first' => 'Password123','second' => 'Password123!'],'profil' => 'id_profil'], $filePdf, false],
            [['username' => 'u','email' => 'test@test.fr','prenom' => 'Test','nom' => 'test','sexe' => 'M','dateNaissance' => '01/10/2008','adresse' => 'Rue des tests','codePostal' => '45000','ville' => 'Orléans','telephone' => '0238427378','plainPassword' => ['first' => 'Password123!','second' => 'Password123!'],'profil' => 'id_profil'], $filePdf, false],
            [['username' => 'user','email' => 'test@test','prenom' => 'Test','nom' => 'test','sexe' => 'M','dateNaissance' => '01/10/2008','adresse' => 'Rue des tests','codePostal' => '45000','ville' => 'Orléans','telephone' => '0238427378','plainPassword' => ['first' => 'Password123!','second' => 'Password123!'],'profil' => 'id_profil'], $filePdf, false],
            [['username' => 'user','email' => 'test','prenom' => 'Test','nom' => 'test','sexe' => 'M','dateNaissance' => '01/10/2008','adresse' => 'Rue des tests','codePostal' => '45000','ville' => 'Orléans','telephone' => '0238427378','plainPassword' => ['first' => 'Password123!','second' => 'Password123!'],'profil' => 'id_profil'], $filePdf, false],
            [['username' => 'user','email' => 'test@test.fr','prenom' => 'Test','nom' => 'test','sexe' => 'M','dateNaissance' => '01/10/2008','adresse' => 'Rue des tests','codePostal' => '45000','ville' => 'Orléans','telephone' => '02384273','plainPassword' => ['first' => 'Password123!','second' => 'Password123!'],'profil' => 'id_profil'], $filePdf, false],
            [['username' => 'user','email' => 'test@test.fr','prenom' => 'Test','nom' => 'test','sexe' => 'M','dateNaissance' => '01/10/2008','adresse' => 'Rue des tests','codePostal' => '45000','ville' => 'Orléans','telephone' => '0238427378','plainPassword' => ['first' => 'P','second' => 'P'],'profil' => 'id_profil'], $filePdf, false],
            [['username' => 'user','email' => 'test@test.fr','prenom' => 'Test','nom' => 'test','sexe' => 'M','dateNaissance' => '01/10/2008','adresse' => 'Rue des tests','codePostal' => '45000','ville' => 'Orléans','telephone' => '0238427378','plainPassword' => ['first' => 'Password123!','second' => 'Password123!'],'profil' => 'id_profil'], $fileTxt, false],
        ];
    }

    /**
     * @dataProvider dataProviderPreInscriptionForm
     *
     * @covers App\Controller\UcaWeb\utilisateurController::preInscriptionAction
     */
    public function testPostPreInscription($data, $file, $redirect): void
    {
        $csrfToken = static::getContainer()->get('security.csrf.token_manager')->getToken('ucaSport_Utilisateur');
        if ($data['profil'] === 'id_profil') {
            $data['profil'] = $this->ids['profil'];
        }

        // On force le captcha à avoir la valeur test5 (car sinon impossible de connaitre la valeur générée donc impossible de valider le formulaire)
        $session = static::getContainer()->get(SessionInterface::class);
        $session->set('captcha_whitelist_key', ['_captcha_captcha']);
        $captcha = 'test5';
        $session->set('_captcha_captcha', ['phrase'=> $captcha]);


        $this->client->request(
            'POST',
            $this->router->generate('UcaWeb_preInscription'),
            ['ucaSport_Utilisateur' => array_merge($data, ['captcha' => $captcha,'_token' => $csrfToken->getValue(), 'save' => ''])],
            ['ucaSport_Utilisateur' => ['documentFile' => ['file' => $file]]]
        );


        $response = $this->client->getResponse();
        if ($response->getStatusCode() === Response::HTTP_FOUND && null !== $file) {
            $newUser = $this->em->getRepository(Utilisateur::class)->findBy([], ['id' => 'DESC'], 1, 0)[0];
            copy(__DIR__.'/../../fixtures/documents/'.$newUser->getDocument(), $file->getPathname());
            $this->em->remove($newUser);
            $this->em->flush();
        }

        if ($redirect) {
            $expectedRoute = $this->router->generate('UcaWeb_preInscription_confirmation');
            $this->assertResponseRedirects($expectedRoute);
            $this->assertEmailCount(2);
        } else {
            $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        }
    }
}
