<?php

namespace App\Entity;

use App\Repository\AnimalRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AnimalRepository::class)]
class Animal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\Column(type: 'boolean')]
    private $isAlive;

    #[ORM\Column(type: 'datetime')]
    private $lastActive;

    #[ORM\Column(type: 'datetime')]
    private $createdAt;

    #[ORM\ManyToOne(targetEntity: AnimalType::class, inversedBy: 'animals')]
    #[ORM\JoinColumn(nullable: false)]
    private $AnimalType;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'animals')]
    #[ORM\JoinColumn(nullable: false)]
    private $user;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getIsAlive(): ?bool
    {
        return $this->isAlive;
    }

    public function setIsAlive(bool $isAlive): self
    {
        $this->isAlive = $isAlive;

        return $this;
    }

    public function getLastActive(): ?\DateTimeInterface
    {
        return $this->lastActive;
    }

    public function setLastActive(\DateTimeInterface $lastActive): self
    {
        $this->lastActive = $lastActive;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getAnimalType(): ?AnimalType
    {
        return $this->AnimalType;
    }

    public function setAnimalType(?AnimalType $AnimalType): self
    {
        $this->AnimalType = $AnimalType;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
