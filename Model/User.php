<?php

namespace NyroDev\NyroCmsBundle\Model;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Gedmo\Mapping\Annotation as Gedmo;
use NyroDev\UtilityBundle\Model\AbstractUploadable;
use NyroDev\UtilityBundle\Model\StringablePropertyable;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[Gedmo\SoftDeleteable(fieldName: 'deleted', timeAware: false)]
abstract class User extends AbstractUploadable implements UserInterface, EquatableInterface, PasswordAuthenticatedUserInterface, StringablePropertyable
{
    public static function getStringableProperty(): array
    {
        return ['firstname', 'lastname', 'email'];
    }

    protected $id;

    #[Assert\NotBlank]
    #[Assert\Email]
    #[Gedmo\Versioned]
    protected ?string $email = null;

    #[Assert\NotBlank]
    #[Gedmo\Versioned]
    protected ?string $firstname = null;

    #[Assert\NotBlank]
    #[Gedmo\Versioned]
    protected ?string $lastname = null;

    protected ?string $password = 'dummy';

    #[Assert\NotBlank]
    #[Gedmo\Versioned]
    protected ?string $userType = null;

    #[Gedmo\Versioned]
    protected ?bool $developper = false;

    #[Gedmo\Versioned]
    protected ?bool $valid = true;

    #[Gedmo\Versioned]
    protected ?DateTimeInterface $validStart = null;

    #[Gedmo\Versioned]
    protected ?DateTimeInterface $validEnd = null;

    #[Gedmo\Versioned]
    protected ?string $passwordKey = null;

    #[Gedmo\Versioned]
    protected ?DateTimeInterface $passwordKeyEnd = null;

    #[Gedmo\Timestampable(on: 'create')]
    protected ?DateTimeInterface $inserted = null;

    #[Gedmo\Timestampable(on: 'update')]
    protected ?DateTimeInterface $updated = null;

    protected ?DateTimeInterface $deleted = null;

    protected Collection $userRoles;

    public function __construct()
    {
        $this->userRoles = new ArrayCollection();
    }

    public function getId()
    {
        return $this->id;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setFirstname(?string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setLastname(?string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setUserType(?string $userType): self
    {
        $this->userType = $userType;

        return $this;
    }

    public function getUserType(): ?string
    {
        return $this->userType;
    }

    public function setDevelopper(?bool $developper): self
    {
        $this->developper = $developper;

        return $this;
    }

    public function getDevelopper(): ?bool
    {
        return $this->developper;
    }

    public function setValid(?bool $valid): self
    {
        $this->valid = $valid;

        return $this;
    }

    public function getValid(): ?bool
    {
        return $this->valid;
    }

    public function setValidStart(?DateTimeInterface $validStart): self
    {
        $this->validStart = $validStart;

        return $this;
    }

    public function getValidStart(): ?DateTimeInterface
    {
        return $this->validStart;
    }

    public function setValidEnd(?DateTimeInterface $validEnd): self
    {
        $this->validEnd = $validEnd;

        return $this;
    }

    public function getValidEnd(): ?DateTimeInterface
    {
        return $this->validEnd;
    }

    public function setPasswordKey(?string $passwordKey): self
    {
        $this->passwordKey = $passwordKey;

        return $this;
    }

    public function getPasswordKey(): ?string
    {
        return $this->passwordKey;
    }

    public function setPasswordKeyEnd(?DateTimeInterface $passwordKeyEnd): self
    {
        $this->passwordKeyEnd = $passwordKeyEnd;

        return $this;
    }

    public function getPasswordKeyEnd(): ?DateTimeInterface
    {
        return $this->passwordKeyEnd;
    }

    public function setInserted(DateTimeInterface $inserted): self
    {
        $this->inserted = $inserted;

        return $this;
    }

    public function getInserted(): ?DateTimeInterface
    {
        return $this->inserted;
    }

    public function setUpdated(DateTimeInterface $updated): self
    {
        $this->updated = $updated;

        return $this;
    }

    public function getUpdated(): ?DateTimeInterface
    {
        return $this->updated;
    }

    public function setDeleted(?DateTimeInterface $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function getDeleted(): ?DateTimeInterface
    {
        return $this->deleted;
    }

    public function addUserRole(UserRole $userRoles): self
    {
        $this->userRoles[] = $userRoles;

        return $this;
    }

    public function removeUserRole(UserRole $userRoles): self
    {
        $this->userRoles->removeElement($userRoles);

        return $this;
    }

    public function getUserRoles(): Collection
    {
        return $this->userRoles;
    }

    protected $serializeVars = [
        'id',
        'email',
        'password',
        'userType',
        'firstname',
        'lastname',
    ];

    public function eraseCredentials(): void
    {
    }

    public function isEqualTo(UserInterface $user): bool
    {
        if (!$user instanceof self
                || $this->getPassword() !== $user->getPassword()
                || $this->getUsername() !== $user->getUsername()
                || $this->getId() !== $user->getId()) {
            return false;
        }

        return true;
    }

    public function __serialize(): array
    {
        $vars = [];
        foreach ($this->serializeVars as $field) {
            $vars[$field] = $this->{$field};
        }

        return $vars;
    }

    public function __unserialize(array $vars): void
    {
        foreach ($vars as $k => $v) {
            $this->{$k} = $v;
        }
    }

    public function getRoles(): array
    {
        $ret = [
            'ROLE_USER',
            'ROLE_'.strtoupper($this->getUserType()),
        ];
        if ($this->getDevelopper()) {
            $ret[] = 'ROLE_DEVELOPPER';
        }
        if ($this->getValid()) {
            foreach ($this->getUserRoles() as $role) {
                $ret[] = $role->getSecurityRoleName();
            }
        }

        return $ret;
    }

    public function hasRole(string $role): bool
    {
        return in_array($role, $this->getRoles());
    }

    public function getUserIdentifier(): string
    {
        return $this->getEmail();
    }

    public function getUsername(): ?string
    {
        return $this->getUserIdentifier();
    }

    public function getSalt()
    {
        return null;
    }

    public function __toString(): string
    {
        return $this->getFirstname().' '.$this->getLastname().' ('.$this->getEmail().')';
    }

    public function getUsualName()
    {
        return $this->getFirstname().' '.$this->getLastname();
    }

    protected function getFileFields(): array
    {
        return [];
    }
}
