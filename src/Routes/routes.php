<?php

Route::middleware(['web'])->namespace('Cirote\Movimientos\Controllers')
	->prefix('posiciones')
	->name('posiciones.')
	->group(function() 
	{
		Route::get('/', 'PosicionesController@index')->name('index');
		Route::get('/abiertas/resumen', 'PosicionesController@resumenAbiertas')->name('abiertas.resumen');
		Route::get('/abiertas/{activo?}/{broker?}', 'PosicionesController@abiertas')->name('abiertas');
		Route::get('/cerradas/resumen', 'PosicionesController@resumenCerradas')->name('cerradas.resumen');
		Route::get('/cerradas/{activo?}/{broker?}', 'PosicionesController@cerradas')->name('cerradas');
	});
