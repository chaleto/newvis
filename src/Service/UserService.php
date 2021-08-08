<?php


namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserService
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * @var UserRepository
     */
    private $repository;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    
    public function __construct(
        UserPasswordEncoderInterface $passwordEncoder,
        EntityManagerInterface       $entityManager
    )
    {
        $this->passwordEncoder = $passwordEncoder;
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(User::class);
    }

    public function findByEmail(string $email)
    {
        return $this->repository->findOneBy(['email' => $email]);
    }

    public function register(User $user): User
    {
        $this->applyPassword($user);

        $this->entityManager->persist($user);
        $this->entityManager->flush();


        return $user;
    }

    public function applyPassword(UserInterface $user): void
    {
        if (!$user->getPlainPassword()) {
            return;
        }

        $encodedPassword = $this->passwordEncoder->encodePassword(
            $user,
            $user->getPlainPassword()
        );
        $user->setPassword($encodedPassword);
    }

    public function createRegistrationToken(User $User)
    {
        $token = new RegistrationToken();
        $token->setUser($User);

        $this->entityManager->persist($token);
        $this->entityManager->flush();

        return $token;
    }

    public function useRegistrationToken(RegistrationToken $token)
    {
        $token->getUser()->setIsActive(true);
        $token->setIsUsed(true);
    }

    public function sendRegistrationEmail(RegistrationToken $token)
    {
        /** @var User $tokenUser */
        $tokenUser = $token->getUser();

        $email = new RegistrationConfirmationEmail(
            $tokenUser->getEmail(), [
                'token' => $token,
            ]
        );

        $this->emailManager->send($email);
    }

    public function createPasswordResetToken(User $user)
    {
        $expirationDate = DateTimeHelper::getCurrentDate();
        $expirationDate->modify('+2 days');
        $expirationDate->setTime(23, 59, 59);

        $token = new PasswordResetToken();
        $token->setExpirationDate($expirationDate);
        $token->setUser($user);

        $this->entityManager->persist($token);
        $this->entityManager->flush();

        return $token;
    }

    public function sendPasswordResetEmail(PasswordResetToken $token)
    {
        /** @var User $tokenUser */
        $tokenUser = $token->getUser();

        $email = new PasswordResetEmail(
            $tokenUser->getEmail(), [
                'token' => $token,
            ]
        );

        $this->emailManager->send($email);
    }

    public function usePasswordResetToken(PasswordResetToken $token)
    {
        $this->applyPassword($token->getUser());
        $token->setIsUsed(true);
    }
}
