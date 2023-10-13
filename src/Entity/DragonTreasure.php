<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Traits\Timestamp;
use App\Repository\DragonTreasureRepository;
use Carbon\Carbon;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

#[ApiResource(
    shortName: 'Treasure',
    description: 'Rare and valuable resources', operations: [
    new Get('custom-treasure-url/{id}'),
    new GetCollection('get-list'),
    new Post('store'),
    new Put('update/{id}'),
    new Patch('toggle/{id}'),
//    new Delete()
],
    normalizationContext: [
        'groups' => ['treasure:read']
    ],
    denormalizationContext: [
        'groups' => ['treasure:write']
    ]
)]
#[ORM\Entity(repositoryClass: DragonTreasureRepository::class)]
class DragonTreasure
{
    use Timestamp;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Groups(['treasure:read', 'treasure:write'])]
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[Groups(['treasure:read'])]
    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    /**
     * Value of the treasure
     */
    #[Groups(['treasure:read', 'treasure:write'])]
    #[ORM\Column]
    private ?int $value = null;

    #[Groups(['treasure:read', 'treasure:write'])]
    #[ORM\Column]
    private ?int $coolFactor = null;

    #[Groups(['treasure:read', 'treasure:write'])]
    #[ORM\Column]
    private ?bool $isPublished = false;

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

//    public function setName(string $name): static
//    {
//        $this->name = $name;
//
//        return $this;
//    }

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

    public function getValue(): ?int
    {
        return $this->value;
    }

    public function setValue(int $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getCoolFactor(): ?int
    {
        return $this->coolFactor;
    }

    public function setCoolFactor(int $coolFactor): static
    {
        $this->coolFactor = $coolFactor;

        return $this;
    }

    public function getIsPublished(): ?bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): static
    {
        $this->isPublished = $isPublished;

        return $this;
    }
}
