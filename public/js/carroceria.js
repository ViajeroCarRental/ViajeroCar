/* ====== CARROCERÍA ====== */
const BODY = [
  { folio:'CAR-001', car:1, date:'18/08/2025', zone:'Defensa trasera', damage:'Rayón', sev:'Leve', shop:'Taller Gómez', estimate:1800, status:'En proceso' },
  { folio:'CAR-002', car:3, date:'02/08/2025', zone:'Puerta izquierda', damage:'Abolladura', sev:'Media', shop:'Pinturas Leo', estimate:5200, status:'Cotizado' },
  { folio:'CAR-003', car:4, date:'12/06/2025', zone:'Cofre', damage:'Golpe', sev:'Alta', shop:'Autobody Pro', estimate:9800, status:'Refacciones' },
  { folio:'CAR-004', car:12,date:'15/07/2025', zone:'Fascia delantera', damage:'Raspones', sev:'Leve', shop:'Pinturas Leo', estimate:1500, status:'Terminado' },
  { folio:'CAR-005', car:14,date:'28/05/2025', zone:'Parabrisas', damage:'Estrellado', sev:'Alta', shop:'Cristales Max', estimate:3500, status:'Pendiente autorización' }
];

const bodyBody = document.querySelector('#tblCarroceria tbody');

function paintBody() {
  bodyBody.innerHTML = BODY.map(b => {
    const car = FLEET.find(c => c.id === b.car);
    return `
      <tr>
        <td>${b.folio}</td>
        <td>${car.name} ${car.year} (${car.plate})</td>
        <td>${b.date}</td>
        <td>${b.zone}</td>
        <td>${b.damage}</td>
        <td>${b.sev}</td>
        <td>${b.shop}</td>
        <td>${money(b.estimate)}</td>
        <td>${b.status}</td>
        <td>R${car.rim}</td>
      </tr>`;
  }).join('');
}
paintBody();

// Buscador
document.getElementById('qCarroceria').addEventListener('input', e => {
  const t = e.target.value.toLowerCase();
  [...bodyBody.rows].forEach(r => {
    r.style.display = r.innerText.toLowerCase().includes(t) ? '' : 'none';
  });
});

// Exportar CSV
document.getElementById('exportCarroceria')
  .addEventListener('click', () => exportCSV('tblCarroceria', 'carroceria.csv'));
