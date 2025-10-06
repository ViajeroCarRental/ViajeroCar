@extends('layouts.Ventas')
@section('Titulo', 'Home Ventas')

@section('css-vistaHomeVentas')
  <link rel="stylesheet" href="{{ asset('css/homeVentas.css') }}">
@endsection

@section('contenidoHomeVentas')
<main class="main">
  <div class="topbar">
    <button class="burger" id="burger">☰</button>
    <div class="hi">Rentas · Resumen</div>
    <div class="top-actions">
      <button class="btn ghost" id="refreshDash">↻ Actualizar</button>
    </div>
  </div>

  <div class="content container">
    <p class="muted" id="hello">Hola, este es tu resumen de desempeño.</p>

    <!-- ===== KPIs ===== -->
    @php
      $kpis = $kpis ?? [
        'ingresos' => 385000,
        'reservas' => 126,
        'ocupacion' => 0.76,
        'ticket' => 3058
      ];
      $deltas = $deltas ?? [
        'ingresos' => +0.12,
        'reservas' => +0.06,
        'ocupacion' => -0.03,
        'ticket' => +0.04
      ];
    @endphp

    <section class="kpi-grid">
      <div class="kpi">
        <div class="kpi-t">Ingresos (MXN)</div>
        <div class="kpi-v">${{ number_format($kpis['ingresos'], 0) }}</div>
        <div class="kpi-d {{ $deltas['ingresos']>=0?'up':'down' }}">
          {{ $deltas['ingresos']>=0 ? '▲' : '▼' }} {{ number_format(abs($deltas['ingresos'])*100,1) }}% vs. periodo prev.
        </div>
      </div>
      <div class="kpi">
        <div class="kpi-t">Reservas</div>
        <div class="kpi-v">{{ number_format($kpis['reservas']) }}</div>
        <div class="kpi-d {{ $deltas['reservas']>=0?'up':'down' }}">
          {{ $deltas['reservas']>=0 ? '▲' : '▼' }} {{ number_format(abs($deltas['reservas'])*100,1) }}%
        </div>
      </div>
      <div class="kpi">
        <div class="kpi-t">Ocupación</div>
        <div class="kpi-v">{{ number_format($kpis['ocupacion']*100,1) }}%</div>
        <div class="kpi-d {{ $deltas['ocupacion']>=0?'up':'down' }}">
          {{ $deltas['ocupacion']>=0 ? '▲' : '▼' }} {{ number_format(abs($deltas['ocupacion'])*100,1) }}%
        </div>
      </div>
      <div class="kpi">
        <div class="kpi-t">Ticket Promedio</div>
        <div class="kpi-v">${{ number_format($kpis['ticket'],0) }}</div>
        <div class="kpi-d {{ $deltas['ticket']>=0?'up':'down' }}">
          {{ $deltas['ticket']>=0 ? '▲' : '▼' }} {{ number_format(abs($deltas['ticket'])*100,1) }}%
        </div>
      </div>
    </section>

    <!-- ===== Gráficas ===== -->
    @php
      // Series temporales (últimos 12 puntos)
      $seriesBookings = $seriesBookings ?? [
        'labels' => ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'],
        'data'   => [9,12,15,18,20,23,25,22,21,24,26,31],
      ];
      $seriesRevenue = $seriesRevenue ?? [
        'labels' => $seriesBookings['labels'],
        'data'   => [110,125,150,168,190,220,245,230,225,260,285,320],
      ];
      $statusCounts = $statusCounts ?? [
        'labels' => ['Entregadas','En curso','Confirmadas','Canceladas'],
        'data'   => [48,22,36,8],
      ];
      $channelMix = $channelMix ?? [
        'labels' => ['Web','WhatsApp','Mostrador','Agencias'],
        'data'   => [45,28,16,11],
      ];
      $topCars = $topCars ?? [
        'labels' => ['Sedán A','SUV B','Compacto C','Pickup D','Van E'],
        'data'   => [28,24,22,15,12],
      ];
    @endphp

    <section class="grid">
      <!-- Línea: Reservas por mes -->
      <div class="card">
        <div class="head">
          <h3>Reservas por mes</h3>
          <span class="small">Tendencia</span>
        </div>
        <div class="cnt">
          <canvas id="chBookings" height="120"></canvas>
        </div>
      </div>

      <!-- Dona: Mix de canal -->
      <div class="card">
        <div class="head">
          <h3>Mix de canal</h3>
          <span class="small">Origen de reservas</span>
        </div>
        <div class="cnt donut-wrap">
          <canvas id="chChannels" height="120"></canvas>
          <div class="donut-center">
            <div class="dc-big">{{ array_sum($channelMix['data']) }}</div>
            <div class="dc-sub">reservas</div>
          </div>
        </div>
      </div>

      <!-- Barras: Ingresos por mes -->
      <div class="card">
        <div class="head">
          <h3>Ingresos por mes</h3>
          <span class="small">($ miles)</span>
        </div>
        <div class="cnt">
          <canvas id="chRevenue" height="120"></canvas>
        </div>
      </div>

      <!-- Barras horizontales: Top vehículos por reservas -->
      <div class="card">
        <div class="head">
          <h3>Top vehículos</h3>
          <span class="small">Por número de reservas</span>
        </div>
        <div class="cnt">
          <canvas id="chTopCars" height="140"></canvas>
        </div>
      </div>

      <!-- Pastel: Estados de reservas -->
      <div class="card">
        <div class="head">
          <h3>Estatus de reservas</h3>
          <span class="small">Distribución actual</span>
        </div>
        <div class="cnt">
          <canvas id="chStatus" height="120"></canvas>
        </div>
      </div>

      <!-- Indicador: Ocupación (mini radial simulado) -->
      <div class="card">
        <div class="head">
          <h3>Ocupación de flota</h3>
          <span class="small">Tiempo real / periodo</span>
        </div>
        <div class="cnt kpi-ocp">
          <div class="ocp-ring">
            <svg viewBox="0 0 36 36">
              <path class="bg" d="M18 2 a 16 16 0 1 1 0 32 a 16 16 0 1 1 0 -32"/>
              @php $pct = (int)round(($kpis['ocupacion'] ?? 0.76) * 100); @endphp
              <path class="fg" stroke-dasharray="{{ $pct }},100" d="M18 2 a 16 16 0 1 1 0 32 a 16 16 0 1 1 0 -32"/>
            </svg>
            <div class="ocp-label">{{ $pct }}%</div>
          </div>
          <div class="ocp-meta small">Meta 80% · Últimos 30 días</div>
        </div>
      </div>
    </section>
  </div>
