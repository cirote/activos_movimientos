@extends('activos::layouts.master')

@section('body')
<div class="row">
	<div class="col-md-4">
		<div class="box">

			<div class="box-header with-border">
				<h3 class="box-title">Opciones de {{ $activo->denominacion }}</h3>
			</div>

			<div class="box-body">
				<table class="table table-bordered">
					<tbody>
						<tr>
							<th style="width: 10px">#</th>
							<th>Ticker</th>
							<th>Año</th>
							<th>Mes</th>
							<th>Strike</th>
							<th>Tipo</th>
						</tr>
						@foreach($opciones as $opcion)
						<tr>
							<td>{{ $opciones->firstItem() + $loop->index }}.</td>
							<td>{{ $opcion->ticker }}</td>
							<td>{{ $opcion->año }}</td>
							<td>{{ $opcion->mes }}</td>
							<td>{{ $opcion->strike }}</td>
							<td>{{ $opcion->tipo }}</td>
						</tr>
						@endforeach
					</tbody>
				</table>
			</div>

			<div class="box-footer clearfix">
				<a href="{{ route('inexistentes.index') }}" class="btn btn-default btn-sm">Regresar</a>
				{{ $opciones->links('layout::pagination.default') }}
			</div>

		</div>
	</div>
</div>
@endsection