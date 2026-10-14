<?php

namespace App\Models;

use Database\Factories\AudioDictionaryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'word',
    'audio'
])]
class AudioDictionary extends Model
{
    use HasFactory;
}