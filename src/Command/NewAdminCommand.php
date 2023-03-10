<?php

namespace App\Command;

use App\Entity\Uca\Groupe;
use App\Entity\Uca\Utilisateur;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Class NewAdminCommand
 * @package App\Command
 */
class NewAdminCommand extends Command
{
    protected static $defaultName = 'app:new-admin';

    /**
     * @var ObjectManager
     */
    private ObjectManager $em;

    /**
     * @var UserPasswordHasherInterface
     */
    private UserPasswordHasherInterface $passwordEncoder;

    /**
     * NewAdminCommand constructor.
     * @param ManagerRegistry $registry
     * @param UserPasswordHasherInterface $passwordEncoder
     */
    public function __construct(ManagerRegistry $registry, UserPasswordHasherInterface $passwordEncoder)
    {
        $this->em = $registry->getManager();
        $this->passwordEncoder = $passwordEncoder;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Creates a new administrator user.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(
            ['<question>New administrator user creation</question>', '===================================']
        );
        $helper = $this->getHelper('question');

        /** Username **/
        do {
            $validUsername = true;
            $question = new Question(
                'Enter the username (3 characters minimum except spaces) [administrator]: ',
                'administrator'
            );
            $username = $helper->ask($input, $output, $question);

            if (preg_match('/^[^\s]{3,}$/i', $username) !== 1) {
                $validUsername = false;
                $output->writeln('<error>username must contains 3 alphanumerics chars minimum</error>');
            }

            $user = $this->em->getRepository(Utilisateur::class)->findOneByUsername($username);
            if ($user instanceof Utilisateur) {
                $question = new ConfirmationQuestion(
                    'username \'' . $username . '\' is already in use.'
                    . ' Do you want update it by adding Admin role ? [yes|no] : ',
                    false
                );
                if ($helper->ask($input, $output, $question)) {
                    $this->addAdminGroup($user);
                    $this->em->flush();
                    $output->writeln('<info>User updated successfully</info>');
                }
                return self::SUCCESS;
            }
        } while (!$validUsername);

        $user = new Utilisateur();
        $user->setUsername($username)
            ->setEnabled(true);
        $this->addAdminGroup($user);

        /** Email **/
        do {
            $validEmail = true;
            $question = new Question('Enter a user email: ', null);
            $email = $helper->ask($input, $output, $question);

            if (preg_match('/^.+@.+\..+$/i', $email) !== 1) {
                $validEmail = false;
                $output->writeln('<error>Email format is incorrect</error>');
            }
        } while (!$validEmail);
        $user->setEmail($email);

        /** Shibboleth user **/
        $question = new ConfirmationQuestion('Is this a Shibboleth user ? [yes|no] : ', false);
        if ($helper->ask($input, $output, $question)) {
            $user->setShibboleth(true)
                ->setPassword(Utilisateur::getRandomPassword());
            $this->em->persist($user);
            $this->em->flush();

            $output->writeln('<info>User created successfully</info>');

            return self::SUCCESS;
        }

        $user->setShibboleth(false);

        $question = new Question('Enter the lastname:');
        $lastname = $helper->ask($input, $output, $question);
        $user->setNom($lastname);

        $question = new Question('Enter the firstname:');
        $firstname = $helper->ask($input, $output, $question);
        $user->setPrenom($firstname);

        do {
            do {
                $validPassword = true;
                $question = new Question('Enter the user password (6 chars minimum): ', null);
                $question->setHidden(true)
                    ->setHiddenFallback(false);
                $password = $helper->ask($input, $output, $question);

                if (preg_match('/^.{6,}$/i', $password) !== 1) {
                    $validPassword = false;
                    $output->writeln('<error>Password must contains 6 chars minimum</error>');
                }
            } while (!$validPassword);

            $question = new Question('Confirm password: ', null);
            $question->setHidden(true)->setHiddenFallback(false);
            $confirmPassword = $helper->ask($input, $output, $question);

            if ($password !== $confirmPassword) {
                $validPassword = false;
                $output->writeln('<error>Passwords does not match</error>');
            }
        } while (!$validPassword);
        $user->setPassword($this->passwordEncoder->hashPassword($user, $password));

        $this->em->persist($user);
        $this->em->flush();

        $output->writeln('<info>User created successfully</info>');

        return self::SUCCESS;
    }

    /**
     * @param Utilisateur $user
     */
    private function addAdminGroup(Utilisateur $user): void
    {
        $group = $this->em->getRepository(Groupe::class)->findOneByName(1);
        $user->addGroup($group);
    }
}
