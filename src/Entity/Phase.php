<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PhaseRepository;
use DateTime;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: PhaseRepository::class)]
class Phase
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME)]
    #[Groups(['phase.get', 'opportunity.get.item'])]
    private ?Uuid $id = null;

    #[ORM\Column(length: 100)]
    #[Groups(['phase.get', 'opportunity.get.item'])]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['phase.get'])]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['phase.get'])]
    private ?DateTime $startDate = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['phase.get'])]
    private ?DateTime $endDate = null;

    #[ORM\Column]
    #[Groups(['phase.get'])]
    private ?bool $status = null;

    #[ORM\Column(nullable: true)]
    #[Groups(['phase.get'])]
    private ?int $sequence = null;

    #[ORM\ManyToOne(targetEntity: Agent::class)]
    #[ORM\JoinColumn(name: 'created_by_id', referencedColumnName: 'id', nullable: false)]
    #[Groups(['phase.get'])]
    private ?Agent $createdBy = null;

    #[ORM\ManyToOne(targetEntity: Opportunity::class, inversedBy: 'phases')]
    #[ORM\JoinColumn(name: 'opportunity_id', referencedColumnName: 'id', nullable: false)]
    #[Groups(['phase.get'])]
    private ?Opportunity $opportunity = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    #[Groups(['phase.get.item'])]
    private ?array $extraFields = null;

    #[ORM\Column]
    #[Groups('phase.get')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    #[Groups('phase.get')]
    private ?DateTime $updatedAt = null;

    #[ORM\Column(nullable: true)]
    #[Groups('phase.get')]
    private ?DateTime $deletedAt = null;

    public function __construct()
    {
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): void
    {
        $this->id = $id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getStartDate(): ?DateTime
    {
        return $this->startDate;
    }

    public function setStartDate(?DateTime $startDate): void
    {
        $this->startDate = $startDate;
    }

    public function getEndDate(): ?DateTime
    {
        return $this->endDate;
    }

    public function setEndDate(?DateTime $endDate): void
    {
        $this->endDate = $endDate;
    }

    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): void
    {
        $this->status = $status;
    }

    public function getSequence(): ?int
    {
        return $this->sequence;
    }

    public function setSequence(?int $sequence): void
    {
        $this->sequence = $sequence;
    }

    public function getCreatedBy(): Agent
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?Agent $createdBy): void
    {
        $this->createdBy = $createdBy;
    }

    public function getOpportunity(): ?Opportunity
    {
        return $this->opportunity;
    }

    public function setOpportunity(?Opportunity $opportunity): void
    {
        $this->opportunity = $opportunity;
    }

    public function getExtraFields(): ?array
    {
        return $this->extraFields;
    }

    public function setExtraFields(?array $extraFields): void
    {
        $this->extraFields = $extraFields;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?DateTime $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    public function getDeletedAt(): ?DateTime
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?DateTime $deletedAt): void
    {
        $this->deletedAt = $deletedAt;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id?->toRfc4122(),
            'name' => $this->name,
            'startDate' => $this->createdAt->format('Y-m-d H:i:s'),
            'endDate' => $this->createdAt->format('Y-m-d H:i:s'),
            'status' => $this->status,
            'sequence' => $this->sequence,
            'createdBy' => $this->createdBy->toArray(),
            'opportunity' => $this->getOpportunity()->toArray(),
            'extraFields' => $this->getExtraFields(),
            'createdAt' => $this->createdAt->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updatedAt?->format('Y-m-d H:i:s'),
            'deletedAt' => $this->deletedAt?->format('Y-m-d H:i:s'),
        ];
    }
}
