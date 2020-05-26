<li class="header">POSICIONES</li>
<li class="{{ Request::routeIs('posiciones.abiertas.resumen') ? "active" : "" }}">
    <a href="{{ route('posiciones.abiertas.resumen', [], false) }}">
        <i class="fa fa-table"></i> <span>Resumen de aperturas</span>
    </a>
</li>
<li class="{{ Request::routeIs('posiciones.abiertas') ? "active" : "" }}">
    <a href="{{ route('posiciones.abiertas', [], false) }}">
        <i class="fa fa-eye"></i> <span>Posiciones abiertas</span>
    </a>
</li>

<li class="{{ Request::routeIs('posiciones.cerradas.resumen') ? "active" : "" }}">
    <a href="{{ route('posiciones.cerradas.resumen', [], false) }}">
        <i class="fa fa-table"></i> <span>Resumen de cierres</span>
    </a>
</li>
<li class="{{ Request::routeIs('posiciones.cerradas') ? "active" : "" }}">
    <a href="{{ route('posiciones.cerradas', [], false) }}">
        <i class="fa fa-close"></i> <span>Posiciones cerradas</span>
    </a>
</li>
