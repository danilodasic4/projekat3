<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use OpenApi\Attributes as OA;

#[ORM\MappedSuperclass]
abstract class AbstractVehicle
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[OA\Property(description: "The unique identifier of the vehicle")]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[OA\Property(description: "Brand of the vehicle")]
    protected ?string $brand = null;

    #[ORM\Column(length: 255)]
    #[OA\Property(description: "Model of the vehicle")]
    protected ?string $model = null;

    #[ORM\Column]
    #[OA\Property(description: "Year of manufacture")]
    protected ?int $year = null;

    #[ORM\Column]
    #[OA\Property(description: "Engine capacity in cubic centimeters")]
    protected ?int $engineCapacity = null;

    #[ORM\Column]
    #[OA\Property(description: "Horse power of the vehicle")]
    protected ?int $horsePower = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[OA\Property(description: "Color of the vehicle", nullable: true)]
    protected ?string $color = null;

    #[ORM\Column(type: "date")]
    #[OA\Property(type: "string", format: "date", description: "Registration date of the vehicle")]
    protected ?\DateTimeInterface $registrationDate = null;

    #[ORM\Column(type: "datetime")]
    #[OA\Property(type: "string", format: "date-time", description: "Creation timestamp")]
    protected ?\DateTimeInterface $created_at = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    #[OA\Property(type: "string", format: "date-time", description: "Last updated timestamp", nullable: true)]
    protected ?\DateTimeInterface $updated_at = null;

    #[ORM\Column(type: "datetime", nullable: true)]
    #[OA\Property(type: "string", format: "date-time", description: "Timestamp when the vehicle was deleted, if applicable", nullable: true)]
    protected ?\DateTimeInterface $deleted_at = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'cars')]
    #[ORM\JoinColumn(nullable: false)] 
    #[OA\Property(ref: "#/components/schemas/User", description: "User who owns the car")]
    private ?User $user = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): self
    {
        $this->brand = $brand;
        return $this;
    }

    public function getModel(): ?string
    {
        return $this->model;
    }

    public function setModel(string $model): self
    {
        $this->model = $model;
        return $this;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(int $year): self
    {
        $this->year = $year;
        return $this;
    }

    public function getEngineCapacity(): ?int
    {
        return $this->engineCapacity;
    }

    public function setEngineCapacity(int $engineCapacity): self
    {
        $this->engineCapacity = $engineCapacity;
        return $this;
    }

    public function getHorsePower(): ?int
    {
        return $this->horsePower;
    }

    public function setHorsePower(int $horsePower): self
    {
        $this->horsePower = $horsePower;
        return $this;
    }

    public function getColor(): ?string
    {
        return $this->color;
    }

    public function setColor(?string $color): self
    {
        $this->color = $color;
        return $this;
    }

    public function getRegistrationDate(): ?\DateTimeInterface
    {
        return $this->registrationDate;
    }

    public function setRegistrationDate(\DateTimeInterface $registrationDate): self
    {
        $this->registrationDate = $registrationDate;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): self
    {
        $this->created_at = $created_at;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(?\DateTimeInterface $updated_at): self
    {
        $this->updated_at = $updated_at;
        return $this;
    }

    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deleted_at;
    }

    public function setDeletedAt(?\DateTimeInterface $deleted_at): self
    {
        $this->deleted_at = $deleted_at;
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

}

