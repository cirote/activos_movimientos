@extends('movimientos::layouts.master')

@section('main_content')
<div class="row">
	<div class="col-md-6">
		<div class="box">

			<div class="box-header with-border">
				<h3 class="box-title">Resumen de Posiciones abiertas</h3>
			</div>

			<div class="box-body">
				<table class="table table-bordered">
					<tbody>
						<tr>
							<th rowspan="2" style="width: 10px">#</th>
							<th rowspan="2">Activo</th>
							<th rowspan="2">Cantidad</th>
							<th rowspan="2">Tipo</th>
							<th colspan="4">Precios en dólares</th>
							<th colspan="2">Monto</th>
							<th rowspan="2">Resultado</th>
						</tr>
						<tr>
							<th>Minimo</th>
							<th>Promedio</th>
							<th>Máximo</th>
							<th>Actual</th>
							<th>Invertido</th>
							<th>Actual</th>
						</tr>
						@foreach($posiciones as $posicion)
						<tr>
							<td>{{ $posiciones->firstItem() + $loop->index }}.</td>
							<td>
								<a href="{{ route('posiciones.abiertas', ['activo' => $posicion->activo]) }}">
									{{ $posicion->activo->ticker->ticker }}
								</a>
							</td>
							<td align="right">{{  number_format($posicion->cantidad, 0, ',', '.') }}</td>
							<td>{{ $posicion->tipo }}</td>
							<td align="right">{{ number_format($posicion->menor_precio_en_dolares, 2, ',', '.') }}</td>
							<td align="right">{{ number_format(($posicion->precioXcantidad / $posicion->cantidad), 2, ',', '.') }}</td>
							<td align="right">{{ number_format($posicion->mayor_precio_en_dolares, 2, ',', '.') }}</td>
							<td></td>
							<td align="right">{{ number_format($posicion->monto_total_en_dolares, 2, ',', '.') }}</td>
							<td></td>
							<td></td>
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