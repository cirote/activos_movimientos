<?php

Route::middleware(['web'])->namespace('Cirote\Movimientos\Controllers')
	->prefix('movimientos')
	->name('movimientos.')
	->group(function() 
	{
		Route::get('/', 'MovimientosController@prueba')->name('index');
	});
