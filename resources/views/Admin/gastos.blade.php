@extends('layouts.Flotillas')
@section('Titulo', 'Gastos')

@section('css-vistaGastos')
<link rel="stylesheet" href="{{ asset('css/gastos.css') }}">
@endsection

@section('contenidoGastos')
<main>
  <div class="topbar">
    <div><strong>Autos · Gastos</strong></div>
  </div>

  <div class="content">
    <h1 class="title">Gastos</h1>
    <p class="sub">Todos los gastos por categoría y vehículo (rango por día/semana/mes).</p>

    <div class="toolbar">
      <div class="search">
        <input id="qCost" type="text" placeholder="Buscar por coche, categoría o descripción">
      </div>
      <button class="btn ghost" id="exportCost">⬇️ Exportar Excel</button>
    </div>

    <!-- Tarjetas resumen -->
    <div class="cards" id="cardsSummary">
      <div class="card" data-type="total"><h4>Total</h4><div class="amt" id="gTot">$0</div><div class="small" id="gCount">0 movimientos</div></div>
      <div class="card" data-type="mantenimiento"><h4>Mantenimiento</h4><div class="amt" id="gMaint">$0</div></div>
      <div class="card" data-type="póliza"><h4>Pólizas</h4><div class="amt" id="gPol">$0</div></div>
      <div class="card" data-type="carrocería"><h4>Carrocería</h4><div class="amt" id="gBody">$0</div></div>
      <div class="card" data-type="otros"><h4>Siniestros / Otros</h4><div class="amt" id="gOther">$0</div></div>
    </div>

    <div class="toolbar">
      <div class="dates">
        <input type="date" id="from"> —
        <input type="date" id="to">
        <button class="btn" id="applyRange">Aplicar</button>
        <button class="btn ghost" onclick="filtrarRango('hoy')">Hoy</button>
        <button class="btn ghost" onclick="filtrarRango('semana')">Semana</button>
        <button class="btn ghost" onclick="filtrarRango('mes')">Mes</button>
      </div>
    </div>

    <div style="overflow:auto">
      <table class="table" id="tblCost">
        <thead>
          <tr>
            <th>Fecha</th>
            <th>Carro</th>
            <th>Placa</th>
            <th>Categoría</th>
            <th>Descripción</th>
            <th>Importe</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($gastos as $g)
            <tr data-type="{{ strtolower($g->tipo) }}">
              <td>{{ \Carbon\Carbon::parse($g->fecha)->format('d/m/Y') }}</td>
              <td>{{ $g->nombre_publico }}</td>
              <td>{{ $g->placa }}</td>
              <td>{{ ucfirst($g->tipo) }}</td>
              <td>{{ $g->descripcion ?? '—' }}</td>
              <td>${{ number_format($g->monto, 2) }}</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</main>
@endsection
@section('js-vistaGastos')
<script>
// ========= Cargar totales desde backend =========
async function cargarTotales() {
  try {
    const res = await fetch('{{ route("gastos.totales") }}');
    if (!res.ok) throw new Error("Error al consultar totales");
    const data = await res.json();

    // Convertir a número en caso de venir como string
    const total = parseFloat(data.total ?? 0) || 0;
    const mantenimiento = parseFloat(data.mantenimiento ?? 0) || 0;
    const poliza = parseFloat(data.poliza ?? 0) || 0;
    const carroceria = parseFloat(data.carroceria ?? 0) || 0;
    const otros = parseFloat(data.otros ?? 0) || 0;

    document.getElementById("gTot").innerText = "$" + total.toLocaleString('es-MX', { minimumFractionDigits: 2 });
    document.getElementById("gMaint").innerText = "$" + mantenimiento.toLocaleString('es-MX', { minimumFractionDigits: 2 });
    document.getElementById("gPol").innerText = "$" + poliza.toLocaleString('es-MX', { minimumFractionDigits: 2 });
    document.getElementById("gBody").innerText = "$" + carroceria.toLocaleString('es-MX', { minimumFractionDigits: 2 });
    document.getElementById("gOther").innerText = "$" + otros.toLocaleString('es-MX', { minimumFractionDigits: 2 });

    // Contador de movimientos
    const movimientos = (data.total > 0) ? document.querySelectorAll("#tblCost tbody tr").length : 0;
    document.getElementById("gCount").innerText = movimientos + " movimientos";
  } catch (e) {
    console.error("Error cargando totales:", e);
  }
}

// ========= Filtrado al hacer clic en las tarjetas =========
function aplicarFiltroCategoria(tipo) {
  const rows = document.querySelectorAll("#tblCost tbody tr");
  rows.forEach(r => {
    const tipoRow = r.dataset.type.normalize("NFD").replace(/[\u0300-\u036f]/g, ""); // sin acentos
    const tipoCard = tipo.normalize("NFD").replace(/[\u0300-\u036f]/g, "");

    if (tipoCard === "total") {
      r.style.display = "";
    } else if (tipoCard === "otros") {
      if (["siniestro", "otros"].includes(tipoRow)) {
        r.style.display = "";
      } else {
        r.style.display = "none";
      }
    } else {
      r.style.display = (tipoRow === tipoCard) ? "" : "none";
    }
  });
}

// ========= Filtro rápido (Hoy / Semana / Mes) =========
async function filtrarRango(tipo) {
  try {
    const res = await fetch(`/admin/gastos/rango/${tipo}`);
    if (!res.ok) throw new Error("Error al filtrar rango");
    const data = await res.json();

    const tbody = document.querySelector("#tblCost tbody");
    tbody.innerHTML = "";

    data.forEach(g => {
      const tr = document.createElement("tr");
      tr.dataset.type = g.tipo.toLowerCase();
      tr.innerHTML = `
        <td>${g.fecha}</td>
        <td>${g.nombre_publico}</td>
        <td>${g.placa}</td>
        <td>${g.tipo}</td>
        <td>${g.descripcion ?? '—'}</td>
        <td>$${parseFloat(g.monto).toFixed(2)}</td>`;
      tbody.appendChild(tr);
    });

    // Volver a cargar totales después de filtrar
    cargarTotales();
  } catch (e) {
    console.error("Error en rango:", e);
  }
}

// ========= 🔍 Buscador dinámico =========
document.getElementById("qCost").addEventListener("keyup", function() {
  const filtro = this.value.toLowerCase();
  const filas = document.querySelectorAll("#tblCost tbody tr");

  filas.forEach(fila => {
    const texto = fila.textContent.toLowerCase();
    fila.style.display = texto.includes(filtro) ? "" : "none";
  });
});

// ========= 🧾 Exportar Excel (CSV) =========
document.getElementById("exportCost").addEventListener("click", () => {
  window.location.href = "{{ route('gastos.exportar') }}";
});

// ========= Inicialización =========
document.addEventListener("DOMContentLoaded", () => {
  // Mostrar todos los registros al cargar la página (sin filtros)
  const rows = document.querySelectorAll("#tblCost tbody tr");
  rows.forEach(r => r.style.display = ""); // muestra todo

  // Cargar totales desde backend
  cargarTotales();

  // Activar filtros al hacer clic en las tarjetas
  document.querySelectorAll(".card[data-type]").forEach(card => {
    card.addEventListener("click", () => aplicarFiltroCategoria(card.dataset.type));
  });
});
</script>
@endsection
