<?php

Route::middleware(['web'])->namespace('Cirote\Movimientos\Controllers')
	->prefix('posiciones')
	->name('posiciones.')
	->group(function() 
	{
		Route::get('/', 'PosicionesController@index')->name('index');
		Route::get('/abiertas', 'PosicionesController@abiertas')->name('abiertas');
		Route::get('/cerradas', 'PosicionesController@cerradas')->name('cerradas');
	});
