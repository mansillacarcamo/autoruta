<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'telefono_whatsapp', 'region', 'comuna', 'rol', 'nombre_comercial', 'logo', 'mostrar_logo'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function vehiculos()
    {
        return $this->hasMany(Vehiculo::class);
    }

    // Logo visible en sus avisos solo si lo subió y eligió mostrarlo en Mi cuenta.
    public function logoVisibleUrl(): ?string
    {
        return $this->mostrar_logo && $this->logo ? \App\Support\Archivos::url('logos/' . $this->logo) : null;
    }

    public function esAdmin(): bool
    {
        return $this->rol === 'admin';
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'mostrar_logo' => 'boolean',
        ];
    }
}
