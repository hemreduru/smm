<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ['workspace_id', 'name', 'slug'];

    public function workspace()
    {
        return $this->belongsTo(Workspace::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }
}
