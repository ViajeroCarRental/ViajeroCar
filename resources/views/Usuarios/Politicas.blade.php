@extends('layouts.Usuarios')

@section('Titulo','Politicas de Privacidad y Términos de Servicio')

@section('css-vistaPoliticas')
    <link rel="stylesheet" href="{{ asset('css/politicas.css') }}">
@endsection

@section('contenidoHome')
<main class="page">
    <section class="hero hero-mini">
      <div class="hero-bg">
        <img src="{{ asset('img/politicas.png') }}" alt="Politicas">
      </div>
      <div class="hero-overlay"></div>
      <div class="hero-content">
        <h1>Políticas <span>Viajero</span></h1>
        <p>Aviso de privacidad, limpieza, renta y términos</p>
      </div>
    </section>

    {{-- ✅ GRID DE CARDS (REEMPLAZA ACORDEÓN) --}}
    <section class="policies-wrap">
      <div class="policies-grid">

        {{-- Requisitos de renta --}}
        <button type="button" class="policy-card" data-modal="tpl-requisitos" data-title="Requisitos de renta">
          <span class="pc-icon"><i class="fa-solid fa-clipboard-check"></i></span>
          <span class="pc-title">Requisitos de renta</span>
        </button>

        {{-- Formas de pago --}}
        <button type="button" class="policy-card" data-modal="tpl-pago" data-title="Formas de pago">
          <span class="pc-icon"><i class="fa-solid fa-wallet"></i></span>
          <span class="pc-title">Formas de pago</span>
        </button>

        {{-- Depósitos --}}
        <button type="button" class="policy-card" data-modal="tpl-depositos" data-title="Depósitos">
          <span class="pc-icon"><i class="fa-solid fa-mobile-screen-button"></i></span>
          <span class="pc-title">Depósitos</span>
        </button>

        {{-- ✅ NUEVA: Pre Check-In --}}
        <button type="button" class="policy-card" data-modal="tpl-precheckin" data-title="Pre Check-In">
          <span class="pc-icon"><i class="fa-solid fa-circle-check"></i></span>
          <span class="pc-title">Pre Check-In</span>
        </button>

        {{-- Cancelaciones y reembolsos --}}
        <button type="button" class="policy-card" data-modal="tpl-cancelaciones" data-title="Cancelaciones y reembolsos">
          <span class="pc-icon"><i class="fa-solid fa-rotate-left"></i></span>
          <span class="pc-title">Cancelaciones y reembolsos</span>
        </button>

        {{-- Seguro de cancelación --}}
        <button type="button" class="policy-card" data-modal="tpl-seguro-cancelacion" data-title="Seguro de cancelación">
          <span class="pc-icon"><i class="fa-solid fa-shield-heart"></i></span>
          <span class="pc-title">Seguro de cancelación</span>
        </button>

        {{-- Política de suciedad --}}
        <button type="button" class="policy-card" data-modal="tpl-limpieza" data-title="Política de suciedad">
          <span class="pc-icon"><i class="fa-solid fa-spray-can-sparkles"></i></span>
          <span class="pc-title">Política de suciedad</span>
        </button>

        {{-- Políticas de infracciones --}}
        <button type="button" class="policy-card" data-modal="tpl-infracciones" data-title="Políticas de infracciones">
          <span class="pc-icon"><i class="fa-solid fa-file-lines"></i></span>
          <span class="pc-title">Políticas de infracciones</span>
        </button>

        {{-- (Opcional) Aviso de privacidad --}}
        <button type="button" class="policy-card" data-modal="tpl-privacidad" data-title="Aviso de privacidad">
          <span class="pc-icon"><i class="fa-solid fa-shield-halved"></i></span>
          <span class="pc-title">Aviso de privacidad</span>
        </button>

        {{-- (Opcional) Términos y condiciones --}}
        <button type="button" class="policy-card" data-modal="tpl-terminos" data-title="Términos y condiciones">
          <span class="pc-icon"><i class="fa-solid fa-scale-balanced"></i></span>
          <span class="pc-title">Términos y condiciones</span>
        </button>

        {{-- (Opcional) Definiciones de cargos e impuestos --}}
        <button type="button" class="policy-card" data-modal="tpl-cargos" data-title="Definiciones de cargos e impuestos">
          <span class="pc-icon"><i class="fa-solid fa-receipt"></i></span>
          <span class="pc-title">Definiciones de cargos e impuestos</span>
        </button>

      </div>
    </section>

    {{-- ✅ MODAL REUTILIZABLE --}}
    <div class="vj-modal" id="policyModal" aria-hidden="true">
      <div class="vj-modal__backdrop" data-close="1"></div>

      <div class="vj-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="policyModalTitle">
        <div class="vj-modal__header">
          <h3 class="vj-modal__title" id="policyModalTitle">Título</h3>
          <button type="button" class="vj-modal__close" aria-label="Cerrar" data-close="1">
            <i class="fa-solid fa-xmark"></i>
          </button>
        </div>

        <div class="vj-modal__body" id="policyModalBody">
          {{-- aquí se inyecta contenido --}}
        </div>
      </div>
    </div>

    {{-- ✅ TEMPLATES DE CONTENIDO (NO VISIBLES) --}}

    {{-- AVISO PRIVACIDAD --}}
    <template id="tpl-privacidad">
      <div class="policy-content">
        <p><strong>ELABORADO POR VIAJERO CAR RENTAL, PARA LA PROTECCIÓN DE DATOS DE SUS CLIENTES</strong></p>
        <p>Persona física con Actividad Empresarial, con domicilio fiscal en Blvd. Bernardo Quintana 8300, Centro Sur, 76090 Santiago de Querétaro, Qro., tratará sus datos personales recabados para fines de identificación, operación, administración y comercialización relacionada con el alquiler de vehículos automotores. Si Usted no manifiesta su oposición para que sus datos personales sean tratados, se entenderá que ha otorgado su consentimiento para ello.</p>
        <p>Los datos personales pueden ser recabados de manera directa —como cuando se proporcionan de manera personal—, o por medio indirecto —ya sean directorios telefónicos, de servicios o laborales—, y son entre otros: Nombres y apellidos, género, fecha de nacimiento, domicilio, teléfono fijo y/o móvil, correo electrónico, etc. En cuanto a los datos financieros, de conformidad con las excepciones que señalan los artículos 8, 10 y 37 de la Ley, no son considerados como que requieran de consentimiento expreso para ser utilizados.</p>
        <p>Los datos serán utilizados estrictamente para las actividades que se desprendan de brindarle algún bien o servicio, que en forma enunciativa pero no limitativa se describen a continuación: La renta de automóviles, así como la prestación de servicios inherentes a sus viajes de placer o negocios, de actualización y confirmación, con fines promocionales, publicitarios y de contratación y crediticios, realizar estudios sobre hábitos de consumo y preferencias, la preparación de opciones financieras, la cobranza y procuración de pago y contactarlo para cualquier tema relacionado a los servicios que prestamos o a la presente política de privacidad. De conformidad con lo estipulado en el artículo 37 fracción III de la Ley, los datos personales no serán transferidos a terceros sin consentimiento, con excepción de aquellas sociedades que forman parte de nuestro grupo o socios comerciales.</p>
        <p>Se podrán ejercitar, a partir del día 6 de julio del año 2018, los derechos ARCO, o sea de acceder, rectificar y cancelar datos personales, así como a oponerse al tratamiento de los mismos o revocar el consentimiento que para tal fin se haya otorgado, a través del procedimiento que hemos implementado, es decir, bastará con dirigirse al C. Juan de Dios Hernadez Resendiz, encargado del área de atención a cliente por teléfono al número <strong>(442) 716 9793</strong>, o por medio de su correo electrónico <a href="mailto:reservaciones@viajerocar-rental.mx">reservaciones@viajerocar-rental.mx</a>, redactando un documento en idioma español, en donde agregue nombre completo, copia simple de su identificación oficial o, en medios electrónicos versión digitalizada de la misma (escaneo), indicación del correo electrónico o dirección física que designe para notificaciones y algún número telefónico de contacto. Una vez hecho, en un plazo máximo de 20 días hábiles se le informará de la procedencia de dicha solicitud; en caso de que se le envíe carta de respuesta al domicilio físico indicado, los veinte días se darán por cumplidos al momento de entregar el documento al servicio postal.</p>
        <p>La falta de oposición para que los datos personales sean transferidos como se menciona, será interpretada en el sentido de que ha otorgado su consentimiento para ello.</p>
        <p>Nos reservamos el derecho a efectuar en cualquier momento modificaciones o actualizaciones al presente aviso de privacidad, en atención de novedades legislativas, políticas internas o nuevos requerimientos para la prestación u ofrecimiento de nuestros servicios o productos. Estas modificaciones estarán disponibles al público a través de nuestra página de Internet, vía correo electrónico o por escrito.</p>
      </div>
    </template>

    {{-- POLÍTICA LIMPIEZA / SUCIEDAD --}}
    <template id="tpl-limpieza">
      <div class="policy-content">
        <h4>Política libre de tabaco, suciedad y olores desagradables – Viajero Car Rental</h4>
        <p>
          En <strong>Viajero Car Rental</strong> entregamos todos nuestros vehículos limpios, ventilados y en condiciones
          óptimas de higiene para tu comodidad y la de los siguientes clientes. Por ello, contamos con una
          <strong>política estricta libre de tabaco, suciedad excesiva y olores desagradables</strong>.
        </p>

        <p>
          <strong>Recuerda:</strong> Devolver el vehículo con presencia de humo de tabaco, suciedad excesiva o con olores
          fuertes que requieran limpieza profunda originará un <strong>cargo de $4,000 MXN (aprox. $250 USD)</strong>, que
          será aplicado a la forma de pago registrada en tu contrato de renta.
        </p>

        <h5>¿Qué se considera suciedad u olor que viola esta política?</h5>
        <p>Entre otros, se consideran motivos para aplicar el cargo de limpieza especial:</p>
        <ul>
          <li>Olor evidente a <strong>cigarro, tabaco, vape o marihuana</strong> dentro del vehículo.</li>
          <li>Restos de <strong>ceniza, colillas</strong> o quemaduras en vestiduras o tapicería.</li>
          <li>Manchas excesivas de comida, bebidas, grasa, lodo, vómito u otros residuos difíciles de remover.</li>
          <li>Olores fuertes a químicos, cloro, humedad, basura o alimentos en descomposición.</li>
          <li>Acumulación de pelo de mascota, restos orgánicos o insectos por falta de limpieza básica.</li>
        </ul>

        <h5>¿Cómo se valida este cargo?</h5>
        <ul>
          <li>Al momento de la devolución, el vehículo es inspeccionado por el personal de <strong>Viajero Car Rental</strong>.</li>
          <li>En caso de detectar suciedad u olor que requiera limpieza profunda, se <strong>tomarán fotografías</strong> y se documentará la condición del vehículo.</li>
          <li>Se aplicará la <strong>cuota fija de $4,000 MXN</strong> por limpieza profunda y desodorización.</li>
        </ul>

        <h5>¿Qué puedes hacer para evitar este cargo?</h5>
        <ul>
          <li>No fumar dentro del vehículo bajo ninguna circunstancia.</li>
          <li>Evitar consumir alimentos o bebidas que puedan derramarse o dejar olores fuertes.</li>
          <li>Retirar toda la basura y pertenencias personales antes de la devolución.</li>
          <li>Si viajas con mascotas, utilizar fundas, mantas o accesorios que protejan las vestiduras.</li>
        </ul>

        <p>
          Esta política tiene como objetivo garantizar que cada vehículo de <strong>Viajero Car Rental</strong>
          se entregue siempre en las mejores condiciones posibles para todos nuestros clientes.
        </p>
      </div>
    </template>

    {{-- REQUISITOS RENTA --}}
    <template id="tpl-requisitos">
      <div class="policy-content">
        <h4>Edad mínima</h4>
        <p>
          La edad mínima para rentar un vehículo con <strong>Viajero Car Rental</strong> es de 21 años.
          Se aplica un cargo por conductor joven para clientes entre 21 y 24 años, con un costo de
          <strong>$241 MXN por día</strong>. Este cargo puede estar sujeto a tarifa de aeropuerto
          y otros cargos aplicables más IVA.
        </p>

        <h4>Licencia de conducir vigente</h4>
        <p>
          El cliente principal deberá presentar una licencia de conducir vigente del país de
          residencia con un mínimo de un año de antigüedad. Viajero Car Rental se reserva el
          derecho de verificar la licencia y negar la renta si no cumple con los lineamientos.
          Para conductores adicionales solo se requiere licencia vigente, sin antigüedad mínima.
        </p>

        <h4>Identificación oficial vigente</h4>
        <p>
          Se debe presentar una identificación oficial con fotografía. Para ciudadanos mexicanos
          se solicita INE/IFE con al menos un año de antigüedad. Para extranjeros se acepta
          pasaporte, Passport Card, FM2, FM3 o Residencia Permanente.
        </p>

        <h4>Tarjeta de crédito a nombre del titular</h4>
        <p>
          Es indispensable presentar una tarjeta de crédito bancaria a nombre del titular para
          iniciar la renta. El pago final puede hacerse con tarjeta de débito. Se aceptan
          American Express, Visa y MasterCard. La tarjeta debe tener mínimo un año de antigüedad.
          Viajero Car Rental puede rechazar tarjetas en mal estado, ilegibles o de programas
          especiales no compatibles.
        </p>

        <h4>Derecho a negar el servicio</h4>
        <p>
          Viajero Car Rental se reserva el derecho de no completar la renta si cualquiera de los
          requisitos no se cumple o si se detecta alguna irregularidad en la documentación presentada.
        </p>
      </div>
    </template>

    {{-- ✅ NUEVO: PRE CHECK-IN --}}
    <template id="tpl-precheckin">
      <div class="policy-content">
        <h4>¿Por qué activar el Pre Check-In en Viajero?</h4>
        <p><strong>Ahorra tiempo y evita filas</strong> completando tu registro en línea antes de llegar.</p>

        <p><strong>Solo necesitas proporcionar:</strong></p>
        <ul>
          <li>Identificación oficial</li>
          <li>Licencia de conducir vigente</li>
          <li>Datos de contacto</li>
          <li>Método de pago</li>
        </ul>

        <p>Cuando llegues, tu contrato estará listo y tu vehículo preparado. <strong>Comienza tu viaje sin esperas.</strong></p>

        <h4>¿Listo para comenzar?</h4>
        <ul>
          <li>Si envías tu información con anticipación, agilizamos tu proceso de entrega.</li>
          <li>Presenta tu identificación y licencia vigentes al recoger el vehículo.</li>
          <li>Nuestro equipo te asistirá en mostrador para la firma final y entrega de llaves.</li>
          <li>Revisa tu unidad y empieza tu experiencia con Viajero.</li>
        </ul>
      </div>
    </template>

    {{-- SEGURO CANCELACIÓN --}}
    <template id="tpl-seguro-cancelacion">
      <div class="policy-content">
        <h4>Términos y condiciones del seguro de cancelación – Viajero Car Rental</h4>
        <p>
          Para realizar la cancelación de una reserva prepagada y comenzar el proceso de reembolso
          del total prepagado, el cliente deberá comunicarse con <strong>Viajero Car Rental</strong>
          al teléfono <strong>01 (442) 716 9793</strong> o escribir al correo
          <a href="mailto:reservaciones@viajerocarental.com">reservaciones@viajerocarental.com</a>.
        </p>
        <p>En caso de haber adquirido el <strong>seguro de cancelación</strong>, este reembolsará el <strong>100% del total prepagado</strong> por la renta.</p>
        <p>El cliente podrá solicitar su reembolso hasta <strong>24 horas antes</strong> de la fecha y hora programadas de inicio de la renta.</p>
        <p>El pago de <strong>$200 MXN</strong> por el seguro de cancelación <strong>no es reembolsable</strong>.</p>
        <p>
          El reembolso se procesará y se reflejará en la tarjeta con la que se realizó el prepago,
          en un lapso de hasta <strong>8 días hábiles</strong>.
        </p>
      </div>
    </template>

    {{-- CARGOS E IMPUESTOS --}}
    <template id="tpl-cargos">
      <div class="policy-content">
        <h4>Impuesto (IVA)</h4>
        <p>Todos los cargos relacionados con la renta del vehículo están sujetos al <strong>IVA</strong> vigente.</p>

        <h4>Cargo por recuperación de aeropuerto (Airport Fee)</h4>
        <p>Aplica únicamente en sucursales ubicadas dentro de un aeropuerto.</p>

        <h4>Cargo por telemetría (Telematic Fee)</h4>
        <p>Corresponde al <strong>7.5% sobre todos los conceptos de la renta</strong>.</p>

        <h4>Cargo por dejar el vehículo en otra ciudad (Drop-Off / One-Way)</h4>
        <p>Aplica cuando el cliente toma el automóvil en una ciudad y lo devuelve en otra distinta.</p>

        <h4>Cargo por gasolina</h4>
        <p>La gasolina no está incluida en la tarifa. El vehículo debe devolverse con el tanque lleno.</p>
      </div>
    </template>

    {{-- FORMAS DE PAGO --}}
    <template id="tpl-pago">
      <div class="policy-content">
        <h4>Formas de pago aceptadas por Viajero Car Rental</h4>
        <h5>Tarjetas bancarias (crédito y débito)</h5>
        <ul>
          <li><strong>American Express</strong></li>
          <li><strong>Visa</strong></li>
          <li><strong>Mastercard</strong></li>
        </ul>

        <h5>PayPal</h5>
        <p>Puedes realizar tu pago mediante <strong>PayPal</strong> cuando esté habilitado o vía enlace seguro.</p>

        <h5>Pago en efectivo</h5>
        <p><strong>Sí aceptamos efectivo</strong> para el total de la renta, pero la garantía se realiza con tarjeta.</p>

        <h5>Depósitos en OXXO</h5>
        <p>Requiere coordinación con un asesor para referencia y confirmación.</p>

        <h5>Mercado Pago</h5>
        <p>Pago por enlace o QR generado por asesor.</p>
      </div>
    </template>

    {{-- CANCELACIONES --}}
    <template id="tpl-cancelaciones">
      <div class="policy-content">
        <h4>Política de cancelaciones, no llegada (No show) y reembolsos – Viajero Car Rental</h4>

        <h5>Reembolso del 100%</h5>
        <p>Cancelación <strong>21 días o más</strong> antes de la fecha de renta (prepago).</p>

        <h5>Reembolso del 50%</h5>
        <p>Cancelación entre <strong>20 y 15 días</strong> previos (prepago).</p>

        <h5>Reembolso del 25%</h5>
        <p>Cancelación entre <strong>14 y 7 días</strong> previos (prepago).</p>

        <h5>Sin reembolso</h5>
        <p>Cancelación <strong>6 días o menos</strong> y casos de <strong>No show</strong>.</p>

        <h5>Procedimiento</h5>
        <p>Teléfono: <strong>01 (442) 303 2668</strong> · Correo: <a href="mailto:reservaciones@viajerocar-rental.mx">reservaciones@viajerocar-rental.mx</a></p>
      </div>
    </template>

    {{-- DEPÓSITOS (GARANTÍA / PRE-AUTORIZACIÓN) --}}
    <template id="tpl-depositos">
      <div class="policy-content">
        <h4>Garantía de renta o pre-autorizaciones – Viajero Car Rental</h4>
        <p>
          La <strong>garantía</strong> o <strong>pre-autorización</strong> es un monto retenido temporalmente en la tarjeta del cliente
          como respaldo por la renta. <strong>No es un cargo definitivo</strong> al abrir contrato.
        </p>

        <h5>Liberación</h5>
        <p>
          Se libera generalmente dentro de <strong>48 horas hábiles</strong> tras la devolución,
          o en el plazo que indique el banco emisor, siempre que la unidad se entregue en condiciones.
        </p>

        <h5>Tabla referencial de garantías</h5>
        <div class="tabla-garantias">
          <table class="tabla-viajero">
            <thead>
              <tr>
                <th>Categoría</th>
                <th>Tamaño</th>
                <th>LDW</th>
                <th>PDW</th>
                <th>CDW 10%</th>
                <th>CDW 20%</th>
                <th>CDW declinado</th>
              </tr>
            </thead>
            <tbody>
              <tr><td>C</td><td>Compacto Chevrolet aveo o similar</td><td>$MXN5,000</td><td>$MXN8,000</td><td>$MXN15,000</td><td>$MXN25,000</td><td>$MXN330,000</td></tr>
              <tr><td>D</td><td>Medianos Nissan Virtus o similar</td><td>$MXN5,000</td><td>$MXN8,000</td><td>$MXN18,000</td><td>$MXN25,000</td><td>$MXN380,000</td></tr>
              <tr><td>E</td><td>Grandes Volkswagen Jetta o similar</td><td>$MXN5,000</td><td>$MXN8,000</td><td>$MXN20,000</td><td>$MXN30,000</td><td>$MXN500,000</td></tr>
              <tr><td>F</td><td>Full size Camry o similar</td><td>$MXN5,000</td><td>$MXN15,000</td><td>$MXN30,000</td><td>$MXN40,000</td><td>$MXN650,000</td></tr>
              <tr><td>IC</td><td>Suv compacta Jeep Renegade o similar</td><td>$MXN5,000</td><td>$MXN8,000</td><td>$MXN20,000</td><td>$MXN30,000</td><td>$MXN500,000</td></tr>
              <tr><td>I</td><td>Suv Mediana Kia Seltos o similar</td><td>$MXN5,000</td><td>$MXN10,000</td><td>$MXN30,000</td><td>$MXN40,000</td><td>$MXN600,000</td></tr>
              <tr><td>IB</td><td>Suv Familiar compacta Toyota avanza o similar</td><td>$MXN5,000</td><td>$MXN8,000</td><td>$MXN18,000</td><td>$MXN25,000</td><td>$MXN400,000</td></tr>
              <tr><td>M</td><td>Minivan Honda Odyssey o similar</td><td>$MXN10,000</td><td>$MXN20,000</td><td>$MXN30,000</td><td>$MXN40,000</td><td>$MXN800,000</td></tr>
              <tr><td>L</td><td>Pasajeros de 12 usuarios Toyota Hiace o similar</td><td>$MXN10,000</td><td>$MXN20,000</td><td>$MXN30,000</td><td>$MXN40,000</td><td>$MXN800,000</td></tr>
              <tr><td>H</td><td>Pick up Doble Cabina Nissan Frontier o similar</td><td>$MXN10,000</td><td>$MXN20,000</td><td>$MXN30,000</td><td>$MXN40,000</td><td>$MXN600,000</td></tr>
              <tr><td>HI</td><td>Pick up 4x4 Doble Cabina Toyota Tacoma o similar</td><td>$MXN10,000</td><td>$MXN20,000</td><td>$MXN30,000</td><td>$MXN40,000</td><td>$MXN900,000</td></tr>
            </tbody>
          </table>
        </div>

        <p style="margin-top: 1rem;"><em>Montos sujetos a cambio sin previo aviso.</em></p>
      </div>
    </template>

    {{-- INFRACCIONES --}}
    <template id="tpl-infracciones">
      <div class="policy-content">
        <h4>Políticas de infracciones</h4>
        <p>
          Aquí puedes colocar la información oficial de infracciones (multas, cargos administrativos,
          detenciones, etc.). Si ya tienes el texto en otro lado, me lo pasas y lo incrusto tal cual.
        </p>
      </div>
    </template>

    {{-- TÉRMINOS --}}
    <template id="tpl-terminos">
      <div class="policy-content">
        <h4>Políticas y Procedimientos</h4>
        <p>
          El total aproximado del alquiler está basado en la información suministrada al momento de hacer su reservación.
          El conductor deberá presentar una tarjeta de crédito vigente con suficiente saldo disponible, licencia de
          conducir vigente y una identificación oficial a la hora de aperturar el contrato...
        </p>
        <p>
          Atención y aclaraciones:
          Teléfono: <a href="tel:+524423032668">01 (442) 303 2668</a> ·
          Correo: <a href="mailto:reservaciones@viajerocar-rental.mx">reservaciones@viajerocar-rental.mx</a>
        </p>
      </div>
    </template>

</main>
@endsection {{-- cierre de contenidoHome --}}

@section('js-vistaPoliticas')
  <script src="{{ asset('js/politicas.js') }}"></script>
@endsection