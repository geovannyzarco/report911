<?php

use App\Filament\Resources\DespachoResource\Pages\CreateDespacho;
use App\Models\Categoria;
use App\Models\Sector;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Filament::setCurrentPanel('monitoreo');

    $this->categoria = Categoria::factory()->create();
    $this->sectorA = Sector::factory()->create();
    $this->sectorB = Sector::factory()->create();

    $user = User::factory()->create(['oni' => 'ep40001']);
    Role::firstOrCreate(['name' => 'super_admin']);
    $user->assignRole('super_admin');
    $this->actingAs($user);
});

it('crea un despachador asignandole varios sectores desde la pagina', function () {
    Livewire::test(CreateDespacho::class)
        ->fillForm([
            'oni' => 'ep11111',
            'nombre' => 'Despachador Multi-Sector',
            'password' => 'secret123',
            'categoria_id' => $this->categoria->id,
            'sectores' => [$this->sectorA->id, $this->sectorB->id],
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertRedirect();

    $this->assertDatabaseHas('despachos', ['oni' => 'ep11111']);
    $this->assertDatabaseHas('despacho_sector', ['sector_id' => $this->sectorA->id]);
    $this->assertDatabaseHas('despacho_sector', ['sector_id' => $this->sectorB->id]);
});

it('exige al menos un sector al crear un despachador', function () {
    Livewire::test(CreateDespacho::class)
        ->fillForm([
            'oni' => 'ep22222',
            'nombre' => 'Sin Sectores',
            'password' => 'secret123',
            'categoria_id' => $this->categoria->id,
            'sectores' => [],
        ])
        ->call('create')
        ->assertHasFormErrors(['sectores']);
});
