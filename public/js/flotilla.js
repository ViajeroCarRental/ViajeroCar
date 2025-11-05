// Viajero Car Rental/assets/session.js

// Usuarios demo y módulos permitidos
const USERS = [
  { name: 'Jose Juan',    email: 'JoseJuan@viajero.mx',   pass: '1234', modules: ['autos','rentas','admin'] },
  { name: 'Ivan',   email: 'Ivan@viajero.mx',  pass: '1234', modules: [] },
  { name: 'Vista', email: 'Vista@viajero.mx',   pass: '1234', modules: [''] },
  { name: 'Antonio',   email: 'Antonio@viajero.mx',  pass: '1234', modules: ['autos'] }
];

const STORAGE_KEY = 'vc_user';

function login(email, pass) {
  const user = USERS.find(u => u.email === email && u.pass === pass);
  if (!user) return false;
  const payload = { name: user.name, email: user.email, modules: user.modules };
  localStorage.setItem(STORAGE_KEY, JSON.stringify(payload));
  return true;
}

function getUser() {
  try {
    const raw = localStorage.getItem(STORAGE_KEY);
    return raw ? JSON.parse(raw) : null;
  } catch {
    return null;
  }
}

function logout() {
  localStorage.removeItem(STORAGE_KEY);
  location.href = 'index.html';
}

function requireLogin() {
  const user = getUser();
  if (!user) location.href = 'index.html';
  return user;
}

function hasAccess(modName) {
  const user = getUser();
  if (!user) return false;
  return user.modules.includes(modName);
}

function guardModule(modName) {
  const user = requireLogin();
  if (!user.modules.includes(modName)) {
    alert('No tienes permiso para este módulo.');
    location.href = '../dashboard.html';
  }
}

