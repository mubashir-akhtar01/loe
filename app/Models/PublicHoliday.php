<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'holiday_date'])]
class PublicHoliday extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'holiday_date' => 'date',
        ];
    }
}
