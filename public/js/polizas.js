/* ====== PÓLIZAS ====== */
const polBody = document.querySelector('#tblPolizas tbody');

// Estado de la póliza según fecha de vencimiento
function polState(c) {
  const d = daysUntil(c.policy.end);
  if (d < 0) return ['Vencida', 'red'];
  if (d <= 30) return [`Por vencer (${d} días)`, 'orange'];
  return ['Vigente', 'blue'];
}

// Pintar tabla
function paintPol() {
  polBody.innerHTML = FLEET.map(c => {
    const [label, cls] = polState(c);
    return `
      <tr>
        <td>${c.name} ${c.year} (${c.plate})</td>
        <td>${c.policy.num}</td>
        <td>${c.policy.insurer}</td>
        <td>${c.policy.start} → ${c.policy.end}</td>
        <td><span class="tag ${cls}">${label}</span></td>
      </tr>`;
  }).join('');
}
paintPol();

// Buscador
document.getElementById('qPolizas').addEventListener('input', e => {
  const t = e.target.value.toLowerCase();
  [...polBody.rows].forEach(r => {
    r.style.display = r.innerText.toLowerCase().includes(t) ? '' : 'none';
  });
});

// Exportar CSV
document.getElementById('exportPolizas')
  .addEventListener('click', () => exportCSV('tblPolizas', 'polizas.csv'));
