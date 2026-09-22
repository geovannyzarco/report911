<?php

use App\Filament\Resources\TurnoResource\Pages\CreateTurno;
use App\Models\Agente;
use App\Models\Categoria;
use App\Models\Despacho;
use App\Models\EstadoRecurso;
use App\Models\Sector;
use App\Models\Turno;
use App\Models\TurnoAgente;
use App\Models\TurnoVehiculo;
use App\Models\User;
use App\Models\Vehiculo;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel('monitoreo');

    $this->categoria = Categoria::factory()->create();
    $this->sector = Sector::factory()->create();
    $this->estado = EstadoRecurso::factory()->create();
    $this->user = User::factory()->create(['oni' => 'ep30001']);

    Role::firstOrCreate(['name' => 'super_admin']);
    $this->user->assignRole('super_admin');
    $this->actingAs($this->user);
});

it('crea un turno con sus agentes y vehiculos desde la pagina', function () {
    $despacho = Despacho::factory()->create([
        'categoria_id' => $this->categoria->id,
    ]);
    $despacho->sectores()->attach($this->sector->id);
    $agente = Agente::factory()->create([
        'categoria_id' => $this->categoria->id,
        'sector_id' => $this->sector->id,
    ]);
    $vehiculo = Vehiculo::factory()->create([
        'sector_id' => $this->sector->id,
    ]);

    Livewire::test(CreateTurno::class)
        ->fillForm([
            'despacho_id' => $despacho->id,
            'inicio' => '2026-09-21 08:00:00',
            'fin' => '2026-09-21 20:00:00',
            'turnoAgentes' => [
                [
                    'agente_id' => $agente->id,
                    'estado_recurso_id' => $this->estado->id,
                    'nota' => 'Turno diurno',
                ],
            ],
            'turnoVehiculos' => [
                [
                    'vehiculo_id' => $vehiculo->id,
                    'estado_recurso_id' => $this->estado->id,
                ],
            ],
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertRedirect();

    $turno = Turno::query()->where('despacho_id', $despacho->id)->first();

    expect($turno)->not->toBeNull()
        ->and(TurnoAgente::query()->where('turno_id', $turno->id)->where('agente_id', $agente->id)->exists())->toBeTrue()
        ->and(TurnoVehiculo::query()->where('turno_id', $turno->id)->where('vehiculo_id', $vehiculo->id)->exists())->toBeTrue();
});

it('valida los campos obligatorios del turno', function () {
    Livewire::test(CreateTurno::class)
        ->fillForm([
            'despacho_id' => null,
            'inicio' => null,
            'turnoAgentes' => [],
            'turnoVehiculos' => [],
        ])
        ->call('create')
        ->assertHasFormErrors([
            'despacho_id' => 'required',
            'inicio' => 'required',
        ]);
});
