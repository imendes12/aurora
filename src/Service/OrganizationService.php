<?php

declare(strict_types=1);

namespace App\Service;

use App\DTO\OrganizationDto;
use App\Entity\Organization;
use App\Exception\Organization\OrganizationResourceNotFoundException;
use App\Exception\ValidatorException;
use App\Repository\Interface\OrganizationRepositoryInterface;
use App\Service\Interface\OrganizationServiceInterface;
use DateTime;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class OrganizationService extends AbstractEntityService implements OrganizationServiceInterface
{
    public function __construct(
        private OrganizationRepositoryInterface $repository,
        private Security $security,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
    ) {
        parent::__construct($security);
    }

    public function count(): int
    {
        return $this->repository->count(
            $this->getDefaultParams()
        );
    }

    public function create(array $organization): Organization
    {
        $organizationDto = $this->serializer->denormalize($organization, OrganizationDto::class);

        $violations = $this->validator->validate($organizationDto, groups: OrganizationDto::CREATE);

        if ($violations->count() > 0) {
            throw new ValidatorException(violations: $violations);
        }

        $organizationObj = $this->serializer->denormalize($organization, Organization::class);

        return $this->repository->save($organizationObj);
    }

    public function findBy(array $params = [], int $limit = 50): array
    {
        return $this->repository->findBy(
            [...$params, ...$this->getUserParams()],
            ['createdAt' => 'DESC'],
            $limit
        );
    }

    public function findOneBy(array $params): ?Organization
    {
        return $this->repository->findOneBy(
            [...$params, ...$this->getDefaultParams()]
        );
    }

    public function get(Uuid $id): Organization
    {
        $organization = $this->repository->findOneBy([
            ...['id' => $id],
            ...$this->getDefaultParams(),
        ]);

        if (null === $organization) {
            throw new OrganizationResourceNotFoundException();
        }

        return $organization;
    }

    public function list(int $limit = 50): array
    {
        return $this->repository->findBy(
            $this->getDefaultParams(),
            ['createdAt' => 'DESC'],
            $limit
        );
    }

    public function remove(Uuid $id): void
    {
        $organization = $this->findOneBy(
            [...['id' => $id], ...$this->getUserParams()]
        );

        if (null === $organization) {
            throw new OrganizationResourceNotFoundException();
        }

        $organization->setDeletedAt(new DateTime());

        $this->repository->save($organization);
    }

    public function update(Uuid $identifier, array $organization): Organization
    {
        $organizationFromDB = $this->get($identifier);

        $organizationDto = $this->serializer->denormalize($organization, OrganizationDto::class);

        $violations = $this->validator->validate($organizationDto, groups: OrganizationDto::UPDATE);

        if ($violations->count() > 0) {
            throw new ValidatorException(violations: $violations);
        }

        $organizationObj = $this->serializer->denormalize($organization, Organization::class, context: [
            'object_to_populate' => $organizationFromDB,
        ]);

        $organizationObj->setUpdatedAt(new DateTime());

        return $this->repository->save($organizationObj);
    }
}
