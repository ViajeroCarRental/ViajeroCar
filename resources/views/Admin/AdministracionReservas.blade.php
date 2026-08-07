@extends('layouts.Ventas')

@section('Titulo', 'Administración De Reservas')

@section('css-vistaAdministracionReservaciones')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/AdministracionReservas.css') }}?v={{ @filemtime(public_path('css/AdministracionReservas.css')) ?: time() }}">
@endsection

@section('contenidoAdministracionReservaciones')
<main class="main">
    <div class="page">
        <header class="page-heading">
            <h1 class="h1">Contratos Abiertos</h1>
        </header>

        <section class="filters-panel" aria-labelledby="filtersTitle">
            <div class="filters-panel-heading">
                <div class="filters-panel-title">
                    <span class="filters-panel-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24">
                            <path d="M4 6h16"></path>
                            <path d="M7 12h10"></path>
                            <path d="M10 18h4"></path>
                        </svg>
                    </span>
                    <div>
                        <h2 id="filtersTitle">Filtros de búsqueda</h2>
                        <p>Combina los campos para encontrar un contrato.</p>
                    </div>
                </div>

                <button id="btnLimpiarFiltros" class="clear-filters-btn" type="button">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M4 7h16"></path>
                        <path d="M9 7V4h6v3"></path>
                        <path d="m7 7 1 13h8l1-13"></path>
                    </svg>
                    <span>Limpiar filtros</span>
                </button>
            </div>

            <div class="topbar">
                <label class="filter-control search search-control">
                    <span class="filter-label">Buscar</span>
                    <span class="search-field">
                        <svg viewBox="0 0 24 24" aria-hidden="true">
                            <circle cx="11" cy="11" r="7"></circle>
                            <path d="m20 20-3.5-3.5"></path>
                        </svg>
                        <input id="txtSearch" class="input" type="search"
                            placeholder="Contrato, reservación o cliente">
                    </span>
                </label>

                <label class="filter-control">
                    <span class="filter-label">Oficina de regreso</span>
                    <select id="filtroOficina" class="select">
                        <option value="">Todas</option>
                    </select>
                </label>

                <label class="filter-control">
                    <span class="filter-label">Estatus del contrato</span>
                    <select id="filtroEstatus" class="select">
                        <option value="">Todos</option>
                    </select>
                </label>

                <label class="filter-control date-filter">
                    <span class="filter-label">Fecha de checkout</span>
                    <input id="filtroFechaCierre" class="input" type="date">
                </label>
            </div>

            <div class="filters-footer">
                <section class="category-filter" aria-labelledby="categoryFilterTitle">
                    <div class="category-filter-heading">
                        <span id="categoryFilterTitle">Categoría</span>
                        <small>Filtra por tipo de vehículo</small>
                    </div>
                    <div id="categoryFilters" class="category-filter-options">
                        <button class="category-chip is-active" type="button" data-category="">
                            <span>Todas</span><strong>0</strong>
                        </button>
                    </div>
                </section>

                <label class="records-control">
                    <span>Mostrar</span>
                    <select id="selSize" class="select">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span>registros</span>
                </label>
            </div>
        </section>

        <div class="table-wrap">
            <table class="table" id="tbl">
                <thead>
                    <tr>
                        <th class="col-toggle"></th>
                        <th>Fecha Checkout</th>
                        <th>Hora Checkout</th>
                        <th>No. Contrato</th>
                        <th>Drop off</th>
                        <th>Categoría</th>
                        <th>Usuario</th>
                        <th>Tarifa diaria</th>
                        <th>Días de renta</th>
                        <th>Total de la renta</th>
                    </tr>
                </thead>
                <tbody id="tbody"></tbody>
            </table>
        </div>

        <div class="pager">
            <button class="pbtn" id="prev" type="button">« Anterior</button>
            <div id="pgInfo" class="small"></div>
            <button class="pbtn" id="next" type="button">Siguiente »</button>
        </div>
    </div>
</main>

<div id="modalFinalizar" class="modal-fin" style="display:none;">
    <div class="modal-fin-box">
        <h2 id="mf_titulo">Finalizar contrato</h2>
        <p id="mf_msg">Mensaje aquí…</p>
        <div class="mf-btns">
            <button id="mf_cancel" class="btn b-gray" type="button">Cancelar</button>
            <button id="mf_ok" class="btn b-primary" type="button">Aceptar</button>
        </div>
    </div>
</div>

<script>
    const esSuperAdmin = @json($soloSuperAdmin);
</script>
@endsection

@section('js-vistaAdministracionReservaciones')
    <script src="{{ asset('js/AdministracionReservas.js') }}?v={{ @filemtime(public_path('js/AdministracionReservas.js')) ?: time() }}"></script>
@endsection
