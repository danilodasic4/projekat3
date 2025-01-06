<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\MappedSuperclass]
abstract class AbstractVehicle
{
    #[ORM\Column(length: 255)]
    private ?string $brand = null;

    #[ORM\Column(length: 255)]
    private ?string $model = null;

    #[ORM\Column]
    private ?int $year = null;

    #[ORM\Column]
    private ?int $engineCapacity = null;

    #[ORM\Column]
    private ?int $horsePower = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $color = null;

    #[ORM\Column(type: "date")]
    private ?\DateTimeInterface $registrationDate = null;

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
}
