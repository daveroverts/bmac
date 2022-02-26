<?php

namespace App\Models;

use App\Events\FileCreated;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\UnableToDeleteFile;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class File extends Model
{
    use HasFactory;
    use Prunable;

    protected $guarded = [];

    protected $dispatchesEvents = [
        'created' => FileCreated::class,
    ];

    public function prunable()
    {
        return static::where('created_at', '<=', now()->subDay());
    }

    public function fileable(): MorphTo
    {
        return $this->morphTo();
    }

    protected function pruning()
    {
        Storage::disk($this->disk)
            ->delete($this->path);
    }
}
