<?php

namespace LaravelEnso\Products\app\Http\Controllers\MeasurementUnits;

use Illuminate\Routing\Controller;
use LaravelEnso\Tables\app\Traits\Data;
use LaravelEnso\Products\app\Tables\Builders\MeasurementUnitTable;

class TableData extends Controller
{
    use Data;

    protected $tableClass = MeasurementUnitTable::class;
}
