<?php

namespace App\Models;

use App\Traits\ModelTrait;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;
    use ModelTrait;

    protected $casts = [
        'id' => 'integer',
        'username' => 'string',
        'email' => 'string',
        'phone' => 'string',
        'dob' => 'integer',
        'active' => 'boolean',
    ];

    public function transform()
    {
        $transformer = new \stdClass();
        $transformer->id = $this->id;
        $transformer->username = $this->username;
        $transformer->email = $this->email;
        $transformer->phone = $this->phone ?: "";
        $transformer->dob = $this->dob ?: "";
        $transformer->active = $this->active;
        $transformer->created_at = $this->created_at->format('Y-m-d H:i');
        return $transformer;
    }

    public function profileTransform()
    {
        $transformer = new \stdClass();
        $transformer->username = $this->username;
        $transformer->email = $this->email;
        $transformer->phone = $this->phone ?: "";
        $transformer->dob = $this->dob ?: "";

        return $transformer;
    }




    protected static function boot()
    {
        parent::boot();
        static::deleting(function ($user) { });

        static::deleted(function (User $user) { });
    }
}
