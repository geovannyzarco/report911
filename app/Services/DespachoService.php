<?php

namespace App\Services;

use App\Models\Despacho;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

/**
 * Servicio responsable del alta de despachadores en el modulo "Recursos por Turno".
 */
class DespachoService
{
    /**
     * Nombre del rol que identifica a los despachadores.
     */
    public const ROL_DESPACHO = 'despacho';

    /**
     * Alta de un despachador: crea el usuario con rol despacho y el registro de despacho
     * (asignando sus sectores) en una sola transaccion. El correo se deriva del ONI.
     *
     * @param  array{oni: string, nombre: string, categoria_id: int, sectores: int[], password: string}  $data
     */
    public function darDeAlta(array $data): Despacho
    {
        return DB::transaction(function () use ($data): Despacho {
            $user = User::create([
                'name' => $data['nombre'],
                'oni' => $data['oni'],
                'email' => $data['oni'].'@pnc.gob.sv',
                'password' => Hash::make($data['password']),
                'email_verified_at' => now(),
            ]);

            $rol = Role::firstOrCreate(['name' => self::ROL_DESPACHO]);
            $user->assignRole($rol);

            $despacho = Despacho::create([
                'user_id' => $user->id,
                'oni' => $data['oni'],
                'nombre' => $data['nombre'],
                'categoria_id' => $data['categoria_id'],
                'activo' => true,
            ]);

            // Asigna los sectores seleccionados al despachador.
            $despacho->sectores()->attach($data['sectores']);

            return $despacho;
        });
    }
}
