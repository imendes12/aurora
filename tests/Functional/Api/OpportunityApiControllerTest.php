<?php

declare(strict_types=1);

namespace App\Tests\Functional\Api;

use App\DataFixtures\Entity\AgentFixtures;
use App\DataFixtures\Entity\EventFixtures;
use App\DataFixtures\Entity\InitiativeFixtures;
use App\DataFixtures\Entity\OpportunityFixtures;
use App\DataFixtures\Entity\PhaseFixtures;
use App\DataFixtures\Entity\SpaceFixtures;
use App\Entity\Opportunity;
use App\Tests\AbstractWebTestCase;
use App\Tests\Fixtures\OpportunityTestFixtures;
use DateTimeInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;

class OpportunityApiControllerTest extends AbstractWebTestCase
{
    private const string BASE_URL = '/api/opportunities';

    public function testCanCreateWithPartialRequestBody(): void
    {
        $requestBody = OpportunityTestFixtures::partial();

        $client = self::apiClient();

        $client->request(Request::METHOD_POST, self::BASE_URL, content: json_encode($requestBody));

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $organization = $client->getContainer()->get(EntityManagerInterface::class)
            ->find(Opportunity::class, $requestBody['id']);

        $this->assertResponseBodySame([
            'id' => $requestBody['id'],
            'name' => $requestBody['name'],
            'parent' => null,
            'space' => null,
            'initiative' => null,
            'event' => null,
            'createdBy' => ['id' => AgentFixtures::AGENT_ID_1],
            'extraFields' => null,
            'phases' => [],
            'createdAt' => $organization->getCreatedAt()->format(DateTimeInterface::ATOM),
            'updatedAt' => null,
            'deletedAt' => null,
        ]);
    }

    public function testCanCreateWithCompleteRequestBody(): void
    {
        $requestBody = OpportunityTestFixtures::complete();

        $client = self::apiClient();

        $client->request(Request::METHOD_POST, self::BASE_URL, content: json_encode($requestBody));

        self::assertResponseStatusCodeSame(Response::HTTP_CREATED);

        $organization = $client->getContainer()->get(EntityManagerInterface::class)
            ->find(Opportunity::class, $requestBody['id']);

        $this->assertResponseBodySame([
            'id' => $requestBody['id'],
            'name' => $requestBody['name'],
            'parent' => [
                'id' => OpportunityFixtures::OPPORTUNITY_ID_1,
                'name' => 'Inscrição para o Concurso de Cordelistas - Festival de Literatura Nordestina',
                'parent' => null,
                'space' => ['id' => SpaceFixtures::SPACE_ID_1],
                'initiative' => ['id' => InitiativeFixtures::INITIATIVE_ID_1],
                'event' => ['id' => EventFixtures::EVENT_ID_1],
                'createdBy' => ['id' => AgentFixtures::AGENT_ID_1],
                'extraFields' => [
                    'type' => 'Cultural',
                    'period' => [
                        'startDate' => '2024-08-15',
                        'endDate' => '2024-09-15',
                    ],
                    'description' => 'O Festival de Literatura Nordestina é um evento cultural que reúne escritores, poetas, cordelistas e artistas populares para celebrar a literatura e a cultura do Nordeste.',
                    'areasOfActivity' => [
                        0 => 'Literatura',
                        1 => 'Cultura popular',
                    ],
                    'tags' => [
                        0 => 'Literatura',
                        1 => 'Cordel',
                        2 => 'Nordeste',
                    ],
                ],
                'phases' => [
                    [
                        'id' => PhaseFixtures::PHASE_ID_1,
                        'name' => 'Fase de submissão',
                    ],
                    [
                        'id' => PhaseFixtures::PHASE_ID_2,
                        'name' => 'Fase de validação',
                    ],
                    [
                        'id' => PhaseFixtures::PHASE_ID_3,
                        'name' => 'Fase de recurso',
                    ],
                ],
                'createdAt' => '2024-09-06T10:00:00+00:00',
                'updatedAt' => '2024-09-06T16:00:00+00:00',
                'deletedAt' => null,
            ],
            'space' => ['id' => SpaceFixtures::SPACE_ID_1],
            'initiative' => ['id' => InitiativeFixtures::INITIATIVE_ID_1],
            'event' => ['id' => EventFixtures::EVENT_ID_1],
            'createdBy' => ['id' => AgentFixtures::AGENT_ID_1],
            'extraFields' => [
                'type' => 'Cultural',
                'period' => [
                    'startDate' => '2024-08-15',
                    'endDate' => '2024-09-15',
                ],
                'description' => 'Oportunidade para escritores de cordel',
                'areasOfActivity' => [
                    0 => 'Literatura',
                    1 => 'Cultura popular',
                ],
                'tags' => [
                    0 => 'Literatura',
                    1 => 'Cordel',
                    2 => 'Nordeste',
                ],
            ],
            'phases' => [],
            'createdAt' => $organization->getCreatedAt()->format(DateTimeInterface::ATOM),
            'updatedAt' => null,
            'deletedAt' => null,
        ]);
    }

