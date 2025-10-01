@extends('layouts.Usuarios')

@section('Titulo','Politicas de Privacidad y Términos de Servicio')

@section('css-vistaPoliticas')
    <link rel="stylesheet" href="{{ asset('css/politicas.css') }}">
@endsection

@section('contenidoHome')
<main class="page">
    <section class="hero hero-mini">
      <div class="hero-bg">
        <img src="https://images.unsplash.com/photo-1517142089942-ba376ce32a0a?q=80&w=1600&auto=format&fit=crop" alt="">
      </div>
      <div class="hero-overlay"></div>
      <div class="hero-content">
        <h1>Políticas <span>Viajero</span></h1>
        <p>Aviso de privacidad, limpieza, renta y términos</p>
      </div>
    </section>

    <section class="policies-wrap">
      <ul class="policy-list">
        <li class="policy-item">
          <button class="policy-head" type="button" aria-expanded="false">
            <div class="ph-left">
              <span class="ph-icon"><i class="fa-solid fa-shield-halved"></i></span>
              <span class="ph-title">Aviso de privacidad</span>
            </div>
            <i class="fa-solid fa-chevron-down ph-caret"></i>
          </button>
          <div class="policy-body" aria-hidden="true">
            <div class="policy-content">
              <p><strong>ELABORADO POR VIAJERO CAR RENTAL (JUAN DE DIOS HERNANDEZ RESENDIZ), PARA LA PROTECCIÓN DE DATOS DE SUS CLIENTES</strong></p>
              <p>Persona física con Actividad Empresarial, con domicilio fiscal en Blvd. Bernardo Quintana 8300, Centro Sur, 76090 Santiago de Querétaro, Qro., tratará sus datos personales recabados para fines de identificación, operación, administración y comercialización relacionada con el alquiler de vehículos automotores. Si Usted no manifiesta su oposición para que sus datos personales sean tratados, se entenderá que ha otorgado su consentimiento para ello.</p>
              <p>Los datos personales pueden ser recabados de manera directa —como cuando se proporcionan de manera personal—, o por medio indirecto —ya sean directorios telefónicos, de servicios o laborales—, y son entre otros: Nombres y apellidos, género, fecha de nacimiento, domicilio, teléfono fijo y/o móvil, correo electrónico, etc. En cuanto a los datos financieros, de conformidad con las excepciones que señalan los artículos 8, 10 y 37 de la Ley, no son considerados como que requieran de consentimiento expreso para ser utilizados.</p>
              <p>Los datos serán utilizados estrictamente para las actividades que se desprendan de brindarle algún bien o servicio, que en forma enunciativa pero no limitativa se describen a continuación: La renta de automóviles, así como la prestación de servicios inherentes a sus viajes de placer o negocios, de actualización y confirmación, con fines promocionales, publicitarios y de contratación y crediticios, realizar estudios sobre hábitos de consumo y preferencias, la preparación de opciones financieras, la cobranza y procuración de pago y contactarlo para cualquier tema relacionado a los servicios que prestamos o a la presente política de privacidad. De conformidad con lo estipulado en el artículo 37 fracción III de la Ley, los datos personales no serán transferidos a terceros sin consentimiento, con excepción de aquellas sociedades que forman parte de nuestro grupo o socios comerciales.</p>
              <p>Se podrán ejercitar, a partir del día 6 de julio del año 2018, los derechos ARCO, o sea de acceder, rectificar y cancelar datos personales, así como a oponerse al tratamiento de los mismos o revocar el consentimiento que para tal fin se haya otorgado, a través del procedimiento que hemos implementado, es decir, bastará con dirigirse al C. Juan de Dios Hernadez Resendiz, encargado del área de atención a cliente por teléfono al número <strong>(442) 716 9793</strong>, o por medio de su correo electrónico <a href="mailto:reservaciones@viajerocar-rental.mx">reservaciones@viajerocar-rental.mx</a>, redactando un documento en idioma español, en donde agregue nombre completo, copia simple de su identificación oficial o, en medios electrónicos versión digitalizada de la misma (escaneo), indicación del correo electrónico o dirección física que designe para notificaciones y algún número telefónico de contacto. Una vez hecho, en un plazo máximo de 20 días hábiles se le informará de la procedencia de dicha solicitud; en caso de que se le envíe carta de respuesta al domicilio físico indicado, los veinte días se darán por cumplidos al momento de entregar el documento al servicio postal.</p>
              <p>La falta de oposición para que los datos personales sean transferidos como se menciona, será interpretada en el sentido de que ha otorgado su consentimiento para ello.</p>
              <p>Nos reservamos el derecho a efectuar en cualquier momento modificaciones o actualizaciones al presente aviso de privacidad, en atención de novedades legislativas, políticas internas o nuevos requerimientos para la prestación u ofrecimiento de nuestros servicios o productos. Estas modificaciones estarán disponibles al público a través de nuestra página de Internet, vía correo electrónico o por escrito.</p>
            </div>
          </div>
        </li>

        <li class="policy-item">
          <button class="policy-head" type="button" aria-expanded="false">
            <div class="ph-left">
              <span class="ph-icon"><i class="fa-solid fa-soap"></i></span>
              <span class="ph-title">Política de limpieza</span>
            </div>
            <i class="fa-solid fa-chevron-down ph-caret"></i>
          </button>
          <div class="policy-body" aria-hidden="true">
            <div class="policy-content">
              <p>Nos caracterizamos por ser una compañía comprometida con sus clientes, nuestra forma de operar se basa en un conjunto de Políticas y Procedimientos claros y transparentes de cara hacia ti.</p>
              <p>Si tuvieras alguna pregunta o comentario, comunícate al <strong>01 (442) 303 2668</strong> o por correo electrónico a <a href="mailto:reservaciones@viajerocar-rental.mx">reservaciones@viajerocar-rental.mx</a> donde un especialista de Servicio a Clientes te atenderá.</p>
              <h4>¿POR QUÉ HEMOS CREADO ESTAS POLÍTICAS?</h4>
              <p>Esta política se diseñó para asegurar la comodidad de nuestros clientes, una prioridad para VIAJERO. Te escuchamos; la solicitud de nuestros clientes era: “Quiero un auto de No Fumar” y cumplimos con esta solicitud.</p>
              <p>El humo del tabaco deja residuos en los asientos y vestiduras, los cuales emiten olores que la mayoría de los clientes consideran desagradables.</p>
              <p>Cuando un vehículo debe ser limpiado por olor de tabaco o residuos en exceso, el proceso toma un tiempo considerable y obliga a que el auto sea retirado de la flota de renta hasta que esté completamente limpio.</p>
              <p>Esto ocasiona costos incrementales para nosotros pues el vehículo no puede rentarse.</p>
              <p>El cobro de cargos nos comprometen más que nunca a validar en nuestros estándares que el auto está completamente limpio a la entrega, tanto por dentro, como por fuera.</p>
              <h4>¿CÓMO DETERMINA VIAJERO QUE UN VEHÍCULO PRESENTA EXCESO DE SUCIEDAD?</h4>
              <p>Buscaremos evidencia física en el automóvil: pelo de animal, infestado de insectos, cera (tablas de surf), bloqueador solar, manchas de sal (asientos mojados), vómito, lodo, grasa, cloro o cualquier químico que deteriore, quemaduras de vestiduras/paeles, cenizas, colillas de cigarro, entre otros daños que deterioren el interior del auto y requiera lavado o tratamiento especial. <strong>Cuota de $4,000 MXN</strong>.</p>
              <p>Por olores desagradables como tabaco, cloro, amoniaco, pescado, sudor, etc. <strong>Cuota de $4,000 MXN</strong>.</p>
              <p>En caso de devolver el auto con engomados (por pancartas promocionales, eventos, etc.), que puedan dañar la pintura y requieran un proceso de limpieza o encerado especial. <strong>Cuota de $4,000 MXN</strong>.</p>
              <p>Se tomarán fotografías para sustentar el cobro de limpieza.</p>
              <h4>¿CUÁNDO SE HACE ESTA INSPECCIÓN SE NOTIFICA AL CLIENTE?</h4>
              <p>En la mayoría de los casos la inspección y la notificación al cliente ocurrirán al momento de recibir el auto en el área de entrega.</p>
              <p>Si el agente de mostrador encuentra evidencia de que el vehículo presenta exceso de suciedad, el cliente será informado inmediatamente sobre el cargo y se le entregará un comprobante amparando este cobro.</p>
              <p>Más tarde, el Gerente de la Oficina inspeccionará el vehículo para validar lo encontrado y determinará el cargo.</p>
              <p>En algunas circunstancias, el Agente de Servicio que recibe el vehículo no podrá comunicar al cliente sobre el posible, por ejemplo:</p>
              <ul>
                <li>El cliente deja el área de recepción de vehículos antes que la inspección concluya.</li>
                <li>El agente que recibe el vehículo no logra revisar el automóvil a detalle, la inspección entonces se realizará por Staff entrenado al momento de preparar el auto para la siguiente renta.</li>
                <li>El vehículo se entrega fuera de horario de recepción y no existe un Agente para inspeccionar el vehículo.</li>
              </ul>
              <p>En cualquiera de los casos anteriores, el cliente será notificado vía email sobre el cargo por limpieza y el cargo a su tarjeta de crédito.</p>
              <h4>¿QUÉ PUEDES HACER PARA ASEGURARTE QUE NO TE COBREN CARGOS DE LIMPIEZA?</h4>
              <ul>
                <li>Restringirte de fumar en el vehículo de renta.</li>
                <li>Evita entrar al vehículo mojado, conserva alimentos y bebidas en bolsas o recipientes sellados y evita dejar basura.</li>
                <li>Revisar el auto antes de salir de la oficina de renta y asegurarte que esté en excelentes condiciones.</li>
                <li>Si al revisar el automóvil que te asignamos no estás 100% satisfecho, pide que la unidad te sea cambiada; procederemos entonces a cambiarte el automóvil sin ningún cargo para ti.</li>
              </ul>
            </div>
          </div>
        </li>

        <li class="policy-item">
          <button class="policy-head" type="button" aria-expanded="false">
            <div class="ph-left">
              <span class="ph-icon"><i class="fa-solid fa-file-signature"></i></span>
              <span class="ph-title">Política de renta</span>
            </div>
            <i class="fa-solid fa-chevron-down ph-caret"></i>
          </button>
          <div class="policy-body" aria-hidden="true">
            <div class="policy-content">
              <div class="sub-accordion" id="renta-accordion">
                <div class="sub-item">
                  <button class="sub-head" type="button" aria-expanded="false">
                    <span>Requisitos para rentar un auto VIAJERO</span>
                    <i class="fa-solid fa-angle-down"></i>
                  </button>
                  <div class="sub-body" aria-hidden="true">
                    <div class="sub-content">
                      <p><em>Aquí colocaremos tus requisitos detallados (identificación, licencia, depósito, edad, etc.).</em></p>
                    </div>
                  </div>
                </div>
                <div class="sub-item">
                  <button class="sub-head" type="button" aria-expanded="false">
                    <span>Nuestras políticas de renta</span>
                    <i class="fa-solid fa-angle-down"></i>
                  </button>
                  <div class="sub-body" aria-hidden="true">
                    <div class="sub-content">
                      <p><em>Aquí irán tus políticas (uso del vehículo, combustible, kilometraje, etc.).</em></p>
                    </div>
                  </div>
                </div>
                <div class="sub-item">
                  <button class="sub-head" type="button" aria-expanded="false">
                    <span>Definiciones de cargos e impuestos</span>
                    <i class="fa-solid fa-angle-down"></i>
                  </button>
                  <div class="sub-body" aria-hidden="true">
                    <div class="sub-content">
                      <p><em>Aquí definimos cargos (protecciones, recargos, impuestos, etc.).</em></p>
                    </div>
                  </div>
                </div>
                <div class="sub-item">
                  <button class="sub-head" type="button" aria-expanded="false">
                    <span>Nuestras tarifas</span>
                    <i class="fa-solid fa-angle-down"></i>
                  </button>
                  <div class="sub-body" aria-hidden="true">
                    <div class="sub-content">
                      <p><em>Tabla / explicación de tarifas (por día, categoría, temporada, extensiones, etc.).</em></p>
                    </div>
                  </div>
                </div>
                <div class="sub-item">
                  <button class="sub-head" type="button" aria-expanded="false">
                    <span>Sobre el lugar y fecha de devolución del automóvil</span>
                    <i class="fa-solid fa-angle-down"></i>
                  </button>
                  <div class="sub-body" aria-hidden="true">
                    <div class="sub-content">
                      <p><em>Políticas de devolución (sucursales, horarios, devoluciones tardías, etc.).</em></p>
                    </div>
                  </div>
                </div>
                <div class="sub-item">
                  <button class="sub-head" type="button" aria-expanded="false">
                    <span>Políticas sobre nuestras reservaciones</span>
                    <i class="fa-solid fa-angle-down"></i>
                  </button>
                  <div class="sub-body" aria-hidden="true">
                    <div class="sub-content">
                      <p><em>Reservas: cambios, cancelaciones, no-show, prepagos/adelantos, etc.</em></p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </li>

        <li class="policy-item">
          <button class="policy-head" type="button" aria-expanded="false">
            <div class="ph-left">
              <span class="ph-icon"><i class="fa-solid fa-scale-balanced"></i></span>
              <span class="ph-title">Términos y condiciones</span>
            </div>
            <i class="fa-solid fa-chevron-down ph-caret"></i>
          </button>
          <div class="policy-body" aria-hidden="true">
            <div class="policy-content">
              <p>Incluye coberturas, responsabilidad, cargos por retraso, cambios, cancelaciones, métodos de pago y jurisdicción aplicable. La versión vigente se publica en el sitio. Atención: <a href="tel:+524423032668">01 (442) 303 2668</a> · <a href="mailto:reservaciones@viajerocar-rental.mx">reservaciones@viajerocar-rental.mx</a></p>
            </div>
          </div>
        </li>

      </ul>
    </section>
  </main>
@section('js-vistaPoliticas')
    <script src="{{ asset('js/politicas.js') }}"></script>

@endsection
@endsection
