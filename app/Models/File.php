<?php

namespace App\Models;

use App\Events\FileCreated;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class File extends Model
{
    use HasFactory;

    protected $dispatchesEvents = [
        'created' => FileCreated::class,
    ];

    protected $guarded = [];

    public function fileable(): MorphTo
    {
        return $this->morphTo();
    }
}