    #[DataProvider('provideValidationCreateCases')]
    public function testValidationCreate(array $requestBody, array $expectedErrors): void
    {
        $client = self::apiClient();

        $client->request(Request::METHOD_POST, self::BASE_URL, content: json_encode($requestBody));

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertResponseBodySame([
            'error_message' => 'not_valid',
            'error_details' => $expectedErrors,
        ]);
    }

    public static function provideValidationCreateCases(): array
    {
        $requestBody = OpportunityTestFixtures::partial();

        return [
            'missing required fields' => [
                'requestBody' => [],
                'expectedErrors' => [
                    ['field' => 'id', 'message' => 'This value should not be blank.'],
                    ['field' => 'name', 'message' => 'This value should not be blank.'],
                    ['field' => 'createdBy', 'message' => 'This value should not be blank.'],
                ],
            ],
            'id is not a valid UUID' => [
                'requestBody' => array_merge($requestBody, ['id' => 'invalid-uuid']),
                'expectedErrors' => [
                    ['field' => 'id', 'message' => 'This value is not a valid UUID.'],
                ],
            ],
            'name should be string' => [
                'requestBody' => array_merge($requestBody, ['name' => 123]),
                'expectedErrors' => [
                    ['field' => 'name', 'message' => 'This value should be of type string.'],
                ],
            ],
            'name too short' => [
                'requestBody' => array_merge($requestBody, ['name' => 'a']),
                'expectedErrors' => [
                    ['field' => 'name', 'message' => 'This value is too short. It should have 2 characters or more.'],
                ],
            ],
            'name too long' => [
                'requestBody' => array_merge($requestBody, ['name' => str_repeat('a', 101)]),
                'expectedErrors' => [
                    ['field' => 'name', 'message' => 'This value is too long. It should have 100 characters or less.'],
                ],
            ],
            'parent should exists' => [
                'requestBody' => array_merge($requestBody, ['parent' => Uuid::v4()->toRfc4122()]),
                'expectedErrors' => [
                    ['field' => 'parent', 'message' => 'This id does not exist.'],
                ],
            ],
            'space should exists' => [
                'requestBody' => array_merge($requestBody, ['space' => Uuid::v4()->toRfc4122()]),
                'expectedErrors' => [
                    ['field' => 'space', 'message' => 'This id does not exist.'],
                ],
            ],
            'initiative should exists' => [
                'requestBody' => array_merge($requestBody, ['initiative' => Uuid::v4()->toRfc4122()]),
                'expectedErrors' => [
                    ['field' => 'initiative', 'message' => 'This id does not exist.'],
                ],
            ],
            'event should exists' => [
                'requestBody' => array_merge($requestBody, ['event' => Uuid::v4()->toRfc4122()]),
                'expectedErrors' => [
                    ['field' => 'event', 'message' => 'This id does not exist.'],
                ],
            ],
            'createdBy should exists' => [
                'requestBody' => array_merge($requestBody, ['createdBy' => Uuid::v4()->toRfc4122()]),
                'expectedErrors' => [
                    ['field' => 'createdBy', 'message' => 'This id does not exist.'],
                ],
            ],
            'extraFields should be a valid JSON' => [
                'requestBody' => array_merge($requestBody, ['extraFields' => 'invalid-json']),
                'expectedErrors' => [
                    ['field' => 'extraFields', 'message' => 'This value should be of type json object.'],
                ],
            ],
        ];
    }

    public function testGet(): void
    {
        $client = static::apiClient();

        $client->request(Request::METHOD_GET, self::BASE_URL);
        $response = $client->getResponse()->getContent();

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertCount(count(OpportunityFixtures::OPPORTUNITIES), json_decode($response));

        $this->assertJsonContains([
            'id' => OpportunityFixtures::OPPORTUNITY_ID_10,
            'name' => 'Edital para Seleção de Artistas de Rua - Circuito Cultural Nordestino',
            'parent' => null,
            'space' => ['id' => SpaceFixtures::SPACE_ID_10],
            'initiative' => ['id' => InitiativeFixtures::INITIATIVE_ID_10],
            'event' => ['id' => EventFixtures::EVENT_ID_10],
            'createdBy' => ['id' => AgentFixtures::AGENT_ID_10],
            'createdAt' => '2024-09-15T19:00:00+00:00',
            'updatedAt' => null,
            'deletedAt' => null,
        ]);
    }

