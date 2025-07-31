<?php

namespace Modules\GiftCardManagement\Service;

use App\Service\BaseService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Modules\GiftCardManagement\Repository\GiftCardRepositoryInterface;
use Modules\GiftCardManagement\Service\Interface\GiftCardServiceInterface;

class GiftCardService extends BaseService implements GiftCardServiceInterface
{
    protected $giftCardRepository;

    public function __construct(GiftCardRepositoryInterface $giftCardRepository)
    {
        parent::__construct($giftCardRepository);
        $this->giftCardRepository = $giftCardRepository;
    }

    public function export(array $criteria = [], array $relations = [], array $orderBy = [], int $limit = null, int $offset = null): Collection|LengthAwarePaginator
    {
        return $this->giftCardRepository->getBy(criteria:$criteria, relations:$relations, orderBy:$orderBy, limit:$limit, offset:$offset);
    }

    public function getStatistics(array $criteria = [], array $relations = [], array $orderBy = [], int $limit = null, int $offset = null): Collection|LengthAwarePaginator
    {
        return $this->giftCardRepository->getBy(criteria:$criteria, relations:$relations, orderBy:$orderBy, limit:$limit, offset:$offset);
    }
}
