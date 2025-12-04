document.addEventListener("DOMContentLoaded", () => {
  const tbl = document.querySelector("#tblCost tbody");
  const from = document.querySelector("#from");
  const to = document.querySelector("#to");
  const gTot = document.querySelector("#gTot");
  const gMaint = document.querySelector("#gMaint");
  const gPol = document.querySelector("#gPol");
  const gBody = document.querySelector("#gBody");
  const gOther = document.querySelector("#gOther");
  const topCar = document.querySelector("#topCar");
  const topCarName = document.querySelector("#topCarName");
  const avgPerDay = document.querySelector("#avgPerDay");
  const rangeLabel = document.querySelector("#rangeLabel");

  async function cargarGastos(params = {}) {
    const query = new URLSearchParams(params).toString();
    const res = await fetch(`/admin/gastos/filtrar?${query}`);
    const data = await res.json();
    renderTabla(data);
    calcularTotales(data);
    renderGrafica(data);
  }

  function renderTabla(data) {
    tbl.innerHTML = "";
    data.forEach(g => {
      const tr = document.createElement("tr");
      tr.innerHTML = `
        <td>${g.fecha}</td>
        <td>${g.marca} ${g.modelo}</td>
        <td>${g.placa || "-"}</td>
        <td>${g.tipo}</td>
        <td>${g.descripcion || "-"}</td>
        <td>$${Number(g.monto).toLocaleString("es-MX", { minimumFractionDigits: 2 })}</td>
      `;
      tbl.appendChild(tr);
    });
  }

  function calcularTotales(data) {
    const total = data.reduce((acc, g) => acc + Number(g.monto), 0);
    gTot.textContent = `$${total.toLocaleString("es-MX", { minimumFractionDigits: 2 })}`;

    const porTipo = (tipo) =>
      data.filter(g => g.tipo.toLowerCase().includes(tipo))
          .reduce((a, g) => a + Number(g.monto), 0);

    gMaint.textContent = `$${porTipo("mantenimiento").toLocaleString("es-MX", { minimumFractionDigits: 2 })}`;
    gPol.textContent   = `$${porTipo("poliza").toLocaleString("es-MX", { minimumFractionDigits: 2 })}`;
    gBody.textContent  = `$${porTipo("carrocer").toLocaleString("es-MX", { minimumFractionDigits: 2 })}`;
    gOther.textContent = `$${(total - (porTipo("mantenimiento") + porTipo("seguro") + porTipo("carrocer"))).toLocaleString("es-MX", { minimumFractionDigits: 2 })}`;

    // Top vehículo
    const porCarro = {};
    data.forEach(g => {
      const key = `${g.marca} ${g.modelo}`;
      porCarro[key] = (porCarro[key] || 0) + Number(g.monto);
    });
    const top = Object.entries(porCarro).sort((a, b) => b[1] - a[1])[0];
    if (top) {
      topCar.textContent = `$${top[1].toLocaleString("es-MX", { minimumFractionDigits: 2 })}`;
      topCarName.textContent = top[0];
    }

    // Promedio diario
    if (from.value && to.value) {
      const diff = (new Date(to.value) - new Date(from.value)) / (1000 * 3600 * 24) + 1;
      avgPerDay.textContent = `$${(total / diff).toLocaleString("es-MX", { minimumFractionDigits: 2 })}`;
      rangeLabel.textContent = `${diff} días`;
    }
  }

  // 📊 Chart.js
  let chart;
  function renderGrafica(data) {
    const ctxId = "chartGastos";
    if (!document.getElementById(ctxId)) {
      const canvas = document.createElement("canvas");
      canvas.id = ctxId;
      document.querySelector(".content").appendChild(canvas);
    }
    const ctx = document.getElementById(ctxId).getContext("2d");

    const porTipo = {};
    data.forEach(g => porTipo[g.tipo] = (porTipo[g.tipo] || 0) + Number(g.monto));

    const labels = Object.keys(porTipo);
    const values = Object.values(porTipo);

    if (chart) chart.destroy();
    chart = new Chart(ctx, {
      type: "pie",
      data: { labels, datasets: [{ data: values }] },
      options: {
        plugins: {
          legend: { position: "bottom" },
          title: { display: true, text: "Distribución de gastos por tipo" }
        }
      }
    });
  }

  // Filtros rápidos
  document.getElementById("applyRange").addEventListener("click", () =>
    cargarGastos({ from: from.value, to: to.value })
  );
  document.getElementById("quickToday").addEventListener("click", () => {
    const d = new Date().toISOString().split("T")[0];
    from.value = to.value = d;
    cargarGastos({ from: d, to: d });
  });
  document.getElementById("quickWeek").addEventListener("click", () => {
    const now = new Date();
    const start = new Date(now.setDate(now.getDate() - 7)).toISOString().split("T")[0];
    const end = new Date().toISOString().split("T")[0];
    from.value = start;
    to.value = end;
    cargarGastos({ from: start, to: end });
  });
  document.getElementById("quickMonth").addEventListener("click", () => {
    const now = new Date();
    const start = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().split("T")[0];
    const end = new Date(now.getFullYear(), now.getMonth() + 1, 0).toISOString().split("T")[0];
    from.value = start;
    to.value = end;
    cargarGastos({ from: start, to: end });
  });

  cargarGastos();
});
