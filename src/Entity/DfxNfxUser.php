<?php


namespace App\Entity;

use App\Repository\DfxNfxUserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;


#[ORM\Entity(repositoryClass: DfxNfxUserRepository::class)]
class DfxNfxUser implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column(type: 'string', length: 180, unique: true)]
    private ?string $username = null;


    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column(type: 'string')]
    private ?string $password = null;

    private ?string $init = null;

    private array $rolesM = [];

    private array $rolesG = [];


    private ?DfxKonf $datefix = null;

    private ?DfxNfxKunden $kunde = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set id
     *
     * @param integer $id
     * @return DfxNfxUser
     */
    public function setId(int $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): static
    {
        $this->username = $username;

        return $this;
    }


    /**
     * The web representation of the user (e.g. a username, an email address, etc.)
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string)$this->username;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(?array $roles): static
    {
        $this->roles = $roles ?? [];

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }



    private ?string $nameLang = null;

    public function getNameLang(): ?string
    {
        return $this->nameLang;
    }

    public function setNameLang(string $nameLang): static
    {
        $this->nameLang = $nameLang;

        return $this;
    }

    public function setInit(?string $init): static
    {
        $this->init = $init;

        return $this;
    }

    public function getInit(): ?string
    {
        return $this->init;
    }

    public function setDatefix(?DfxKonf $datefix): static
    {
        $this->datefix = $datefix;

        return $this;
    }

    public function getDatefix(): ?DfxKonf
    {
        return $this->datefix;
    }

    public function setKunde(?DfxNfxKunden $kunde): static
    {
        $this->kunde = $kunde;

        return $this;
    }

    public function getKunde(): ?DfxNfxKunden
    {
        return $this->kunde;
    }

    /**
     * Set rolesM
     *
     * @param array $rolesM
     * @return DfxNfxUser
     */
    public function setRolesM(array $rolesM): static
    {
        $this->rolesM = $rolesM;

        return $this;
    }

    /**
     * Get rolesM
     *
     * @return array
     */
    public function getRolesM(): array
    {
        return $this->rolesM ?? [];
    }

    /**
     * Set rolesG
     *
     * @param array $rolesG
     * @return DfxNfxUser
     */
    public function setRolesG(array $rolesG): static
    {
        $this->rolesG = $rolesG;

        return $this;
    }

    /**
     * Get rolesG
     *
     * @return array
     */
    public function getRolesG(): array
    {
        return $this->rolesG ?? [];
    }

    #[\Deprecated('No temporary sensitive data is stored on this user.')]
    public function eraseCredentials(): void
    {
        // TODO: Implement eraseCredentials() method.
    }
}
