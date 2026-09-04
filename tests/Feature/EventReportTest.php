<?php

use App\Filament\Pages\EventReport;
use Livewire\Livewire;

test('buscarPorFecha requires both dates', function () {
    Livewire::test(EventReport::class)
        ->call('buscarPorFecha')
        ->assertHasErrors([
            'fechaDesde' => 'required',
            'fechaHasta' => 'required',
        ]);
});

test('buscarPorFecha dispatches search with dates', function () {
    Livewire::test(EventReport::class)
        ->set('fechaDesde', '2026-09-04 00:00:00')
        ->set('fechaHasta', '2026-09-04 23:59:59')
        ->call('buscarPorFecha')
        ->assertHasNoErrors()
        ->assertDispatched('search');
});

test('buscarPorEvento requires a busqueda term', function () {
    Livewire::test(EventReport::class)
        ->call('buscarPorEvento')
        ->assertHasErrors([
            'busqueda' => 'required',
        ]);
});

test('buscarPorEvento dispatches search with event number', function () {
    Livewire::test(EventReport::class)
        ->set('busqueda', 'SE911:2026:09:04:288607')
        ->call('buscarPorEvento')
        ->assertHasNoErrors()
        ->assertDispatched('search');
});
