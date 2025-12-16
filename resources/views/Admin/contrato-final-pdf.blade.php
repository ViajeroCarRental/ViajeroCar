<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">

<style>
/* =========================================
      AJUSTE GLOBAL *TAL CUAL A LA HOJA*
========================================= */
html, body {
    margin: 0;
    padding: 0;

    /* 🔥 Coincidir EXACTAMENTE con el tamaño del PDF */
    width: 1000px !important;
    height: 1500px !important;

    background: #f5f7fb;
    font-family: 'Poppins', Arial, sans-serif;
    color: #333;
}

/* Contenedor general para controlar el layout */
.pdf-wrap {
    width: 100%;
    height: 100%;
    padding: 25px 35px;   /* Ajusta según necesites */
    box-sizing: border-box;
}

/* =========================================
      ENCABEZADO ROJO FULL WIDTH
========================================= */
.encabezado {
    background: #ff1e2d;
    width: 100%;
    padding: 28px 40px;
    border-radius: 15px;
    color: #fff;

    display: flex;
    align-items: center;
}

.logo-contrato {
    width: 130px;
    margin-right: 18px;
}

.encabezado h2 {
    margin: 0;
    font-size: 28px;
    font-weight: 700;
}

.encabezado p {
    margin: 0;
    font-size: 16px;
    opacity: .95;
}

/* =========================================
      SECCIONES
========================================= */
.section {
    width: 100%;
    background: #fff;
    border-radius: 14px;
    margin-top: 18px;
    padding: 18px 22px;
    border-left: 6px solid #E50914;
    box-shadow: 0 3px 10px rgba(0,0,0,.06);
}

.section h3 {
    margin: 0 0 8px 0;
    font-size: 20px;
    color: #E50914;
}

/* =========================================
      TABLAS
========================================= */
.table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 14px;
    table-layout: fixed; /* Evita que se expanda */
}

.table th, .table td {
    border: 1px solid #ddd;
    padding: 10px;
    font-size: 14px;
    word-break: break-word;
}

.table th {
    background: #fce7e7;
    color: #b1060f;
    font-weight: 600;
}

/* =========================================
      FIRMAS
========================================= */
.firma-img {
    width: 200px;
    height: 90px;
    object-fit: contain;
    border: 1px solid #ccc;
    margin-top: 10px;
}

/* =========================================
      TOTALES
========================================= */
.total-final {
    font-size: 22px;
    font-weight: 800;
    color: #b1060f;
    text-align: right;
    margin-top: 15px;
}

/* Contenedor que centra todo el PDF */
.pdf-center {
    width: 1000px;     /* igual al tamaño del PDF */
    height: 1500px;    /* igual al tamaño del PDF */
    margin: 0 auto;    /* 🔥 CENTRADO HORIZONTAL PERFECTO */
    padding: 0;
    background: #f5f7fb;
}

/* Contenedor interno */
.pdf-wrap {
    width: 90%;               /* ajusta cuánto espacio quieres usar del ancho */
    margin: 0 auto;           /* 🔥 centra el contenido dentro del PDF */
    padding: 20px 0;
}

/* =========================
   ✅ SALTO A NUEVA HOJA (DomPDF)
========================= */
.page-break{
  page-break-after: always;
}

/* =========================
   ✅ HOJA 2 · CLÁUSULAS
========================= */
.hoja2 {
  width: 1000px;
  height: 1500px;
  margin: 0 auto;
  background: #ffffff;
  position: relative;
  box-sizing: border-box;
}

/* Marco redondeado (como el PDF) */
.hoja2-marco{
  position: absolute;
  inset: 22px;                 /* margen exterior */
  border: 2px solid #2b2b2b;
  border-radius: 70px;
  padding: 42px 55px 170px 55px; /* deja espacio abajo para firmas */
  box-sizing: border-box;
}

/* Encabezado largo (todo en mayúsculas) */
.hoja2-encabezado{
  font-family: Arial, sans-serif;
  font-size: 18px;
  line-height: 1.25;
  text-transform: uppercase;
  text-align: left;
  margin: 0 0 28px 0;
}

