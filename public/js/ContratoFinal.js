/* =======================================
   OBTENER ID DEL CONTRATO (GLOBAL)
======================================= */
const contratoApp = document.getElementById("contratoApp");
const CONTRATO_ID = contratoApp?.dataset.idContrato || null;

console.log("🔎 ID CONTRATO:", CONTRATO_ID);


/* =======================================
   EJECUTAR DESPUÉS DE CARGAR EL DOM
======================================= */
document.addEventListener("DOMContentLoaded", () => {

    /* ================================
       FIRMA DEL CLIENTE
    ================================= */
    const modalCliente = document.getElementById("modalCliente");
    const btnCliente   = document.getElementById("btnFirmaCliente");
    const canvasC      = document.getElementById("padCliente");

    let padCliente = new SignaturePad(canvasC);

    btnCliente?.addEventListener("click", () => {
        modalCliente.style.display = "flex";
        padCliente.clear();
    });

    document.getElementById("clearCliente").onclick = () => padCliente.clear();

    document.getElementById("saveCliente").onclick = () => {
        if (padCliente.isEmpty()) return alert("Firma vacía");

        fetch("/contrato/firma-cliente", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                id_contrato: CONTRATO_ID,
                firma: padCliente.toDataURL("image/png")
            })
        })
        .then(() => {
            alert("Firma del cliente guardada");
            modalCliente.style.display = "none";
            location.reload();
        });
    };


    /* ================================
       FIRMA DEL ARRENDADOR
    ================================= */
    const modalArr = document.getElementById("modalArrendador");
    const btnArr   = document.getElementById("btnFirmaArrendador");
    const canvasA  = document.getElementById("padArrendador");

    let padArr = new SignaturePad(canvasA);

    btnArr?.addEventListener("click", () => {
        modalArr.style.display = "flex";
        padArr.clear();
    });

    document.getElementById("clearArr").onclick = () => padArr.clear();

    document.getElementById("saveArr").onclick = () => {
        if (padArr.isEmpty()) return alert("Realiza la firma");

        fetch("/contrato/firma-arrendador", {
            method:"POST",
            headers:{
                "Content-Type":"application/json",
                "X-CSRF-TOKEN":document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                id_contrato: CONTRATO_ID,
                firma: padArr.toDataURL("image/png")
            })
        })
        .then(() => {
            alert("Firma del arrendador guardada");
            modalArr.style.display = "none";
            location.reload();
        });
    };



    /* =======================================
       MODAL DEL AVISO — VERSIÓN CORRECTA
    ======================================== */
    const modalAviso = document.getElementById("modalAviso");
    const btnAbrirModalAviso = document.getElementById("btnAbrirModalAviso");
    const cancelarAviso = document.getElementById("cancelarAviso");
    const confirmarAviso = document.getElementById("confirmarAviso");
    const textoCliente = document.getElementById("textoCliente");

    btnAbrirModalAviso.addEventListener("click", () => {
        modalAviso.style.display = "flex";
        textoCliente.value = "";
    });

    cancelarAviso.addEventListener("click", () => {
        modalAviso.style.display = "none";
    });

    confirmarAviso.addEventListener("click", async () => {

        const cliente = textoCliente.value.trim();

        if (cliente.length < 15) {
            alert("Por favor escriba el mensaje para confirmar.");
            return;
        }

        const resp = await fetch(`/contrato/${CONTRATO_ID}/enviar-correo`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                aviso: cliente
            })
        });

        const data = await resp.json();

        alert(data.msg);
        modalAviso.style.display = "none";
    });

});


/* =======================================
   BOTÓN IMPRIMIR / PDF
======================================= */
const btnPDF = document.querySelector(".btn-pdf");
btnPDF?.addEventListener("click", () => window.print());