    public function testGetAnOpportunityItemWithSuccess(): void
    {
        $client = static::apiClient();

        $url = sprintf('%s/%s', self::BASE_URL, OpportunityFixtures::OPPORTUNITY_ID_3);

        $client->request(Request::METHOD_GET, $url);

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertResponseBodySame([
            'id' => OpportunityFixtures::OPPORTUNITY_ID_3,
            'name' => 'Credenciamento de Quadrilhas Juninas - São João do Nordeste',
            'parent' => null,
            'space' => [
                'id' => SpaceFixtures::SPACE_ID_3,
            ],
            'initiative' => [
                'id' => InitiativeFixtures::INITIATIVE_ID_3,
            ],
            'event' => [
                'id' => EventFixtures::EVENT_ID_3,
            ],
            'createdBy' => [
                'id' => AgentFixtures::AGENT_ID_3,
            ],
            'extraFields' => [
                'type' => 'Cultural',
                'period' => [
                    'startDate' => '2024-06-01',
                    'endDate' => '2024-06-30',
                ],
                'description' => 'O São João do Nordeste celebra as tradições juninas com apresentações de quadrilhas e eventos culturais.',
                'areasOfActivity' => [
                    0 => 'Dança',
                    1 => 'Cultura popular',
                ],
                'tags' => [
                    0 => 'Quadrilhas',
                    1 => 'São João',
                    2 => 'Nordeste',
                ],
            ],
            'phases' => [
                [
                    'id' => PhaseFixtures::PHASE_ID_6,
                    'name' => 'Fase de submissão',
                ],
                [
                    'id' => PhaseFixtures::PHASE_ID_7,
                    'name' => 'Fase de documentação',
                ],
            ],
            'createdAt' => '2024-09-08T12:00:00+00:00',
            'updatedAt' => null,
            'deletedAt' => null,
        ]);
    }

    public function testGetAResourceWhenNotFound(): void
    {
        $client = static::apiClient();

        $client->request(Request::METHOD_GET, sprintf('%s/%s', self::BASE_URL, Uuid::v4()->toRfc4122()));

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $this->assertResponseBodySame([
            'error_message' => 'not_found',
            'error_details' => [
                'description' => 'The requested Opportunity was not found.',
            ],
        ]);
    }

