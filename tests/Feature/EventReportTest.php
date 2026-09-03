<?php

use App\Filament\Pages\EventReport;
use Livewire\Livewire;

test('requires dates when no event number is provided', function () {
    Livewire::test(EventReport::class)
        ->call('buscar')
        ->assertHasErrors([
            'fechaDesde' => 'required',
            'fechaHasta' => 'required',
        ]);
});

test('does not require dates when event number is provided', function () {
    Livewire::test(EventReport::class)
        ->set('busqueda', '279215')
        ->call('buscar')
        ->assertHasNoErrors(['fechaDesde' => 'required'])
        ->assertHasNoErrors(['fechaHasta' => 'required'])
        ->assertDispatched('search');
});
