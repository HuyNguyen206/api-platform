<?php

namespace App\Entity\Traits;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Mapping\Annotation\Timestampable;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Annotation\Groups;

trait Timestamp
{
    use TimestampableEntity;

//    /**
//     * @var \DateTime|null
//     * @Timestampable(on="create")
//     * @Column(type="datetime")
//     */
//    #[Timestampable(on: 'create')]
//    #[Column(type: Types::DATETIME_MUTABLE)]
//    protected $createdAt;
//
//    /**
//     * @var \DateTime|null
//     * @Gedmo\Timestampable(on="update")
//     * @ORM\Column(type="datetime")
//     */
//    #[Timestampable(on: 'update')]
//    #[Column(type: Types::DATETIME_MUTABLE)]
//    protected $updatedAt;
}