{{-- resources/views/Admin/contrato-final-pdf.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Contrato Final - PDF</title>

<style>
/* ==========================================================
   PÁGINA
========================================================== */
@page { size: A4 portrait; margin: 0; }

html, body {
  margin: 0;
  padding: 0;
  background: #ffffff;
  color: #111827;
  font-family: "DejaVu Sans", sans-serif;
  font-size: 9px;
  line-height: 1.25;
}

p { margin: 0; padding: 0; }
h1, h2, h3 { margin: 0; padding: 0; font-weight: normal; }
table { border-collapse: collapse; }

.pad { padding-left: 6mm; padding-right: 6mm; }

/* ==========================================================
   TABLAS DE ICONO + TEXTO
========================================================== */
.tabla-ico             { width: 100%; }
.tabla-ico td          { border: 0; padding: 0; vertical-align: top; }
.tabla-ico td.celda-ico { width: 11px; padding: 1px 2px 0 0; }
.tabla-roja .tabla-ico td { border: 0; padding: 0; }
.tabla-roja .tabla-ico td.celda-ico { width: 11px; padding: 1px 2px 0 0; }

/* ==========================================================
   ENCABEZADO
========================================================== */
.encabezado { width: 100%; }
.enc-logo   { width: 45%; vertical-align: middle; padding: 5px 0 5px 6mm; }
.enc-datos  { width: 55%; vertical-align: middle; padding: 5px 6mm 5px 0; text-align: right; }

.logo-contrato { width: 138px; height: auto; }

.enc-linea { margin: 3px 0; font-size: 10px; color: #222222; }
.enc-linea .etq { font-weight: bold; }

.burbuja-roja {
  display: inline-block;
  background-color: #d32f2f;
  color: #ffffff;
  padding: 1px 8px;
  border-radius: 8px;
  font-weight: bold;
  font-size: 9.5px;
  margin-left: 4px;
}

/* ==========================================================
   SALUDO
========================================================== */
.bloque-gracias { padding: 4px 6mm 4px 6mm; }
.gracias        { font-size: 14px; color: #1a1a1a; }
.gracias strong { font-weight: bold; color: #000000; }
.frase          { font-size: 9.5px; color: #4b5563; font-style: italic; margin-top: 3px; }

/* ==========================================================
   TÍTULOS DE SECCIÓN
========================================================== */
.titulo-seccion {
  color: #D6001C;
  font-size: 14px;
  font-weight: bold;
  text-transform: uppercase;
  margin: 3px 0 1px 0;
}
.titulo-seccion.sangrado { padding-left: 6mm; }

/* ==========================================================
   VEHÍCULO
========================================================== */
.bloque-vehiculo {
  background-color: #d32f2f;
  color: #ffffff;
  margin-left: 6mm;
  padding: 5px 12px 5px 16px;
  border-top-left-radius: 20px;
  border-bottom-left-radius: 20px;
}
.tabla-vehiculo, .tabla-gasolina { width: 100%; }
.tabla-vehiculo td { vertical-align: top; }
.veh-label { display: block; font-size: 8px; font-weight: bold; color: #ffffff; margin-bottom: 2px; }
.veh-value { display: block; font-size: 9px; font-weight: bold; color: #ffffff; }

.tabla-gasolina    { margin-top: 5px; }
.tabla-gasolina td { width: 50%; vertical-align: top; color: #ffffff; font-size: 9px; }
.tabla-gasolina .gas-label { font-weight: bold; }

/* ==========================================================
   REJILLA DE DOS COLUMNAS
========================================================== */
.dos-col { width: 100%; margin-top: 5px; }
.dos-col td.c-izq { width: 50%; padding-right: 4px; vertical-align: top; }
.dos-col td.c-der { width: 50%; padding-left: 4px;  vertical-align: top; }

/* ==========================================================
   ARRENDATARIO
========================================================== */
.tabla-arrendatario { width: 100%; }
.tabla-arrendatario td {
  padding: 1px 0;
  border-bottom: 1px solid #f3f4f6;
  font-size: 8.5px;
  vertical-align: top;
}
.tabla-arrendatario td.arr-label { width: 36mm; font-weight: bold; color: #4b5563; }
.tabla-arrendatario tr:last-child td { border-bottom: none; }

.licencia-table { width: 100%; margin-top: 3px; font-size: 8px; }
.licencia-table th {
  background: #f3f4f6;
  border: 1px solid #e5e7eb;
  padding: 1.5px 3px;
  font-weight: bold;
  text-align: left;
}
.licencia-table td { border: 1px solid #e5e7eb; padding: 1.5px 3px; }

/* ==========================================================
   ITINERARIO
========================================================== */
.it-grupo  { margin-bottom: 4px; }
.it-label  { display: block; font-weight: bold; color: #111827; font-size: 9.5px; margin-bottom: 1px; }
.it-texto  { font-size: 9px; font-weight: bold; color: #111827; line-height: 1.3; }

/* ==========================================================
   TARIFAS / ADICIONALES
========================================================== */
.dos-col-bleed {
  width: 100%;
  margin-top: 7px;
  border-collapse: separate;
  border-spacing: 4px 0;
}
.dos-col-bleed td.tit-izq,
.dos-col-bleed td.tit-der { width: 50%; padding: 0; vertical-align: bottom; }

.dos-col-bleed td.celda-roja {
  width: 50%;
  vertical-align: top;
  background: #D6001C;
  color: #ffffff;
  padding: 4px 6px;
}
.dos-col-bleed td.roja-izq { border-top-right-radius: 10px; border-bottom-right-radius: 10px; }
.dos-col-bleed td.roja-der { border-top-left-radius: 10px;  border-bottom-left-radius: 10px; }

.tabla-roja { width: 100%; font-size: 8px; color: #ffffff; }
.tabla-roja th {
  border: 1px solid #ffffff;
  padding: 1px 3px;
  font-weight: bold;
  text-align: left;
  color: #ffffff;
}
.tabla-roja td {
  border: 1px solid #ffffff;
  padding: 1px 3px;
  color: #ffffff;
  font-weight: bold;
}

.totales {
  margin-top: 4px;
  padding-top: 3px;
  text-align: right;
  border-top: 2px solid #ffffff;
}
.totales p { font-size: 8.5px; color: #ffffff; margin: 1px 0; }
.totales .total-final { font-size: 11.5px; font-weight: bold; color: #ffffff; }

.marca      { font-size: 7.5px; font-weight: normal; color: #ffd9de; }
.marca.tenue { font-style: italic; }

.fila-total-adicionales td { border-top: 2px solid #ffffff; }

/* ==========================================================
   ACEPTACIÓN Y FIRMAS
========================================================== */
.aceptacion-texto {
  font-size: 10px;
  line-height: 1.3;
  text-align: justify;
  color: #111827;
  margin-bottom: 3px;
}
.aceptacion-texto a { color: #D6001C; text-decoration: underline; }

.tabla-firmas    { width: 100%; margin-top: 4px; }
.tabla-firmas td { width: 50%; text-align: center; vertical-align: bottom; padding: 0 18px; }
.firma-label     { font-size: 12px; font-weight: bold; color: #4b5563; text-transform: uppercase; margin-bottom: 1px; }
.firma-img       { height: 45px; }
.firma-linea     { border-bottom: 2px solid #000000; height: 2px; }
.firma-nombre    { font-size: 10px; font-weight: bold; margin-top: 6px; color: #111827; }

/* ==========================================================
   GASOLINA / NOTAS / FACTURACIÓN
========================================================== */
.gasolina-texto { font-size: 10px; color: #111827; line-height: 1.35; margin-top: 10px; }
.nota-gas       { display: block; font-size: 7.5px; color: #6b7280; }

.nota-title { font-size: 12px; font-weight: bold; color: #111827; margin-bottom: 2px; }
.nota       { font-size: 9px; line-height: 1.2; margin: 1px 0; color: #111827; }

.fact-title      { font-size: 12px; font-weight: bold; color: #111827; margin-bottom: 5px; }
.tabla-fact      { width: 100%; }
.tabla-fact td   { width: 25%; padding: 1px 4px 1px 0; border-bottom: 1px solid #f3f4f6; font-size: 10px; vertical-align: top; }
.tabla-fact .lbl { font-weight: bold; color: #4b5563; }

/* ==========================================================
   PIE ROJO
========================================================== */
.pie-rojo {
  background: #D6001C;
  color: #ffffff;
  padding: 6px 6mm 7px 6mm;
  margin-top: 9px;
  page-break-inside: avoid;
}
.pie-empresa      { font-size: 12px; font-weight: bold; margin-bottom: 2px; }
.tabla-pie        { width: 100%; margin-top: 3px; }
.tabla-pie td     { vertical-align: top; font-size: 12px; line-height: 1.25; color: #ffffff; }
.tabla-pie td.izq { width: 55%; text-align: left; }
.tabla-pie td.der { width: 45%; text-align: right; }
.tabla-pie span   { display: block; }

/* ==========================================================
   HOJA 2 — CLÁUSULAS
========================================================== */
.pagina-clausulas { width: 100%; page-break-before: always; }
.esp-top    { height: 4mm; }
.esp-bottom { height: 6mm; }
.celda-clausulas { padding: 0 8mm; vertical-align: top; border: 0; }

.clausulas-intro {
  text-align: center;
  font-size: 7.5pt;
  line-height: 1.25;
  text-transform: uppercase;
  margin-bottom: 4mm;
}
.clausulas-title {
  text-align: center;
  font-size: 20pt;
  font-weight: bold;
  color: #4a4a4a;
  text-transform: uppercase;
  margin-bottom: 4mm;
}
.clausula {
  margin: 0 0 2.2mm 0;
  font-size: 7.5pt;
  line-height: 1.3;
  text-align: justify;
}
.clausula-tag { font-weight: bold; text-transform: uppercase; color: #111111; }

.clausulas-notas       { margin-top: 3mm; font-size: 7pt; line-height: 1.3; color: #222222; }
.clausulas-notas .nota-titulo { font-weight: bold; text-transform: uppercase; }
.clausulas-notas .bloque-nota { margin: 1.5mm 0 2.5mm 0; }

.tabla-fecha        { width: 100%; margin: 5mm 0 2mm 0; font-size: 8.5pt; color: #333333; }
.tabla-fecha td     { padding: 0 1mm; vertical-align: bottom; border: 0; }
.tabla-fecha .linea { border-bottom: 1px solid #000000; text-align: center; font-weight: bold; }

.tabla-firmas-clausulas    { width: 100%; margin-top: 5mm; }
.tabla-firmas-clausulas td { width: 50%; text-align: center; vertical-align: bottom; padding: 0 10mm; border: 0; }
.firma-img-clausulas       { height: 11mm; }
.firma-line                { border-top: 1px solid #000000; margin-top: 1mm; padding-top: 1mm; }
.firma-nombre-clausulas    { font-size: 9.5pt; font-weight: bold; }
</style>
</head>

<body>

@php
/* ==========================================================
   AYUDANTES
========================================================== */
$mostrarIconos = true;

$iconosPng = [
    'ubicacion_blanco' => 'iVBORw0KGgoAAAANSUhEUgAAACQAAAAkCAYAAADhAJiYAAAABmJLR0QA/wD/AP+gvaeTAAACj0lEQVRYhe2WPWhTURTHz81HHRLQBtpNEBTRWnWwHUS7iIsf7dKpu1AE8QPBrQ4K6lYXKR20Dl10EDdR6SCoQxHFD9CqtQ5VaJVaK0pDTH8OOQ9PbtIk5L22oP3DJe+dc+45v3tf7ofIqlb1j8mFTQCkRaRZX6eccz/D5qwHogW4DLylVGNAP7B1OUAywHUgXwbEVx64BqxbKpgtwEQNIL7Ggc1Rw2wAPnuF5oArQCeFT9gCdAED6rP6BKyPCiYBjHoFbgFNFfo0A7e9Po+BeBRAR73EVwGnvg5gGHimbRjoUF8MGPL6HgkLEwM+mIRPgaTO2hCwQKkW1JcAGoAXxvcuGEy9QLu9YgfUfqkMiK8LGtvp2dvDAJ0xiaZ0xtYC88Y+CfRpmzT2eY2NA1+N/XQYoEGT6I7aDhlbHtho4jdRvEcdVPs9YxuoVDNWhSllnmf1166uGefcePDinHsvIjPGH8R+M7Z0GKAfZZJ/MbaMP0MikjH+INYOYq5SwUQVoDfmuQ1IishDEcmKyBopDOgBMKgxvfJ3kFkReQQ0iMguk+d1lZqLC9jprZDDaq9llV3U2C7PvqNuIE04ZpLdVVtN+5DG3je++mfHAJ3yCu4xvkV3avXv9fqeiAIoDUybpKPUcCZR2H+emH7TFC5z4QUc90Z6soY+/sweiwRGkycpPpN+AdsqxLdqTKDnwX8qSqh2IGeKvARSZeJSwCsTlwPaIoUxxc56n+EG5vQGHHDTi+lbEhgtGAdGvILnjP+85xshiktZFagm4KNXuFeb1QQVbpVRQ20Hvpvivyk+5WeB1mWBMVD7gSylygL7lhXGQHVTvPJyQPeKwBioHgq3w3mgZ0VhAgGNQONKc/wf+gPcQAwS2M2gmgAAAABJRU5ErkJggg==',
    'calendario_blanco' => 'iVBORw0KGgoAAAANSUhEUgAAACQAAAAkCAYAAADhAJiYAAAABmJLR0QA/wD/AP+gvaeTAAABxklEQVRYhe2XsU4UQRiAvwFUOgIWNpoQSTBB0WisLKx4AXpigrwDPAPxCQhvAdbaURktKIyJ2lCZGC9GE0A5Pwv2yNxwu3NLbq/aL5lk7p9/7/+yO7szAy0jRJ1RZ5rKryuzoh4XbWXU+T0majitAdNFW2sgH4CpqkH1OnC7+DkbDc2qdzP/PShf4CiEcDasYE9kWX2rnjp6fquv1flhZZ6rJw2IpPxQH6X1QyJzDfgILNS6pVfnA/A0hPCvF0gn9cNEZhNYLGJ7UXyviFW1QfmLwKso/hi4Hwukk/pB1P8eQri4WO1EY50QwlcqKMtXt4ANYK4YewIclgndiPpd+9+kA+BFr2/+LavK/1tS85JQ/FW9BXwpKbaTkamT3/clT+fQvZqFRkFfzVTo0xhFBtZMH9nPqP8NeNaQxAHnUyKtWbl0dHNv0lVRu2VjdRbXsdAK5WiFcrRCOVqhHK1QjlToNOpPq4FmiDdlcc1Li+v7qD8HbKu7QOliWJNJYBW4GcUO44T01DEBvON88z0OPgNLIYSLLW3fIyuOI+tAh+b5A7yMZUpR59V99VcDB8QT9Y26PKh25aRVp4A7ubyaHA11V1pK+A+bvccj5YWQHQAAAABJRU5ErkJggg==',
    'reloj_blanco' => 'iVBORw0KGgoAAAANSUhEUgAAACQAAAAkCAYAAADhAJiYAAAABmJLR0QA/wD/AP+gvaeTAAACrklEQVRYhe2Yy2oUQRSGz4mJm1whRFREJqI+QqIxhCy8jKCShQ9hRB9BN9EsAwmIkJhd1KUL3WjcxstKoqALx+ANnFzcOMGNl89F90DlTHVNd8+4COSHgerqv/76qnqmqqZFdrTNpHkbArtE5LCI7BWRA3H1FxFZFZGSqv5pHK8+hAJngXlgjWStAneAIpB70PVgRoGXAYgkPQdGmgnSCtyq02kl/oQ0A7Q2CtMDPPWEvwcmgAGg3fG3A4PADaDkabcI9DQyMxZmDbiUZqRAGzAOrJuMJ7lmitrH9Ao4mOAtAsWEewVg2WRNZ4UZ9cB0JHjHHd94gqfTAzWcFkaBF+YxeWcm9i843oWArwBsON5neJaEFk/boogMOtfXVPVzYAyaUN5qUv0oItedqucicjoN0EWnXBKR+QBMVs2JyEpCX7VARNvBeafqnqr+bhaNqv4SkftO1YW4Tz+QRHtTn3P9qFkwjh465T0icigEtN9cv/sPQG9DfVqgfU75h6puZuzsFDAWMqhqRUQqaYEI3EvSJ6fcJyIPgLtAb4groU9DU7sgehdD06bDrEVVffOt3kCX8SWfBICjxjxQD8hpOxZDuPoJdBvfMeM5EgptMaETaYHi9r3x4woBTTr3y0D4q0F00quqBLRlgYozzgG3gZOmfjew4uTPpgkrmin1bph5BFw12TVbh6+REh07q1oHCk2A6Qe+O7lLWRqPmJEsA50NwHQBb0zmiawhMx6oQg6YfuC1yZrKmlM9wi6aoA3gMim+6ERH2CvmMQE8xmyoWaB6iM7AVh+Am0RrSofj74zrJtn6a3JhukN9poFqBaY94a4qwGYdz1TumUkAGyY6dmbVEjDUNBADpcAZYI7o73KSysAsadYZo0ZeNrRI7cuGryJSluhlw9+82TvaVvoHQ6ftNERnl34AAAAASUVORK5CYII=',
    'persona_blanco' => 'iVBORw0KGgoAAAANSUhEUgAAACQAAAAkCAYAAADhAJiYAAAABmJLR0QA/wD/AP+gvaeTAAACCUlEQVRYhe2WPWsUURSGn7vRgJWyAa1WUMHGxUpLCyUgWEhEzB/wB1iIRcTC2j9gI3apFUGMkkL0ByQRbBJU0DarWMTEfD0WmSWXyX7Mnd1JIftWd2fOec8zs+fcOzDSSP+ZQtlE9ThwF7gOnMkufwPmgOchhN+D4xWHmVJbdldLnTosmNvqdg+YtrbVW6n+SX+ZOgEsA/X2JeA18D77fRW4Efm2gPMhhJ+pYEWBHkRvYEe90yFmWt2N4u5XApMVm48KvegR9zKKe5dSo5bI1IjWH3rEfYzWp1MKpALFKtp/SX2aCvQjWl/pERff+55Yo7hyTb1bRVMPc+wFrnGYY59BVboxloWq7OgY5uEqe4frWwY4XI+UhKkBZ4FNYIH96VsFtoBz6lIIYaeMfwpIQ32irhbooVYW2+jvnA5SU++pGwVA8tpUH6tjRWr17SH1GPAKmOxw+xfwmb3xBpgAmsCJDrHzwM0QwnoRsG4w4+pc7ok31GfqpayX8jlj6uUsJv9G36hHBwGayRl+UpsJ+c0sJ9ZMWZiGuhYZLan1/pkHfOpZbltrlml09VFksqVeTDbZ97qQNXdbD8uYLEYGs2VhIr/ZyG8hNbmWe6IDp3oJoOnI768dBgK6fw+dAuJp+DIoEPA1Wo8DJ1OA/gDt/WJ9SEArOc+0/UidVJ+qnTbEUqrCc6SR8voHR3jn+Bz85pQAAAAASUVORK5CYII=',
    'bebe_blanco' => 'iVBORw0KGgoAAAANSUhEUgAAACQAAAAkCAYAAADhAJiYAAAABmJLR0QA/wD/AP+gvaeTAAAB60lEQVRYhe2WQUtUURTH/0drEEEXRS0sFxERuNGyRFeCgfkZ6pMI5TL6Aq3CZa2jVS1q0aI0sAgxRcVAaZM1gW3DX4u50fE2vnl33rxhovnDwJ/zzj3nd+fdd++VuvrHZEUGAyclTUi6EEKfJC2b2c+iYKkgFeAu8JW/tQ/cASrtghkEluqAxHoDDLYD6Gmd5gfhF+tJ2TA3ooZLwDX3/Hqdf2+mTKBF12gXGKiTMwDsubyHKT16EpnGnH9kZj/ihBB77EJXygQ65fznjLw950+nNEgF+u78uYy8YeerKQ1Sgd47f/u4NSTplgu9S+yRXx33lYWmnbMPBaDO2qkDVGlnWStPe1Q77d+2/bT/r9TUKwuLdU7STUmXJZ1R7ZXtS9qU9FzSMzM7aBHnsSD9wAJQzfGVVcPC7y8L5hLwIQdIrI/ASN4+uV4ZcFHSa0lnfVjSiqRX+nOYnpc0LWk8qv1F0pSZ7eQFy4LpAzaiWb8AxjLGjIYcr3WgrxVA81HhB0BvjnG9IddrvihMhaML+CWQ+4YA9IQxv1Wltpk2DTQXzfBqEzXGoxqzWfmNZjvh/JaZJd9tzGxF0rYLTRYB8rfC9VQYpw3nh7ISGwF9c36taRxp1fnMK+2JBoXuSToM/n4BoFbV6aqrhvoFrrbfnr83tn8AAAAASUVORK5CYII=',
    'camion_blanco' => 'iVBORw0KGgoAAAANSUhEUgAAACQAAAAkCAYAAADhAJiYAAAABmJLR0QA/wD/AP+gvaeTAAABpUlEQVRYhe2Wvy41QRjGf++JkC/xpxE+hYhIRKHyJ/kqlYSCe3AHSteAe0DhFiSESkNDIeQLFZWQKFCQQzyKs2QM5uyZPXsU9kkmu/POPO/+dnf23YFChQr9MpnbkTQJzAHNkfnKwJqZ7WQGktQBXAJ/YpMlegB6zOw2xlxyzrvrAEOSozvW3BQY2wTuU+ZpA6ZjIVyFgObN7CxNEkmDwKkT6pX07PRfgAszUxagLPpqUe9KmjWzu5CxFBqssyaALUntoUmNBAL4RxWovIAmgYGkDQH7tUABlYWpjxpMe/VqXkntkva8OXuS2vxcDXllyUKe4vOTWvgRIA/q2An3/xgQvEMdheaE6pBf3ELqrcHbGgsU/cfO4nVfWTkDQKye/IALdAFsN46FR2DdD/obNAP6qIB2AhvJ0dcNMANcf3OxNN7/Ne2ZJC17hazs9Zfy8IY++zHnfAVoAVad2Hge3rR1yJz2pqp7mzp4Hcfnx/7s9Rfz8IaAuiRd6mtdSfqbh7ca1KikQy/hgaSRvLwWGkwSl4BhKuXgHDgxs5eUNxTtLVQoVq9NjKA8Pt/vmgAAAABJRU5ErkJggg==',
    'meta_blanco' => 'iVBORw0KGgoAAAANSUhEUgAAACQAAAAkCAYAAADhAJiYAAAABmJLR0QA/wD/AP+gvaeTAAABB0lEQVRYhe2XoQ7CMBCG/xJCgiIoFHaKx8AhSOAFeBOCAAyvMTyO7E0QeAwKgsD8mImlW9Prui2I+9x1be/beu02QFHiMK4LJOcANgAGkTnuAPbGmHftGUiOSH7YHAdp7p6jfQJgWPuOyjhXwqYv7HcF8BL2HQOYF+IHgJNUqBKSifXIk4CxZ2vsKkomRojkwhqXhuZ21VBdlla8C52gaaGLFW8bmTWyhlJr7Dokd+V2zAVuhabYXTYzxjxDxEpCIaeegKM0d9M15ILSjm0cjDZ3AOJXRyUxRR1LV0smRoV8qJAPFfKhQj5UyIcK+XAJfT1xt5A0JLP80yMjKf7zbFtq+hcySoEfagVadz9yTY8AAAAASUVORK5CYII=',
    'fuego_blanco' => 'iVBORw0KGgoAAAANSUhEUgAAACQAAAAkCAYAAADhAJiYAAAABmJLR0QA/wD/AP+gvaeTAAAB2klEQVRYhe2WzytEURTHz2MQTUQ22MvGLOz8A1LCglJYsVSiKStl4z+wVDZ2wkJRspLFRMkOaxqUFTHR/PhYzJucud689/Bm3uZ969U9955zz6fTefdekUg1ENAOtIfNISIiwDDwYX9zYfMIsM23CkAybKBdfmohTKATB6A8MB4W0I0DEMAr0FdrmAa7mUvasfuopCugsZZAA0ZVEsC6MbdaS6CkSvwC1AMxIKXm34GeagFMAdfAPtACnKrEB8qvH8iqtY1qwAwDOZVkheLfVNK84b+p1t6AtiBh4kDa6I17Nf4EOoyYXsobPLhTHFjGXbsV4s6Uz56fXHU+maY91rcqzB+q8aDPXO4Cmo3eMfUMxCrEDhm+rV75/FSoS0Tqlf1hrKcsy8pViE0bdmcQQKbWRCSr7FsXX7NyBa/NHUtt6FFE8vJdpTsp9sOciGREZN0ltl+NcyLy5COft4BL1QdHv4jTL4HzQGDsjZOUa8JHzJQRsxQkUCvwpDbPAJMeMBnlnwbigQHZScYoP3kBjoEZindXApjl54MtD4wECqOgFh2g3JSn2s9ZYBR49AHzULXKOEDFKd5vF5Sf4jngHFjijz1jBQDXJCLdtvlgWdbnf/eMFClSpDD1BbOVPDH0ogCjAAAAAElFTkSuQmCC',
    'ubicacion_rojo' => 'iVBORw0KGgoAAAANSUhEUgAAACQAAAAkCAYAAADhAJiYAAAABmJLR0QA/wD/AP+gvaeTAAAEc0lEQVRYhe2XT2wUdRTHP29maw+AZAklMRxErS0yOztbtggEUA5GxYsJBg4YERIRLxjDFSWx/DmiNwEVId5siDfUxCAqYAjtbmd2R0oLxMRgohvaInho2ZnnobO7E9LutqVoorzLzvvO7733+f3m92/hgT2w/5jJvSbwLWtuYBiLAFTkd8fz/vrHgfyOjmWh6puovgQ8eVfCgVD1lCQSR+1c7tJ9BfIta0FgmodE5DXAaNA8BE6URXZ39PWNzDqQZ9tLDcP4SmHJVGMiuxbAhozrDswaUD6TWZJQPQ88EpNvofq5wNdimlcBCILWUGQD8Cowr9JQ4TcJglV2sfjrPQN9t359YuHw8HlgRUz+8o5h7Fyez5cminHT6UUmHFWRl6ui6k+X2tvXbe7uDurVSzQCWjg0tAOROMyxlOu+IaBuOr3OENkJWNE7P1Q94njejwobfcf5VGEbACKrl12+vB34pF69uiOkYBQd5wrwWOTnxxKJlbfmzdOW4eGPFV6fIIcKnCglkzsWlUpGmEj0AHb07krKddsEdLKadVdKoaNjZQUGgDDc09nbe6dleHh/1POJOiQK2xaOjHRZvj8mqnti71r9TKazXs26QIbqupj7h10ofNOTzc5XeCemX1fYq7AXuF5VVXf3ZLPzf25vP4XqjZqsz8wYCNUnqo+QEwibg2At0BzJIUHwbNp196Vdd59hmusZ338Amh8KgjWbu7sDRHKxrK0zBlKYU3kWGIm0lliTIbtYvFpxrFzuCjAUS15pOxyLmTtjIOBWDKgl+o0v9QWFVKo6iv7y5a3Agoof1trWOqH6Z72C9Ze9aj8yPm8VOnuy2aZRONtcLo8y/tkMTPN7z3GOAIRBsJNaJ0fHTPOcb1kPhZCtphSpe77VHSExzTMxd35zufxCZ2/vTYEPY/pigS6BLmBxNRY+6OztvYlpvgg8XNWD4IcZA6XyeVcgfgbtAiglk+8KHGfi/UQFjpeSyfcAwigmsn63WPTq1Wy4U4dwWOBQ5D5fzGTWpM6cOQdsd9PpY5Pt1AC+46wN4bkaqh5uVK/hWeZb1twwkbhGbWJevNTWtrrRmfTFpk3mU4ODF1CtzJ+SUS4/bvn+7XpxjVYZlu/fRnV/TFqxbHBw16QBkS0dGHg7BoNCVyOYKQEBjDY1fQQUqslVD/qOY03W3k2nUwIHqoKIdyOZbPi5YBoXtGIms0LH70SVeVcMVVfdfYd20+k5InJBavOqjGGstvP5nqnUmdIIAaT6+i4q7ItLhsgxjXVKQUyRz2IwKHRNFWZaQAD9bW0HFE7HpM2+47xfcQqO06WwKQZzur+t7eB0akz7X0euo6OlKQwvAo/GCr8VJavOE4Ffxgzj6clulbMGBODZti2GcZbaDhxEuSojfjNUXet4XnG6uaf1ySqWLhQKahivAGORZMZyjRmqG2cCM2MggHQ+/62obgHKMbksqlsszzs9Wdx9AwJIed5JUd0KjAKjoro15Xkn7yXnrJhn20nPtpP/Nsf/w/4G0XaytF1AuS8AAAAASUVORK5CYII=',
    'calendario_rojo' => 'iVBORw0KGgoAAAANSUhEUgAAACQAAAAkCAYAAADhAJiYAAAABmJLR0QA/wD/AP+gvaeTAAACZ0lEQVRYhe2XMW/TQBTH/+8ckmxVwtAFFiIVicS2XHfqgFqpX4AdIQErM3wGxMaGkOA7VEwsjYTUKSWpXZAqFYTUpUgoERIMSX33WGLH5yZ2UpxM/kuWzs//0/v57t35DBTKUR3XXeu47tqy/AAg5jV6jrNXCYKLShBceI6zl7d/YSBI+RBAFUB13M7XP1Yp7eGXZrMshbgFAAKo8TgugJpnmnfS+k7zMxH36/Xz3XY7mNWPpgU90zRJiNcAtgGU0xJfQ38BtAOiZ06v9yMTyDPN+yTERwCVnEGSGpAQu61u9zge1Gqo47o3yDDerQAGAGpKqfecYNBuyqORBeZGFCB6bhBtsFINYt6Pwsz7rFQj7ZrmN4g2wPwqigOOb5rNOINW1MIwWsxhKeKX2etFnT3LGoTzq4CB5fvf015/lp+BFye2/RRAfZxzE4A/FUgBlaiomKW2koQ4BPOjsJ21ymb5fQDEfAmiKGe8mwZEwGRXJWonom/R/WTkQMxvIDK2sDn9Wk4kakgx303Pkr+SOfUKJzpdLc7VnNqUMfA7VkM/mXl7GRBEdAii9TDnTCANjkhanpe6kq4rz7bl1E8EFvm4rkgFUJYKoCwVQFkqgLJUAGUpeZ4dhm0Cqjzjr+R/RczRoSyeE0ieGKX8TJODVP3Esl5+FeJtIKXMA6RkGIYCHjDzzTAmlfLjHm0EGBC+bXcIcPIAmENnw1Lp3tbR0WUY0KaMACWEeAxgsAKYkWJ+Eoe5AgQArW73OCDaBPABwJ8lgAwBHLBSW7bnfUo+TC3ag52dUr3fv03MuRX3qFw+T45KoUX0D1JEB0GRNSrmAAAAAElFTkSuQmCC',
    'reloj_rojo' => 'iVBORw0KGgoAAAANSUhEUgAAACQAAAAkCAYAAADhAJiYAAAABmJLR0QA/wD/AP+gvaeTAAAElklEQVRYhe2YX2hbVRzHv79zbw1l6zTajio+rK403ZLcm5JpHTqoyEYR1im0Y/55cyAWURjUPbkHH0QHMnT4B9THzdEVde2EWQcd0hUfEnpv0riE1jH/oKtrV7XVNSS5Px96E05ucvOn1Adl35fk9++cz7n3nnPuucBt/cdE6y0cHhhQfKlUOylKK5jvBwDkcj8x0XzK55s7ePZs7l8HYoDiwWAvCdEPYD+AFpfU3wCMCeaRnbHYVwTwhgPFQ6EeML8F4KFaa2x9y5Z1VIvHv9kQoImeHrX55s13QDRYIW3F/t1cIefkgtd75LFLl7LrBpoOhe5qYB5h4HFHaI6BMwowlmVO6LHYXwBgatomFQhYwH4IcQjM24uqmC9mhRjoMozf6waa6OlRW5aWLjhgbgA4tuD1flxtpJFwuMGTyRwG0esAmqXQ1wte7xNu9a5AcU17z3GbDKGqB/zR6I/O3ISm9QKAPxa74IxNh0LbVOAcmDXJ/W7QNF+pGch+gCeKYLLZPf5EYqUkV9NeBNH7AADmwWAs9oEzJ+nzNWUaGydlKAHs8ZvmpDNXOB0MEDO/KbluCFU9UA5mbUj0SNn/kjpTqeUscADMi3mfxXycy1yQEqB4MNhLQLcE+Fq521RgYKZy/53qMoxrJMQxCX53Qtf3VQWyF7285ha93k/cOqlXq4ryEYCreZuJ+p05RUDDAwMK1lbgNTjgdLXZVI92RaMZMH9acDD32X2WB/KlUu2QtwOi8xsFI3U4JplbA7OzD7gCKUT3yTZlMlc2GkhJp7+T7ayjzyIgi+heyfzTdWa5iIn2xkKhJyvldKZSywCWCwC5nDuQsCx2i7lCCPGDZLYQ8+czmnbqSmfnPRXK5NlY9CZQ1CkL8atkbk74/ZU2y7UGMpk3AJwqaofomazHM5NfwWXNdndvgbQJW8y/uALlHEGo6s5qQP5EYiVoms8x0VMArkuhVovos0g4fKec//etW0VtqooiX4RiIN005+RGc9ISUE2aYXyhptMBYj5dKU8I0SeZ8zsM43tXIAIsAF8WbKKnI+FwQ61QO5LJxUAs9izWBvIhC9G3Kxr9Ix9P+P13gPlQ3mbmUbvPgtSSETCPWETP2xXbPZnMYQAlG2YlBU3zPICSNcxS1RcAtOVtIhpx5pTsPQzQjK5PAXjYdi1kiR7sMoxr9UA5ZWpamyCKALjbdk0FTbNkMy7dywBmyzoquZpV4FzS52taL8xsd/cWQTQqwYCIXi2XW3atsV/ITxYczFqmsXFyOhTaVi+MqWltq6urkwACheaAEwHDuFwzEAAseL1HwHxRhlItKzITCg3W8qBHwuGGmK6/ZN+moBQaT3Z0DLnVVX3JV5mHAex1hK6C6IxlWWNqLjeT32KSPl9T2uPxCyH67NnU5qgbT6vqQXnm1QUE2MegpaW3AbxcIW3FbmuTWwIDJ5IdHUPVTrQ1HxQTuv6oxXwcRLtrrbE1BaKhoGFM1ZJc91E6oev7mKgfzH0AtrqkzjPzKBGNBE1zvJ4+1v2xgQFh6nq7sKxWKEr+Y8PPlhDXddOcc67At/W/1T8Edr5ePZF1vAAAAABJRU5ErkJggg==',
    'persona_rojo' => 'iVBORw0KGgoAAAANSUhEUgAAACQAAAAkCAYAAADhAJiYAAAABmJLR0QA/wD/AP+gvaeTAAADC0lEQVRYhe2XwWsTURCHv3lJ2qqIYkFPFVSU0iSbar0oeFAKBQ9VsCkiimgPnvwD9CpUPHjy4kVPItoiakFQEJQKerEm2SRWLCro2YiILTWbHQ9N0rXtttmmEZHOaWf292a+ffvem11YtVX7z0yWO/B1V9eGJscZEOgBtpXDnxQe/wqHb+4dG/v+14Dszs6jonoD2OQjKajIgJVOP2g4UM6yjqnIXSC0hLQEJOOZzP2GAY23t7c6zc3vmZ0ZBR4Bz8v+QeBwNa/qV1Mq7Yrm84Vaa4SDADktLWdRrcC4KnLcSqeHPZKr2USiH7gDCCKtbjh8Brhaaw0TBEhUezzuyBwYAOKZzJCojnhCPXM1KwYEtFUuFEb9RCrywuNubSSQ12pafxJwnQYF+uIpdMAXQrV6T1U/NwxIRZ543CN2Z2dyriabSPSrSO8snTwOUmPFtr2CChyizm3/zx2MgRd1zLbvqUgfsNhTF1SkLygMrGBzFVCdaa5P6mmuy9r2CmaN42wHfgmkgFGFFwJpoBgpFncMJZNLvdIFLdAMZWOxNow5D5xFpHUJeQHVG7jutXgu92UJbTAgBZOzrPOIXAGaa01etiIig+M7d17qHx4u1Q30ct++Net//hxBpHuB29+AvKh+BdCZWYsBG+cpVZ/+WLeud/+rV1PLBspHo01uODzCnw1yGriFMddjqdQbAdc7ZiiZDHVMTOxR1XPASTwzWv6a7N07NlZcFlDWsi4gMugNuaonEradW2xcxTKWFTMit4F4Nah6MW7blwMDZWOxNkKhd8DaGaXYplg8GOTUBchHo5vcSOQZqlY5NEmp1O630H23vYRCp6sw4OA4p4LCAETz+YJRPQFUXtNajDnlp/cFUuirwqkOxXM5OyhMFSqTyYvq7MecyLymvCiQzsQ7Kr5rTOC/h3k5RR563A71qb1gcLyrawsQqSZz3Q/1AmHMR4/X9DYa3Vwz0CRMApXzYqoYidQNNG3MhDfnVEvLgueR7y6zd+/uFtc9psbcs1Kpp/UCNSrnqq3aXPsNxr4tZRvASmkAAAAASUVORK5CYII=',
    'bebe_rojo' => 'iVBORw0KGgoAAAANSUhEUgAAACQAAAAkCAYAAADhAJiYAAAABmJLR0QA/wD/AP+gvaeTAAADO0lEQVRYhe2Wy29bRRSHv3OvcW8RdVMQIEErtUKJI9nxQ07jsqFKoS0bBAs2PNR/ARBFrGCJEBIgNv0DEI8NG1gBRVCxQDXE2I7j1iGIVApIiIZGfYjcJtdzWJCbDFZi5zo2ElJ+0pVmzpzHp5m5MwO7+p9JdhI8VSjc4bVaE6p6BEBE5q8ODZUmL1wI/lOgRioVb8Vir4rqC4jc0za8KPCeBMFbqUZjZeBAc8ViYtn3vxQodnG96Hne6eFS6UaU/E5UIN/3P9gE5ubaZ+vY7eXl96PmjwRUy2QeBZ4I+wolHOfoWK2WGKvVEiIyoVBaHxd5spHJnBgYkCPyrNVdiPv+ybFKZSo0pKvVH+K+fxL4NbQZx3lmYEBALmwIfDg6O9u+TIzOzt5U+Mgy5QcGJHB32DbwWwfXhfUY1fa/sH9ABpaswAc7uB4KGwrXBgYkULEKPddMJve1+zSTyX0CG3tN5MeBARlVe28cWvG88/V8fjw0zORyR1c87zxwcL2AMR9HqRH5YKxns59h/fprCjf3v2ZMVD9NT08/FSV/5IPR87zn7bPGAmlfvot79u49EzV/ZKDhUumGGwSPKLyO6p+buCwKvOYEwfGo1wb08bZXERWYXxwa+n4nt/2uuqmnJZsrFhO+7z8OnAaSAvcqKHAV+An4wvO8zwe+h6YKhTvjQXBW4EXgQBf3JYV3V2Kxt8fL5b/6DnQplxtuwSeoZrYbA4DqZcd1n05VKpf6BlRPpx/Cdb8D7rNLoVoWkW8RWfinth5E9Tgihbbcf6gxD2fq9V92DDR/+LB3a//+KpDcIOFrV+TlVLVa3SxmJp/PGmPeEbAfZ827rl/PH7lyxe9Ur+vBeCuReMmGQfVcc2Tk1FYwAOlKpdYcGTmF6jnLPLqWq6M6zlAjlYqbWOx3NjbwN+la7TEB0y0xgIIzk81+BUyumZZux2L3j5fLq1vFdJ4h1z1hwWBUz24XBkDAqDGvWKYDe4JgcsuAbkAtkQmrO5edno70tgHI1Otl4Oewr3CsZyD7Vagil6PCWGqGDYEHegZSkfXbXKDRM45I3Wp3fNLGOg06q6tvGNc1AE4QvNkrT7/y7GpX29HfNSwk9BHNPcAAAAAASUVORK5CYII=',
    'camion_rojo' => 'iVBORw0KGgoAAAANSUhEUgAAACQAAAAkCAYAAADhAJiYAAAABmJLR0QA/wD/AP+gvaeTAAACTUlEQVRYhe2WsW7TUBSG/3Pt4FSiMFAJMVQVqkqRSGJXDSoTUyVAKgtPUEYmNngIBt4AGHgFJBCdmIhoiGMnglKpasVAFYmBMhAS5xyGNtQxtmM5dTqQf7rn3Puf+8nX9+gCE0000X8m8gfO0tIqMa8TcCZNMQE6otSLUq22MTLQ5vLyecPzvgGYSlvsSL9+6/qlcrX6I41Z9Qe65108ARgAmDqqlUp61IQArxXwM0kRBqYJuJ0WIhEQAw9L9fqXJEVs07yiAVv9mERmnWLR68c9TWPLtvcIkNRAo0gRbYCO74sugoZlvds2jLsLlcpBrDcLoFCJ3Gy322+2V1bOxS0bH9ChbgyDygSIRVaFeV6Y51nkKoD3SaEyARKiryXX3Sm57o7pOFv5fP5WGNTnxcXpsQAFtVCpHIRBdQ3j8akABaAa/RwBl08NqA9FIk7cmsg+FGxucRKRWX/fifOyUmdJovtjJFCwucUpuCrWGwMD+I5MiDqJdj9BMdAN5v4CWba9B+DtGHna0LSXweTAdxWAbMua03o9xcwzei73SkRm/ilF9J2Z10ikFbZTEm83l/sU9mYa+IcIENj2LgC4lvUgULALIHdILhcUcK/ouo/CgJJ4y9VqqDf62ouUfdGzQr1uAHh+TE/Xs/Am7UMEgGjwiIe+bdJ4o4GINn3R/YZpdgRY91X8kIU3EoiZnwDY96U037imed7TLLyRQKbjtIR5TYBaYOoji9y51mzuhxpH9A5txQKoRqFQIKXmWGS36LpNAniYb1TvRBOl1R86ByLyEuGIngAAAABJRU5ErkJggg==',
    'meta_rojo' => 'iVBORw0KGgoAAAANSUhEUgAAACQAAAAkCAYAAADhAJiYAAAABmJLR0QA/wD/AP+gvaeTAAABcElEQVRYhe2YIU/DQBTH/6/rWKYICgyWhFC6LvsKEyQIBAoUHoHBgCEIUHyOSRJISEj2FRBdx5JBMoHDoIYpO/owGynZdb2jBzP3c+/u9d3v8u5OFLBYikFZE1G93qQkOSBgoeAaAxLiYqPXe1dJdmWDD43GIglxC6DKBW0AgMtlBnCqkuvIBl0hlgFUDbgAAIg5sxNTa6skMXDvAEOlXOYlEDVTQ6+lOL4yKpQAR36n86ySGwVBi/hHow/X+/03o0KqdINgG8x7k5iYW14UXevUkJ6h38JJspOOBdG5bg2jQuQ4N+nYZT7TrWFUaDMM74i5NYmZaP/R93d1akivY1irrZWAp+/CBW+ZG8ee6sFWOtQEbCk/kDS1x5XPSuUYwInK50ZblgUTKe/H+MMoYUCj0aVRIZ2HsSj/0jIdrFAeVigPK5SHFcrDCuVhhfKQCjHRx6z4L5EKBWH4AqA9DtvjeL4wQF3PW+UZPyQs8+AL+U5u6wgRDLkAAAAASUVORK5CYII=',
    'fuego_rojo' => 'iVBORw0KGgoAAAANSUhEUgAAACQAAAAkCAYAAADhAJiYAAAABmJLR0QA/wD/AP+gvaeTAAAC/ElEQVRYhe2VS0hUURjH//8zY45mpdGirE1QkEz33CkXYrQJXFiREUXRgyKhNj02QdTKFtGmFhG0KXuQlAsXFeHGVS3KFibjXC9FuZCgLLCIkOkxnvu1cGa8jY+5OqKb+cOF+33ne/w4T6CoeVDCsqoSllU1F7VUoQVcrRup1BCVGnJsu3nBgTzyMIDS9Nfq2Pa5BQUCUOb7J4Br/bHYqdkWKxxIZNlEl9xIxGK7FwaIXDNZXYq0JSxrw7wC9dTWlgBYm2UDOgBI2lxCpdrdaHTRvAGVpFIWxjYzAECMuQyRK76QmITD5+cNSJHbfObPtzU17vDy5ZcAvM5CAhfjtr06aM3wTAAc2z4AkRaQ7/6Ew0dgTBMks0J4sb+jwwBAwrJOUqnedP3yMHABwJkgPQLPkKt1I4CHIGsA7IkYcxoiW7MBIk8zv9pxHJD3s0PA8Z7a2gmncdZAbjRaYcg7AELj/eWML/+vMuaxP8eIXMX4Bl9cOjq6d86ATDh8gkB1jtt/3J9FXfe7fzDW1/cewEufa+ecAVHk0HTjQt6d1A90+sz6OQF6VV9fBnLTNCHD3yoruyYbINDrM1d9qKtbWjBQxcjIKvj2DoDfOSHd254/H52i+Ce//SuZXFEwUK5ItgBIZR0i76aKFaX+u1ZMKOTlq5/3HhqpqBhakkwaZGbJ8z6KSD3JZiiVjEQil6cE8jzLZ45W/vjxpWCgLd3dvxzb7gOwGQCg1FEdj+8A8CZfLkSOgcxYvWsHB3OXe4KCLtmj8R6yvV/rvHeKY9sHQDZkbJLtQRoFAopEIrcBfM1CkW2u1vumhQHuZeOBz0ylWoP0Yv6QMblaN3nkk5ycLiEfwJgERYhQSKeXqcEX44FssuLxTgRQYCAAcLQ+C/L6DPI8kmc3xuM3g/aYERAA9Gu9S8hbAFbmCR0CeSLozMwaCEg/tmPv20GMnb7MxWkAvCHZzlSqNeq6IzOtPSsgvz6sW1eaLC+vBoDyZPLz+oGBP4XWLKqooopaSP0DqPjvDMKKkWsAAAAASUVORK5CYII=',
];

$ico = function (string $nombre, string $color = 'blanco', int $tam = 9) use ($iconosPng, $mostrarIconos): string {
    if (!$mostrarIconos || !isset($iconosPng[$nombre . '_' . $color])) {
        return '';
    }
    return '<img style="width:' . $tam . 'px; height:' . $tam . 'px; display:block;"'
         . ' src="data:image/png;base64,' . $iconosPng[$nombre . '_' . $color] . '">';
};

$aDecorativa = (function () {
    $altoDeseado = 150;
    $sangrado    = 45;
    $desdeArriba = 0;
    $gris        = [150, 150, 150];
    $opacidad    = 0.45;

    $origen = public_path('img/A.png');
    if (!is_file($origen) || !function_exists('imagecreatefrompng')) {
        return null;
    }

    $cache = storage_path('app/a-contrato-' . $altoDeseado . '.png');

    if (!is_file($cache) || filemtime($cache) < filemtime($origen)) {
        try {
            $src = @imagecreatefrompng($origen);
            if (!$src) {
                return null;
            }

            $anchoOrig = imagesx($src);
            $altoOrig  = imagesy($src);
            $alto      = $altoDeseado;
            $ancho     = max(1, (int) round($anchoOrig * $alto / $altoOrig));

            $dest = imagecreatetruecolor($ancho, $alto);
            imagealphablending($dest, false);
            imagesavealpha($dest, true);
            imagefilledrectangle($dest, 0, 0, $ancho, $alto, imagecolorallocatealpha($dest, 255, 255, 255, 127));
            imagecopyresampled($dest, $src, 0, 0, 0, 0, $ancho, $alto, $anchoOrig, $altoOrig);

            for ($y = 0; $y < $alto; $y++) {
                for ($x = 0; $x < $ancho; $x++) {
                    $alfa = (imagecolorat($dest, $x, $y) >> 24) & 0x7F;
                    if ($alfa === 127) {
                        continue;
                    }
                    $nuevoAlfa = (int) round(127 - (127 - $alfa) * $opacidad);
                    imagesetpixel($dest, $x, $y, imagecolorallocatealpha($dest, $gris[0], $gris[1], $gris[2], $nuevoAlfa));
                }
            }

            if (!is_dir(dirname($cache))) {
                @mkdir(dirname($cache), 0775, true);
            }
            imagepng($dest, $cache);
            imagedestroy($src);
            imagedestroy($dest);
        } catch (\Throwable $e) {
            return null;
        }
    }

    $dim = is_file($cache) ? @getimagesize($cache) : false;
    if (!$dim) {
        return null;
    }

    $anchoPagina = 794;

    return [
        'src'   => 'data:image/png;base64,' . base64_encode(file_get_contents($cache)),
        'ancho' => $dim[0],
        'alto'  => $dim[1],
        'left'  => $anchoPagina - $dim[0] + $sangrado,
        'top'   => $desdeArriba,
    ];
})();

$logoSrc = $logoBase64 ?? (function () {
    $aDataUri = fn (string $ruta) => 'data:image/png;base64,' . base64_encode(file_get_contents($ruta));

    $rojo = public_path('img/LogoRojo.png');
    if (is_file($rojo)) {
        return $aDataUri($rojo);
    }

    $origen = public_path('img/LogoB.png');
    if (!is_file($origen)) {
        return null;
    }

    $cache = storage_path('app/logo-contrato-rojo.png');
    if (is_file($cache) && filemtime($cache) >= filemtime($origen)) {
        return $aDataUri($cache);
    }

    if (!function_exists('imagecreatefrompng')) {
        return $aDataUri($origen);
    }

    try {
        $src = @imagecreatefrompng($origen);
        if (!$src) {
            return $aDataUri($origen);
        }

        $ancho = imagesx($src);
        $alto  = imagesy($src);

        $dest = imagecreatetruecolor($ancho, $alto);
        imagealphablending($dest, false);
        imagesavealpha($dest, true);
        imagefilledrectangle($dest, 0, 0, $ancho, $alto, imagecolorallocatealpha($dest, 255, 255, 255, 127));

        for ($y = 0; $y < $alto; $y++) {
            for ($x = 0; $x < $ancho; $x++) {
                $alfa = (imagecolorat($src, $x, $y) >> 24) & 0x7F;
                if ($alfa === 127) {
                    continue;
                }
                imagesetpixel($dest, $x, $y, imagecolorallocatealpha($dest, 214, 0, 28, $alfa));
            }
        }

        if (!is_dir(dirname($cache))) {
            @mkdir(dirname($cache), 0775, true);
        }
        imagepng($dest, $cache);
        imagedestroy($src);
        imagedestroy($dest);

        return is_file($cache) ? $aDataUri($cache) : $aDataUri($origen);
    } catch (\Throwable $e) {
        return $aDataUri($origen);
    }
})();

$firmaSrc = function (...$candidatos) {
    $validar = function (?string $binario, string $mime = 'image/png'): ?string {
        if ($binario === null || $binario === '' || @getimagesizefromstring($binario) === false) {
            return null;
        }
        return 'data:' . $mime . ';base64,' . base64_encode($binario);
    };

    foreach ($candidatos as $valor) {
        if (empty($valor) || !is_string($valor)) {
            continue;
        }

        if (str_starts_with($valor, 'data:image')) {
            $partes = explode(',', $valor, 2);
            if (count($partes) === 2) {
                $mime = 'image/png';
                if (preg_match('#^data:(image/[a-z+]+)#i', $partes[0], $m)) {
                    $mime = strtolower($m[1]);
                }
                if ($ok = $validar(base64_decode(preg_replace('/\s+/', '', $partes[1]), false), $mime)) {
                    return $ok;
                }
            }
            continue;
        }

        if (strlen($valor) < 500) {
            foreach ([
                public_path(ltrim($valor, '/')),
                storage_path('app/public/' . ltrim(str_replace('storage/', '', $valor), '/')),
            ] as $ruta) {
                if (is_file($ruta) && ($bytes = @file_get_contents($ruta)) !== false) {
                    $ext  = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));
                    $mime = in_array($ext, ['jpg', 'jpeg']) ? 'image/jpeg'
                          : ($ext === 'gif' ? 'image/gif' : 'image/png');
                    if ($ok = $validar($bytes, $mime)) {
                        return $ok;
                    }
                }
            }
        }

        if ($ok = $validar(base64_decode(preg_replace('/\s+/', '', $valor), false))) {
            return $ok;
        }
    }

    return null;
};

$nombreCliente    = trim(($reservacion->nombre_cliente ?? '') . ' ' . ($reservacion->apellidos_cliente ?? ''));
$firmaAsesor = $firmaAsesor ?? null;

$firmaCliente = $firmaSrc(
    $contrato->firma_cliente ?? null,
    $contrato->firma_aviso ?? null
);

$firmaPropietario = $firmaSrc(
    $vehiculo->firma_propietario ?? null,
    $firmaAsesor,
    $contrato->firma_arrendador ?? null,
    $reservacion->firma_arrendador ?? null
);

$firmaArrendador = $firmaSrc(
    $firmaAsesor,
    $contrato->firma_arrendador ?? null,
    $reservacion->firma_arrendador ?? null,
    $vehiculo->firma_propietario ?? null
);
@endphp

@if ($aDecorativa)
  <img src="{{ $aDecorativa['src'] }}" alt=""
       style="position: absolute;
              top: {{ $aDecorativa['top'] }}px;
              left: {{ $aDecorativa['left'] }}px;
              width: {{ $aDecorativa['ancho'] }}px;
              height: {{ $aDecorativa['alto'] }}px;">
@endif

{{-- HOJA 1 — CARÁTULA --}}

<table class="encabezado">
  <tr>
    <td class="enc-logo">
      @if ($logoSrc)
        <img src="{{ $logoSrc }}" class="logo-contrato" alt="Viajero Car Rental">
      @endif
    </td>
    <td class="enc-datos">
      <p class="enc-linea">
        <span class="etq">No. Rental Agreement:</span>
        <span class="burbuja-roja">{{ $contrato->id_contrato ?? '—' }}</span>
      </p>
      <p class="enc-linea">
        <span class="etq">Fecha de apertura:</span>
        <span class="burbuja-roja">{{ now()->translatedFormat('d/M/Y H:i') }}</span>
      </p>
      <p class="enc-linea">
        <span class="etq">Reservación:</span>
        <span class="burbuja-roja">{{ $reservacion->id_reservacion ?? '—' }}</span>
      </p>
    </td>
  </tr>
</table>

<div class="bloque-gracias">
  <p class="gracias">Gracias por tu reserva, <strong>{{ $nombreCliente ?: 'Cliente' }}</strong></p>
  <p class="frase">Disfruta el camino tanto como tu destino.</p>
</div>

{{-- VEHÍCULO --}}
<h3 class="titulo-seccion sangrado">Información de tu vehículo</h3>

<div class="bloque-vehiculo">
  <table class="tabla-vehiculo">
    <tr>
      <td><span class="veh-label">Modelo:</span><span class="veh-value">{{ $vehiculo->modelo ?? '—' }}</span></td>
      <td><span class="veh-label">Categoría:</span><span class="veh-value">{{ $vehiculo->categoria ?? '—' }}</span></td>
      <td><span class="veh-label">Color:</span><span class="veh-value">{{ $vehiculo->color ?? '—' }}</span></td>
      <td><span class="veh-label">Placas:</span><span class="veh-value">{{ $vehiculo->placa ?? '—' }}</span></td>
      <td><span class="veh-label">Transmisión:</span><span class="veh-value">{{ $vehiculo->transmision ?? '—' }}</span></td>
      <td><span class="veh-label">Kilometraje:</span><span class="veh-value">{{ number_format($vehiculo->kilometraje ?? 0) }}</span></td>
    </tr>
  </table>

  <table class="tabla-gasolina">
    <tr>
      <td>
        <table class="tabla-ico">
          <tr>
            <td class="celda-ico">{!! $ico('fuego', 'blanco') !!}</td>
            <td>
              <span class="gas-label">Capacidad del tanque:</span>
              {{ $vehiculo->capacidad_tanque ?? '—' }} LITROS
            </td>
          </tr>
        </table>
      </td>
      <td>
        <span class="gas-label">Gasolina de salida:</span>
        {{ $vehiculo->gasolina_actual ?? '—' }} LITROS
      </td>
    </tr>
  </table>
</div>

{{-- ARRENDATARIO + ITINERARIO --}}
<div class="pad">
  <table class="dos-col">
    <tr>
      <td class="c-izq">
        <h3 class="titulo-seccion">Arrendatario</h3>
        <table class="tabla-arrendatario">
          <tr><td class="arr-label">Nombre:</td><td>{{ $nombreCliente ?: '—' }}</td></tr>
          <tr><td class="arr-label">Fecha de nacimiento (DOB):</td><td>{{ $fechaNacimiento ? \Carbon\Carbon::parse($fechaNacimiento)->format('d/m/Y') : '—' }}</td></tr>
          <tr><td class="arr-label">Edad:</td><td>{{ isset($edad) && $edad !== null ? $edad . ' años' : '—' }}</td></tr>
          <tr><td class="arr-label">Teléfono:</td><td>{{ $reservacion->telefono_cliente ?? '—' }}</td></tr>
          <tr><td class="arr-label">Correo:</td><td>{{ $reservacion->email_cliente ?? '—' }}</td></tr>
          <tr><td class="arr-label">Dirección:</td><td>{{ $reservacion->direccion_cliente ?? '—' }}</td></tr>
        </table>

        <table class="licencia-table">
          <thead>
            <tr>
              <th>No. Licencia</th>
              <th>Vencimiento</th>
              <th>País</th>
              <th>Estado</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>{{ $licencia->numero_identificacion ?? '—' }}</td>
              <td>{{ $licencia->fecha_vencimiento ?? '—' }}</td>
              <td>{{ $licencia->pais_emision ?? '—' }}</td>
              <td>{{ $licencia->estado ?? '—' }}</td>
            </tr>
          </tbody>
        </table>
      </td>

      <td class="c-der">
        <h3 class="titulo-seccion">Itinerario</h3>

        @php
          $tramos = [
            'Check in:' => [
              $reservacion->sucursal_retiro_nombre ?? '—',
              $reservacion->fecha_inicio ? \Carbon\Carbon::parse($reservacion->fecha_inicio)->translatedFormat('d/M/Y') : '—',
              ($reservacion->hora_retiro ? \Carbon\Carbon::parse($reservacion->hora_retiro)->format('H:i') : '—') . ' HRS',
            ],
            'Check out:' => [
              $reservacion->sucursal_entrega_nombre ?? '—',
              $reservacion->fecha_fin ? \Carbon\Carbon::parse($reservacion->fecha_fin)->translatedFormat('d/M/Y') : '—',
              ($reservacion->hora_entrega ? \Carbon\Carbon::parse($reservacion->hora_entrega)->format('H:i') : '—') . ' HRS',
            ],
          ];
          $iconosTramo = ['ubicacion', 'calendario', 'reloj'];
        @endphp

        @foreach ($tramos as $etiqueta => $lineas)
          <div class="it-grupo">
            <span class="it-label">{{ $etiqueta }}</span>
            <table class="tabla-ico">
              @foreach ($lineas as $indice => $linea)
                <tr>
                  <td class="celda-ico">{!! $ico($iconosTramo[$indice], 'rojo') !!}</td>
                  <td class="it-texto">{{ $linea }}</td>
                </tr>
              @endforeach
            </table>
          </div>
        @endforeach
      </td>
    </tr>
  </table>
</div>

{{-- TARIFAS + ADICIONALES --}}
<table class="dos-col-bleed">
  <tr>
    <td class="tit-izq"><h3 class="titulo-seccion sangrado">Tarifas</h3></td>
    <td class="tit-der"><h3 class="titulo-seccion">Adicionales</h3></td>
  </tr>
  <tr>
    <td class="celda-roja roja-izq">
      <table class="tabla-roja">
          <thead>
            <tr>
              <th>Concepto</th>
              <th>Días</th>
              <th>Precio por día</th>
              <th>Total</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Tarifa base</td>
              <td>{{ $dias }}</td>
              <td>$ {{ number_format($tarifaBase, 2) }}</td>
              <td>$ {{ number_format($tarifaBase * $dias, 2) }}</td>
            </tr>

            @foreach ($paquetes as $p)
              <tr>
                <td>{{ $p->nombre }}</td>
                <td>{{ $dias }}</td>
                <td>$ {{ number_format($p->precio_por_dia, 2) }}</td>
                <td>$ {{ number_format($p->precio_por_dia * $dias, 2) }}</td>
              </tr>
            @endforeach

            @foreach ($individuales as $i)
              <tr>
                <td>{{ $i->nombre }}</td>
                <td>{{ $dias }}</td>
                <td>$ {{ number_format($i->precio_por_dia, 2) }}</td>
                <td>$ {{ number_format($i->precio_por_dia * $dias, 2) }}</td>
              </tr>
            @endforeach
          </tbody>
        </table>

        <div class="totales">
          <p><strong>Subtotal:</strong> $ {{ number_format($subtotal, 2) }}</p>
          <p><strong>IVA.</strong> $ {{ number_format($subtotal * 0.16, 2) }}</p>
          <p><strong>Cuotas locales e impuestos federales</strong> $ {{ number_format($subtotal * 0.16, 2) }}</p>
        <p class="total-final"><strong>TOTAL:</strong> $ {{ number_format($totalFinal, 2) }}</p>
      </div>
    </td>

    <td class="celda-roja roja-der">
      <table class="tabla-roja">
          <thead>
            <tr>
              <th>Producto</th>
              <th>Días</th>
              <th>Precio por día</th>
            </tr>
          </thead>
          <tbody>
            @php
              $totalAdicionales = 0;

              $extrasSeleccionados = [];
              foreach (($extras ?? []) as $extra) {
                  $extrasSeleccionados[$extra->nombre] = [
                      'precio'   => $extra->precio_unitario ?? 0,
                      'cantidad' => $extra->cantidad ?? 1,
                  ];
              }

              $deliveryActivo = isset($deliveryInfo) && $deliveryInfo && (($deliveryInfo->precio_unitario ?? 0) > 0);
              $dropoffActivo  = isset($dropoffInfo)  && $dropoffInfo  && (($dropoffInfo->precio_unitario ?? 0) > 0);
              $gasolinaActiva = isset($gasolinaInfo) && $gasolinaInfo && (($gasolinaInfo->precio_unitario ?? 0) > 0);

              $serviciosMostrar = [
                  'Additional driver'   => ['icono' => 'persona',   'es_especial' => false],
                  'Conductor menor'     => ['icono' => 'persona',   'es_especial' => false],
                  'Baby seat'           => ['icono' => 'bebe',      'es_especial' => false],
                  'GPS'                 => ['icono' => 'ubicacion', 'es_especial' => false],
                  'Delivery'            => ['icono' => 'camion',    'es_especial' => true],
                  'Drop Off'            => ['icono' => 'meta',      'es_especial' => true],
                  'Gasolina (faltante)' => ['icono' => 'fuego',     'es_especial' => true],
              ];
            @endphp

            @foreach ($serviciosMostrar as $nombre => $config)
              @php
                $esEspecial   = $config['es_especial'];
                $seleccionado = isset($extrasSeleccionados[$nombre]);
                $detalles     = '';

                if ($nombre === 'Delivery' && $deliveryActivo) {
                    $seleccionado = true;
                    $precio   = $deliveryInfo->precio_unitario ?? 0;
                    $cantidad = 1;
                    $detalles = $deliveryInfo->direccion ?? '';
                } elseif ($nombre === 'Drop Off' && $dropoffActivo) {
                    $seleccionado = true;
                    $precio   = $dropoffInfo->precio_unitario ?? 0;
                    $cantidad = 1;
                    $detalles = $dropoffInfo->destino ?? '';
                } elseif ($nombre === 'Gasolina (faltante)' && $gasolinaActiva) {
                    $seleccionado = true;
                    $precio   = $gasolinaInfo->precio_unitario ?? 0;
                    $cantidad = $gasolinaInfo->cantidad ?? 1;
                    $detalles = ($gasolinaInfo->litros ?? 0) > 0 ? $gasolinaInfo->litros . ' L' : '';
                } elseif ($seleccionado) {
                    $precio   = $extrasSeleccionados[$nombre]['precio'];
                    $cantidad = $extrasSeleccionados[$nombre]['cantidad'];
                } else {
                    $precio   = 0;
                    $cantidad = 0;
                }

                if ($seleccionado && $precio > 0) {
                    $totalAdicionales += $esEspecial ? $precio : $precio * $cantidad * $dias;
                }

                $activo          = $seleccionado && $precio > 0;
                $estadoTexto     = $activo ? number_format($precio, 2) : '0.00';
                $cantidadMostrar = ($seleccionado && $cantidad > 0) ? $cantidad : 0;
                $diasMostrar     = $esEspecial ? '—' : $dias;
              @endphp
              <tr>
                <td>
                  <table class="tabla-ico">
                    <tr>
                      <td class="celda-ico">{!! $ico($config['icono'], 'blanco') !!}</td>
                      <td>
                        {{ $nombre }}@if ($activo && $cantidadMostrar > 1)<span class="marca"> x{{ $cantidadMostrar }}</span>@endif
                        @if ($activo && !empty($detalles))<span class="marca tenue"> {{ $detalles }}</span>@endif
                        @if (!$activo)<span class="marca tenue"> (No seleccionado)</span>@endif
                      </td>
                    </tr>
                  </table>
                </td>
                <td>{{ $diasMostrar }}</td>
                <td>$ {{ $estadoTexto }}</td>
              </tr>
            @endforeach

            @if ($totalAdicionales > 0)
              <tr class="fila-total-adicionales">
                <td colspan="2" style="text-align: right;">TOTAL ADICIONALES</td>
                <td>$ {{ number_format($totalAdicionales, 2) }}</td>
              </tr>
            @else
              <tr class="fila-total-adicionales">
                <td colspan="3" style="text-align: center;">Ningún adicional seleccionado</td>
              </tr>
            @endif
        </tbody>
      </table>
    </td>
  </tr>
</table>

{{-- ACEPTACIÓN + FIRMAS --}}
<div class="pad" style="margin-top: 4px;">
  <p class="aceptacion-texto">
    Acepto plenamente las obligaciones descritas en la carátula y en el clausulado de este contrato.
    Declaro bajo protesta de decir verdad, haber recibido el auto descrito en el apartado de salida
    y acepto las condiciones generales al inicio de la renta, así mismo entiendo y acepto las condiciones
    del tratamiento de mis datos personales como se describe en el aviso de privacidad que se encuentra
    a mi disposición en:
    <a href="https://www.viajeroacrental.mx/">https://www.viajeroacrental.mx/</a>
  </p>

  <table class="tabla-firmas">
    <tr>
      <td>
        <p class="firma-label">(firma de arrendatario)</p>
        @if ($firmaCliente)
          <img src="{{ $firmaCliente }}" class="firma-img" alt="">
        @endif
        <div class="firma-linea"></div>
        <p class="firma-nombre">{{ $nombreCliente ?: 'CLIENTE' }}</p>
      </td>
      <td>
        <p class="firma-label">(firma de arrendador)</p>
        @if ($firmaPropietario)
          <img src="{{ $firmaPropietario }}" class="firma-img" alt="">
        @endif
        <div class="firma-linea"></div>
        <p class="firma-nombre">VIAJERO CAR RENTAL</p>
      </td>
    </tr>
  </table>
</div>

{{-- GASOLINA --}}
<div class="pad" style="margin-top: 4px;">
  <p class="gasolina-texto">
    <strong>GASOLINA:</strong> PRECIO POR LITRO FALTANTE $13.16 MXN MAS CARGO POR SERVICIO DE 23.96
    MXN POR LITRO FALTANTE IMPUESTOS INCLUIDOS
    <span class="nota-gas">(APLICABLE SI LA OPCION DE PREPAGO DE GAS NO FUE ADQUIRIDA)</span>
  </p>
</div>

{{-- NOTAS --}}
<div class="pad" style="margin-top: 4px;">
  <p class="nota-title">INFORMACIÓN DE LOS CARGOS TOTALES:</p>
  <table style="width: 100%;">
    <tr>
      <td style="width: 50%; vertical-align: top; padding-right: 8px;">
        <p class="nota"><strong>(1)</strong> Al firmar este contrato el cliente declara tener conocimiento de todas las condiciones establecidas y acepta el clausulado al reverso.</p>
        <p class="nota"><strong>(2)</strong> Los cargos son ESTIMADOS, el importe total a pagar del contrato aparecerá al cierre del mismo.</p>
        <p class="nota"><strong>(3)</strong> Usted va alquilar y devolver el vehículo en el momento y lugares indicados. Gasolina no reembolsable en prepago. EXCEPTO si se regresa con tanque lleno.</p>
        <p class="nota"><strong>(4)</strong> NO SE ACEPTA EFECTIVO como pago ni como deposito.</p>
      </td>
      <td style="width: 50%; vertical-align: top; padding-left: 8px;">
        <p class="nota"><strong>(5)</strong> CDW 0% incluye: ROBO %, llantas, rines, cristales y espejos.</p>
        <p class="nota"><strong>(6)</strong> CDW20%, CDW10%, PCDW NO incluye llantas, rines, cristales y espejos.</p>
        <p class="nota"><strong>(7)</strong> Ninguna protección cubre GPS, Placas o llaves.</p>
        <p class="nota"><strong>(8)</strong> CDW20%, CDW10%, PDW. LDW revocado en caso de negligencia del conductor o si existen conductores NO autorizados en el contrato.</p>
      </td>
    </tr>
  </table>
</div>

{{-- FACTURACIÓN --}}
<div class="pad" style="margin-top: 4px;">
  <p class="fact-title">Datos de Facturación</p>
  <table class="tabla-fact">
    <tr>
      <td><span class="lbl">No. cliente fiscal:</span> {{ $reservacion->cliente_fiscal ?? '—' }}</td>
      <td><span class="lbl">RFC:</span> {{ $reservacion->rfc_cliente ?? '—' }}</td>
      <td><span class="lbl">Razón social:</span> {{ $reservacion->razon_social_cliente ?? '—' }}</td>
      <td><span class="lbl">Calle:</span> {{ $reservacion->direccion_cliente ?? '—' }}</td>
    </tr>
    <tr>
      <td><span class="lbl">No. Ext.</span> {{ $reservacion->num_ext_cliente ?? '—' }}</td>
      <td><span class="lbl">No. Int.</span> {{ $reservacion->num_int_cliente ?? '—' }}</td>
      <td><span class="lbl">C.P.:</span> {{ $reservacion->cp_cliente ?? '—' }}</td>
      <td><span class="lbl">Colonia:</span> {{ $reservacion->colonia_cliente ?? '—' }}</td>
    </tr>
    <tr>
      <td><span class="lbl">Estado:</span> {{ $reservacion->estado_cliente ?? '—' }}</td>
      <td><span class="lbl">Municipio:</span> {{ $reservacion->municipio_cliente ?? '—' }}</td>
      <td><span class="lbl">País:</span> {{ $reservacion->pais_cliente ?? '—' }}</td>
      <td><span class="lbl">Ciudad:</span> {{ $reservacion->ciudad_cliente ?? '—' }}</td>
    </tr>
  </table>
</div>

{{-- PIE ROJO --}}
<div class="pie-rojo">
  <p class="pie-empresa">VIAJERO CAR RENTAL</p>
  <table class="tabla-pie">
    <tr>
      <td class="izq">
        <span>Business Center INNERA Central Park. Armando Birlain Shaffler #2001, Torre 2, Centro Sur, Qro.</span>
        <span>Teléfono: 442 303 26 68 &nbsp; Celular: 442 716 97 93 &nbsp;|&nbsp; 442 343 07 70</span>
      </td>
      <td class="der">
        <span>Arrendador: José Juan de Dios Hernández Resendiz</span>
        <span>Facturación: facturacion@viajeroacr-rental.com</span>
        <span>Reservaciones: reservaciones@viajeroacr-rental.com</span>
      </td>
    </tr>
  </table>
</div>


{{-- HOJA 2 — CLÁUSULAS --}}
@php
  $arrendadorNombre   = $arrendadorNombre   ?? '—';
  $arrendatarioNombre = $arrendatarioNombre ?? ($nombreCliente ?: '—');
  $lugarFirma = $lugarFirma ?? '—';
  $diaFirma   = $diaFirma   ?? '—';
  $mesFirma   = $mesFirma   ?? '—';
  $anioFirma  = $anioFirma  ?? '—';

  $clausulas = [
    ['PRIMERA.', 'LA ARRENDADORA entrega en arrendamiento a la ARRENDATARIA cuyo nombre aparece en la carátula de este documento y dicha ARRENDATARIA recibe en tal carácter el vehículo objeto de este contrato en condiciones normales, mecánicas y de carrocería, consignadas en el inventario respectivo, con el carácter de BIEN ARRENDADO, a tener bajo su custodia y a su entera satisfacción, el vehículo de referencia y se obliga a pagar a la ARRENDADORA la renta señalada del contrato y a precisar de mercado, el o los faltantes de accesorios y partes del vehículo que recibe en el momento de entrega del mismo.'],
    ['SEGUNDA.', 'El término forzoso de este contrato de arrendamiento está señalado en la carátula de este contrato y nunca podrá ser prorrogado por ninguna de las partes, sin que aparezca constancia de voluntad de los mismos. en un nuevo contrato de arrendamiento.'],
    ['TERCERA.', 'LA ARRENDATARIA pagará como precio del arrendamiento el anticipo y precisamente el lugar donde deberán ser pagadas las cantidades estipuladas en el contrato de arrendamiento. Los pagos serán efectuados conforme a lo indicado en la carátula de este contrato. La renta deberá ser totalmente pagada aun cuando el vehículo se encuentre en uso de LA ARRENDATARIA, desde este momento, en plena posesión del automóvil y hasta la fecha en que lo reciba en devolución, a su entera satisfacción. LA ARRENDADORA.'],
    ['CUARTA.', 'LA ARRENDATARIA se obliga a entregar en devolución el vehículo arrendado precisamente en la hora y fecha convenidas y en la oficina de la ARRENDADORA en que se hubiera pactado la devolución, apareciendo esos datos en la carátula de este contrato de la misma que el vehículo se encontrará lavado y en condiciones normales, siendo el vehículo devuelto con el tanque lleno de gasolina. LA ARRENDATARIA deberá devolverlo en el lugar indicado en la carátula de este contrato. LA ARRENDATARIA deberá devolver el vehículo al lugar convenido en el contrato y en el plazo estipulado, más el importe que corresponda si la arrendataria del tiempo normal de traslado del lugar donde LA ARRENDATARIA haya dejado el vehículo a la oficina donde debió entregarlo de acuerdo con este contrato, aplicándose en todo caso la cuota diaria.'],
    ['QUINTA.', 'LA ARRENDATARIA tal como antes se señala se obliga a entregar el vehículo arrendado al término de este contrato, con el solo desgaste del uso normal y moderado, precisamente en la fecha y hora convenida y saldando en la carátula del contrato. con el pago del arrendamiento y en las condiciones señaladas en el contrato.'],
    ['SEXTA.', 'En caso de que LA ARRENDADORA niegue cualquier diligencia, previa autorización del pago de las prestaciones debidas por LA ARRENDATARIA o bien obtenga el vehículo devuelto legalmente aplicando las medidas de orden judicial o por acuerdo entre las partes, se autoriza LA ARRENDADORA para disponer del vehículo en la forma que estime más adecuada, ya sea. venderlo, arrendarlo o cualquier otra forma de disposición que convenga a los intereses de LA ARRENDADORA.'],
    ['SÉPTIMA.', 'LA ARRENDATARIA se obliga a mantener el vehículo en buenas condiciones, realizando los servicios de mantenimiento preventivo y correctivo necesarios para su buen funcionamiento y conservación, así como a cubrir los gastos derivados de su uso normal.'],
    ['OCTAVA.', 'El vehículo arrendado se destinará única y exclusivamente al transporte de LA ARRENDATARIA y sus acompañantes, y solo podrá ser manejado por LA ARRENDATARIA o por conductores autorizados que cuenten con licencia vigente. Queda prohibido usar el vehículo para fines distintos a los pactados.'],
    ['NOVENA.', 'El vehículo arrendado no podrá ser conducido fuera de los límites del territorio de la República Mexicana, sin el previo consentimiento expreso y por escrito de LA ARRENDADORA.'],
    ['DÉCIMA.', 'LA ARRENDATARIA se obliga a no permitir que el vehículo sea utilizado para actividades ilícitas o contrarias a la ley.'],
    ['DÉCIMA PRIMERA.', 'LA ARRENDATARIA será responsable de cualquier daño. desperfecto o pérdida total o parcial del vehículo durante la vigencia del presente contrato. aun cuando sea causado por terceros.'],
    ['DÉCIMA SEGUNDA.', 'En caso de accidente, robo o pérdida total del vehículo. LA ARRENDATARIA deberá dar aviso inmediato a LA ARRENDADORA y a las autoridades correspondientes. obligándose a cubrir los daños y perjuicios conforme a lo estipulado en este contrato.'],
    ['DÉCIMA TERCERA.', 'LA ARRENDATARIA no podrá subarrendar. prestar. ceder o permitir el uso del vehículo a terceros sin autorización previa y por escrito de LA ARRENDADORA.'],
    ['DÉCIMA CUARTA.', 'LA ARRENDATARIA se obliga a pagar todas las multas, infracciones, gastos de arrastre. corralón y cualquier otro cargo que se genere por el uso del vehículo durante la vigencia del contrato.'],
    ['DÉCIMA QUINTA.', 'LA ARRENDATARIA deberá cubrir el importe de los daños ocasionados al vehículo por negligencia. imprudencia o mal uso del mismo.'],
    ['DÉCIMA SEXTA.', 'LA ARRENDADORA no será responsable por los objetos personales dejados dentro del vehículo arrendado durante el tiempo que se encuentre en posesión de LA ARRENDATARIA.'],
    ['DÉCIMA SÉPTIMA.', 'LA ARRENDATARIA reconoce que ha recibido el vehículo en óptimas condiciones y se obliga a devolverlo en el mismo estado, salvo el desgaste normal por el uso.'],
    ['DÉCIMA OCTAVA.', 'En caso de incumplimiento de cualquiera de las obligaciones establecidas en el presente contrato. LA ARRENDADORA podrá darlo por rescindido sin necesidad de declaración judicial.'],
    ['DÉCIMA NOVENA.', 'Para la interpretación y cumplimiento del presente contrato. las partes se someten a la jurisdicción de los tribunales competentes del Estado de Querétaro. renunciando a cualquier otro fuero que pudiera corresponderles por razón de su domicilio presente o futuro.'],
    ['VIGÉSIMA.', 'Las partes manifiestan que conocen y aceptan todas y cada una de las cláusulas del presente contrato. firmándolo de conformidad.'],
  ];
@endphp

<table class="pagina-clausulas">
  <thead>
    <tr><td style="border:0;"><div class="esp-top"></div></td></tr>
  </thead>
  <tfoot>
    <tr><td style="border:0;"><div class="esp-bottom"></div></td></tr>
  </tfoot>
  <tbody>
    <tr>
      <td class="celda-clausulas">
        <div class="clausulas-intro">
          CONTRATO DE ARRENDAMIENTO, QUE CELEBRA POR UNA PARTE LA COMPAÑÍA CUYA RAZÓN SOCIAL APARECE EN EL APARTADO NO. 1 DEL ANVERSO DE ESTE CONTRATO COMO ARRENDADORA, Y POR LA OTRA, LA PERSONA CUYO NOMBRE APARECE EN EL APARTADO NO. 2 DEL ANVERSO DE ESTE CONTRATO, CON CARÁCTER DE ARRENDATARIA.
        </div>
        <div class="clausulas-title">Clausulas</div>
      </td>
    </tr>

    @foreach ($clausulas as [$etiqueta, $texto])
      <tr>
        <td class="celda-clausulas">
          <p class="clausula"><span class="clausula-tag">{{ $etiqueta }}</span> {{ $texto }}</p>
        </td>
      </tr>
    @endforeach

    <tr>
      <td class="celda-clausulas">
        <div class="clausulas-notas">
          <div class="bloque-nota">
            <span class="nota-titulo">GASOLINA:</span> PRECIO POR LITRO FALTANTE $13.16 MXN MAS CARGO POR SERVICIO DE 23.96 MXN POR LITRO FALTANTE IMPUESTOS INCLUIDOS (APLICABLE SI LA OPCION DE PREPAGO DE GAS NO FUE ADQUIRIDA)
          </div>
          <div class="bloque-nota">
            <span class="nota-titulo">INFORMACIÓN DE LOS CARGOS TOTALES:</span><br>
            (1) Al firmar este contrato el cliente declara tener conocimiento de todas las condiciones establecidas y acepta el clausulado al reverso.
            (2) Los cargos son ESTIMADOS, el importe total a pagar del contrato aparecera al cierre del mismo.
            (3) Usted va a alquilar y devolver el vehiculo en el momento y lugares indicados. Gasolina no reembolsable en prepago, EXCEPTO si se regresa con tanque lleno.
            <br><br>
            <span class="nota-titulo">1.</span> NO SE ACEPTA EFECTIVO como pago ni como deposito.
            <span class="nota-titulo">2.</span> CDW 0% incluye: ROBO %, llantas, rines, cristales y espejos.
            <span class="nota-titulo">3.</span> CDW20%, CDW10%, PCDW NO incluye llantas, rines, cristales y espejos.
            <span class="nota-titulo">4.</span> Ninguna protección cubre GPS. Placas o llaves.
            <span class="nota-titulo">5.</span> CDW20%, CDW10%, PDW, LDW revocado en caso de negligencia del conductor o si existen conductores NO autorizados en el contrato.
          </div>
        </div>
      </td>
    </tr>

    <tr>
      <td class="celda-clausulas">
        <table class="tabla-fecha">
          <tr>
            <td>En</td>
            <td class="linea">{{ $lugarFirma }}</td>
            <td>al día</td>
            <td class="linea" style="width: 16mm;">{{ $diaFirma }}</td>
            <td>del mes de</td>
            <td class="linea" style="width: 28mm;">{{ $mesFirma }}</td>
            <td>del año</td>
            <td class="linea" style="width: 20mm;">{{ $anioFirma }}</td>
          </tr>
        </table>
      </td>
    </tr>

    <tr>
      <td class="celda-clausulas">
        <table class="tabla-firmas-clausulas">
          <tr>
            <td>
              @if ($firmaArrendador)
                <img class="firma-img-clausulas" src="{{ $firmaArrendador }}" alt="">
              @endif
              <div class="firma-line">
                <p class="firma-nombre-clausulas">{{ $arrendadorNombre }}</p>
              </div>
            </td>
            <td>
              @if ($firmaCliente)
                <img class="firma-img-clausulas" src="{{ $firmaCliente }}" alt="">
              @endif
              <div class="firma-line">
                <p class="firma-nombre-clausulas">{{ $arrendatarioNombre }}</p>
              </div>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </tbody>
</table>

</body>
</html>
