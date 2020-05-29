@extends('movimientos::layouts.master')

@section('main_content')
<div class="row">
	<div class="col-md-7">
		<div class="box">

			<div class="box-header with-border">
				<h3 class="box-title">Resumen de posiciones abiertas</h3>
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
							<th colspan="2">Resultado</th>
						</tr>
						<tr>
							<th>Minimo</th>
							<th>Promedio</th>
							<th>Máximo</th>
							<th>Actual</th>
							<th>Invertido</th>
							<th>Actual</th>
							<th>Dólares</th>
							<th>%</th>
						</tr>
						@php($suma = 0)
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
							@if($posicion->activo->precioActualDolares)
								<td align="right">{{ number_format($posicion->activo->precioActualDolares, 2, ',', '.') }}</td>
							@else
								<td></td>
							@endif
							<td align="right">{{ number_format($posicion->monto_total_en_dolares, 2, ',', '.') }}</td>
							@if($valor = $posicion->activo->precioActualDolares * $posicion->cantidad)
								@php($suma += $valor)
								<td align="right">{{ number_format($valor, 2, ',', '.') }}</td>
								@php($resultado = $valor - $posicion->monto_total_en_dolares)
								<td align="right">{{ number_format($resultado, 2, ',', '.') }}</td>
								@if($resultado > 0)
									<td align="right" style="color:green">{{ number_format($resultado * 100 / $posicion->monto_total_en_dolares, 2, ',', '.') }}</td>
								@else
									<td align="right" style="color:red">{{ number_format($resultado * 100 / $posicion->monto_total_en_dolares, 2, ',', '.') }}</td>
								@endif
							@else
								<td>{{ $posicion->activo->precioActualDolares }}</td>
								<td></td>
								<td></td>
							@endif
						</tr>
						@endforeach
						<tr>
							<td></td>
							<td colspan="7"><b>Valor actual</b></td>
							<td align="right">{{ number_format($inversionRealizada, 2, ',', '.') }}</td>
							<td align="right">{{ number_format($suma, 2, ',', '.') }}</td>
							@php($resultado = $suma - $inversionRealizada)
							@if($resultado > 0)
								<td align="right" style="color:green">{{ number_format($resultado , 2, ',', '.') }}</td>
								<td align="right" style="color:green">{{ number_format($resultado * 100 / $inversionRealizada, 2, ',', '.') }}</td>
							@else
								<td align="right" style="color:red">{{ number_format($resultado , 2, ',', '.') }}</td>
								<td align="right" style="color:red">{{ number_format($resultado * 100 / $inversionRealizada, 2, ',', '.') }}</td>
							@endif
						</tr>
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