</main>

<div class="scrim" id="scrim"></div>
@endsection

@section('js-vistaHomeVentas')
  <!-- Chart.js CDN (ligero y suficiente para el módulo) -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js" integrity="sha256-1Qxj6xHn6o0q3Vd8E8M8R5pHjIhYw11W9r9Vg2Q4v8U=" crossorigin="anonymous"></script>
  <script>
    // Paleta consistente con Viajero
    const pal = {
      brand:  '#FF1E2D',
      brand2: '#D6121F',
      ink:    '#101828',
      muted:  '#667085',
      blue:   '#3B82F6',
      green:  '#10B981',
      amber:  '#F59E0B',
      red:    '#EF4444',
      sky:    '#38BDF8',
      indigo: '#6366F1',
      gray:   '#CFD8E3'
    };

    // Data desde PHP (con fallback incorporado en Blade)
    const bookings = @json($seriesBookings);
    const revenue  = @json($seriesRevenue);
    const status   = @json($statusCounts);
    const channel  = @json($channelMix);
    const topCars  = @json($topCars);

    // Linea: Reservas por mes
    new Chart(document.getElementById('chBookings'), {
      type: 'line',
      data: {
        labels: bookings.labels,
        datasets: [{
          label: 'Reservas',
          data: bookings.data,
          borderColor: pal.brand,
          backgroundColor: 'rgba(255,30,45,.12)',
          fill: true,
          tension: .35,
          borderWidth: 2
        }]
      },
      options: {
        plugins: { legend: { display:false } },
        scales: {
          x: { grid:{ display:false }, ticks:{ color: pal.muted }},
          y: { grid:{ color:'#eef2f7' }, ticks:{ color: pal.muted, precision:0 } }
        }
      }
    });

    // Barras: Ingresos por mes
    new Chart(document.getElementById('chRevenue'), {
      type: 'bar',
      data: {
        labels: revenue.labels,
        datasets: [{
          label: 'Ingresos ($ miles)',
          data: revenue.data,
          backgroundColor: 'rgba(255,30,45,.85)',
          borderRadius: 8,
          maxBarThickness: 36
        }]
      },
      options: {
        plugins: { legend: { display:false } },
        scales: {
          x: { grid:{ display:false }, ticks:{ color: pal.muted }},
          y: { grid:{ color:'#eef2f7' }, ticks:{ color: pal.muted } }
        }
      }
    });

    // Dona: Mix de canal
    new Chart(document.getElementById('chChannels'), {
      type: 'doughnut',
      data: {
        labels: channel.labels,
        datasets: [{
          data: channel.data,
          backgroundColor: [pal.brand, pal.indigo, pal.green, pal.sky],
          borderWidth: 0
        }]
      },
      options: {
        plugins: { legend: { position:'bottom', labels:{ color: pal.muted } } },
        cutout: '62%'
      }
    });

    // Pastel: Estatus
    new Chart(document.getElementById('chStatus'), {
      type: 'pie',
      data: {
        labels: status.labels,
        datasets: [{
          data: status.data,
          backgroundColor: [pal.green, pal.indigo, pal.brand, pal.red],
          borderWidth: 0
        }]
      },
      options: {
        plugins: { legend: { position:'bottom', labels:{ color: pal.muted } } }
      }
    });

    // Barras horizontales: Top vehículos
    new Chart(document.getElementById('chTopCars'), {
      type: 'bar',
      data: {
        labels: topCars.labels,
        datasets: [{
          label: 'Reservas',
          data: topCars.data,
          backgroundColor: 'rgba(16,185,129,.9)',
          borderRadius: 8,
          maxBarThickness: 28
        }]
      },
      options: {
        indexAxis:'y',
        plugins: { legend: { display:false } },
        scales: {
          x: { grid:{ color:'#eef2f7' }, ticks:{ color: pal.muted, precision:0 } },
          y: { grid:{ display:false }, ticks:{ color: pal.muted } }
        }
      }
    });
  </script>
@endsection
