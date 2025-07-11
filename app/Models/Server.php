<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Server extends Model
{
    protected $fillable = [
        'name',
        'hostname',
        'user',
        'token',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $hidden = [
        'hostname',
        'user',
        'token',
    ];

    protected array $unencrypted = ['name', 'is_active', 'created_at', 'updated_at'];

    public function setAttribute($key, $value)
    {
        if (!in_array($key, $this->unencrypted) && $value !== null) {
            $value = Crypt::encryptString($value);
        }

        return parent::setAttribute($key, $value);
    }

    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if (!in_array($key, $this->unencrypted) && $value !== null) {
            try {
                return Crypt::decryptString($value);
            } catch (\Exception $e) {
                return $value;
            }
        }

        return $value;
    }
}
