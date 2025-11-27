<svg
    viewBox="0 0 300 780"
    xmlns="http://www.w3.org/2000/svg"
    class="car-map">

    <!-- ==== IMAGEN DEL AUTO (TAMAÑO MEDIO) ==== -->
    <image
        href="{{ asset('img/diagrama-carro-danos3.png') }}"
        x="0"
        y="0"
        width="300"
        height="780"
        preserveAspectRatio="xMidYMid meet"
    />

    <!-- ========================================= -->
    <!--          ZONAS CLICKEABLES 1–18           -->
    <!-- ========================================= -->

    <!-- Cada zona es INVISIBLE pero marca daño al hacer clic -->

    <!-- 1 Defensa delantera -->
    <g class="zone" data-zone="1">
        <rect x="80" y="10" width="140" height="55" fill="transparent"/>
        <text x="150" y="40" text-anchor="middle" font-size="18" font-weight="700">1</text>
    </g>

    <!-- 2 Cofre -->
    <g class="zone" data-zone="2">
        <rect x="95" y="95" width="110" height="90" fill="transparent"/>
        <text x="150" y="145" text-anchor="middle" font-size="18" font-weight="700">2</text>
    </g>

    <!-- 14 Parte superior central -->
    <g class="zone" data-zone="14">
        <rect x="95" y="200" width="110" height="65" fill="transparent"/>
        <text x="150" y="235" text-anchor="middle" font-size="18" font-weight="700">14</text>
    </g>

    <!-- 5 Techo -->
    <g class="zone" data-zone="5">
        <rect x="80" y="280" width="140" height="135" fill="transparent"/>
        <text x="150" y="350" text-anchor="middle" font-size="18" font-weight="700">5</text>
    </g>

    <!-- 10 Parte trasera central -->
    <g class="zone" data-zone="10">
        <rect x="80" y="435" width="140" height="110" fill="transparent"/>
        <text x="150" y="490" text-anchor="middle" font-size="18" font-weight="700">10</text>
    </g>

    <!-- 13 Defensa trasera -->
    <g class="zone" data-zone="13">
        <rect x="80" y="595" width="140" height="65" fill="transparent"/>
        <text x="150" y="635" text-anchor="middle" font-size="18" font-weight="700">13</text>
    </g>

    <!-- LATERAL IZQUIERDO (3,6,8,11) -->
    <g class="zone" data-zone="3">
        <rect x="25" y="150" width="45" height="55" fill="transparent"/>
        <text x="47" y="185" text-anchor="middle" font-size="18" font-weight="700">3</text>
    </g>

    <g class="zone" data-zone="6">
        <rect x="25" y="220" width="45" height="65" fill="transparent"/>
        <text x="47" y="260" text-anchor="middle" font-size="18" font-weight="700">6</text>
    </g>

    <g class="zone" data-zone="8">
        <rect x="25" y="300" width="45" height="65" fill="transparent"/>
        <text x="47" y="340" text-anchor="middle" font-size="18" font-weight="700">8</text>
    </g>

    <g class="zone" data-zone="11">
        <rect x="25" y="380" width="45" height="75" fill="transparent"/>
        <text x="47" y="420" text-anchor="middle" font-size="18" font-weight="700">11</text>
    </g>

    <!-- LATERAL DERECHO (4,7,9,12) -->
    <g class="zone" data-zone="4">
        <rect x="230" y="150" width="45" height="55" fill="transparent"/>
        <text x="253" y="185" text-anchor="middle" font-size="18" font-weight="700">4</text>
    </g>

    <g class="zone" data-zone="7">
        <rect x="230" y="220" width="45" height="65" fill="transparent"/>
        <text x="253" y="260" text-anchor="middle" font-size="18" font-weight="700">7</text>
    </g>

    <g class="zone" data-zone="9">
        <rect x="230" y="300" width="45" height="65" fill="transparent"/>
        <text x="253" y="340" text-anchor="middle" font-size="18" font-weight="700">9</text>
    </g>

    <g class="zone" data-zone="12">
        <rect x="230" y="380" width="45" height="75" fill="transparent"/>
        <text x="253" y="420" text-anchor="middle" font-size="18" font-weight="700">12</text>
    </g>

    <!-- RUEDAS (15,16,17,18) -->
    <g class="zone" data-zone="15">
        <circle cx="60" cy="110" r="40" fill="transparent"/>
        <text x="60" y="115" text-anchor="middle" font-size="18" font-weight="700">15</text>
    </g>

    <g class="zone" data-zone="16">
        <circle cx="240" cy="110" r="40" fill="transparent"/>
        <text x="240" y="115" text-anchor="middle" font-size="18" font-weight="700">16</text>
    </g>

    <g class="zone" data-zone="17">
        <circle cx="60" cy="540" r="40" fill="transparent"/>
        <text x="60" y="545" text-anchor="middle" font-size="18" font-weight="700">17</text>
    </g>

    <g class="zone" data-zone="18">
        <circle cx="240" cy="540" r="40" fill="transparent"/>
        <text x="240" y="545" text-anchor="middle" font-size="18" font-weight="700">18</text>
    </g>

</svg>
