<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VisitorHistories extends Model
{
    use HasFactory;


    public function visitor():BelongsTo{
        return $this->belongsTo(Visitor::class);
    }
}
