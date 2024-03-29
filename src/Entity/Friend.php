<?php

namespace App\Entity;

use App\Repository\FriendRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FriendRepository::class)]
class Friend
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 40)]
    private ?string $firstName = null;

    #[ORM\Column(length: 30)]
    private ?string $lastName = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $phoneNumber = null;

    #[ORM\Column(length: 30, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 40)]
    private ?string $user = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $birthDate = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $notification_date = null;

    #[ORM\Column(nullable: true)]
    private ?int $notification_offset = null;

    private $checkBox = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(?string $phoneNumber): self
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }
    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getUser(): ?string
    {
        return $this->user;
    }

    public function setUser(string $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getBirthDate(): ?\DateTimeInterface
    {
        return $this->birthDate;
    }

    public function setBirthDate(\DateTimeInterface $birthDate): self
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    public function getNotificationDate(): ?\DateTimeInterface
    {
        return $this->notification_date;
    }

    public function setNotificationDate(?\DateTimeInterface $notification_date): self
    {
        $this->notification_date = $notification_date;

        return $this;
    }

    public function getNotificationOffset(): ?int
    {
        return $this->notification_offset;
    }

    public function setNotificationOffset(?int $notification_offset): self
    {
        $this->notification_offset = $notification_offset;

        return $this;
    }

    public function setCheckBox(bool $check)
    {
        $this->checkBox = $check;
    }

    public function getCheckBox(): bool
    {
        return $this->checkBox;
    }
}
