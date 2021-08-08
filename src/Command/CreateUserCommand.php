<?php

namespace App\Command;

use App\Service\UserService;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateUserCommand extends Command
{
    protected static $defaultName = 'create:user';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var UserService
     */
    private $userManagerService;

    /**
     * @var SymfonyStyle
     */
    private $consoleOutput;
    private $faker;


    public function __construct(
        EntityManagerInterface $entityManager,
        UserService $userManagerService
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->userManagerService = $userManagerService;
    }

    protected function configure()
    {
        $this
            ->setDescription(
                'Generates users with the password "123123", except the default admin user.'
            )
            ->addArgument('count', InputArgument::OPTIONAL, 'Generated users count.')
            ->addOption(
                'admin',
                null,
                InputOption::VALUE_NONE,
                'Assigns the admin role to created user(s).'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->consoleOutput = new SymfonyStyle($input, $output);


        $count = (int)$input->getArgument('count');
        $adminFlag = $input->getOption('admin');

        /** @var UserRepository $userRepository */
        $userRepository = $this->entityManager->getRepository(User::class);

        $generatedUserNumber = $userRepository->getGeneratedUserNumber();

        $this->createDefaultAdmin();

        $created = 0;
        while ($created < $count) {
            $roles = $adminFlag ? ['ROLE_ADMIN'] : [];

            $currentNumber = $created + $generatedUserNumber;

            $this->createUser(
                sprintf('testuser%s', $currentNumber),
                sprintf('testuser%s@example.com', $currentNumber),
                '123123',
                $roles
            );

            $created++;
        }

        $this->entityManager->flush();

        if ($count === 1) {
            $this->consoleOutput->success(
                sprintf(
                    "You've created an user with username 'testuser%s' and password '123123'.",
                    $generatedUserNumber
                )
            );
        } else {
            $this->consoleOutput->success(
                sprintf(
                    "You've created %s user(s) with username 'testuser<id>' (id is between %s and %s including) and password '123123'.",
                    $created,
                    $generatedUserNumber,
                    $generatedUserNumber + $count - 1
                )
            );
        }

        return 0;
    }

    private function createDefaultAdmin(): void
    {
        $existingAdmin = $this->entityManager->getRepository(User::class)->findOneBy(
            ['username' => 'admin']
        );

        if ($existingAdmin instanceof User) {
            return;
        }

        $this->createUser('admin', 'admin@example.com', 'admin', ['ROLE_ADMIN']);

        $this->consoleOutput->success(
            "You've created a default admin user with username 'admin' and password 'admin'."
        );
    }

    private function createUser(
        string $username,
        string $email,
        string $password,
        array $roles = []
    ): User {
        $user = (new User())
            ->setUsername($username)
            ->setEmail($email)
            ->setPlainPassword($password)
            ->setRoles($roles);

        $this->userManagerService->applyPassword($user);
        $this->entityManager->persist($user);

        return $user;

    }

}
