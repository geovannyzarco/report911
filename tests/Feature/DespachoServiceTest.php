<?php

use App\Models\Categoria;
use App\Models\Sector;
use App\Models\User;
use App\Services\DespachoService;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('da de alta un despachador con su usuario, correo institucional y rol', function () {
    $categoria = Categoria::factory()->create();
    $sector = Sector::factory()->create();

    $despacho = app(DespachoService::class)->darDeAlta([
        'oni' => 'ep12345',
        'nombre' => 'Despachador de Prueba',
        'categoria_id' => $categoria->id,
        'sector_id' => $sector->id,
        'password' => 'secret123',
    ]);

    expect($despacho->exists)->toBeTrue()
        ->and($despacho->user->oni)->toBe('ep12345')
        ->and($despacho->user->email)->toBe('ep12345@pnc.gob.sv')
        ->and($despacho->user->hasRole(DespachoService::ROL_DESPACHO))->toBeTrue()
        ->and(Role::where('name', DespachoService::ROL_DESPACHO)->exists())->toBeTrue()
        ->and(Hash::check('secret123', $despacho->user->password))->toBeTrue();
});

it('no deja registros sueltos si la transaccion falla', function () {
    $categoria = Categoria::factory()->create();
    $sector = Sector::factory()->create();

    User::factory()->create(['oni' => 'ep99999']);

    expect(fn () => app(DespachoService::class)->darDeAlta([
        'oni' => 'ep99999',
        'nombre' => 'Duplicado',
        'categoria_id' => $categoria->id,
        'sector_id' => $sector->id,
        'password' => 'secret123',
    ]))->toThrow(QueryException::class);

    expect(DB::table('despachos')->count())->toBe(0)
        ->and(User::count())->toBe(1);
});
