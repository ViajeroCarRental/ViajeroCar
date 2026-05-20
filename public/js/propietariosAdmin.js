// ==============================
// CONFIG
// ==============================

const baseUrl = "/admin/propietarios";
const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

let padFirmaNuevo = null;
let padFirmaEditar = null;


// ==============================
// INICIO
// ==============================

document.addEventListener("DOMContentLoaded", () => {

    cargarPropietarios();

    const canvasNuevo = document.getElementById("padFirmaNuevo");
    const canvasEditar = document.getElementById("padFirmaEditar");

    if (canvasNuevo && window.SignaturePad) {
        padFirmaNuevo = new SignaturePad(canvasNuevo, {
            minWidth:1,
            maxWidth:2
        });
    }

    if (canvasEditar && window.SignaturePad) {
        padFirmaEditar = new SignaturePad(canvasEditar, {
            minWidth:1,
            maxWidth:2
        });
    }

});


// ==============================
// CARGAR TABLA
// ==============================

async function cargarPropietarios() {

    const res = await fetch("/admin/propietarios/list");
    const data = await res.json();

    const tbody = document.getElementById("tbodyPropietarios");
    tbody.innerHTML = "";

    data.forEach(v => {

        const propietario = v.nombre_propietario ?? "Sin propietario";

        const firma = v.firma_propietario
            ? `<img src="${v.firma_propietario}" width="120">`
            : "Sin firma";

        const btnAccion = v.nombre_propietario
            ? `
                <button class="btn btn-primary btn-sm"
                onclick="abrirEditar(${v.id_vehiculo})">
                Editar
                </button>

                <button class="btn btn-danger btn-sm"
                onclick="eliminarPropietario(${v.id_vehiculo})">
                Eliminar
                </button>
            `
            : `
                <button class="btn btn-success btn-sm"
                onclick="abrirNuevo(${v.id_vehiculo})">
                Registrar propietario
                </button>
            `;

        tbody.innerHTML += `
        <tr>
            <td>${v.marca}</td>
            <td>${v.modelo}</td>
            <td>${v.placa ?? "-"}</td>
            <td>${propietario}</td>
            <td>${firma}</td>
            <td>${btnAccion}</td>
        </tr>
        `;
    });

}


// ==============================
// ABRIR NUEVO (con autocompletado al perder foco en el nombre)
// ==============================

function abrirNuevo(idVehiculo){

    document.getElementById("newVehiculo").value = idVehiculo;
    document.getElementById("newNombre").value   = "";
    document.getElementById("modalNuevo").style.display = "block";

    if(padFirmaNuevo){
        padFirmaNuevo.clear();
    }

}


// ==============================
// GUARDAR NUEVO
// ==============================

document.getElementById("btnGuardarNuevo").addEventListener("click", async () => {

    const nombre = document.getElementById("newNombre").value.trim();

    if(nombre === ""){
        alert("Debe ingresar el nombre del propietario.");
        return;
    }

    if(!padFirmaNuevo || padFirmaNuevo.isEmpty()){
        alert("Debe capturar la firma del propietario.");
        return;
    }

    const firma = padFirmaNuevo.toDataURL("image/png");

    const data = {

        id_vehiculo: document.getElementById("newVehiculo").value,
        nombre_propietario: nombre,
        firma_propietario: firma

    };

    await fetch(baseUrl, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": csrf
        },
        body: JSON.stringify(data)
    });

    closeModal("modalNuevo");
    cargarPropietarios();

});


// ==============================
// ABRIR EDITAR (precarga la firma existente)
// ==============================

async function abrirEditar(id) {

    const res  = await fetch(baseUrl + "/" + id);
    const data = await res.json();

    document.getElementById("editId").value     = data.id_vehiculo;
    document.getElementById("editNombre").value = data.nombre_propietario ?? "";

    document.getElementById("modalEditar").style.display = "block";

    if (padFirmaEditar) {
        padFirmaEditar.clear();

        // Precargar la firma actual en el canvas
        if (data.firma_propietario) {
            const img = new Image();
            img.onload = () => {
                const canvas = document.getElementById("padFirmaEditar");
                const ctx = canvas.getContext("2d");
                ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                // Marcar como no vacío para SignaturePad
                padFirmaEditar._isEmpty = false;
            };
            img.src = data.firma_propietario;
        }
    }
}

// ==============================
// ACTUALIZAR
// ==============================

document.getElementById("btnGuardarEdit").addEventListener("click", async () => {


    const nombre = document.getElementById("editNombre").value.trim();

    if(nombre === ""){
        alert("Debe ingresar el nombre del propietario.");
        return;
    }

    const id = document.getElementById("editId").value;

    if(!padFirmaEditar || padFirmaEditar.isEmpty()){
        alert("Debe capturar la firma del propietario.");
        return;
    }

    const firma = padFirmaEditar.toDataURL("image/png");

    const data = {

        nombre_propietario: nombre,
        firma_propietario: firma

    };

    await fetch(baseUrl + "/" + id, {
        method: "PUT",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": csrf
        },
        body: JSON.stringify(data)
    });

    closeModal("modalEditar");
    cargarPropietarios();

});


// ==============================
// ELIMINAR
// ==============================

async function eliminarPropietario(id) {

    if (!confirm("¿Eliminar propietario?")) return;

    await fetch(baseUrl + "/" + id, {
        method: "DELETE",
        headers: {
            "X-CSRF-TOKEN": csrf
        }
    });

    cargarPropietarios();

}


// ==============================
// LIMPIAR FIRMA NUEVO
// ==============================

document.getElementById("clearFirmaNuevo")?.addEventListener("click", () => {
    if(padFirmaNuevo){
        padFirmaNuevo.clear();
    }
});


// ==============================
// LIMPIAR FIRMA EDITAR
// ==============================

document.getElementById("clearFirmaEditar")?.addEventListener("click", () => {
    if(padFirmaEditar){
        padFirmaEditar.clear();
    }
});


// ==============================
// CERRAR MODALES
// ==============================

function closeModal(id) {
    document.getElementById(id).style.display = "none";
}


// Listener: al salir del campo nombre, si el propietario ya existe,
// cargar su firma automáticamente
document.getElementById("newNombre")?.addEventListener("blur", async () => {

    const nombre = document.getElementById("newNombre").value.trim();
    if (nombre === "") return;

    const res  = await fetch(`/admin/propietarios-buscar/firma?nombre=${encodeURIComponent(nombre)}`);
    const data = await res.json();

    if (data.firma && padFirmaNuevo) {
        padFirmaNuevo.clear();
        const img = new Image();
        img.onload = () => {
            const canvas = document.getElementById("padFirmaNuevo");
            const ctx = canvas.getContext("2d");
            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
            padFirmaNuevo._isEmpty = false;
        };
        img.src = data.firma;
    }
});
