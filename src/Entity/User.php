<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Serializer\Filter\PropertyFilter;
use App\Entity\Traits\Timestamp;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Mapping\Annotation\Timestampable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\NotBlank;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['users:read', 'users:item:read']]),
        new GetCollection(),
        new Post(security: 'is_granted("PUBLIC_ACCESS")',
        validationContext: ['groups' => ['Default', 'PostValidation']]),
        new Put( security: 'is_granted("ROLE_USER_EDIT")'),
        new Patch( security: 'is_granted("ROLE_USER_EDIT")'),
        new Delete()
    ],
    normalizationContext: ['groups' => 'users:read'],
    denormalizationContext: ['groups' => 'users:write'],
    security: 'is_granted("ROLE_USER")',
)]
#[ApiResource(
    uriTemplate: 'treasures/{treasure_id}/owner.{_format}',
    operations: [
        new Get()
    ],
    uriVariables: [
        'treasure_id' => new Link(
            fromProperty: 'owner', fromClass: DragonTreasure::class,
//            toProperty: 'owner'
        )
    ],
    normalizationContext: [
        'groups' => ['users:read']
    ],
    security: 'is_granted("ROLE_USER")',
)]
#[UniqueEntity(fields: 'email', message: 'There is already an account with this email')]
#[UniqueEntity(fields: 'name', message: 'There is already an account with this name')]
#[ApiFilter(PropertyFilter::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use Timestamp;

    private ?array $accessTokenScopes = null;


    /**
     * @var \DateTime|null
     * @Timestampable(on="create")
     * @Column(type="datetime")
     */
    #[Timestampable(on: 'create')]
    #[Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['users:read'])]
    protected $createdAt;

    /**
     * @var \DateTime|null
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    #[Timestampable(on: 'update')]
    #[Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['users:read'])]
    protected $updatedAt;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['users:read'])]
    private ?int $id = null;

    #[Assert\Type('string')]
    #[Assert\Email()]
    #[ORM\Column(length: 180, unique: true)]
    #[Groups(['users:read', 'users:write', 'treasure:item:read'])]
    #[NotBlank]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[Groups(['users:write'])]
    #[SerializedName('password')]
    #[NotBlank(groups: ['PostValidation'])]
    private ?string $plainPassword = null;

    #[ORM\Column(length: 255)]
    #[Groups(['users:read', 'users:write', 'treasure:item:read'])]
    #[NotBlank]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: DragonTreasure::class, cascade: ['persist'])]
    #[Groups(['users:read', 'users:write'])]
    #[Assert\Valid(groups: ['PostValidation'])]
    private Collection $dragonTreasures;

    #[ORM\OneToMany(mappedBy: 'owner', targetEntity: ApiToken::class)]
    private Collection $apiTokens;

    public function __construct()
    {
        $this->dragonTreasures = new ArrayCollection();
        $this->apiTokens = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        if (null === $this->accessTokenScopes) {
            // logged in via the full user mechanism
            $roles = $this->roles;
            $roles[] = 'ROLE_FULL_USER';
        } else {
            $roles = $this->accessTokenScopes;
        }
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
         $this->plainPassword = null;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection<int, DragonTreasure>
     */
    public function getDragonTreasures(): Collection
    {
        return $this->dragonTreasures;
    }

    public function addDragonTreasure(DragonTreasure $dragonTreasure): static
    {
        if (!$this->dragonTreasures->contains($dragonTreasure)) {
            $this->dragonTreasures->add($dragonTreasure);
            $dragonTreasure->setOwner($this);
        }

        return $this;
    }

    public function removeDragonTreasure(DragonTreasure $dragonTreasure): static
    {
        if ($this->dragonTreasures->removeElement($dragonTreasure)) {
            // set the owning side to null (unless already changed)
            if ($dragonTreasure->getOwner() === $this) {
                $dragonTreasure->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ApiToken>
     */
    public function getApiTokens(): Collection
    {
        return $this->apiTokens;
    }

    public function addApiToken(ApiToken $apiToken): static
    {
        if (!$this->apiTokens->contains($apiToken)) {
            $this->apiTokens->add($apiToken);
            $apiToken->setOwner($this);
        }

        return $this;
    }

    public function removeApiToken(ApiToken $apiToken): static
    {
        if ($this->apiTokens->removeElement($apiToken)) {
            // set the owning side to null (unless already changed)
            if ($apiToken->getOwner() === $this) {
                $apiToken->setOwner(null);
            }
        }

        return $this;
    }

    /**
     * @return string[]
     */
    public function getValidTokenStrings(): array
    {
        return $this->getApiTokens()
            ->filter(fn (ApiToken $token) => $token->isValid())
            ->map(fn (ApiToken $token) => $token->getToken())
            ->toArray()
            ;
    }

    public function markAsTokenAuthenticated(array $scopes)
    {
        $this->accessTokenScopes = $scopes;
    }

    public function setPlainPassword(string $plainPassword): User
    {
        $this->plainPassword = $plainPassword;
        return $this;
    }
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }
}
