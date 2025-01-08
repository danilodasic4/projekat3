<?php

namespace App\Entity;

use App\Repository\CarRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Attributes as OA;

#[ORM\Entity(repositoryClass: CarRepository::class)]
#[ORM\Table(name: "cars")]
#[Gedmo\SoftDeleteable(fieldName: "deleted_at")] 
#[OA\Schema(
    description: "Represents a car in the system",
    type: "object",
    properties: [
        new OA\Property(property: "id", type: "integer", description: "The unique identifier of the car"),
        new OA\Property(property: "brand", type: "string", description: "Brand of the car"),
        new OA\Property(property: "model", type: "string", description: "Model of the car"),
        new OA\Property(property: "year", type: "integer", description: "Year of manufacture"),
        new OA\Property(property: "engineCapacity", type: "integer", description: "Engine capacity in cubic centimeters"),
        new OA\Property(property: "horsePower", type: "integer", description: "Horse power of the car"),
        new OA\Property(property: "color", type: "string", description: "Color of the car", nullable: true),
        new OA\Property(property: "user", ref: "#/components/schemas/User", description: "User who owns the car"),
        new OA\Property(property: "created_at", type: "string", format: "date-time", description: "Creation timestamp"),
        new OA\Property(property: "updated_at", type: "string", format: "date-time", description: "Last updated timestamp", nullable: true),
        new OA\Property(property: "deleted_at", type: "string", format: "date-time", description: "Timestamp when the car was deleted, if applicable", nullable: true),
        new OA\Property(property: "registrationDate", type: "string", format: "date", description: "Registration date of the car")
    ]
)]
class Car extends AbstractVehicle
{

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'cars')]
    #[ORM\JoinColumn(nullable: false)] 
    #[OA\Property(ref: "#/components/schemas/User", description: "User who owns the car")]
    private ?User $user = null;
    
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[OA\Property(type: "string", format: "date-time", description: "Creation timestamp")]
    private ?\DateTimeInterface $created_at = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[OA\Property(type: "string", format: "date-time", description: "Last updated timestamp", nullable: true)]
    private ?\DateTimeInterface $updated_at = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[OA\Property(type: "string", format: "date-time", description: "Timestamp when the car was deleted, if applicable", nullable: true)]
    private ?\DateTimeInterface $deleted_at = null;



    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTimeInterface $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deleted_at;
    }

    public function setDeletedAt(?\DateTimeInterface $deleted_at): static
    {
        $this->deleted_at = $deleted_at;

        return $this;
    }

}
