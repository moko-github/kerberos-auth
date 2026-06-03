<?php

declare(strict_types=1);

namespace MokoGithub\KerberosAuth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use MokoGithub\KerberosAuth\Support\Kerberos;

/**
 * @property int $id
 * @property string $name
 */
class Role extends Model
{
    protected $fillable = ['name'];

    public function users(): HasMany
    {
        return $this->hasMany(Kerberos::userModel());
    }
}
