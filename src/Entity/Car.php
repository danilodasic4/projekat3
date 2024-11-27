<?php

namespace App\Entity;

use App\Repository\CarRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Attributes as OA;

#[ORM\Entity(repositoryClass: CarRepository::class)]
#[ORM\Table(name: "cars")]
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
class Car
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[OA\Property(description: "The unique identifier of the car")]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[OA\Property(description: "Brand of the car")]
    private ?string $brand = null;

    #[ORM\Column(length: 255)]
    #[OA\Property(description: "Model of the car")]
    private ?string $model = null;

    #[ORM\Column]
    #[OA\Property(description: "Year of manufacture")]
    private ?int $year = null;

    #[ORM\Column]
    #[OA\Property(description: "Engine capacity in cubic centimeters")]
    private ?int $engineCapacity = null;

    #[ORM\Column]
    #[OA\Property(description: "Horse power of the car")]
    private ?int $horsePower = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[OA\Property(description: "Color of the car", nullable: true)]
    private ?string $color = null;

    #[ORM\ManyToOne(inversedBy: 'cars')]
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

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[OA\Property(type: "string", format: "date", description: "Registration date of the car")]
    private ?\DateTimeInterface $registrationDate = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): static
    {
        $this->brand = $brand;

        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(string $model): static
    {
        $this->model = $model;

        return $this;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(int $year): static
    {
        $this->year = $year;

        return $this;
    }

    public function getEngineCapacity(): ?int
    {
        return $this->engineCapacity;
    }

    public function setEngineCapacity(int $engineCapacity): static
    {
        $this->engineCapacity = $engineCapacity;

        return $this;
    }

    public function getHorsePower(): ?int
    {
        return $this->horsePower;
    }

    public function setHorsePower(int $horsePower): static
    {
        $this->horsePower = $horsePower;

        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): static
    {
        $this->color = $color;

        return $this;
    }

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

    public function getRegistrationDate(): ?\DateTimeInterface
    {
        return $this->registrationDate;
    }

    public function setRegistrationDate(\DateTimeInterface $registrationDate): static
    {
        $this->registrationDate = $registrationDate;

        return $this;
    }
}
