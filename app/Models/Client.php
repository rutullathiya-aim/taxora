<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasUlids, SoftDeletes;

    protected $fillable = [
        'client_name',
        'company_name',
        'email',
        'phone',
        'address',
        'is_active',
    ];

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function clientDocuments()
    {
        return $this->hasMany(ClientDocument::class);
    }
}
