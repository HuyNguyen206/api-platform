<?php

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\BooleanFilter;
use ApiPlatform\Doctrine\Orm\Filter\RangeFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\JsonLd\Serializer\ObjectNormalizer;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Response;
use ApiPlatform\Serializer\Filter\PropertyFilter;
use App\Controller\DownloadController;
use App\Entity\Traits\Timestamp;
use App\Repository\DragonTreasureRepository;
use App\Validator\IsValidOwner;
use Carbon\Carbon;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Mapping\Annotation\Timestampable;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Contracts\Service\Attribute\Required;
use function Symfony\Component\String\u;

#[ApiResource(
    shortName: 'Treasure',
    description: 'Rare and valuable resources',
    operations: [
        new Get(uriTemplate: 'download/{id}',
            controller: DownloadController::class,
            read: false, normalizationContext: ['groups' => 'nothing'],
            openapi: new Operation(
                responses: [
                    '200' => new Response(
                        description: 'Download', content: new \ArrayObject([
                        'application/pdf' => [
                            'schema' => [
                                'type' => 'object',
//                                'properties' => [
//                                    'name' => ['type' => 'string'],
//                                    'description' => ['type' => 'string']
//                                ]
                            ]
                    ]])
                    )
                ]
            )
        ),
        new Get(normalizationContext: ['groups' => ['treasure:read', 'treasure:item:read']]),
        new GetCollection(),
        new Post(security: 'is_granted("ROLE_TREASURE_CREATE")',),
//        new Put( security: 'is_granted("ROLE_TREASURE_EDIT")',),
        new Patch(
            security: 'is_granted("EDIT", object)',
//            securityPostDenormalize: 'is_granted("EDIT", object)'
        ),
        new Delete(  security: 'is_granted("ROLE_ADMIN")',)
    ],
    formats: [
        'jsonld',
        'json',
        'html',
        'jsonhal',
        'csv' => 'text/csv'
    ],
    normalizationContext: [
        'groups' => ['treasure:read']
    ],
    denormalizationContext: [
        'groups' => ['treasure:write'],
        AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => false
    ],
    paginationItemsPerPage: 10
)]
#[ApiResource(
    uriTemplate: 'users/{user_id}/treasures.{_format}',
    shortName: 'Treasure',
    operations: [
        new GetCollection()
    ],
    uriVariables: [
        'user_id' => new Link(
            fromProperty: 'dragonTreasures', fromClass: User::class,
//            toProperty: 'owner'
        )
    ],
    normalizationContext: [
        'groups' => ['treasure:read']
    ],
    denormalizationContext: [
        AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => false
    ],
)]
#[ApiFilter(BooleanFilter::class, properties: ['isPublished'])]
#[ApiFilter(PropertyFilter::class)]
#[ORM\Entity(repositoryClass: DragonTreasureRepository::class)]
#[ApiFilter(SearchFilter::class, properties: ['owner.name' => 'partial'])]
class DragonTreasure
{
    use Timestamp;

    /**
     * @var \DateTime|null
     * @Timestampable(on="create")
     * @Column(type="datetime")
     */
    #[Timestampable(on: 'create')]
    #[Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['treasure:read'])]
    protected $createdAt;

    /**
     * @var \DateTime|null
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    #[Timestampable(on: 'update')]
    #[Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['treasure:read'])]
    protected $updatedAt;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['treasure:read'])]
    private ?int $id = null;

    #[Groups(['treasure:read', 'treasure:write', 'users:item:read'])]
    #[ORM\Column(length: 255)]
    #[ApiFilter(SearchFilter::class, strategy: SearchFilter::STRATEGY_IPARTIAL)]
    #[NotBlank]
    #[NotNull]
    private ?string $name = null;

    #[Groups(['treasure:read', 'users:item:read'])]
    #[ORM\Column(type: Types::TEXT)]
    #[ApiFilter(SearchFilter::class, strategy: SearchFilter::STRATEGY_IPARTIAL)]
    private ?string $description = null;

    /**
     * Value of the treasure
     */
    #[Groups(['treasure:read', 'treasure:write', 'users:item:read'])]
    #[ORM\Column]
    #[ApiFilter(RangeFilter::class)]
    #[GreaterThanOrEqual(0)]
    #[NotBlank]
    #[NotNull]
    #[Type('integer')]
    private ?int $value = 0;

    #[Groups(['treasure:read', 'treasure:write'])]
    #[ORM\Column]
    #[GreaterThanOrEqual(0)]
    #[LessThanOrEqual(10)]
    #[NotBlank]
    #[Type('numeric')]
    private ?int $coolFactor;

    #[Groups(['admin:read', 'admin:write', 'owner:read'])]
    #[ORM\Column]
    #[Type('bool')]
//    #[ApiProperty(readable: false)]
//    #[ApiProperty(security: 'is_granted("EDIT", object)')] //To indicate this property should return when user can edit via DragonTreasureVoter
    private ?bool $isPublished = false;

    #[Groups(['treasure:read', 'treasure:write'])]
    #[ORM\ManyToOne(inversedBy: 'dragonTreasures')]
    #[ORM\JoinColumn(name: 'owner_id', onDelete: 'CASCADE')]
    #[ApiFilter(SearchFilter::class, 'exact')]
    #[IsValidOwner]
    private ?User $owner = null;

    public function __construct(string $name = null)
    {
        $this->name = $name;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    #[Groups(['treasure:read'])]
    public function getShortDescription(): ?string
    {
        return u($this->description)->truncate(10, '...');
    }

    #[Groups(['treasure:read'])]
    public function getIsRich(): ?string
    {
        return true;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    #[Groups(['treasure:read'])]
    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    #[Groups(['treasure:read'])]
    public function getCreatedAtAgo(): ?string
    {
        return Carbon::parse($this->createdAt)->diffForHumans();
    }

    #[Groups(['treasure:write'])]
    #[SerializedName('description')]
    public function setTextDescription(string $description): static
    {
        $this->description = nl2br($description);

        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getCoolFactor(): ?int
    {
        return $this->coolFactor;
    }

    public function setCoolFactor(?int $coolFactor): static
    {
        $this->coolFactor = $coolFactor;

        return $this;
    }

    public function getIsPublished()
    {
        return $this->isPublished;
    }

    public function setIsPublished(?bool $isPublished): static
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): static
    {
        $this->owner = $owner;

        return $this;
    }
}
