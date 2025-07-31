<?php

namespace Modules\GiftCardManagement\Service\Interface;

use App\Service\BaseServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface GiftCardServiceInterface extends BaseServiceInterface
{
    public function export(array $criteria = [], array $relations = [], array $orderBy = [], int $limit = null, int $offset = null): Collection|LengthAwarePaginator;

    public function getStatistics(array $criteria = [], array $relations = [], array $orderBy = [], int $limit = null, int $offset = null): Collection|LengthAwarePaginator;

}
