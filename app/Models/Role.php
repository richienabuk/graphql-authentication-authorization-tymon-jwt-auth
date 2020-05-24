<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    protected $fillable = ['name'];

    public function users(): belongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    const ROLE_SUPER_ADMIN = 'superadmin';
    const ROLE_ADMIN = 'admin';
    const ROLE_MODERATOR = 'moderator';
    const ROLE_EDITOR = 'editor';
    const ROLE_AUTHENTICATED = 'authenticated';
}
