@extends('movimientos::layouts.master')

@section('main_content')
<div class="row">
	<div class="col-md-6">
		<div class="box">

			<div class="box-header with-border">
				<h3 class="box-title">Posiciones abiertas</h3>
			</div>

			<div class="box-body">
				<table class="table table-bordered">
					<tbody>
						<tr>
							<th rowspan="2" style="width: 10px">#</th>
							<th rowspan="2">Apertura</th>
							<th rowspan="2">Broker</th>
							<th rowspan="2">Activo</th>
							<th rowspan="2">Cantidad</th>
							<th rowspan="2">Tipo</th>
							<th colspan="2">Pesos</th>
							<th colspan="2">Dólares</th>
						</tr>
						<tr>
							<th>Precio</th>
							<th>Monto</th>
							<th>Precio</th>
							<th>Monto</th>
						</tr>
						@foreach($posiciones as $posicion)
						<tr>
							<td>{{ $posiciones->firstItem() + $loop->index }}.</td>
							<td>{{ $posicion->fecha_apertura->format('d/m/Y') }}</td>
							<td>{{ $posicion->broker->sigla }}</td>
							<td>{{ $posicion->activo->ticker->ticker }}</td>
							<td align="right">{{ $posicion->cantidad }}</td>
							<td>{{ $posicion->tipo }}</td>
							<td align="right">{{ number_format($posicion->precio_en_pesos, 2, ',', '.') }}</td>
							<td align="right">{{ number_format($posicion->monto_en_pesos, 2, ',', '.') }}</td>
							<td align="right">{{ number_format($posicion->precio_en_dolares, 2, ',', '.') }}</td>
							<td align="right">{{ number_format($posicion->monto_en_dolares, 2, ',', '.') }}</td>
						</tr>
						@endforeach
					</tbody>
				</table>
			</div>

			<div class="box-footer clearfix">
				<a href="{{ route('inexistentes.index') }}" class="btn btn-default btn-sm">Regresar</a>
				{{ $posiciones->links('layouts::pagination.default') }}
			</div>

		</div>
	</div>
</div>
@endsection