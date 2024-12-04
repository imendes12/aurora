<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Helper\EntityIdNormalizerHelper;
use App\Service\Interface\FaqServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Uid\Uuid;

class FaqApiController extends AbstractApiController
{
    public function __construct(
        public readonly FaqServiceInterface $service,
    ) {
    }

    public function create(Request $request): JsonResponse
    {
        $faq = $this->service->create($request->toArray());

        return $this->json(
            data: $faq,
            status: Response::HTTP_CREATED,
            context: ['groups' => ['faq.get', 'faq.get.item']]
        );
    }

    public function get(Uuid $id): JsonResponse
    {
        $faq = $this->service->get($id);

        return $this->json($faq, context: ['groups' => ['faq.get', 'faq.get.item']]);
    }

    public function list(): JsonResponse
    {
        return $this->json($this->service->list(), context: [
            'groups' => 'faq.get',
            AbstractNormalizer::CALLBACKS => [
                'parent' => [EntityIdNormalizerHelper::class, 'normalizeEntityId'],
            ],
        ]);
    }

    public function remove(Uuid $id): JsonResponse
    {
        $this->service->remove($id);

        return $this->json(data: [], status: Response::HTTP_NO_CONTENT);
    }

    public function update(Uuid $id, Request $request): JsonResponse
    {
        $faq = $this->service->update($id, $request->toArray());

        return $this->json(
            data: $faq,
            status: Response::HTTP_OK,
            context: ['groups' => ['faq.get', 'faq.get.item']]
        );
    }
}