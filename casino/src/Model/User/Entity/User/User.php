<?php


namespace App\Model\User\Entity\User;


use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints\Date;

/**
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="user_users", uniqueConstraints={
 *      @ORM\UniqueConstraint(columns={"email"}),
 *      @ORM\UniqueConstraint(columns={"reset_token_token"}),
 * })
 */
class User
{
    public const STATUS_WAIT = 'wait';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_NEW = 'new';

    /**
     * @ORM\Column(type="user_user_id")
     * @ORM\Id()
     */
    private Id $id;
    /**
     * @ORM\Column(type="datetime_immutable")
     */
    private DateTimeImmutable $date;
    /**
     * @ORM\Column(type="user_user_email", nullable=true)
     */
    private Email $email;
    /**
     * @ORM\Column(type="string", length=255, nullable=true, name="password_hash")
     */
    private ?string $passwordHash;
    /**
     * @ORM\Column(type="string", length=255, nullable=true, name="confirm_token")
     */
    private ?string $confirmToken;
    /**
     * @ORM\Embedded(class="ResetToken", columnPrefix="reset_token_")
     */
    private ?ResetToken $resetToken;
    /**
     * @ORM\Column(type="user_user_role")
     */
    private Role $role;
    /**
     * @ORM\Column(type="string", length=16)
     */
    private string $status;
    /**
     * @var Network[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="Network", mappedBy="user", orphanRemoval=true, cascade="persist")
     */
    private $networks;

    private function __construct(Id $id, DateTimeImmutable $date)
    {
        $this->id = $id;
        $this->date = $date;
//        $this->status = self::STATUS_NEW;
        $this->role = Role::user();
        $this->networks = new ArrayCollection();
    }

    public static function signUpByEmail(Id $id,
                                         DateTimeImmutable $dateTimeImmutable,
                                         Email $email,
                                         string $hash,
                                         string $token): self
    {
//        if (!$this->isNew()) {
//            throw new \DomainException('User already signed up');
//        }
        $user = new self($id, $dateTimeImmutable);
        $user->email = $email;
        $user->passwordHash = $hash;
        $user->confirmToken = $token;
        $user->status = self::STATUS_WAIT;

        return $user;
    }

    public function confirmSignUp(): void
    {
        if (!$this->isWait()) {
            throw new \DomainException('User is already confirmed.');
        }

        $this->status = self::STATUS_ACTIVE;
        $this->confirmToken = null;
    }

    public static function signUpByNetwork(Id $id,
                                           DateTimeImmutable $dateTimeImmutable,
                                           string $network,
                                           string $identity): self
    {
//        if (!$this->isNew()) {
//            throw new \DomainException('User is already signed up');
//        }
        $user = new self($id, $dateTimeImmutable);
        $user->attachNetwork($network, $identity);
        $user->status = self::STATUS_ACTIVE;
        return $user;
    }

    private function attachNetwork(string $network, string $identity): void
    {
        foreach ($this->networks as $existing) {
            if ($existing->isForNetwork($network)) {
                throw new \DomainException('Network is already attached');
            }
        }
        $this->networks->add(new Network($this, $network, $identity));
    }

    public function changeRole(Role $role): void
    {
        if ($this->role->isEqual($role)) {
            throw new \DomainException('Role is already same.');
        }

        $this->role = $role;
    }

    public function requestPasswordReset(ResetToken $token, DateTimeImmutable $date): void
    {
        if (!$this->isActive()) {
            throw new \DomainException('User is not active');
        }
        if (!isset($this->email)) {
            throw new \DomainException('Email is not specified');
        }
        if (isset($this->resetToken) && !$this->resetToken->isExpiredTo($date)) {
            throw new \DomainException('Resetting is already requested.');
        }

        $this->resetToken = $token;
    }

    public function passwordReset(DateTimeImmutable $date, string $hash): void
    {
        if (!isset($this->resetToken)) {
            throw new \DomainException('Resetting is not requested');
        }

        if ($this->resetToken->isExpiredTo($date)) {
            throw new \DomainException('Reset token is expired');
        }

        $this->passwordHash = $hash;
    }

    public function getResetToken(): ?ResetToken
    {
        return $this->resetToken;
    }

    public function getNetworks(): array
    {
        return $this->networks->toArray();
    }

    public function isNew(): bool
    {
        return $this->status === self::STATUS_NEW;
    }

    public function isWait(): bool
    {
        return $this->status === self::STATUS_WAIT;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function getId(): Id
    {
        return $this->id;
    }

    public function getDate(): DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(DateTimeImmutable $date): void
    {
        $this->date = $date;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function setEmail(Email $email): void
    {
        $this->email = $email;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function setPasswordHash(string $passwordHash): void
    {
        $this->passwordHash = $passwordHash;
    }

    public function getConfirmToken(): ?string
    {
        return $this->confirmToken;
    }

    public function setConfirmToken(?string $confirmToken): void
    {
        $this->confirmToken = $confirmToken;
    }

    public function getRole(): Role
    {
        return $this->role;
    }

    public function setRole(Role $role): void
    {
        $this->role = $role;
    }

    /**
     * @ORM\PostLoad()
     */
    public function checkEmbeds(): void
    {
        if ($this->resetToken->isEmpty()) {
            $this->resetToken = null;
        }
    }
}