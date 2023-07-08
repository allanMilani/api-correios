<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TokenCorreios extends Model
{
    use HasFactory;

    protected $fillable = [
        'user',
        'correios_id',
        'cnpj',
        'emissao',
        'expira_em',
        'token'
    ];

}
