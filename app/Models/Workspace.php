<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Workspace extends Model
{
    protected $fillable = ['name', 'slug', 'owner_id'];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'workspace_user')
                    ->withPivot('role_id')
                    ->withTimestamps();
    }

    public function roles()
    {
        return $this->hasMany(Role::class);
    }
}
