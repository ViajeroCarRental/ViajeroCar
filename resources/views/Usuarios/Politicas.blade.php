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

    <section class="policies-wrap">
      <ul class="policy-list">

        {{-- AVISO DE PRIVACIDAD --}}
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
              <p><strong>ELABORADO POR VIAJERO CAR RENTAL, PARA LA PROTECCIÓN DE DATOS DE SUS CLIENTES</strong></p>
              <p>Persona física con Actividad Empresarial, con domicilio fiscal en Blvd. Bernardo Quintana 8300, Centro Sur, 76090 Santiago de Querétaro, Qro., tratará sus datos personales recabados para fines de identificación, operación, administración y comercialización relacionada con el alquiler de vehículos automotores. Si Usted no manifiesta su oposición para que sus datos personales sean tratados, se entenderá que ha otorgado su consentimiento para ello.</p>
              <p>Los datos personales pueden ser recabados de manera directa —como cuando se proporcionan de manera personal—, o por medio indirecto —ya sean directorios telefónicos, de servicios o laborales—, y son entre otros: Nombres y apellidos, género, fecha de nacimiento, domicilio, teléfono fijo y/o móvil, correo electrónico, etc. En cuanto a los datos financieros, de conformidad con las excepciones que señalan los artículos 8, 10 y 37 de la Ley, no son considerados como que requieran de consentimiento expreso para ser utilizados.</p>
              <p>Los datos serán utilizados estrictamente para las actividades que se desprendan de brindarle algún bien o servicio, que en forma enunciativa pero no limitativa se describen a continuación: La renta de automóviles, así como la prestación de servicios inherentes a sus viajes de placer o negocios, de actualización y confirmación, con fines promocionales, publicitarios y de contratación y crediticios, realizar estudios sobre hábitos de consumo y preferencias, la preparación de opciones financieras, la cobranza y procuración de pago y contactarlo para cualquier tema relacionado a los servicios que prestamos o a la presente política de privacidad. De conformidad con lo estipulado en el artículo 37 fracción III de la Ley, los datos personales no serán transferidos a terceros sin consentimiento, con excepción de aquellas sociedades que forman parte de nuestro grupo o socios comerciales.</p>
              <p>Se podrán ejercitar, a partir del día 6 de julio del año 2018, los derechos ARCO, o sea de acceder, rectificar y cancelar datos personales, así como a oponerse al tratamiento de los mismos o revocar el consentimiento que para tal fin se haya otorgado, a través del procedimiento que hemos implementado, es decir, bastará con dirigirse al C. Juan de Dios Hernadez Resendiz, encargado del área de atención a cliente por teléfono al número <strong>(442) 716 9793</strong>, o por medio de su correo electrónico <a href="mailto:reservaciones@viajerocar-rental.mx">reservaciones@viajerocar-rental.mx</a>, redactando un documento en idioma español, en donde agregue nombre completo, copia simple de su identificación oficial o, en medios electrónicos versión digitalizada de la misma (escaneo), indicación del correo electrónico o dirección física que designe para notificaciones y algún número telefónico de contacto. Una vez hecho, en un plazo máximo de 20 días hábiles se le informará de la procedencia de dicha solicitud; en caso de que se le envíe carta de respuesta al domicilio físico indicado, los veinte días se darán por cumplidos al momento de entregar el documento al servicio postal.</p>
              <p>La falta de oposición para que los datos personales sean transferidos como se menciona, será interpretada en el sentido de que ha otorgado su consentimiento para ello.</p>
              <p>Nos reservamos el derecho a efectuar en cualquier momento modificaciones o actualizaciones al presente aviso de privacidad, en atención de novedades legislativas, políticas internas o nuevos requerimientos para la prestación u ofrecimiento de nuestros servicios o productos. Estas modificaciones estarán disponibles al público a través de nuestra página de Internet, vía correo electrónico o por escrito.</p>
            </div>
          </div>
        </li>

        {{-- POLÍTICA DE LIMPIEZA --}}
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
                <li>Al momento de la devolución, el vehículo es inspeccionado por el personal de
                  <strong>Viajero Car Rental</strong>.
                </li>
                <li>En caso de detectar suciedad u olor que requiera limpieza profunda, se
                  <strong>tomarán fotografías</strong> y se documentará la condición del vehículo.</li>
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
                Esta política tiene como objetivo garantizar que cada vehículo de
                <strong>Viajero Car Rental</strong> se entregue siempre en las mejores condiciones posibles para todos
                nuestros clientes.
              </p>

            </div>
          </div>
        </li>

        {{-- POLÍTICA DE RENTA --}}
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

                {{-- REQUISITOS PARA RENTAR UN AUTO VIAJERO --}}
                <div class="sub-item">
                  <button class="sub-head" type="button" aria-expanded="false">
                    <span>Requisitos para rentar un auto VIAJERO</span>
                    <i class="fa-solid fa-angle-down"></i>
                  </button>

                  <div class="sub-body" aria-hidden="true">
                    <div class="sub-content">

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
                  </div>
                </div>

                {{-- TÉRMINOS Y CONDICIONES DEL SEGURO DE CANCELACIÓN --}}
                <div class="sub-item">
                  <button class="sub-head" type="button" aria-expanded="false">
                    <span>Términos y condiciones del seguro de cancelación</span>
                    <i class="fa-solid fa-angle-down"></i>
                  </button>
                  <div class="sub-body" aria-hidden="true">
                    <div class="sub-content">

                      <h4>Términos y condiciones del seguro de cancelación – Viajero Car Rental</h4>
                      <p>
                        Para realizar la cancelación de una reserva prepagada y comenzar el proceso de reembolso
                        del total prepagado, el cliente deberá comunicarse con <strong>Viajero Car Rental</strong>
                        al teléfono <strong>01 (442) 716 9793</strong> o escribir al correo
                        <a href="mailto:reservaciones@viajerocarental.com">reservaciones@viajerocarental.com</a>.
                      </p>
                      <p>
                        En caso de haber adquirido el <strong>seguro de cancelación</strong>, este reembolsará el
                        <strong>100% del total prepagado</strong> por la renta.
                      </p>
                      <p>
                        El cliente podrá solicitar su reembolso hasta <strong>24 horas antes</strong> de la fecha y hora
                        programadas de inicio de la renta. El seguro de cancelación <strong>no tendrá validez</strong>
                        si el reembolso se solicita con menos de 24 horas previas al momento de la renta.
                      </p>
                      <p>
                        El reembolso aplica únicamente al <strong>monto prepagado originalmente</strong> por la renta.
                        Si se realizan modificaciones a la reserva prepagada y posteriormente se cancela, el reembolso
                        cubrirá exclusivamente el importe original prepagado, sin incluir diferencias de precio
                        generadas por dichas modificaciones. Si las modificaciones se realizan con menos de 24 horas
                        antes de la renta, el seguro de cancelación se <strong>invalida</strong>.
                      </p>
                      <p>
                        El pago de <strong>$200 MXN</strong> por el seguro de cancelación <strong>no es reembolsable</strong>,
                        independientemente de si se utiliza o no.
                      </p>
                      <p>
                        El reembolso se procesará y se reflejará en la <strong>tarjeta con la que se realizó el prepago</strong>,
                        en un lapso de hasta <strong>8 días hábiles</strong> posteriores a la solicitud de reembolso,
                        pudiendo variar según la entidad bancaria y los días hábiles aplicables.
                      </p>
                      <p>
                        No aplica reembolso del prepago ni del seguro de cancelación si, para la fecha y hora de la renta,
                        el arrendatario presenta <strong>algún adeudo pendiente con Viajero Car Rental</strong>.
                      </p>
                      <p>
                        En caso de que el cliente no desee liquidar el adeudo, pero el prepago haya sido mayor a la suma de
                        dicho adeudo más una <strong>cuota administrativa de $1,000 MXN</strong>, Viajero Car Rental
                        reembolsará únicamente el <strong>remanente</strong>.
                      </p>

                    </div>
                  </div>
                </div>

                {{-- DEFINICIONES DE CARGOS E IMPUESTOS --}}
                <div class="sub-item">
                  <button class="sub-head" type="button" aria-expanded="false">
                    <span>Definiciones de cargos e impuestos</span>
                    <i class="fa-solid fa-angle-down"></i>
                  </button>
                  <div class="sub-body" aria-hidden="true">
                    <div class="sub-content">

                      <h4>Impuesto (IVA)</h4>
                      <p>
                        Todos los cargos relacionados con la renta del vehículo están sujetos al
                        <strong>Impuesto al Valor Agregado (IVA)</strong> vigente en la localidad donde se realiza la renta.
                      </p>

                      <h4>Cargo por recuperación de aeropuerto (Airport Fee)</h4>
                      <p>
                        Aplica únicamente en sucursales ubicadas dentro de un aeropuerto. Es una cuota de recuperación
                        por operar dentro de instalaciones aeroportuarias. Este cargo se detalla siempre en la
                        cotización y/o confirmación de <strong>Viajero Car Rental</strong>.
                      </p>

                      <h4>Cargo por telemetría (Telematic Fee)</h4>
                      <p>
                        Es un cargo por transacción que aplica a todas las rentas para cubrir el uso de la telemetría del vehículo.
                        Este cargo se aplica en mostrador al abrir el contrato y corresponde al
                        <strong>7.5% sobre todos los conceptos de la renta</strong>.
                      </p>

                      <h4>Cargo por Transaction Fee</h4>
                      <p>
                        Es un cargo por transacción que aplica a todo tipo de cliente en todas las rentas,
                        y cubre costos administrativos y operativos internos de <strong>Viajero Car Rental</strong>.
                      </p>

                      <h4>Impuesto de saneamiento ambiental</h4>
                      <p>
                        En algunos municipios o destinos turísticos de México puede aplicar un impuesto por saneamiento ambiental,
                        regulado por la autoridad local. En dichos destinos, podría cobrarse una cuota fija por día de renta,
                        misma que será informada al cliente en su cotización antes de firmar el contrato.
                      </p>

                      <h4>Cargo por dejar el vehículo en otra ciudad (Drop-Off / One-Way)</h4>
                      <p>
                        Aplica cuando el cliente toma el automóvil en una ciudad y lo devuelve en otra distinta.
                        El monto depende de la distancia entre ambas ciudades y se desglosa en la cotización
                        y/o confirmación. Los vehículos solo pueden devolverse en sucursales donde
                        <strong>Viajero Car Rental</strong> tenga operación autorizada.
                      </p>

                      <h4>Excepciones de devolución</h4>
                      <p>
                        Por razones logísticas y de operación, algunas rutas o combinaciones de ciudades pueden
                        restringirse. En caso de aplicarse alguna limitación, esta será informada al cliente
                        en la cotización previa a la renta.
                      </p>

                      <h4>Cargo por gasolina</h4>
                      <p>
                        La gasolina no está incluida en la tarifa, salvo que un paquete o promoción lo especifique
                        expresamente. El vehículo debe devolverse con el tanque lleno; de lo contrario, se aplicará
                        el cargo por el combustible faltante.
                      </p>

                      <h5>Precio de gasolina prepagada (por litro)</h5>
                      <p>
                        El precio será el vigente en la gasolinera. Para referencia, <strong>$31 MXN por litro</strong>,
                        rentando en oficina de aeropuerto o ciudad (incluye cargos e IVA).
                      </p>

                      <h5>Precio por servicio de combustible sin prepago (por litro)</h5>
                      <p>
                        El precio será equivalente al doble del precio vigente en gasolinera. Para referencia,
                        <strong>$62 MXN por litro</strong> (incluye cargos e IVA).
                      </p>

                      <h4>Cargo por conductor adicional</h4>
                      <p>
                        El cliente puede designar un conductor adicional mayor de 21 años, quien deberá presentarse
                        con licencia de conducir vigente. El conductor adicional adquiere las mismas obligaciones
                        que el titular del contrato.
                      </p>
                      <p>
                        <strong>$265 MXN por día</strong>, hasta un máximo de <strong>$1,325 MXN por renta</strong>.
                        Este precio incluye sobrecargos e IVA.
                      </p>
                      <p>
                        Están exentos de este cargo los cónyuges, compañeros de trabajo y compañeros domésticos.
                        Esta cuota puede ser susceptible al Airport Fee y al IVA correspondiente.
                      </p>

                      <h4>Cuota por conductor joven</h4>
                      <p>
                        Aplica a clientes entre 21 y 24 años de edad. El costo es de
                        <strong>$241 MXN por día</strong> (incluye sobrecargos e IVA).
                        Esta cuota puede estar sujeta también al Airport Fee.
                      </p>

                      <h4>Cuota por pérdida de uso (LOU – Loss of Use)</h4>
                      <p>
                        En caso de que, debido a un accidente, mal uso o evento atribuible al cliente, el vehículo
                        quede imposibilitado para rentarse nuevamente, el cliente será responsable de los días de
                        renta perdidos durante el tiempo de reparación o detención de la unidad.
                      </p>
                      <p>
                        Esta cuota puede eximirse al adquirir el paquete de <strong>Protección Total</strong>, siempre
                        y cuando el cliente cumpla con todas las políticas y procedimientos contratados.
                      </p>
                      <p>
                        Si el vehículo es detenido por accidente, alcoholímetro o por estar mal estacionado, además
                        de las multas y gastos de liberación, se aplicarán:
                      </p>
                      <ul>
                        <li>Cobro de los días de renta equivalentes al tiempo que la unidad permanezca detenida.</li>
                        <li>Cuota por servicios administrativos de <strong>$1,000 MXN</strong> (más Airport Fee de aplicar e IVA).</li>
                      </ul>

                      <h4>Cargo por licencia de vehículo (VLF)</h4>
                      <p>
                        Es una cuota de recuperación por trámites y servicios vehiculares, tales como placas,
                        permisos provisionales, verificación, tenencia y otros gastos administrativos relacionados
                        con la operación del vehículo.
                      </p>

                      <h4>Asistencia en el camino Premium (RSA)</h4>
                      <p>
                        Servicio adicional que brinda apoyo en situaciones como envío de duplicado de llaves,
                        cambio de llanta por ponchadura, recarga de batería o suministro de gasolina por desabasto.
                        Este servicio puede adquirirse directamente en mostrador.
                      </p>

                      <h4>Términos y condiciones de Viajero Car Rental</h4>
                      <p>
                        Todas estas definiciones complementan los Términos y Condiciones vigentes de
                        <strong>Viajero Car Rental</strong>, los cuales forman parte del contrato de renta
                        y se encuentran disponibles para consulta en el sitio web oficial.
                      </p>

                    </div>
                  </div>
                </div>

                {{-- FORMAS DE PAGO --}}
                <div class="sub-item">
                  <button class="sub-head" type="button" aria-expanded="false">
                    <span>Formas de Pago </span>
                    <i class="fa-solid fa-angle-down"></i>
                  </button>
                  <div class="sub-body" aria-hidden="true">
                    <div class="sub-content">

                      <h4>Formas de pago aceptadas por Viajero Car Rental</h4>
                      <p>
                        En <strong>Viajero Car Rental</strong> buscamos ofrecer múltiples opciones de pago para tu comodidad
                        y seguridad. Las formas de pago disponibles son las siguientes:
                      </p>

                      <h5>Tarjetas bancarias (crédito y débito)</h5>
                      <p>
                        Aceptamos tarjetas bancarias tanto de <strong>crédito</strong> como de <strong>débito</strong> de las marcas:
                      </p>
                      <ul>
                        <li><strong>American Express</strong></li>
                        <li><strong>Visa</strong></li>
                        <li><strong>Mastercard</strong></li>
                      </ul>
                      <p>Estas tarjetas pueden utilizarse para:</p>
                      <ul>
                        <li>Prepagar una reservación</li>
                        <li>Pagar el total de la renta en mostrador</li>
                        <li>Dejar la garantía / depósito correspondiente al contrato de renta</li>
                      </ul>

                      <h5>PayPal</h5>
                      <p>
                        Puedes realizar tu pago mediante <strong>PayPal</strong>, ya sea directamente desde nuestra página
                        (cuando esté habilitado) o a través de un enlace seguro proporcionado por un asesor de Viajero Car Rental.
                      </p>

                      <h5>Pago en efectivo</h5>
                      <p>
                        <strong>Sí aceptamos efectivo</strong> como forma de pago para cubrir el total de la renta en sucursal.
                        Sin embargo, la <strong>garantía (depósito)</strong> deberá realizarse siempre mediante tarjeta bancaria
                        u otro método electrónico autorizado.
                      </p>

                      <h5>Depósitos en OXXO</h5>
                      <p>
                        Aceptamos pagos mediante <strong>depósitos en tiendas OXXO</strong>. Este método requiere coordinación
                        previa con un asesor de Viajero Car Rental, quien proporcionará la información y referencia necesarias
                        para realizar el depósito.
                      </p>

                      <h5>Mercado Pago</h5>
                      <p>
                        También aceptamos pagos a través de <strong>Mercado Pago</strong>, ya sea mediante enlace directo o
                        código QR. Este método igualmente requiere contacto previo con un asesor para generar el enlace de pago
                        personalizado y seguro.
                      </p>

                      <h5>Avisos importantes</h5>
                      <ul>
                        <li>
                          Viajero Car Rental se reserva el derecho de <strong>no aceptar tarjetas dañadas, ilegibles
                          o no compatibles</strong> con nuestras terminales bancarias.
                        </li>
                        <li>
                          Los pagos realizados por <strong>OXXO</strong> o <strong>Mercado Pago</strong> deben ser
                          <strong>confirmados y verificados</strong> por un asesor antes de considerar la reservación
                          como garantizada.
                        </li>
                        <li>
                          En algunos casos, ciertos métodos de pago podrán estar sujetos a validación adicional o a
                          condiciones específicas según promociones vigentes.
                        </li>
                      </ul>

                    </div>
                  </div>
                </div>

                {{-- POLÍTICAS SOBRE CANCELACIONES DE RESERVACIONES --}}
                <div class="sub-item">
                  <button class="sub-head" type="button" aria-expanded="false">
                    <span>Políticas sobre cancelaciones de reservaciones</span>
                    <i class="fa-solid fa-angle-down"></i>
                  </button>
                  <div class="sub-body" aria-hidden="true">
                    <div class="sub-content">

                      <h4>Política de cancelaciones de reservaciones, no llegada (No show) y reembolsos – Viajero Car Rental</h4>
                      <p>
                        Si necesitas cancelar tu reservación, en <strong>Viajero Car Rental</strong> gestionaremos tu solicitud
                        de reembolso a la brevedad posible. Nuestra política de cancelación flexible se detalla a continuación.
                      </p>

                      <h5>Reembolso del 100%</h5>
                      <p>
                        Aplica para reservaciones con modalidad de <strong>pago en línea (prepago)</strong> cuando la cancelación
                        se realiza <strong>21 días o más</strong> antes de la fecha de renta.
                      </p>

                      <h5>Reembolso del 50%</h5>
                      <p>
                        Aplica para reservaciones con modalidad de <strong>pago en línea (prepago)</strong> cuando la cancelación
                        se realiza entre <strong>20 y 15 días</strong> previos a la fecha de renta.
                      </p>

                      <h5>Reembolso del 25%</h5>
                      <p>
                        Aplica para reservaciones con modalidad de <strong>pago en línea (prepago)</strong> cuando la cancelación
                        se realiza entre <strong>14 y 7 días</strong> previos a la fecha de renta.
                      </p>

                      <h5>Sin reembolso (6 días o menos y No show)</h5>
                      <p>
                        Para reservaciones en la modalidad de <strong>pago en línea (prepago)</strong>,
                        <strong>no se proporcionará ningún reembolso</strong> cuando:
                      </p>
                      <ul>
                        <li>La cancelación se realice <strong>6 días o menos</strong> antes de la fecha de renta, o</li>
                        <li>El cliente <strong>no se presente (No show)</strong> y no cancele la reservación.</li>
                      </ul>
                      <p>
                        Si la reservación se realizó originalmente con <strong>6 días o menos</strong> de anticipación, se entiende
                        y acepta que <strong>no aplica reembolso</strong> en caso de cancelación.
                      </p>

                      <h5>Relación con el Seguro de Cancelación</h5>
                      <p>
                        Los reembolsos <strong>íntegros del 100%</strong> del valor de la reserva solo aplican para aquellas
                        reservaciones que:
                      </p>
                      <ul>
                        <li>Hayan adquirido el <strong>Seguro de Cancelación</strong>, y</li>
                        <li>Hayan seleccionado la modalidad de <strong>pago en línea (prepago)</strong>.</li>
                      </ul>
                      <p>
                        Para ser elegible, la notificación de cancelación debe realizarse <strong>hasta 24 horas antes</strong>
                        de la fecha de renta. Cualquier notificación de cancelación realizada con
                        <strong>menos de 24 horas</strong> de antelación, incluso si se adquirió el Seguro de Cancelación,
                        <strong>no será sujeta a reembolso</strong>.
                      </p>

                      <h5>Devolución anticipada del vehículo</h5>
                      <p>
                        Si el cliente devuelve el vehículo <strong>antes de la fecha/hora pactadas</strong>,
                        <strong>no se reembolsará ninguna cantidad</strong> ni diferencial por los días no utilizados.
                      </p>

                      <h5>Incumplimiento de requisitos al llegar al mostrador</h5>
                      <p>
                        Si el cliente cuenta con una <strong>reserva prepagada</strong> y, al llegar al mostrador,
                        <strong>no cumple con uno o más requisitos</strong>, no se realizará ningún reembolso del total prepagado.
                      </p>
                      <p>Estos requisitos incluyen, de manera enunciativa mas no limitativa:</p>
                      <ul>
                        <li>Presentar licencia de conducir válida y vigente.</li>
                        <li>Presentar tarjeta de crédito válida a nombre del titular de la renta.</li>
                        <li>Presentar identificación oficial vigente.</li>
                        <li>Que los documentos cumplan con la <strong>vigencia</strong> y la
                            <strong>antigüedad mínima</strong> establecida en las políticas de Viajero Car Rental.
                        </li>
                      </ul>

                      <h5>Procedimiento para solicitar reembolsos</h5>
                      <p>
                        En los casos en los que <strong>sí aplique un reembolso</strong>, el cliente deberá dar aviso a
                        <strong>Viajero Car Rental</strong> dentro de los <strong>ocho días naturales siguientes</strong> a la
                        cancelación de la reserva, a través de:
                      </p>
                      <ul>
                        <li>Teléfono: <strong>01 (442) 303 2668</strong></li>
                        <li>Correo electrónico:
                          <a href="mailto:reservaciones@viajerocar-rental.mx">reservaciones@viajerocar-rental.mx</a>
                        </li>
                      </ul>
                      <p>
                        Si el cliente no realiza la notificación en el plazo señalado, el reembolso <strong>no será procedente</strong>.
                      </p>

                      <h5>Adeudos pendientes</h5>
                      <p>
                        No aplica reembolso si, para la fecha y hora de la renta, el arrendatario presenta
                        <strong>algún adeudo pendiente con Viajero Car Rental</strong>.
                      </p>
                      <p>
                        En caso de que el cliente no desee liquidar el adeudo, pero el prepago haya sido mayor a la suma de dicho
                        adeudo más una <strong>cuota administrativa de $1,000 MXN</strong>, Viajero Car Rental reembolsará
                        únicamente el <strong>remanente</strong>.
                      </p>

                    </div>
                  </div>
                </div>

              </div>
            </div>
          </div>
        </li>

        {{-- TÉRMINOS Y CONDICIONES --}}
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
              <h4>Políticas y Procedimientos</h4>
              <p>
        El total aproximado del alquiler está basado en la información suministrada al momento de hacer su reservación.
        El conductor deberá presentar una tarjeta de crédito vigente con suficiente saldo disponible, licencia de
        conducir vigente y una identificación oficial a la hora de aperturar el contrato. Al momento de hacer válida
        la reservación se deberá suscribir la apertura del contrato de arrendamiento, y otorgar un depósito con cargo
        a la tarjeta de crédito, en garantía del cumplimiento fiel y puntual de todas y cada una de sus obligaciones
        adquiridas en el Contrato de Arrendamiento.
              </p>

              <p>
        Usted puede adquirir servicios adicionales que puede elegir al momento del alquiler, tales como recarga de
        combustible, protección LDW para el vehículo, sillas de bebé, etc.; pregunte a nuestros representantes, con gusto
        le darán los detalles.
              </p>

              <p>
        Si usted requiere un conductor adicional, es necesario que la persona esté presente en el momento de la apertura
        del contrato, sea mayor de edad (aplican cargos si es menor a 25 años de edad), tenga una licencia vigente y una
        identificación oficial con el fin de que sea registrado como conductor autorizado.
              </p>

              <p>
        La edad mínima para poder rentar o manejar una de nuestras unidades es de 18 años; habrá un cargo adicional si el
        conductor tiene entre 18 y 24 años de edad. En caso de devolución del vehículo posteriormente a la hora de
        devolución señalada en la reservación, aplicarán cargos adicionales por el tiempo excedente a la fecha de
        devolución, de acuerdo a la tarifa pública vigente exhibida a la vista del público en la localidad de devolución.
              </p>

              <p>
        El periodo mínimo de renta es de un día.
              </p>

              <p>
        El precio de su reservación incluye los servicios mencionados en esta reservación, y Cobertura de Protección de
        Responsabilidad Civil contra daños a terceros hasta por $350,000.00 Moneda Nacional. Incluye cuotas locales e IVA.
        Cualquier Cobertura de Protección y Servicios que no se encuentren mencionados como incluidos en esta reservación
        tienen un costo adicional de acuerdo a la tarifa pública vigente exhibida a la vista del público en la localidad
        de entrega de la Unidad.
              </p>

              <p>
        Para la flota de lujo es obligatorio adquirir una cobertura de Protección por daños, accidente o robo del vehículo
        rentado. Aplican límites de protección y deducibles en algunas coberturas de protección. Aplica para todas las
        localidades <strong>Viajero Car Rental</strong> de la República Mexicana.
              </p>

              <p>
        Deberá conservar el correo con el código de reservación en todo momento para poder hacer válida la garantía
        (en caso de que aplique), así como los pagos realizados a través de PayPal. Para usuarios nacionales aplicará un
        3.95% + 4.00 MXN del monto total, así como para usuarios extranjeros de un 0.05% + 0.030 USD (aprox.).
              </p>

              <p>
        Su reservación es para tomar y devolver el auto en la ciudad y oficina de acuerdo a lo indicado en esta
        confirmación; si usted decide entregarlo en una oficina distinta a la pactada, pueden existir cargos adicionales.
        Pregunte a nuestros representantes.
              </p>

              <p>
        Nos reservamos el derecho a efectuar en cualquier momento modificaciones o actualizaciones a estos términos y
        condiciones, en atención de novedades legislativas, políticas internas o nuevos requerimientos para la prestación
        u ofrecimiento de nuestros servicios o productos. Estas modificaciones estarán disponibles al público a través de
        nuestra página de Internet, vía correo electrónico o por escrito.
              </p>

              <p>
        Atención y aclaraciones:<br>
        Teléfono: <a href="tel:+524423032668">01 (442) 303 2668</a> ·
        Correo: <a href="mailto:reservaciones@viajerocar-rental.mx">reservaciones@viajerocar-rental.mx</a>
              </p>
            </div>
          </div>
        </li>


        {{-- GARANTÍA DE RENTA O PRE-AUTORIZACIONES --}}
        <li class="policy-item">
          <button class="policy-head" type="button" aria-expanded="false">
            <div class="ph-left">
              <span class="ph-icon"><i class="fa-solid fa-credit-card"></i></span>
              <span class="ph-title">Garantía de renta o pre-autorizaciones</span>
            </div>
            <i class="fa-solid fa-chevron-down ph-caret"></i>
          </button>
          <div class="policy-body" aria-hidden="true">
            <div class="policy-content">

              <h4>Garantía de renta o pre-autorizaciones – Viajero Car Rental</h4>
              <p>
                La <strong>garantía de renta</strong> o <strong>pre-autorización</strong> es un monto que se retiene temporalmente en la
                tarjeta bancaria del cliente como respaldo por la renta del vehículo. Este monto <strong>no constituye un cargo
                definitivo</strong> al momento de abrir el contrato.
              </p>
              <p>
                Al finalizar la renta, <strong>Viajero Car Rental</strong> toma de esta pre-autorización el importe correspondiente a la
                renta y a los cargos adicionales autorizados (si los hubiera), y posteriormente <strong>libera el resto del monto
                retenido</strong>.
              </p>

              <h5>Monto de la garantía y tipo de cobertura</h5>
              <p>
                El <strong>tipo de protección o cobertura</strong> que el cliente elija determinará el <strong>monto de la pre-autorización</strong>
                que será requerido en la tarjeta bancaria. A mayor nivel de cobertura, menor será el nivel de responsabilidad
                económica del cliente ante daños o eventos cubiertos, y en consecuencia, puede variar el monto retenido.
              </p>

              <h5>Liberación de la pre-autorización</h5>
              <p>
                La liberación de la pre-autorización se realiza dentro de las <strong>48 horas hábiles</strong> siguientes a la devolución
                del vehículo, o en el tiempo que estipule el banco emisor de la tarjeta, siempre y cuando el automóvil sea
                devuelto en las mismas condiciones mecánicas, estéticas y funcionales en las que fue entregado, y con:
              </p>
              <ul>
                <li>Todas sus <strong>llaves</strong></li>
                <li><strong>Tarjeta de circulación</strong></li>
                <li><strong>Placas</strong></li>
                <li><strong>Herramientas y refacciones</strong> (gato, llave de tuercas, llanta de refacción, etc.)</li>
                <li>Cualquier otro <strong>accesorio</strong> entregado al inicio de la renta</li>
              </ul>

              <p>
                En caso de incidencias, daños, faltantes o violaciones a políticas (por ejemplo, limpieza, uso indebido, multas),
                el monto correspondiente podrá ser tomado parcial o totalmente de la pre-autorización, de acuerdo con lo
                estipulado en el contrato de renta.
              </p>

              <h5>Paquetes de protección y responsabilidad</h5>
              <p>
                <strong>Viajero Car Rental</strong> ofrece distintos <strong>paquetes de protección</strong> para que el cliente elija el que mejor se adapte
                a sus necesidades. La elección del paquete determinará:
              </p>
              <ul>
                <li>El <strong>monto de la garantía</strong> o pre-autorización.</li>
                <li>El <strong>nivel de responsabilidad</strong> económica del cliente ante daños al vehículo, robo parcial/total
                    o terceros, según las coberturas contratadas.</li>
              </ul>
              <p>
                Los montos específicos de garantía se informan al momento de cotizar y se detallan en el contrato de renta
                según el <strong>grupo de vehículo</strong> y el <strong>paquete de protección</strong> elegido.
              </p>

              <h5>Ejemplos referenciales de montos de pre-autorización por grupo de auto</h5>
              <p>
                Los siguientes montos son <strong>ejemplos referenciales</strong> y pueden variar según temporada, sucursal, promociones
                o cambios de política interna. El valor definitivo siempre se indicará antes de firmar el contrato.
              </p>

              {{-- TABLA DE GARANTÍAS --}}
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
                    <tr>
                    <td>C</td><td>Compacto Chevrolet aveo o similar</td><td>$MXN5,000</td><td>$MXN8,000</td><td>$MXN15,000</td><td>$MXN25,000</td><td>$MXN330,000</td>
                    </tr>

                    <tr>
                    <td>D</td><td>Medianos Nissan Virtus o similar</td><td>$MXN5,000</td><td>$MXN8,000</td><td>$MXN18,000</td><td>$MXN25,000</td><td>$MXN380,000</td>
                    </tr>

                    <tr>
                    <td>E</td><td>Grandes Volkswagen Jetta o similar</td><td>$MXN5,000</td><td>$MXN8,000</td><td>$MXN20,000</td><td>$MXN30,000</td><td>$MXN500,000</td>
                    </tr>

                    <tr>
                    <td>F</td><td>Full size Camry o similar</td><td>$MXN5,000</td><td>$MXN15,000</td><td>$MXN30,000</td><td>$MXN40,000</td><td>$MXN650,000</td>
                    </tr>

                    <tr>
                    <td>IC</td><td>Suv compacta Jeep Renegade o similar</td><td>$MXN5,000</td><td>$MXN8,000</td><td>$MXN20,000</td><td>$MXN30,000</td><td>$MXN500,000</td>
                    </tr>

                    <tr>
                    <td>I</td><td>Suv Mediana Kia Seltos o similar</td><td>$MXN5,000</td><td>$MXN10,000</td><td>$MXN30,000</td><td>$MXN40,000</td><td>$MXN600,000</td>
                    </tr>

                    <tr>
                    <td>IB</td><td>Suv Familiar compacta Toyota avanza o similar</td><td>$MXN5,000</td><td>$MXN8,000</td><td>$MXN18,000</td><td>$MXN25,000</td><td>$MXN400,000</td>
                    </tr>

                    <tr>
                    <td>M</td><td>Minivan Honda Odyssey o similar</td><td>$10,000</td><td>$MXN20,000</td><td>$MXN30,000</td><td>$MXN40,000</td><td>$MXN800,000</td>
                    </tr>

                    <tr>
                    <td>L</td><td>Pasajeros de 12 usuarios Toyota Hiace o similar</td><td>$MXN10,000</td><td>$MXN20,000</td><td>$MXN30,000</td><td>$MXN40,000</td><td>$MXN800,000</td>
                    </tr>

                    <tr>
                    <td>H</td><td>Pick up Doble Cabina Nissan Frontier o similar</td><td>$MXN10,000</td><td>$MXN20,000</td><td>$MXN30,000</td><td>$MXN40,000</td><td>$MXN600,000</td>
                    </tr>

                    <tr>
                    <td>HI</td><td>Pick up 4x4 Doble Cabina Toyota Tacoma o similar</td><td>$MXN10,000</td><td>$MXN20,000</td><td>$MXN30,000</td><td>$MXN40,000</td><td>$MXN900,000</td>
                    </tr>
                    </tbody>


                </table>
              </div>

              <p style="margin-top: 1rem;">
                <em>Montos sujetos a cambio sin previo aviso. Los importes definitivos se confirmarán en tu cotización y contrato de renta.</em>
              </p>

              <h5>Medios para realizar la garantía</h5>
              <p>
                El <strong>depósito en garantía o pre-autorización</strong> es un requisito obligatorio para efectuar la renta. En
                <strong>Viajero Car Rental</strong>, la garantía se realiza mediante:
              </p>
              <ul>
                <li><strong>Tarjetas de crédito bancarias</strong> (American Express, Visa, Mastercard).</li>
                <li>En algunos casos, se puede aceptar <strong>tarjeta de débito</strong> para la garantía, siempre que la institución
                    bancaria y el monto requerido lo permitan. Esto se confirmará al momento de la renta.</li>
              </ul>
              <p>
                Aunque aceptamos otros métodos de pago (efectivo, PayPal, depósitos en OXXO, Mercado Pago) para cubrir el
                <strong>costo de la renta</strong>, la <strong>garantía</strong> deberá hacerse siempre mediante una tarjeta bancaria válida y
                autorizada.
              </p>

              <h5>Importante</h5>
              <ul>
                <li>El cliente es responsable de contar con <strong>línea de crédito suficiente</strong> en su tarjeta para cubrir el
                    monto de la garantía.</li>
                <li>La no aprobación de la pre-autorización por parte del banco puede ser motivo para <strong>no completar la renta</strong>.</li>
                <li>Algunos grupos de vehículos de gama alta pueden requerir <strong>protecciones obligatorias</strong> con deducible y
                    montos de garantía mayores, lo cual será informado antes de la firma del contrato.</li>
              </ul>

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
