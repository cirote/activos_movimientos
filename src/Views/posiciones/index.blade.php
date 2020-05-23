@extends('activos::layouts.master')

@section('body')
<div class="row">
	<div class="col-md-4">
		<div class="box">

			<div class="box-header with-border">
				<h3 class="box-title">Activos con opciones inexistentes en la base</h3>
			</div>

			<div class="box-body">
				<table class="table table-bordered">
					<tbody>
						<tr>
							<th style="width: 10px">#</th>
							<th>Activo</th>
						</tr>
						@foreach($activos as $activo)
						<tr>
							<td>{{ $activos->firstItem() + $loop->index }}.</td>
							<td>
								@if($activo->subyacente)
								<a href="{{ route('inexistentes.aprobar', ['activo' => $activo->subyacente]) }}" >
									{{ $activo->subyacente->denominacion }}
								</a>
								@else
									Otras opciones sin identificar
								@endif
							</td>
						</tr>
						@endforeach
					</tbody>
				</table>
			</div>

			<div class="box-footer clearfix">
				{{ $activos->links('layout::pagination.default') }}
			</div>

		</div>
	</div>
</div>
@endsection