/* Título CLAUSULAS */
.hoja2-titulo{
  font-family: Arial, sans-serif;
  font-size: 34px;
  font-weight: 400;
  text-align: center;
  margin: 0 0 26px 0;
  letter-spacing: 1px;
}

/* Dos columnas */
.hoja2-cols{
  display: flex;
  gap: 32px;
}

/* Columnas con texto tipo contrato */
.hoja2-col{
  width: 50%;
  font-family: Arial, sans-serif;
  font-size: 12.5px;
  line-height: 1.35;
  text-align: justify;
}

/* Bloques de cláusula */
.clausula{
  margin: 0 0 14px 0;
}
.clausula b{
  font-weight: 700;
}

/* Lista numerada dentro de “DÉCIMA” */
.obligaciones{
  margin: 8px 0 0 18px;
  padding: 0;
}
.obligaciones li{
  margin: 0 0 6px 0;
}

/* =========================
   ✅ PIE (En ___ a los ___ ...)
========================= */
.hoja2-pie{
  position: absolute;
  left: 55px;
  right: 55px;
  bottom: 55px;
  font-family: Arial, sans-serif;
  font-size: 18px;
}

.pie-linea{
  display: inline-block;
  border-bottom: 2px solid #2b2b2b;
  height: 18px;
  vertical-align: bottom;
}

.w1{ width: 540px; }  /* En ________ */
.w2{ width: 120px; }  /* a los __ */
.w3{ width: 330px; }  /* día del mes de ______ */
.w4{ width: 180px; }  /* de ___ */

.firmas{
  display: flex;
  justify-content: space-between;
  margin-top: 45px;
}

.firma-box{
  width: 45%;
  text-align: center;
  font-size: 13px;
}

.firma-linea{
  border-bottom: 2px solid #2b2b2b;
  height: 22px;
  margin: 0 auto 6px auto;
}


</style>
</head>

<body>
<div class="pdf-center">
    <div class="pdf-wrap">
<!-- ============================
      ENCABEZADO ROJO
============================ -->
<div class="encabezado">
    <div class="logo-titulo">
        <img src="{{ public_path('img/Logo3.jpg') }}" class="logo-contrato" alt="Viajero Car Rental">
        <div class="titulo-texto">
            <h2>VIAJERO CAR RENTAL</h2>
            <p>CONTRATO DE ARRENDAMIENTO / RENTAL AGREEMENT</p>
        </div>
    </div>
</div>

<h2 style="text-align:center; margin-top:-10px; color:#b1060f;">
    Contrato Final de Arrendamiento
</h2>

<!-- ============================
      DATOS CLIENTE
============================ -->
<div class="section">
    <h3>Datos del Arrendatario</h3>
    <ul>
        <li><b>Nombre:</b> {{ $reservacion->nombre_cliente }}</li>
        <li><b>Correo:</b> {{ $reservacion->email_cliente }}</li>
        <li><b>Teléfono:</b> {{ $reservacion->telefono_cliente }}</li>
        <li><b>País:</b> {{ $licencia->pais_emision ?? '—' }}</li>
        <li><b>Vuelo:</b> {{ $reservacion->no_vuelo ?? '—' }}</li>
    </ul>
</div>

<!-- ============================
      LICENCIA
============================ -->
<div class="section">
    <h3>Licencia del cliente</h3>
    <ul>
        <li><b>No. Licencia:</b> {{ $licencia->numero_identificacion ?? '—' }}</li>
        <li><b>Vence:</b> {{ $licencia->fecha_vencimiento ?? '—' }}</li>
        <li><b>Emitida en:</b> {{ $licencia->pais_emision ?? '—' }}</li>
    </ul>
</div>

<!-- ============================
      ITINERARIO
============================ -->
<div class="section">
    <h3>Itinerario</h3>
    <ul>
        <li><b>Oficina de salida:</b> {{ $reservacion->sucursal_retiro_nombre }}</li>
        <li><b>Fecha/Hora salida:</b> {{ $reservacion->fecha_inicio }} {{ $reservacion->hora_retiro }}</li>

        <li><b>Oficina de regreso:</b> {{ $reservacion->sucursal_entrega_nombre }}</li>
        <li><b>Fecha/Hora regreso:</b> {{ $reservacion->fecha_fin }} {{ $reservacion->hora_entrega }}</li>

        <li><b>Días de renta:</b> {{ $dias }}</li>
    </ul>
