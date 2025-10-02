// SIN bloqueos de permisos
const u = getUser();
document.getElementById('hello').textContent = `Bienvenid@ ${u?.name || ''}. Selecciona una sección para continuar.`;
document.getElementById('who').textContent =
  JSON.parse(localStorage.getItem('vc_rent_agent') || 'null')?.name || '';

// Sidebar (mobile)
const sb = document.getElementById('sidebar'),
      burger = document.getElementById('burger'),
      scrim = document.getElementById('scrim');

burger?.addEventListener('click', () => { sb.classList.add('open'); scrim.classList.add('show'); });
scrim?.addEventListener('click', () => { sb.classList.remove('open'); scrim.classList.remove('show'); });

// KPIs demo (pon tus datos reales aquí)
const demo = [
  { id:'R-1001', status:'activa',        fecha:'2025-09-08T09:00' },
  { id:'R-1002', status:'por_entregar',  fecha:'2025-09-08T13:00' },
  { id:'R-1003', status:'activa',        fecha:'2025-09-07T11:00' },
];

const today = new Date().toISOString().slice(0,10);
document.getElementById('kHoy').textContent = demo.filter(r => r.fecha.startsWith(today)).length;
document.getElementById('kAct').textContent = demo.filter(r => r.status === 'activa').length;
document.getElementById('kEnt').textContent = demo.filter(r => r.status === 'por_entregar').length;
document.getElementById('kCot').textContent = 3; // ejemplo
