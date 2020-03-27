<?php

namespace Mercosur\Regimenes\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Mercosur\Regimenes\Config\Regimenes;
use Mercosur\Regimenes\Models\Regimenes\Notificacion;
use Mercosur\Regimenes\Models\Regimenes\Lista;
use Mercosur\Regimenes\Models\Regimenes\Regimen;

class Datos extends Command
{
	protected $signature = 'populate:datos';

	protected $description = 'Construye la base de datos de notificaciones de Regimenes Especiales de importaciones';

	public function handle()
	{
		$this->titulos();

		$this->limpiar();

		$this->agregarDatos();

		$this->footer();
	}

	protected function titulos(): void
	{
		$this->info('');

		$this->info("Cargando informacion sobre notificaciones de Regimenes Especiales de Importacion");
		$this->info("--------------------------------------------------------------------------------");
	}

	protected function limpiar(): void
	{
		$this->info('');
		$this->info('Limpiando la tabla anterior');

		DB::statement('SET foreign_key_checks=0');

		Notificacion::truncate();

		Lista::truncate();

		DB::statement('SET foreign_key_checks=1');
	}

	protected function footer(): void
	{
		$this->info('');
		$this->info("Terminado");
		$this->info('');
	}

	protected function agregarDatos(): void
	{
		foreach (Storage::directories(Regimenes::DIRECTORIO_BASE) as $directorioNacional)
		{
			foreach (Storage::directories($directorioNacional) as $notificacion)
			{
				$this->procesarNotificacion($notificacion);
			}
		}
	}

	protected function getFileInfo($notificacion): \stdClass
	{
		return json_decode(Storage::get($notificacion . '/' . Regimenes::ARCHIVO_DE_INFORMACION));
	}

	protected function procesarNotificacion($datos_de_la_notificacion): void
	{
		$this->info('');
		$this->info('Procesando la notificacion en ' . $datos_de_la_notificacion);

		$info = $this->getFileInfo($datos_de_la_notificacion);

		$notificacion = $this->crearNotificacion($datos_de_la_notificacion, $info);

		foreach ($info->notificaciones as $datos_de_la_lista) {
			$this->info('- Cargando lista de ' . $datos_de_la_lista->regimen);

			$lista = $this->crearLista($notificacion, $datos_de_la_lista);
		}
	}

	protected function crearNotificacion($datos_de_la_notificacion, \stdClass $info): Notificacion
	{
		return Notificacion::create([
			'informante' => explode('/', $datos_de_la_notificacion)[2], 
            'fecha' 	 => Carbon::rawCreateFromFormat("d/m/Y", $info->fecha), 
            'nota' 	 	 => $info->nota,
            'asunto' 	 => [
            	'es' => $info->asunto->es,
            	'pt' => $info->asunto->pt
            ],
            'directorio' => $datos_de_la_notificacion,
            'descargar'  => $info->descargar
		]);
	}

	protected function crearLista(Notificacion $notificacion, $datos_de_la_lista): Lista
	{
		return $notificacion->listas()->create([
			'regimen_id' => Regimen::byAbreviatura($datos_de_la_lista->regimen)->id,
            'tipo' 		 => $datos_de_la_lista->tipo,
            'anio' 		 => $datos_de_la_lista->ano,
            'semestre'   => $datos_de_la_lista->semestre ?? null,
            'trimestre'  => $datos_de_la_lista->trimestre ?? null,
	        'archivo'    => $datos_de_la_lista->archivo,
	        'hoja'       => $datos_de_la_lista->hoja
		]);
	}
}