</div>

<!-- ============================
      VEHÍCULO
============================ -->
<div class="section">
    <h3>Vehículo Asignado</h3>
    <ul>
        <li><b>Modelo:</b> {{ $vehiculo->modelo }}</li>
        <li><b>Categoría:</b> {{ $vehiculo->categoria }}</li>
        <li><b>Color:</b> {{ $vehiculo->color }}</li>
        <li><b>Transmisión:</b> {{ $vehiculo->transmision }}</li>
        <li><b>Kilometraje:</b> {{ number_format($vehiculo->kilometraje) }}</li>
        <li><b>Gasolina inicial:</b> {{ $vehiculo->gasolina_actual }}/16</li>
    </ul>
</div>

<!-- ============================
      TARIFAS Y PROTECCIONES
============================ -->
<div class="section">
    <h3>Tarifas y Protecciones</h3>

<table class="table">
<thead>
<tr>
<th>Concepto</th>
<th>Días</th>
<th>Precio/Día</th>
<th>MXN</th>
</tr>
</thead>
<tbody>

<tr>
<td>Tarifa Base</td>
<td>{{ $dias }}</td>
<td>${{ number_format($tarifaBase,2) }}</td>
<td>${{ number_format($tarifaBase * $dias,2) }}</td>
</tr>

@foreach($paquetes as $p)
<tr>
<td>{{ $p->nombre }}</td>
<td>{{ $dias }}</td>
<td>${{ number_format($p->precio_por_dia,2) }}</td>
<td>${{ number_format($p->precio_por_dia * $dias,2) }}</td>
</tr>
@endforeach

@foreach($individuales as $i)
<tr>
<td>{{ $i->nombre }}</td>
<td>{{ $dias }}</td>
<td>${{ number_format($i->precio_por_dia,2) }}</td>
<td>${{ number_format($i->precio_por_dia * $dias,2) }}</td>
</tr>
@endforeach

@foreach($extras as $e)
<tr>
<td>{{ $e->nombre }}</td>
<td>{{ $dias }}</td>
<td>${{ number_format($e->precio_unitario,2) }}</td>
<td>${{ number_format($e->precio_unitario * $dias,2) }}</td>
</tr>
@endforeach

</tbody>
</table>

<div class="totales">
    <p><b>Subtotal:</b> ${{ number_format($subtotal,2) }}</p>
    <p><b>IVA 16%:</b> ${{ number_format($subtotal * 0.16,2) }}</p>
    <p class="total-final">TOTAL: ${{ number_format($totalFinal,2) }} MXN</p>
</div>

</div>

<!-- ============================
      FIRMAS
============================ -->
<div class="section">
    <h3>Firmas</h3>

    @if($contrato->firma_cliente)
        <p><b>Firma del Cliente:</b></p>
        <img src="{{ $contrato->firma_cliente }}" class="firma-img">
    @endif

    @if($contrato->firma_arrendador)
        <p><b>Firma del Arrendador:</b></p>
        <img src="{{ $contrato->firma_arrendador }}" class="firma-img">
    @endif
</div>
    </div>
</div>


<div class="page-break"></div>

<!-- =========================
     ✅ HOJA 2 · CLÁUSULAS