    public function testDeleteAResourceWhenNotFound(): void
    {
        $client = static::apiClient();

        $client->request(Request::METHOD_DELETE, sprintf('%s/%s', self::BASE_URL, Uuid::v4()->toRfc4122()));

        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);
        $this->assertResponseBodySame([
            'error_message' => 'not_found',
            'error_details' => [
                'description' => 'The requested Opportunity was not found.',
            ],
        ]);
    }

    public function testDeleteAResourceWithSuccess(): void
    {
        $client = static::apiClient();

        $url = sprintf('%s/%s', self::BASE_URL, OpportunityFixtures::OPPORTUNITY_ID_2);

        $client->request(Request::METHOD_DELETE, $url);

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }

    public function testCanUpdate(): void
    {
        $requestBody = OpportunityTestFixtures::complete();
        unset($requestBody['id']);

        $url = sprintf('%s/%s', self::BASE_URL, OpportunityFixtures::OPPORTUNITY_ID_4);
        $client = self::apiClient();

        $client->request(Request::METHOD_PATCH, $url, content: json_encode($requestBody));

        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $opportunity = $client->getContainer()->get(EntityManagerInterface::class)
            ->find(Opportunity::class, OpportunityFixtures::OPPORTUNITY_ID_4);

        $this->assertResponseBodySame([
            'id' => OpportunityFixtures::OPPORTUNITY_ID_4,
            'name' => $requestBody['name'],
            'parent' => [
                'id' => OpportunityFixtures::OPPORTUNITY_ID_1,
                'name' => 'Inscrição para o Concurso de Cordelistas - Festival de Literatura Nordestina',
                'parent' => null,
                'space' => ['id' => SpaceFixtures::SPACE_ID_1],
                'initiative' => ['id' => InitiativeFixtures::INITIATIVE_ID_1],
                'event' => ['id' => EventFixtures::EVENT_ID_1],
                'createdBy' => ['id' => AgentFixtures::AGENT_ID_1],
                'extraFields' => [
                    'type' => 'Cultural',
                    'period' => [
                        'startDate' => '2024-08-15',
                        'endDate' => '2024-09-15',
                    ],
                    'description' => 'O Festival de Literatura Nordestina é um evento cultural que reúne escritores, poetas, cordelistas e artistas populares para celebrar a literatura e a cultura do Nordeste.',
                    'areasOfActivity' => [
                        0 => 'Literatura',
                        1 => 'Cultura popular',
                    ],
                    'tags' => [
                        0 => 'Literatura',
                        1 => 'Cordel',
                        2 => 'Nordeste',
                    ],
                ],
                'phases' => [
                    [
                        'id' => PhaseFixtures::PHASE_ID_1,
                        'name' => 'Fase de submissão',
                    ],
                    [
                        'id' => PhaseFixtures::PHASE_ID_2,
                        'name' => 'Fase de validação',
                    ],
                    [
                        'id' => PhaseFixtures::PHASE_ID_3,
                        'name' => 'Fase de recurso',
                    ],
                ],
                'createdAt' => '2024-09-06T10:00:00+00:00',
                'updatedAt' => '2024-09-06T16:00:00+00:00',
                'deletedAt' => null,
            ],
            'space' => ['id' => $requestBody['space']],
            'initiative' => ['id' => $requestBody['initiative']],
            'event' => ['id' => $requestBody['event']],
            'createdBy' => ['id' => AgentFixtures::AGENT_ID_1],
            'extraFields' => [
                'type' => 'Cultural',
                'period' => [
                    'startDate' => '2024-08-15',
                    'endDate' => '2024-09-15',
                ],
                'description' => 'Oportunidade para escritores de cordel',
                'areasOfActivity' => [
                    0 => 'Literatura',
                    1 => 'Cultura popular',
                ],
                'tags' => [
                    0 => 'Literatura',
                    1 => 'Cordel',
                    2 => 'Nordeste',
                ],
            ],
            'phases' => [
                [
                    'id' => PhaseFixtures::PHASE_ID_8,
                    'name' => 'Fase de submissão',
                ],
                [
                    'id' => PhaseFixtures::PHASE_ID_9,
                    'name' => 'Fase de documentação',
                ],
            ],
            'createdAt' => $opportunity->getCreatedAt()->format(DateTimeInterface::ATOM),
            'updatedAt' => $opportunity->getUpdatedAt()->format(DateTimeInterface::ATOM),
            'deletedAt' => null,
        ]);
    }

    #[DataProvider('provideValidationUpdateCases')]
    public function testValidationUpdate(array $requestBody, array $expectedErrors): void
    {
        $client = self::apiClient();
        $url = sprintf('%s/%s', self::BASE_URL, OpportunityFixtures::OPPORTUNITY_ID_3);
        $client->request(Request::METHOD_PATCH, $url, content: json_encode($requestBody));

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $this->assertResponseBodySame([
            'error_message' => 'not_valid',
            'error_details' => $expectedErrors,
        ]);
    }

    public static function provideValidationUpdateCases(): array
    {
        $requestBody = OpportunityTestFixtures::partial();

        return [
            'name should be string' => [
                'requestBody' => array_merge($requestBody, ['name' => 123]),
                'expectedErrors' => [
                    ['field' => 'name', 'message' => 'This value should be of type string.'],
                ],
            ],
            'name too short' => [
                'requestBody' => array_merge($requestBody, ['name' => 'a']),
                'expectedErrors' => [
                    ['field' => 'name', 'message' => 'This value is too short. It should have 2 characters or more.'],
                ],
            ],
            'name too long' => [
                'requestBody' => array_merge($requestBody, ['name' => str_repeat('a', 101)]),
                'expectedErrors' => [
                    ['field' => 'name', 'message' => 'This value is too long. It should have 100 characters or less.'],
                ],
            ],
            'parent should exists' => [
                'requestBody' => array_merge($requestBody, ['parent' => Uuid::v4()->toRfc4122()]),
                'expectedErrors' => [
                    ['field' => 'parent', 'message' => 'This id does not exist.'],
                ],
            ],
            'space should exists' => [
                'requestBody' => array_merge($requestBody, ['space' => Uuid::v4()->toRfc4122()]),
                'expectedErrors' => [
                    ['field' => 'space', 'message' => 'This id does not exist.'],
                ],
            ],
            'initiative should exists' => [
                'requestBody' => array_merge($requestBody, ['initiative' => Uuid::v4()->toRfc4122()]),
                'expectedErrors' => [
                    ['field' => 'initiative', 'message' => 'This id does not exist.'],
                ],
            ],
            'event should exists' => [
                'requestBody' => array_merge($requestBody, ['event' => Uuid::v4()->toRfc4122()]),
                'expectedErrors' => [
                    ['field' => 'event', 'message' => 'This id does not exist.'],
                ],
            ],
            'createdBy should exists' => [
                'requestBody' => array_merge($requestBody, ['createdBy' => Uuid::v4()->toRfc4122()]),
                'expectedErrors' => [
                    ['field' => 'createdBy', 'message' => 'This id does not exist.'],
                ],
            ],
            'extraFields should be a valid JSON' => [
                'requestBody' => array_merge($requestBody, ['extraFields' => 'invalid-json']),
                'expectedErrors' => [
                    ['field' => 'extraFields', 'message' => 'This value should be of type json object.'],
                ],
            ],
        ];
    }
}
