<?php

namespace Plakhin\RequestChronicle\Tests;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Plakhin\RequestChronicle\Models\Request;

class TestModel extends Model
{
    public $timestamps = false;

    public $fillable = ['slug'];

    public function requests(): MorphMany
    {
        return $this->morphMany(Request::class, 'model');
    }

    public static function migrate(): void
    {
        Schema::create((new static)->getTable(), function (Blueprint $table) {
            $table->id();
            $table->string('slug');
        });
    }
}