========================= -->
<div class="hoja2">
  <div class="hoja2-marco">

    <p class="hoja2-encabezado">
      CONTRATO DE ARRENDAMIENTO, QUE CELEBRA POR UNA PARTE LA COMPAÑÍA CUYA RAZÓN SOCIAL APARECE EN EL APARTADO No. 1
      DEL ANVERSO DE ESTE CONTRATO COMO ARRENDADORA, Y POR LA OTRA, LA PERSONA CUYO NOMBRE APARECE EN EL APARTADO No.2
      DEL ANVERSO DE ESTE CONTRATO, CON CARÁCTER DE ARRENDATARIA.
    </p>

    <h2 class="hoja2-titulo">CLAUSULAS</h2>

    <div class="hoja2-cols">
      <!-- ===== COLUMNA IZQUIERDA ===== -->
      <div class="hoja2-col">

        <p class="clausula">
  <b>PRIMERA:</b>
  LA ARRENDADORA entrega en arrendamiento a la ARRENDATARIA cuyo nombre aparece en la carátula de este documento, y dicha ARRENDATARIA recibe con tal carácter el vehículo objeto de este contrato en condiciones normales, mecánicas y de carrocería, consignadas en el inventario respectivo, con el contador de kilometraje sellado y recibe, salvo defectos ocultos, a su entera satisfacción, el vehículo de referencia y se obliga a pagar a la ARRENDADORA a la terminación del contrato y a precios de mercado, el o los faltantes de accesorios y partes del vehículo que recibe en el momento de la entrega del mismo.
</p>

<p class="clausula">
  <b>SEGUNDA:</b>
  El término forzoso de este contrato de arrendamiento está señalado en la carátula de este contrato y nunca se considerará prorrogado por ninguna de las partes, sin que aparezca constante la voluntad de los mismos, en un nuevo contrato de arrendamiento.
</p>

<p class="clausula">
  <b>TERCERA:</b>
  LA ARRENDATARIA pagará como precio del arrendamiento al contado y precisamente en el lugar donde es celebrado este contrato, las cantidades estipuladas en el anverso de este documento, independientemente de las demás prestaciones, sanciones e intereses previstos en este contrato. La renta correrá desde el momento en que se firme este documento, en virtud de estar LA ARRENDATARIA, desde este momento, en plena posesión del automóvil y hasta la fecha en que lo reciba en devolución, a su entera satisfacción, LA ARRENDADORA.
</p>

<p class="clausula">
  <b>CUARTA:</b>
  LA ARRENDATARIA se obliga a entregar en devolución el vehículo arrendado precisamente en la hora y fecha convenidas y en la oficina de la ARRENDADORA en que se hubiera pactado la devolución, apareciendo esos datos en la carátula de este contrato, de la manera que el vehículo no sufra mayor deterioro en su utilización en condiciones normales. Si el vehículo no fuera devuelto en el lugar señalado en este contrato, LA ARRENDATARIA deberá obtener, previamente a esa devolución, la autorización de la ARRENDADORA y, en todo caso, pagará el importe de la renta del vehículo y el lugar convenido para la devolución, más el importe que corresponda al arrendamiento del tiempo normal de traslado del lugar en donde la ARRENDATARIA haya dejado el vehículo a la oficina donde debió entregarlo de acuerdo con este contrato, aplicándose en todo caso la cuota preestablecida en la carátula.
</p>

        <p class="clausula">
  <b>QUINTA:</b>
  LA ARRENDATARIA tal como antes se señala se obliga a entregar el vehículo arrendado al término de este contrato, con el solo desgaste del uso normal y moderado, precisamente en la fecha y hora convenida y señalada en la carátula de este documento y en el lugar estipulado, tal como ha quedado establecido.
</p>

<p class="clausula">
  <b>SEXTA:</b>
  En caso de que la ARRENDADORA tuviera que ejercer algún derecho, judicialmente, para obtener el pago de las prestaciones debidas por la ARRENDATARIA o bien obtener la devolución del vehículo cuando legalmente así proceda, la propia ARRENDADORA podrá optar por seguir el procedimiento señalado en los artículos 442 fracción IV, 449, 451 y 452 del Código de Procedimientos Civiles para el Distrito Federal y concordantes en los diferentes estados, a efecto de obtener dentro de la vía ejecutiva el pago de las prestaciones a que se hace referencia anteriormente, y/o la devolución del vehículo o por el procedimiento que corresponda en contra de la ARRENDATARIA en la vía penal, en caso de retención o disposición indebida del vehículo arrendado.
</p>

