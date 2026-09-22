<?php

use App\Filament\Resources\TurnoResource;
use App\Models\Categoria;
use App\Models\Despacho;
use App\Models\Sector;
use App\Models\Turno;
use App\Models\User;
use App\Services\DespachoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

it('un despachador solo ve sus propios turnos', function () {
    $categoria = Categoria::factory()->create();
    $sector = Sector::factory()->create();

    Role::firstOrCreate(['name' => DespachoService::ROL_DESPACHO]);

    $userA = User::factory()->create(['oni' => 'ep10001']);
    $despachoA = Despacho::factory()->create([
        'user_id' => $userA->id,
        'oni' => 'ep10001',
        'categoria_id' => $categoria->id,
    ]);
    $despachoA->sectores()->attach($sector->id);
    $userA->assignRole(DespachoService::ROL_DESPACHO);

    $despachoB = Despacho::factory()->create([
        'categoria_id' => $categoria->id,
    ]);
    $despachoB->sectores()->attach($sector->id);

    Turno::factory()->create(['despacho_id' => $despachoA->id]);
    Turno::factory()->create(['despacho_id' => $despachoB->id]);

    $this->actingAs($userA);

    $turnos = TurnoResource::getEloquentQuery()->get();

    expect($turnos)->toHaveCount(1)
        ->and($turnos->first()->despacho_id)->toBe($despachoA->id);
});

it('un rol que no es despacho ve todos los turnos', function () {
    $categoria = Categoria::factory()->create();
    $sector = Sector::factory()->create();

    $despachoA = Despacho::factory()->create([
        'categoria_id' => $categoria->id,
    ]);
    $despachoA->sectores()->attach($sector->id);
    $despachoB = Despacho::factory()->create([
        'categoria_id' => $categoria->id,
    ]);
    $despachoB->sectores()->attach($sector->id);

    Turno::factory()->create(['despacho_id' => $despachoA->id]);
    Turno::factory()->create(['despacho_id' => $despachoB->id]);

    $userAdmin = User::factory()->create(['oni' => 'ep20001']);
    $this->actingAs($userAdmin);

    expect(TurnoResource::getEloquentQuery()->count())->toBe(2);
});
