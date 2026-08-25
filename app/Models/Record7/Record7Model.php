<?php

namespace App\Models\Record7;

use Illuminate\Database\Eloquent\Model;

/**
 * Base for every Record7 model.
 *
 * The connection is pinned here rather than repeated: Record7 owns a separate
 * database, and a model that quietly fell back to the default connection would
 * read the legacy system's tables.
 */
abstract class Record7Model extends Model
{
    protected $connection = 'record7';

    protected $guarded = [];
}