<p class="clausula">
  <b>SÉPTIMA:</b>
  LA ARRENDATARIA entregará en las oficinas de la ARRENDADORA un depósito por la suma indicada en la carátula de este documento, en garantía del cumplimiento fiel y puntual de todos y cada una de las obligaciones. La ARRENDADORA expedirá un recibo por concepto de dicho depósito, se aplique como pago del saldo si hubiere. La ARRENDATARIA faculta de manera expresa a la ARRENDADORA para que disponga total y parcialmente del depósito antes mencionado para cobrarse las prestaciones estipuladas, la reposición de faltantes y la reparación de defectos previa comprobación en la inteligencia de que si el depósito resultase insuficiente para cubrir las sumas adeudadas a la ARRENDADORA, ésta podrá reclamar el pago judicialmente.
</p>

<p class="clausula">
  <b>OCTAVA:</b>
  El vehículo arrendado se destinará única y exclusivamente al transporte de la ARRENDATARIA y sus acompañantes, y dicho vehículo solo podrá ser manejado por la ARRENDATARIA o por el conductor o conductores que ella señale y autorice la ARRENDADORA, que deberán señalar en este mismo contrato, obligándose aquellas a impedir que otra persona haga uso del vehículo arrendado.
</p>

<p class="clausula">
  <b>NOVENA:</b>
  El vehículo arrendado no podrá ser conducido fuera de los límites del territorio de la República Mexicana, sin el previo consentimiento que por escrito en su caso, deberá otorgar la ARRENDADORA estando facultada la misma, en caso de violación de esta estipulación, a obtener la posesión del vehículo de inmediato, en las condiciones y estados en que se localice, dando por terminado el contrato.
