<?php

namespace Modules\GiftCardManagement\Repository\Eloquent;

use App\Repository\Eloquent\BaseRepository;
use Modules\GiftCardManagement\Entities\GiftCard;
use Modules\GiftCardManagement\Repository\GiftCardRepositoryInterface;

class GiftCardRepository extends BaseRepository implements GiftCardRepositoryInterface
{
    public function __construct(GiftCard $model)
    {
        parent::__construct($model);
    }

}
