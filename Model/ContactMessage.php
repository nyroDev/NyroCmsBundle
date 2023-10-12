<?php

namespace NyroDev\NyroCmsBundle\Model;

use DateTimeInterface;
use Gedmo\Mapping\Annotation as Gedmo;

abstract class ContactMessage
{
    protected $id;

    protected ?ContentHandler $contentHandler = null;

    protected ?string $dest = null;

    protected ?string $lastname = null;

    protected ?string $firstname = null;

    protected ?string $company = null;

    protected ?string $phone = null;

    protected ?string $email = null;

    protected ?string $message = null;

    #[Gedmo\Timestampable(on: 'create')]
    protected ?DateTimeInterface $inserted = null;

    public function getId()
    {
        return $this->id;
    }

    public function setContentHandler(ContentHandler $contentHandler): static
    {
        $this->contentHandler = $contentHandler;

        return $this;
    }

    public function getContentHandler(): ?ContentHandler
    {
        return $this->contentHandler;
    }

    public function setDest(?string $dest): static
    {
        $this->dest = $dest;

        return $this;
    }

    public function getDest(): ?string
    {
        return $this->dest;
    }

    public function setLastname(?string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setFirstname(?string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setCompany(?string $company): static
    {
        $this->company = $company;

        return $this;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setMessage(?string $message): static
    {
        $this->message = $message;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setInserted(DateTimeInterface $inserted): static
    {
        $this->inserted = $inserted;

        return $this;
    }

    public function getInserted(): ?DateTimeInterface
    {
        return $this->inserted;
    }
}