</p>


        <p class="clausula"><b>DÉCIMA:</b> Son obligaciones de la ARRENDATARIA y en su caso de los conductores autorizados por la ARRENDADORA.</p>
        <ol class="obligaciones">
          <li>1-Conducir en todo caso, el vehículo arrendado al amparo de la licencia respectiva, otorgada por las vías legales y por las autorizadas competentes.</li>
          <li>2-No manejar el vehículo en estado de ebriedad o bajo la influencia de drogas.</li>
          <li>3-No hacer uso del vehículo en forma lucrativa.</li>
          <li>4-No subarrendar el vehículo.</li>
          <li>5-Obedecer los reglamentos de Tránsito Federal, Estatal o Local.</li>
          <li>6-No utilizar el vehículo para arrastrar remolques, a menos que se tenga autorización expresa y por escrito de la ARRENDADORA.</li>
          <li>7-No sobrecargar el vehículo con relación a su resistencia o capacidad normal.</li>
          <li>8-Revisar en forma periódica razonable, los niveles de aceite en el motor, del agua del radiador y revisar la presión del aire de las llantas del vehículo.</li>
          <li>9-Mantener cerrado con llave el vehículo, siempre y cuando se permanezca fuera de él, salvaguardando el vehículo en lugar cerrado y vigilado cuando se deje estacionado.</li>
          <li>10-No participar con el vehículo, directamente o indirectamente en carreras o pruebas de seguridad, resistencia o velocidad.</li>
          <li>11-No conducir el vehículo en brechas o caminos no pavimentados, respondiendo, en su caso por los daños causados al mismo.</li>
          <li>12-No conducir en el interior del vehículo materias explosivas o inflamables, drogas o estupefacientes aún en el caso de que dicho transporte fuera hecho dentro de las normas legales.</li>
          <li>13-Responder por daños o personas o cosas, causados con el vehículo en el tiempo que el mismo se encuentre en posesión física o jurídica de la ARRENDATARIA.</li>
        </ol>

      </div>

      <!-- ===== COLUMNA DERECHA ===== -->
      <div class="hoja2-col">

        <p class="clausula"><b>14.-</b> Responder por daños sufridos por las personas que viajen dentro del vehículo arrendado durante el tiempo que se encuentre en posesión física o jurídica de la ARRENDATARIA.</p>
        <p class="clausula"><b>15.-</b> Responder por daños que sufra el vehículo mientras el mismo se encuentre en posesión física o jurídica de la ARRENDATARIA.</p>
        <p class="clausula"><b>16.-</b> Responder por el pago de las sanciones impuestas por violación a los Reglamentos de Tránsito o cualquier otra Reglamentación, pudiéndose efectuar, por parte de la ARRENDADORA, el cobro de esos cargos en el mismo momento en que se cubra el importe de la renta correspondiente y las demás prestaciones oportunamente, o posteriormente.</p>
        <p class="clausula"><b>17.-</b> Responder de todos los actos hechos ilícitos efectuados con el vehículo o dentro del mismo.</p>
        <p class="clausula"><b>18.-</b> No realizar reparación alguna al vehículo salvo autorización previa de la ARRENDADORA.</p>
        <p class="clausula"><b>19.-</b> En general no utilizar el vehículo en forma diferente o fines distintos de los estipulados en el presente contrato y responsabilizándose de la posesión del mismo.</p>


        <p class="clausula"><b>DÉCIMA PRIMERA:</b> Si durante la vigencia del presente contrato, el vehículo objeto del mismo sufre algún daño o siniestro la ARRENDATARIA deberá dar aviso de inmediato del hecho tanto a la ARRENDADORA como a las autoridades competentes que deban conocerlos, quedando estipulado que el retardo en el aviso de referencia imputable a la arrendataria, será responsabilidad a su cargo, por lo que se refiere sobre todo, a la falta de recuperación parcial o total o que tuviere derecho a la ARRENDADORA y con relación al importe correspondiente a los mencionados daños y perjuicios, siniestros o renta correspondiente al tiempo en que el vehículo no esté a disposición de la ARRENDADORA.</p>
        <p class="clausula"><b>DÉCIMA SEGUNDA:</b> La responsabilidad de la ARRENDATARIA por causas imputables a la misma, independientemente de lo estipulado en la cláusula anterior, en caso de robo total queda fijada en la cantidad que se marque con respecto al vehículo arrendado valor de venta en la publicación denominada “Guía EBC” (Cuyos datos son tomados como base por las Compañías Aseguradoras), a fecha del siniestro, que será el equivalente al valor del vehículo y en caso de vuelcos o colisiones, la cantidad que arroje el avalúo verificado por la Agencia autorizada de la Marca respectiva.</p>
        <p class="clausula"><b>DÉCIMA TERCERA:</b> En caso de que exista impuntualidad de la ARRENDATARIA por daños a personas o cosas, daños del propio vehículo arrendado y/o daños sufridos por personas que viajen dentro del mismo, así como robo total del vehículo, la ARRENDATARIA podrá evitar esa responsabilidad, cubriendo una cuota de protección, cuyo importe está señalado en la carátula de este contrato, debiendo en todo caso se realicen las circunstancias antes señaladas, cubrir la cantidad correspondiente al 20% del valor del vehículo (señalado mediante el sistema aludido en la cláusula anterior). Ahora bien, si el importe de dicho siniestro, sin que en este caso sea su obligación la cobertura del importe del multicitado 20%.</p>
        <p class="clausula"><b>DÉCIMA CUARTA:</b> En caso de robo, la ARRENDATARIA se hace responsable de dar aviso a la ARRENDADORA en un máximo de ocho horas después de haber conocido el citado robo, teniendo la obligación de dar aviso a las autoridades cumpliendo las formalidades del caso, como levantamiento de actas, fe de hechos, etcétera, responsabilizándose en todo caso, por el abandono del vehículo o por las consecuencias derivadas de la falta de aviso oportuno. En caso de que el siniestro o robo a lo que aquí se hace mención se deriven de negligencia, descuido, mala fe o incumplimiento de las obligaciones señaladas a cargo de la ARRENDATARIA en este contrato esta deberá cubrir a la ARRENDADORA como pena convencional, el importe de treinta días de arrendamiento.</p>
        <p class="clausula"><b>DÉCIMA QUINTA:</b> La arrendadora no es responsable de objetos personales olvidados por la ARRENDATARIA dentro del vehículo ni en sus oficina, ni del daño o del demérito que pudieren sufrir al ser transportados dentro del mismo vehículo.</p>
        <p class="clausula"><b>DÉCIMA SEXTA:</b> Como el número de kilómetros sobre los cuales deberá computarse el pago de la renta del automóvil, se determinará por la lectura del dispositivo registrado de kilometraje (odómetro) instalado de fábrica en el vehículo, queda estipulado que si durante el término del arrendamiento, sobreviniere algún desperfecto o la rotura de los protectores de registrado de kilometraje por culpa de la ARRENDATARIA, las parte convienen en que la parte correspondiente a la renta por kilometraje se computará a base de calcular un uso promedio de 500 kilómetros por día, durante el término en que el vehículo esté a disposición de la ARRENDATARIA.</p>
        <p class="clausula"><b>DÉCIMA SÉPTIMA:</b> De ocurrir algún desperfecto mecánico o eléctrico al vehículo o la pérdida de llaves del mismo, la ARRENDADORA, deberá comunicar ese hecho dentro de las dos horas siguientes a la ARRENDADORA, subsistiendo en todo caso las responsabilidades a cargo de la ARRENDATARIA en caso de que el desperfecto haya sido originado por un acto imputable a la ARRENDATARIA, tal como golpe, sobre carga, uso anormal de vehículo, etc. En este caso el ARRENDADOR está obligado a sustituirle al ARRENDATARIO haya hecho saber la descompostura y el bonificar en el cobro por la renta, el tiempo que el arrendatario no haya podido utilizar el vehículo por la falla no imputable a él, siempre y cuando el vehículo se encuentre en la misma localidad donde se ubica el domicilio del ARRENDADOR. Para el caso del extravío de las llaves el ARRENDADOR la hará llegar al ARRENDATARIO un duplicado de las mismas dentro de las horas siguientes al momento de ser informado de su extravío o de que se cerró el vehículo con las llaves dentro, siempre que el vehículo se encuentre también en la misma localidad donde se ubica el domicilio del ARRENDADOR, aplicándose un cargo por tal concepto el cual se señala en el anverso del presente contrato.</p>
        <p class="clausula"><b>DÉCIMA OCTAVA:</b> Se establece como pena convencional por el incumplimiento de cualquiera de las obligaciones a cargo del ARRENDADOR, el 2% del precio total de arrendamiento.</p>
        <p class="clausula"><b>DÉCIMA NOVENA:</b> Para cualquier queja, reclamación o inconformidad, la ARRENDATARIA podrá comunicar a los teléfonos que aparecen en el anverso del presente contrato o bien presentarse personalmente en el domicilio de la ARRENDADORA en días y horas hábiles.</p>
        <p class="clausula"><b>VIGÉSIMA:</b> Para los efectos de este contrato se señala como domicilio de la ARRENDADORA: Calle Bugambilias #7 Colonia Los Benitos, Municipio Colón, Estado de Querétaro C.P. 76299., y como domicilio de la ARRENDATARIA el señalado en el Apartado No. 2 del Anverso de este documento.</p>
        <p class="clausula"><b>VIGÉSIMA PRIMERA:</b> Para todo lo relativo a la interpretación y cumplimiento de este contrato, las partes se someten a la competencia de la Procuraduría Federal de Protección al Consumidor y de subsistir la controversia ante los tribunales del fuero común, competentes del Edo. de Querétaro con renuncia expresa de cuales quiera otra por domicilio o causa diversa, presente o futura, les pudiere corresponder.</p>


      </div>
    </div>

    <!-- ===== PIE ===== -->
    <div class="hoja2-pie">
      <div>
        En <span class="pie-linea w1"></span>
        a los <span class="pie-linea w2"></span>
        día del mes de <span class="pie-linea w3"></span>
        de <span class="pie-linea w4"></span>
      </div>

      <div class="firmas">
        <div class="firma-box">
          <div class="firma-linea"></div>
          LA ARRENDADORA
        </div>
        <div class="firma-box">
          <div class="firma-linea"></div>
          LA ARRENDATARIA (NOMBRE Y FIRMA)
        </div>
      </div>
    </div>

  </div>
</div>

</body>
</html>
