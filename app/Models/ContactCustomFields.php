<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactCustomFields extends Model
{
    use HasFactory;

    protected $table = 'contact_custom_fields';

    protected $fillable = [
        'contact_id',
        'field_name',
        'field_value',
        'data_type'
    ];
}
