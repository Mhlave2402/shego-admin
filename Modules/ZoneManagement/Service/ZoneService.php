<?php

namespace Modules\ZoneManagement\Service;

use App\Service\BaseService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use MatanYadaev\EloquentSpatial\Objects\LineString;
use MatanYadaev\EloquentSpatial\Objects\Point;
use MatanYadaev\EloquentSpatial\Objects\Polygon;
use Modules\ZoneManagement\Entities\Zone;
use Modules\ZoneManagement\Repository\ZoneRepositoryInterface;
use Modules\ZoneManagement\Service\Interface\ZoneServiceInterface;

class ZoneService extends BaseService implements ZoneServiceInterface
{
    protected $zoneRepository;

    public function __construct(ZoneRepositoryInterface $zoneRepository)
    {
        parent::__construct($zoneRepository);
        $this->zoneRepository = $zoneRepository;
    }

    public function index(array $criteria = [], array $relations = [], array $whereHasRelations = [], array $orderBy = [], int $limit = null, int $offset = null, array $withCountQuery = [], array $appends = [], array $groupBy = []): Collection|LengthAwarePaginator
    {
        $data = [];
        if (array_key_exists('status', $criteria) && $criteria['status'] !== 'all') {
            $data['is_active'] = $criteria['status'] == 'active' ? 1 : 0;
        }
        $searchData = [];
        if (array_key_exists('search', $criteria) && $criteria['search'] != '') {
            $searchData['fields'] = ['name','readable_id'];
            $searchData['value'] = $criteria['search'];
        }
        $whereInCriteria = [];
        $whereBetweenCriteria = [];
        return $this->baseRepository->getBy(criteria: $data, searchCriteria: $searchData, whereInCriteria: $whereInCriteria, whereBetweenCriteria: $whereBetweenCriteria, whereHasRelations: $whereHasRelations, relations: $relations, orderBy: $orderBy, limit: $limit, offset: $offset, withCountQuery: $withCountQuery, appends: $appends, groupBy: $groupBy); // TODO: Change the autogenerated stub
    }

    protected function createPoint($coordinates)
    {

        foreach (explode('),(', trim($coordinates, '()')) as $index => $single_array) {
            if ($index == 0) {
                $lastcord = explode(',', $single_array);
            }
            $coords = explode(',', $single_array);
            $polygon[] = new Point($coords[0], $coords[1]);
        }

        $polygon[] = new Point($lastcord[0], $lastcord[1]);
        return new Polygon([new LineString($polygon)]);
    }

    public function getZones(array $criteria = []): array
    {
        $data = [];
        if (array_key_exists('id', $criteria) && $criteria['id']) {
            $data['id'] = $criteria['id'];
        }
        if (array_key_exists('status', $criteria) && $criteria['status']) {
            if ($criteria['status'] !== 'all') {
                $data['is_active'] = $criteria['status'] == 'active' ? 1 : 0;
            }
        }
        $allZones = $this->zoneRepository->getBy(criteria: $data);
        $allZoneData = [];
        foreach ($allZones as $item) {
            $zoneCoordinate = json_decode($item->coordinates[0]->toJson(), true);
            $allZoneData[] = formatCoordinates($zoneCoordinate['coordinates']);
        }
        return $allZoneData;
    }

    public function create(array $data): ?Model
    {
        if (businessConfig('extra_fare_status')?->value == 1 ? true : false) {
            $extraFareFee = (double)businessConfig('extra_fare_fee')?->value ?? 0;
            $extraFareReason = businessConfig('extra_fare_reason')?->value ?? "";
        }
        $coordinates = $this->createPoint($data['coordinates']);
        $data = [
            'name' => $data['name'],
            'coordinates' => $coordinates,
            'extra_fare_status' => businessConfig('extra_fare_status')?->value == 1 ? true : false,
            'extra_fare_fee' => $extraFareFee ?? 0,
            'extra_fare_reason' => $extraFareReason ?? null
        ];
        return $this->zoneRepository->create(data: $data);
    }

    public function update(string|int $id, array $data = []): ?Model
    {
        $coordinates = $this->createPoint($data['coordinates']);
        $data = [
            'name' => $data['name'],
            'coordinates' => $coordinates
        ];
        return $this->zoneRepository->update(id: $id, data: $data);
    }

    public function export(array $criteria = [], array $relations = [], array $orderBy = [], int $limit = null, int $offset = null, bool $onlyTrashed = false, bool $withTrashed = false): Collection|LengthAwarePaginator|\Illuminate\Support\Collection
    {
        $tripsCount = 0;
        $zoneData = $this->index(criteria: $criteria, relations: $relations, orderBy: $orderBy);
        foreach ($zoneData as $zone) {
            $tripsCount += count($zone['tripRequest']);
        }

        return $this->index(criteria: $criteria)->map(function ($item) use ($tripsCount) {
            $volumePercentage = ($item['tripRequest_count'] > 0) ? ($tripsCount / $item['tripRequest_count']) * 100 : 0;
            return [
                'Id' => $item['id'],
                'Name' => $item['name'],
                'Trip Request Volume' => $volumePercentage < 33.33 ? translate('low') : ($volumePercentage == 66.66 ? translate('medium') : translate('high')),
                "Active Status" => $item['is_active'] == 1 ? "Active" : "Inactive",
            ];
        });
    }


    public function getByPoints($point)
    {
        return $this->zoneRepository->getByPoints($point);
    }

    public function storeExtraFare(array $data)
    {
        $extraFareData = $data;
        if (array_key_exists('extra_fare_status', $data)) {
            $extraFareData['extra_fare_status'] = 1;
        } else {
            $extraFareData['extra_fare_status'] = 0;
        }

        return $this->zoneRepository->update(id: $data['id'], data: $extraFareData);
    }

    public function storeExtraFareAll(array $data)
    {
        if (array_key_exists('all_zone_extra_fare_status', $data)) {
            $extraFareData = [
                'extra_fare_status' => 1,
                'extra_fare_fee' => $data['all_zone_extra_fare_fee'],
                'extra_fare_reason' => $data['all_zone_extra_fare_reason'],
            ];
            $zones = $this->zoneRepository->getAll(withTrashed: true);
            if (count($zones) > 0) {
                $whereInCriteria = [
                    'id' => $zones?->pluck('id')->toArray() ?? []
                ];

                return $this->zoneRepository->updatedBy(whereInCriteria: $whereInCriteria, data: $extraFareData, withTrashed: true);
            }
        }
        return null;
    }

    public function statusChangeExtraFare(string|int $id, array $data): ?Model
    {
        $data = [
            'extra_fare_status' => $data['status'] == 0 ? $data['status'] : 1
        ];
        return $this->baseRepository->update(id: $id, data: $data);
    }

    public function getZoneContainingBothPoints(int|string $zoneId, Point $pickupPoint, Point $destinationPoint): ?Model
    {
        return $this->zoneRepository->getZoneContainingBothPoints($zoneId, $pickupPoint, $destinationPoint);
    }

    public function getZoneByCoordinates($latitude, $longitude)
    {
        $point = new Point($latitude, $longitude);
        return $this->zoneRepository->getByPoints($point)->first();
    }
}
