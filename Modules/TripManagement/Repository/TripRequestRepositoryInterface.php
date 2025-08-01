<?php

namespace Modules\TripManagement\Repository;

use App\Repository\EloquentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

interface TripRequestRepositoryInterface extends EloquentRepositoryInterface
{
    public function calculateCouponAmount($startDate = null, $endDate = null, $startTime = null, $month = null, $year = null): mixed;

    public function fetchTripData($dateRange): Collection;

    public function statusWiseTotalTripRecords(array $attributes): Collection;

    public function pendingParcelList(array $attributes);

    public function updateRelationalTable($attributes): mixed;

    public function findOneWithAvg(array $criteria = [], array $relations = [], array $withCountQuery = [], bool $withTrashed = false, bool $onlyTrashed = false, array $withAvgRelation = []): ?Model;

    public function getWithAvg(array $criteria = [], array $searchCriteria = [], array $whereInCriteria = [], array $relations = [], array $orderBy = [], int $limit = null, int $offset = null, bool $onlyTrashed = false, bool $withTrashed = false, array $withCountQuery = [], array $withAvgRelation = [], array $whereBetweenCriteria = [], array $whereNotNullCriteria = []): Collection|LengthAwarePaginator;

    public function getPendingRides($attributes): mixed;

    public function getZoneWiseStatistics(array $criteria = [], array $searchCriteria = [], array $whereInCriteria = [], array $whereBetweenCriteria = [], array $whereHasRelations = [], array $withAvgRelations = [], array $relations = [], array $orderBy = [], int $limit = null, int $offset = null, bool $onlyTrashed = false, bool $withTrashed = false, array $withCountQuery = [], array $appends = []): Collection|LengthAwarePaginator;

    public function getZoneWiseEarning(array $criteria = [], array $searchCriteria = [], array $whereInCriteria = [], array $whereBetweenCriteria = [], array $whereHasRelations = [], array $withAvgRelations = [], array $relations = [], array $orderBy = [], int $limit = null, int $offset = null, bool $onlyTrashed = false, bool $withTrashed = false, array $withCountQuery = [], array $appends = [], $startDate = null, $endDate = null, $startTime = null, $month = null, $year = null): Collection|LengthAwarePaginator;

    public function getLeaderBoard(string $userType, array $criteria = [], array $searchCriteria = [], array $whereInCriteria = [], array $whereBetweenCriteria = [], array $whereHasRelations = [], array $withAvgRelations = [], array $relations = [], array $orderBy = [], int $limit = null, int $offset = null, bool $onlyTrashed = false, bool $withTrashed = false, array $withCountQuery = [], array $appends = []): Collection|LengthAwarePaginator;

    public function getPopularTips();

    public function getTripHeatMapCompareDataBy(array $criteria = [], array $searchCriteria = [], array $whereInCriteria = [], array $whereBetweenCriteria = [], array $whereHasRelations = [], array $withAvgRelations = [], array $relations = [], array $orderBy = [], int $limit = null, int $offset = null, bool $onlyTrashed = false, bool $withTrashed = false, array $withCountQuery = [], array $appends = [], $startDate = null, $endDate = null): Collection|LengthAwarePaginator;
    public function allRideList(array $criteria = [], array $relations = [], array $orderBy = []): mixed;

    public function getPendingParcel(array $criteria = [], array $relations = [], array $orderBy = [], int $limit = null, int $offset = null): mixed;

    public function getPendingRide(array $criteria = [], array $relations = [], array $whereHasRelations = [],  array $orderBy = [], array $attributes = []): mixed;

    public function getLockedTrip(array $data = []): mixed;

    public function getIncompleteRide(array $criteria = []): mixed;

    public function getRidesBy(array $data, array $whereBetweenCriteria = []): mixed;

    public function getCustomerPendingRideList(array $data, array $relations, int $limit = null, int $offset = null): mixed;
}
