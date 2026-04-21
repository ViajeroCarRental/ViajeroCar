@extends('layouts.Usuarios')
@section('Titulo', 'Home')
@section('css-vistaHome')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
    {{-- Select2 CSS --}}



    {{-- reemplaza visualmente  <select>. --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
        integrity="sha512-p+6F+H1G5p8pP/1hObu/YZ7o2aM5J5lFjAzU5e+0Jx8xR+uEzjFN8IvU3UpUy6v1k3vXv4+XzN0z3VQUpgK6Vw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
        integrity="sha512-p+6F+H1G5p8pP/1hObu/YZ7o2aM5J5lFjAzU5e+0Jx8xR+uEzjFN8IvU3UpUy6v1k3vXv4+XzN0z3VQUpgK6Vw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        :root {
            --brand: #b22222;
            --ink: #0f172a;
            --muted: #6b7280
        }

        .hero-badge-3msi {
            width: clamp(180px, 38vw, 520px);
            max-width: 100%;
            height: auto;
            margin-top: 10px;
            display: block;
            filter: drop-shadow(0 8px 24px rgba(0, 0, 0, .30));
            pointer-events: none;
        }

        /* ===== Banner Reservas (limpio, sin rayas) ===== */
        .rv-banner-wrap {
            position: fixed;
            top: 10px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 9999;
            width: min(1100px, 95%);
            pointer-events: none;
        }

        .rv-banner {
            display: none;
            pointer-events: auto;
            background: #fff;
            color: var(--ink);
            border-radius: 16px;
            box-shadow: 0 14px 40px rgba(0, 0, 0, .18);
            border: 1px solid rgba(0, 0, 0, .08);
            overflow: hidden;
        }

        .rv-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
        }

        .rv-car {
            width: 34px;
            height: 34px;
            border-radius: 10px;
            background: rgba(178, 34, 34, .10);
            display: grid;
            place-items: center;
            animation: rv-car-move 3s ease-in-out infinite;
        }

        .rv-car svg {
            color: var(--brand)
        }

        @keyframes rv-car-move {

            0%,
            100% {
                transform: translateX(0)
            }

            50% {
                transform: translateX(6px)
            }
        }

        .rv-live {
            font-weight: 600;
            color: var(--ink);
            font-size: 13px;
        }

        .rv-live::before {
            content: "";
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 999px;
            margin-right: 6px;
            background: #10b981;
            box-shadow: 0 0 0 0 rgba(16, 185, 129, .5);
            animation: rv-live 1.5s ease-out infinite;
        }

        @keyframes rv-live {
            0% {
                box-shadow: 0 0 0 0 rgba(16, 185, 129, .5)
            }

            70% {
                box-shadow: 0 0 0 10px rgba(16, 185, 129, 0)
            }

            100% {
                box-shadow: 0 0 0 0 rgba(16, 185, 129, 0)
            }
        }

        .rv-text {
            font-size: 13px;
            color: #3f3f46
        }

        .rv-count {
            font-weight: 700;
            color: var(--ink)
        }

        .rv-cta {
            background: var(--brand);
            color: #fff;
            border: 0;
            border-radius: 10px;
            padding: 8px 12px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 8px 20px rgba(178, 34, 34, .25);
            transition: opacity .2s ease;
        }

        .rv-cta:hover {
            opacity: .95
        }

        .rv-close {
            border: 0;
            background: transparent;
            color: #9ca3af;
            font-size: 18px;
            cursor: pointer;
        }

        .rv-close:hover {
            color: #374151
        }

        .rv-bar {
            height: 4px;
            width: 100%;
            background: #f1f5f9
        }

        .rv-bar i {
            display: block;
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, #b22222, #ef4444, #fb923c, #b22222);
            background-size: 300% 100%;
            animation: rv-bar-move 1.6s linear infinite;
        }

        @keyframes rv-bar-move {
            from {
                background-position: 0% 50%
            }

            to {
                background-position: 200% 50%
            }
        }

        .rv-in {
            animation: rv-drop .35s cubic-bezier(.2, .7, .2, 1) forwards
        }

        .rv-out {
            animation: rv-lift .28s ease forwards
        }

        @keyframes rv-drop {
            from {
                opacity: 0;
                transform: translateY(-10px) scale(.98)
            }

            to {
                opacity: 1;
                transform: translateY(0) scale(1)
            }
        }

        @keyframes rv-lift {
            from {
                opacity: 1;
                transform: translateY(0) scale(1)
            }

            to {
                opacity: 0;
                transform: translateY(-10px) scale(.98)
            }
        }

        @media (max-width:560px) {
            .rv-text {
                font-size: 12px
            }

            .rv-cta {
                display: none
            }
        }

        .vj-tiles-swiper {
            padding: 6px 10px 42px;
            width: min(1200px, 94%);
            margin: 28px auto;
        }

        .vj-tiles-swiper .swiper-slide {
            height: auto
        }

        .tile-card {
            background: #fff;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 18px 40px rgba(0, 0, 0, .18);
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .tile-card .tile-media {
            width: 100%;
            height: 230px;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .tile-card .tile-body {
            padding: 18px 20px 22px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            flex: 1
        }

        .tile-card h3 {
            margin: 0;
            font-size: 1.1rem;
            color: var(--brand);
            letter-spacing: .2px
        }

        .tile-card p {
            margin: 0;
            color: var(--ink);
            opacity: .85;
            line-height: 1.45
        }

        .tile-card .tile-link {
            margin-top: auto;
            font-weight: 600;
            color: var(--brand);
            text-decoration: none
        }

        .tile-card .tile-link:hover {
            text-decoration: underline
        }

        .vj-tiles-swiper .swiper-button-prev,
        .vj-tiles-swiper .swiper-button-next {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: #fff;
            box-shadow: 0 10px 26px rgba(0, 0, 0, .18);
        }

        .vj-tiles-swiper .swiper-button-prev:after,
        .vj-tiles-swiper .swiper-button-next:after {
            font-size: 18px;
            color: var(--brand);
        }

        .vj-tiles-swiper .swiper-pagination-bullet {
            opacity: .35;
            background: var(--brand)
        }

        .vj-tiles-swiper .swiper-pagination-bullet-active {
            opacity: 1;
            transform: scale(1.15)
        }

        .tile-card.tile-reviews .tile-body {
            gap: 12px;
        }

        .reviews-summary {
            display: flex;
            align-items: baseline;
            gap: 6px;
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--ink);
        }

        .reviews-score {
            font-size: 1.1rem;
            font-weight: 800;
            color: #f59e0b;
        }

        .reviews-count {
            font-size: 0.85rem;
            color: var(--muted);
        }

        .reviews-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
            max-height: 180px;
            overflow-y: auto;
            padding-right: 4px;
        }

        .review-item {
            background: #f9fafb;
            border-radius: 10px;
            padding: 8px 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, .04);
        }

        .review-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.85rem;
            margin-bottom: 4px;
        }

        .review-head strong {
            font-weight: 700;
            color: #111827;
        }

        .review-stars {
            font-size: 0.8rem;
            color: #f59e0b;
        }

        .review-text {
            margin: 0;
            font-size: 0.85rem;
            color: #374151;
            line-height: 1.4;
        }

        /* =========================
                  ✅ BURBUJA RADIAL
                ========================= */
        .social-fab {
            position: fixed;
            right: 18px;
            bottom: 18px;
            z-index: 9999;
            width: 64px;
            height: 64px;
        }

        .social-fab .fab-main {
            width: 64px;
            height: 64px;
            border-radius: 999px;
            border: 0;
            cursor: pointer;
            display: grid;
            place-items: center;
            color: #fff;
            background: radial-gradient(circle at 30% 30%, #ef4444, var(--brand));
            box-shadow: 0 18px 40px rgba(0, 0, 0, .22);
            transition: transform .18s ease, filter .18s ease;
        }

        .social-fab .fab-main:hover {
            transform: translateY(-2px);
            filter: brightness(1.02);
        }

        .social-fab .fab-item {
            position: absolute;
            right: 6px;
            bottom: 6px;
            width: 52px;
            height: 52px;
            border-radius: 999px;
            display: grid;
            place-items: center;
            color: #fff;
            text-decoration: none;
            box-shadow: 0 16px 30px rgba(0, 0, 0, .18);
            transform: translate(0, 0) scale(.7);
            opacity: 0;
            pointer-events: none;
            transition: transform .22s cubic-bezier(.2, .9, .2, 1), opacity .18s ease;
        }

        .fab-wp {
            background: #22c55e;
        }

        .fab-fb {
            background: #1877f2;
        }

        .fab-ig {
            background: radial-gradient(circle at 30% 30%, #f97316, #d946ef, #0ea5e9);
        }

        .social-fab.open .fab-wp {
            transform: translate(-82px, -8px) scale(1);
            opacity: 1;
            pointer-events: auto;
        }

        .social-fab.open .fab-fb {
            transform: translate(-58px, -72px) scale(1);
            opacity: 1;
            pointer-events: auto;
        }

        .social-fab.open .fab-ig {
            transform: translate(6px, -92px) scale(1);
            opacity: 1;
            pointer-events: auto;
        }

        .select2-container--default .select2-selection--single {
            display: flex;
            align-items: center;
            height: 48px;
            border-radius: 10px;
            padding: 0 10px;
        }

        .select2-container--open .select2-dropdown--above {
            top: 100% !important;
            bottom: auto !important;
        }

        .select2-selection__rendered {
            display: flex;
            align-items: center;
            line-height: normal !important;
        }

        .select2-selection__rendered i {
            margin-right: 8px;
            font-size: 1.2em;
        }

        .select2-results__option .icon-item i {
            margin-right: 8px;
            font-size: 1em;
        }

        .select2-container--default .select2-selection--single {
            display: flex;
            align-items: center;
            height: 48px;
            border-radius: 10px;
            padding: 0 10px;
        }

        .select2-selection__rendered {
            display: flex;
            align-items: center;
            line-height: normal !important;
        }

        .select2-selection__rendered i {
            margin-right: 8px;
            font-size: 1.2em;
        }

        .select2-results__option .icon-item i {
            margin-right: 8px;
            font-size: 1em;
        }

        .social-fab i {
            font-size: 20px;
        }
        }

        /* Botones de carrusel */
        .fleet-btn {
            background-color: #ff0000 !important;
            color: white !important;
            opacity: 1 !important;
            cursor: pointer;
            transition: all 0.3s ease;
            pointer-events: auto !important;
        }

        /* Estado DESACTIVADO  */
        .fleet-btn.is-disabled {
            background-color: #f1f5f9 !important;
            color: #cbd5e1 !important;
            cursor: not-allowed;
            box-shadow: none !important;
        }

        .pulse-animation {
            animation: pulseLimit 0.3s ease-in-out;
        }

        @keyframes pulseLimit {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(0.85);
            }

            100% {
                transform: scale(1);
            }
        }

        /* ========================
               ESTILOS PARA PRECIOS
            =========================== */
        .price-line {
            display: flex;
            align-items: baseline;
            flex-wrap: wrap;
            gap: 4px;
            margin: 12px 0;
        }

        .price-now-wrapper,
        .price-old-wrapper {
            display: inline-flex;
            align-items: baseline;
            gap: 0;
        }

        .currency-symbol {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--brand) !important;
            display: inline-block;
        }

        .price-now {
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--brand) !important;
            display: inline-block;
        }

        .currency-code {
            font-size: 0.85rem;
            font-weight: 500;
            color: var(--brand) !important;
            display: inline-block;
            margin-left: 0px;
            padding-left: 0px;
        }

        .per {
            font-size: 0.85rem;
            font-weight: 500;
            color: #6b7280 !important;
            display: inline-block;
            margin-left: 4px;
            margin-right: 2px;
        }

        .price-old-wrapper {
            display: inline-flex;
            align-items: baseline;
            gap: 0;
            margin-left: 4px;
        }

        .currency-symbol-old {
            font-size: 0.9rem;
            font-weight: 500;
            color: #9ca3af !important;
            display: inline-block;
        }

        .price-old {
            font-size: 0.9rem;
            font-weight: 500;
            text-decoration: line-through;
            color: #9ca3af !important;
            display: inline-block;
        }

        .currency-code-old {
            font-size: 0.75rem;
            font-weight: 500;
            color: #9ca3af !important;
            display: inline-block;
            margin-left: 0px;
            padding-left: 0px;
        }

        /* ============================================================
               MODAL MEMBRESÍA
            ============================================================ */
        .modal-membership {
            position: fixed;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 99999;
            visibility: hidden;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.25s ease, visibility 0.25s;
        }

        .modal-membership.show {
            visibility: visible;
            opacity: 1;
            pointer-events: auto;
        }

        .modal-membership-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
        }

        .modal-membership-card {
            position: relative;
            z-index: 1;
            background: #fff;
            border-radius: 24px;
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.3);
            width: min(480px, 92%);
            padding: 28px 24px 32px;
            text-align: center;
            border: 1px solid rgba(178, 34, 34, 0.15);
            transform: scale(0.95);
            transition: transform 0.25s ease;
        }

        .modal-membership.show .modal-membership-card {
            transform: scale(1);
        }

        .modal-membership-close {
            position: absolute;
            top: 14px;
            right: 14px;
            background: transparent;
            border: none;
            font-size: 22px;
            color: #9ca3af;
            cursor: pointer;
            transition: 0.2s;
        }

        .modal-membership-close:hover {
            color: var(--brand, #b22222);
        }

        .modal-membership-icon {
            width: 60px;
            height: 60px;
            background: rgba(178, 34, 34, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 18px;
        }

        .modal-membership-icon i {
            font-size: 26px;
            color: var(--brand, #b22222);
        }

        .modal-membership-card h3 {
            font-family: 'Playfair Display', serif;
            font-size: 26px;
            color: #0f172a;
            margin-bottom: 12px;
        }

        .modal-membership-card p {
            color: #4b5563;
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 20px;
            text-align: center;
            max-width: 420px;
            margin-left: auto;
            margin-right: auto;
        }

        .modal-membership-actions {
            display: flex;
            gap: 14px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-contact {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 22px;
            border-radius: 999px;
            font-weight: 600;
            font-size: 14px;
            text-decoration: none;
            transition: all 0.25s ease;
        }

        .btn-call {
            background: #0f172a;
            color: #fff;
        }

        .btn-call:hover {
            background: #6b7280;
            transform: translateY(-2px);
        }

        .btn-whatsapp {
            background: #b22222;
            color: #fff;
        }

        .btn-whatsapp:hover {
            background: #8b1d1a;
            transform: translateY(-2px);
        }

        .btn-contact i {
            font-size: 16px;
        }

        .modal-membership-phone {
            font-size: 12px;
            color: #6b7280;
            margin-top: 18px;
            border-top: 1px solid #eee;
            padding-top: 14px;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
            text-align: center;
        }

        @media (max-width: 560px) {
            .modal-membership-card {
                padding: 22px 18px 26px;
            }

            .modal-membership-card h3 {
                font-size: 22px;
            }
        }

        /* ============================================================
               BOTÓN INTERROGACIÓN
            ============================================================ */
        .btn-icon-question-corner {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(178, 34, 34, 0.1);
            border: 1px solid rgba(178, 34, 34, 0.25);
            border-radius: 50%;
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.25s ease;
            color: var(--brand, #b22222);
            font-size: 16px;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            z-index: 2;
        }

        .btn-icon-question-corner:hover {
            background: var(--brand, #b22222);
            border-color: var(--brand, #b22222);
            color: #fff;
            transform: scale(1.08);
            box-shadow: 0 6px 16px rgba(178, 34, 34, 0.25);
        }

        .info-content {
            position: relative;
            padding-top: 30px;
        }

        @media (max-width: 560px) {
            .btn-icon-question-corner {
                width: 32px;
                height: 32px;
                top: 12px;
                right: 12px;
                font-size: 14px;
            }

            .info-content {
                padding-top: 45px;
            }
        }
    </style>
@endsection

@section('contenidoHome')
    @push('head')
        <link rel="preload" as="image" href="{{ asset('img/inicio1.webp') }}" fetchpriority="high">
    @endpush

    @php
        $bookNowUrl = route('rutaReservasIniciar', ['from' => 'welcome']);

        $flota1 = [
            [
                'price' => 467,
                'old' => 899,
                'pct' => 48,
                'cat' => 'Compact',
                'model' => 'Chevrolet Aveo or similar | C',
                'img' => 'aveo.webp',
                'pass' => 5,
                'bags' => 2,
                'brief' => 1,
                'trans' => 'Automatic',
            ],
            [
                'price' => 600,
                'old' => 1049,
                'pct' => 43,
                'cat' => 'Intermediate',
                'model' => 'Volkswagen Virtus or similar | D',
                'img' => 'virtus.webp',
                'pass' => 5,
                'bags' => 2,
                'brief' => 2,
                'trans' => 'Automatic',
            ],
            [
                'price' => 800,
                'old' => 1199,
                'pct' => 33,
                'cat' => 'Full Size',
                'model' => 'Volkswagen Jetta or similar | E',
                'img' => 'jetta.webp',
                'pass' => 5,
                'bags' => 3,
                'brief' => 3,
                'trans' => 'Automatic',
            ],
            [
                'price' => 1550,
                'old' => 1999,
                'pct' => 22,
                'cat' => 'Full Size',
                'model' => 'Toyota Camry or similar | F',
                'img' => 'camry.webp',
                'pass' => 5,
                'bags' => 3,
                'brief' => 3,
                'trans' => 'Automatic',
            ],
            [
                'price' => 1600,
                'old' => 2100,
                'pct' => 24,
                'cat' => 'Compact SUV',
                'model' => 'Jeep Renegade or similar | IC',
                'img' => 'renegade.webp',
                'pass' => 5,
                'bags' => 2,
                'brief' => 3,
                'trans' => 'Automatic',
            ],
            [
                'price' => 1800,
                'old' => 2400,
                'pct' => 25,
                'cat' => 'Midsize SUV',
                'model' => 'Volkswagen Taos or similar | I',
                'img' => 'taos.webp',
                'pass' => 5,
                'bags' => 2,
                'brief' => 3,
                'trans' => 'Automatic',
            ],
        ];

        $flota2 = [
            [
                'price' => 1700,
                'old' => 2200,
                'pct' => 23,
                'cat' => 'Compact Family SUV',
                'model' => 'Toyota Avanza or similar | IB',
                'img' => 'avanza.png',
                'pass' => 7,
                'bags' => 2,
                'brief' => 2,
                'trans' => 'Automatic',
            ],
            [
                'price' => 2600,
                'old' => 3000,
                'pct' => 13,
                'cat' => 'Minivan',
                'model' => 'Honda Odyssey or similar | M',
                'img' => 'Odyssey.png',
                'pass' => 8,
                'bags' => 8,
                'brief' => 4,
                'trans' => 'Automatic',
            ],
            [
                'price' => 2900,
                'old' => 3400,
                'pct' => 15,
                'cat' => 'Passenger Van',
                'model' => 'Nissan Urvan or similar | L | MT',
                'img' => 'Urvan.png',
                'pass' => 13,
                'bags' => 5,
                'brief' => 5,
                'trans' => 'Standard',
            ],
            [
                'price' => 2900,
                'old' => 9400,
                'pct' => 15,
                'cat' => 'Passenger Van',
                'model' => 'Toyota Hiace or similar | L',
                'img' => 'Hiace.png',
                'pass' => 13,
                'bags' => 3,
                'brief' => 3,
                'trans' => 'Standard',
            ],
            [
                'price' => 1950,
                'old' => 2203,
                'pct' => 13,
                'cat' => 'Double Cab Pickup',
                'model' => 'Nissan Frontier or similar | E',
                'img' => 'Frontier.png',
                'pass' => 5,
                'bags' => 3,
                'brief' => 3,
                'trans' => 'Automatic',
            ],
            [
                'price' => 2600,
                'old' => 3000,
                'pct' => 13,
                'cat' => '4x4 Double Cab Pickup',
                'model' => 'Toyota Tacoma or similar | F',
                'img' => 'Tacoma.png',
                'pass' => 5,
                'bags' => 3,
                'brief' => 3,
                'trans' => 'Automatic',
            ],
        ];

        $tiles = [
            [
                'img' => '24.jpg',
                'title' => 'Available 24/7',
                'body' =>
                    'Service and support at all times. Our team is available 24 hours a day, 7 days a week, so you can travel with peace of mind.',
                'link' => '#',
                'linkText' => 'Read more...',
            ],
            [
                'img' => '4x4.png',
                'title' => '4x4 Cars & Trucks',
                'body' =>
                    'Travel without limits. We have SUVs, off-road vehicles and 4x4 trucks ideal for highways, city or adventure.',
                'link' => route('rutaCatalogo'),
                'linkText' => 'Explore our fleet...',
            ],
            [
                'img' => 'Urvancard.png',
                'title' => '13-Passenger Vans',
                'body' => 'Perfect for family or business trips. Comfort, space and safety for all your companions.',
                'link' => route('rutaReservasIniciar', ['from' => 'welcome']),
                'linkText' => 'Book yours...',
            ],
            [
                'img' => 'tarjeta.png',
                'title' => 'We accept cards',
                'body' =>
                    'Pay with credit or debit card. Easy, fast and secure. You can also make your final payment when returning your vehicle.',
                'link' => route('rutaReservasIniciar', ['from' => 'welcome']),
                'linkText' => 'Learn about our options...',
            ],
            [
                'img' => 'Aeropuerto.png',
                'title' => '24/7 Airport Delivery',
                'body' =>
                    'Pick up or drop off your car directly at the airport, no lines or waiting. Available 24 hours a day.',
                'link' => route('rutaReservasIniciar', ['from' => 'welcome']),
                'linkText' => 'Schedule delivery...',
            ],
            [
                'img' => 'Verificacion.png',
                'title' => '00 Verified Vehicles',
                'body' =>
                    'All our cars meet environmental standards and are 00 verified to ensure optimal performance.',
                'link' => route('rutaCatalogo'),
                'linkText' => 'Discover more...',
            ],
            [
                'img' => 'Drop.png',
                'title' => 'Nationwide Drop-off',
                'body' =>
                    'Enjoy your trip without worries. Return your car in another city with our Nationwide Drop-off service (additional cost applies).',
                'link' => route('rutaReservasIniciar', ['from' => 'welcome']),
                'linkText' => 'Check destinations...',
            ],
            [
                'img' => 'nuevos.png',
                'title' => 'New & Modern Cars',
                'body' =>
                    'Drive with style and safety. Our fleet consists of recent vehicles, always in optimal condition.',
                'link' => route('rutaCatalogo'),
                'linkText' => 'Explore the fleet...',
            ],
        ];
    @endphp

    {{-- ===== MACRO INLINE: render de carrusel de flota ===== --}}
    @php
        function renderFlota(array $autos, string $carouselId, string $bookNowUrl): string
        {
            $html = '<section id="' . $carouselId . '" class="fleet">';
            $html .= '<div class="fleet-viewport" id="' . $carouselId . '-viewport">';
            $html .=
                '<button class="fleet-btn prev" aria-label="Anterior"><i class="fa-solid fa-chevron-left"></i></button>';
            $html .= '<div class="fleet-track">';

            foreach ($autos as $a) {
                $imgUrl = asset('img/' . $a['img']);
                $html .=
                    '
        <article class="car-card" data-price-mxn="' .
                    $a['price'] .
                    '" data-old-price-mxn="' .
                    $a['old'] .
                    '">
          <header class="car-title">
            <h3>' .
                    e(__($a['cat'])) .
                    '</h3>
            <p>' .
                    e(__($a['model'])) .
                    '</p>
          </header>
          <div class="car-media">
            <img src="' .
                    $imgUrl .
                    '" alt="' .
                    e(__($a['model'])) .
                    '" loading="lazy" decoding="async" width="340" height="200">
          </div>
          <div class="offer">
            <span class="offer-badge" aria-label="Oferta">-' .
                    $a['pct'] .
                    '%</span>
            <div class="price-line">
              <span class="price-now-wrapper"><span class="currency-symbol">$</span><span class="price-now">' .
                    $a['price'] .
                    '</span></span>
              <span class="currency-code">MXN</span>
              <span class="per">' .
                    e(__('/day')) .
                    '</span>
              <span class="price-old-wrapper"><span class="currency-symbol-old">$</span><span class="price-old">' .
                    $a['old'] .
                    '</span></span>
              <span class="currency-code-old">MXN</span>
            </div>
          </div>
          <ul class="car-specs">
            <li><i class="fa-solid fa-user-large"></i> ' .
                    $a['pass'] .
                    '</li>
            <li><i class="fa-solid fa-suitcase-rolling"></i> ' .
                    $a['bags'] .
                    '</li>
            <li><i class="fa-solid fa-briefcase"></i> ' .
                    $a['brief'] .
                    '</li>
            <li title="' .
                    e(__('Transmission')) .
                    '"><span class="spec-letter">T | ' .
                    e(__($a['trans'])) .
                    '</span></li>
            <li title="' .
                    e(__('Air conditioning')) .
                    '"><i class="fa-regular fa-snowflake"></i><span class="spec-letter">A/C</span></li>
          </ul>
          <div class="car-connect">
            <span class="badge-chip badge-apple" title="Apple CarPlay">
              <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="5"/><polygon points="10,8 16,12 10,16"/></svg>
              CarPlay
            </span>
            <span class="badge-chip badge-android" title="Android Auto">
              <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3 L20 19 H16.8 L12 10.2 L7.2 19 H4 L12 3 Z"/></svg>
              Android Auto
            </span>
          </div>
          <a href="' .
                    e($bookNowUrl) .
                    '" class="car-cta">' .
                    e(__('Book now')) .
                    '</a>
        </article>';
            }

            $html .= '</div>';
            $html .=
                '<button class="fleet-btn next" aria-label="Siguiente"><i class="fa-solid fa-chevron-right"></i></button>';
            $html .= '</div></section>';
            return $html;
        }
    @endphp

    <section class="v-inicio" data-title="Inicio">

        {{-- ===== BANNER RESERVAS EN VIVO ===== --}}
        <div class="rv-banner-wrap" id="rvWrap" aria-live="polite">
            <div class="rv-banner" id="rvBanner" role="status" aria-label="Reservas en vivo">
                <div class="rv-bar"><i id="rvBar"></i></div>
                <div class="rv-row">
                    <div class="rv-car" aria-hidden="true">
                        <svg width="18" height="18" viewBox="0 0 24 24">
                            <path fill="currentColor"
                                d="M5 11l1-3.2A2 2 0 0 1 7.9 6h8.2a2 2 0 0 1 1.9 1.8L20 11v5a1 1 0 0 1-1 1h-1a1.5 1.5 0 0 1 0-3h1v-1H5v1h1a1.5 1.5 0 1 1 0 3H5a1 1 0 0 1-1-1v-5Zm3.2-3a.8.8 0 0 0-.77.6L6.9 10h10.2l-.53-1.4a.8.8 0 0 0-.77-.6H8.2Z" />
                        </svg>
                    </div>
                    <div class="rv-copy" style="flex:1 1 auto">
                        <div class="rv-live" id="rvTitle">{{ __('Searching for booking') }}</div>
                        <div class="rv-text" id="rvMsg">{{ __('Someone else is looking for a reservation right now') }}
                        </div>
                    </div>
                    <button class="rv-cta"
                        onclick="location.href='{{ route('rutaReservaciones') }}'">{{ __('Check availability') }}</button>
                    <button class="rv-close" id="rvClose" aria-label="{{ __('Close') }}">✕</button>
                </div>
            </div>
        </div>

        {{-- ===== HERO ===== --}}
        <section class="hero" id="heroTop">
            <div class="carousel">
                <div class="slide active" style="background-image:url('{{ asset('img/inicio1.webp') }}');"></div>
                <div class="slide" data-src="{{ asset('img/inicio2.webp') }}"></div>
                <div class="slide" data-src="{{ asset('img/inicio3.webp') }}"></div>
                <div class="overlay"></div>
            </div>

            <div class="hero-copy">
                <h2 class="kicker">{{ __('Rent your car with Viajero') }}</h2>

                <div class="hero-icons">
                    @foreach ([['fa-regular fa-clock', 'Active 24 hours a day, 7 days a week'], ['fa-regular fa-credit-card', 'We accept debit and credit cards'], ['fa-solid fa-shield-halved', 'We have 00 verification'], ['fa-solid fa-plane-departure', '24/7 Airport Assistance'], ['fa-solid fa-car-side', 'Cars with recent models']] as [$icon, $label])
                        <div class="icon-item">
                            <i class="{{ $icon }}"></i>
                            <span>{{ __($label) }}</span>
                        </div>
                    @endforeach
                </div>

                {{-- Botón abrir buscador (solo móvil) --}}
                <div class="d-block d-xl-none p-3">
                    <div
                        style="background:white;padding:15px;border-radius:8px;box-shadow:0 4px 6px rgba(0,0,0,.1);text-align:center;">
                        <p style="margin-bottom:10px;font-weight:bold;color:#333;font-size:16px;">
                            {{ __('Find your car here') }}</p>
                        <button type="button" id="btn-abrir-buscador" class="btn btn-danger w-100"
                            style="background-color:#d4002a;border:none;font-weight:700;height:55px;font-size:18px;display:flex;align-items:center;justify-content:center;gap:8px;text-transform:uppercase;border-radius:6px;">
                            <i class="fa-solid fa-magnifying-glass" style="font-size:16px;"></i> {{ __('Search') }}
                        </button>
                    </div>
                </div>

                {{-- ===== FORMULARIO BUSCADOR ===== --}}
                <div class="search-card" id="miBuscador">
                    <div class="d-block d-xl-none text-end mb-3">
                        <button type="button" id="btn-cerrar-buscador" class="btn-close"
                            style="font-size:1.5rem;"></button>
                        <h5 class="text-start mt-2">{{ __('1 Location & date') }}</h5>
                        <hr>
                    </div>

                    <form id="rentalForm" class="search-form" method="GET" action="{{ route('rutaReservasIniciar') }}"
                        novalidate>
                        @csrf
                        <input type="hidden" name="step" value="2">

                        <div class="search-grid">

                            {{-- Columna 1: Ubicaciones --}}
                            <div class="sg-col sg-col-location">
                                <div class="location-head">
                                    <span class="field-title"
                                        data-i18n="rental_location">{{ __('Pick-up location') }}</span>
                                    <label class="inline-check" for="differentDropoff">
                                        <input type="checkbox" id="differentDropoff" name="different_dropoff"
                                            value="1">
                                        <span>{{ __('Different return location') }}</span>
                                    </label>
                                </div>

                                <div class="location-inputs-wrapper" id="locationInputsWrapper">
                                    {{-- Pickup --}}
                                    <div class="field icon-field">
                                        <span class="field-icon"><i class="fa-solid fa-location-dot"></i></span>
                                        <select id="pickupPlace" name="pickup_sucursal_id">
                                            <option value="" disabled selected data-i18n="where_start">
                                                {{ __('Where does your trip begin?') }}</option>
                                            @foreach ($ciudades->where('nombre', 'Querétaro') as $ciudad)
                                                <optgroup
                                                    label="{{ $ciudad->nombre }}{{ $ciudad->estado ? ' — ' . $ciudad->estado : '' }}">
                                                    @foreach ($ciudad->sucursalesActivas as $suc)
                                                        <option value="{{ $suc->id_sucursal }}"
                                                            @selected(request('pickup_sucursal_id') == $suc->id_sucursal)>
                                                            {{ $suc->nombre }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Dropoff --}}
                                    <div class="field icon-field" id="dropoffWrapper">
                                        <span class="field-icon"><i class="fa-solid fa-location-dot"></i></span>
                                        <select id="dropoffPlace" name="dropoff_sucursal_id" class="no-scroll-trap">
                                            <option value="" disabled selected>{{ __('Where does your trip end?') }}
                                            </option>
                                            @foreach ($ciudades as $ciudad)
                                                <optgroup
                                                    label="{{ $ciudad->nombre }}{{ $ciudad->estado ? ' — ' . $ciudad->estado : '' }}">
                                                    @foreach ($ciudad->sucursalesActivas as $suc)
                                                        <option value="{{ $suc->id_sucursal }}"
                                                            @selected(request('dropoff_sucursal_id') == $suc->id_sucursal)>
                                                            {{ $suc->nombre }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- Columna 2: Fechas y horas --}}
                            <div class="sg-col sg-col-datetime">
                                @foreach ([['pickup', 'Pick-up', 'pickup_date', 'pickup_time'], ['dropoff', 'Return', 'dropoff_date', 'dropoff_time']] as [$prefix, $label, $dateField, $timeField])
                                    <div class="field">
                                        <span class="field-title solo-responsivo-izq">{{ __($label) }}</span>
                                        <div class="datetime-row">
                                            <div class="dt-field icon-field">
                                                <span class="field-icon"><i
                                                        class="fa-regular fa-calendar-days"></i></span>
                                                <input id="{{ $prefix }}Date" name="{{ $dateField }}"
                                                    type="text" placeholder="{{ __('Date') }}"
                                                    value="{{ request($dateField) }}"
                                                    data-min="{{ now()->toDateString() }}">
                                            </div>
                                            <div class="dt-field icon-field time-field">
                                                <span class="field-icon"><i class="fa-regular fa-clock"></i></span>
                                                <input type="text" id="{{ $prefix }}Time"
                                                    name="{{ $timeField }}" placeholder="{{ __('Time') }}"
                                                    value="{{ request($timeField) }}">
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            {{-- Columna 3: Botón --}}
                            <div class="sg-col sg-col-submit">
                                <div class="actions">
                                    <button type="submit">
                                        <i class="fa-solid fa-magnifying-glass"></i>
                                        <span data-i18n="search">{{ __('Search') }}</span>
                                    </button>
                                </div>
                            </div>

                        </div>

                        <div id="rangeSummary" class="range-summary" aria-live="polite">
                            @if (request('pickup_date') && request('dropoff_date'))
                                {{ request('pickup_date') }} - {{ request('dropoff_date') }}
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </section>

        <span id="heroEndSentinel" style="position:relative;display:block;width:1px;height:1px;"></span>

        {{-- ===== FLOTA 1 ===== --}}
        {!! renderFlota($flota1, 'fleet-carousel', $bookNowUrl) !!}

        <div class="fleet-meta" aria-label="Beneficios">
            <span>{{ __('Unlimited mileage') }}</span>
            <i class="sep" aria-hidden="true">|</i>
            <span>{{ __('Automatic transmission') }}</span>
        </div>

        {{-- ===== SECCIONES INFO ===== --}}
        <section class="info-sections">
            <div class="info-row">
                <div class="info-media media-carousel" data-interval="4500">
                    <div class="media-slide active" style="background-image:url('{{ asset('img/inicio4.webp') }}');">
                    </div>
                    <div class="media-slide" style="background-image:url('{{ asset('img/inicio5.webp') }}');"></div>
                    <div class="media-slide" style="background-image:url('{{ asset('img/inicio6.webp') }}');"></div>
                </div>
                <div class="info-content">
                    <button type="button" class="btn-icon-question-corner" id="openMembershipModalBtn"
                        aria-label="Más información sobre membresías">
                        <i class="fa-solid fa-question"></i>
                    </button>
                    <h2>{{ __('Frequent traveler?') }}</h2>
                    <p>{{ __('Become a member and unlock exclusive discounts on all your bookings, making every trip more affordable.') }}
                    </p>
                    <p>{{ __('Earn points for every rental and redeem them for discounts, upgrades, or special experiences.') }}
                    </p>
                    <p>{{ __('Activate your membership today and enjoy exclusive rewards.') }}</p>
                    <div class="cta-group" style="align-items:center;gap:12px;">
                        <button type="button" class="btn btn-primary" id="openMembershipModalFromBtn">
                            <i class="fa-solid fa-id-card"></i> {{ __('Get your membership') }}
                        </button>
                    </div>
                </div>
            </div>

            {{-- ===== FLOTA 2 ===== --}}
            {!! renderFlota($flota2, 'fleet-carousel-2', $bookNowUrl) !!}

            <div class="fleet-meta" aria-label="Beneficios">
                <span>{{ __('Unlimited mileage') }}</span>
                <i class="sep" aria-hidden="true">|</i>
                <span>{{ __('Automatic transmission') }}</span>
            </div>

            <div class="info-row reverse">
                <div class="info-content">
                    <h2>{{ __('Business solutions') }}</h2>
                    <p>{{ __('We manage your corporate travel end-to-end so your team can focus on what matters.') }}</p>
                    <p>{{ __('Optimize costs, comfort and safety with our corporate plans.') }}</p>
                    <div class="cta-group">
                        <a href="{{ route('rutaReservaciones', ['from' => 'welcome']) }}" class="btn btn-primary">
                            <i class="fa-regular fa-calendar-check"></i> {{ __('Book now') }}
                        </a>
                    </div>
                </div>
                <div class="info-media media-carousel" data-interval="5200">
                    <div class="media-slide active" style="background-image:url('{{ asset('img/inicio7.webp') }}');">
                    </div>
                    <div class="media-slide" style="background-image:url('{{ asset('img/inicio8.webp') }}');"></div>
                    <div class="media-slide" style="background-image:url('{{ asset('img/inicio9.webp') }}');"></div>
                </div>
            </div>
        </section>

        {{-- ===== SWIPER TILES ===== --}}
        <section aria-label="Explora destinos y servicios">
            <div class="swiper vj-tiles-swiper">
                <div class="swiper-wrapper">

                    @foreach ($tiles as $tile)
                        <div class="swiper-slide">
                            <article class="tile-card">
                                <div class="tile-media"
                                    style="background-image:url('{{ asset('img/' . $tile['img']) }}')">
                                </div>
                                <div class="tile-body">
                                    <h3>{{ __($tile['title']) }}</h3>
                                    <p>{{ __($tile['body']) }}</p>
                                    <a href="{{ $tile['link'] }}" class="tile-link">{{ __($tile['linkText']) }}</a>
                                </div>
                            </article>
                        </div>
                    @endforeach

                    {{-- Tile especial: Reseñas Google --}}
                    <div class="swiper-slide">
                        <article class="tile-card tile-reviews">
                            <div class="tile-media" style="background-image:url('{{ asset('img/Prioridad.webp') }}')">
                            </div>
                            <div class="tile-body">
                                <h3>{{ __('Google Maps Reviews') }}</h3>
                                @if (!empty($googleRating))
                                    <div class="reviews-summary">
                                        <span class="reviews-score">⭐ {{ number_format($googleRating, 1) }}</span>
                                        @if (!empty($googleTotal))
                                            <span class="reviews-count">({{ $googleTotal }} {{ __('reviews') }})</span>
                                        @endif
                                    </div>
                                @endif
                                <div class="reviews-list">
                                    @forelse ($googleReviews ?? collect() as $review)
                                        <div class="review-item">
                                            <div class="review-head">
                                                <strong>{{ $review['author_name'] ?? __('Google User') }}</strong>
                                                @if (!empty($review['rating']))
                                                    <span
                                                        class="review-stars">{{ str_repeat('★', (int) $review['rating']) }}</span>
                                                @endif
                                            </div>
                                            <p class="review-text">
                                                {{ \Illuminate\Support\Str::limit($review['text'] ?? '', 120) }}</p>
                                        </div>
                                    @empty
                                        <div class="review-item">
                                            <p class="review-text">
                                                {{ __('Soon you will see our customers reviews on Google Maps.') }}</p>
                                        </div>
                                    @endforelse
                                </div>
                                <a href="https://www.google.com/maps/place/VIAJERO+CAR+RENTAL+Centro+Sur" target="_blank"
                                    rel="noopener" class="tile-link">
                                    {{ __('See more reviews on Google...') }}
                                </a>
                            </div>
                        </article>
                    </div>

                </div>
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>
                <div class="swiper-pagination"></div>
            </div>
        </section>

        {{-- ===== CTA FINAL ===== --}}
        <section class="cta-hero">
            <div class="cta-bg" style="background-image:url('{{ asset('img/inicio10.webp') }}');"></div>
            <div class="cta-overlay"></div>
            <div class="cta-inner">
                <h2>{{ __('RENT TODAY, EXPLORE TOMORROW, TRAVEL FOREVER!') }}</h2>
                <a href="{{ $bookNowUrl }}" class="btn btn-primary btn-lg">
                    <i class="fa-regular fa-calendar-check"></i> {{ __('Book now') }}
                </a>
            </div>
        </section>

    </section>

    {{-- ===== MODAL MEMBRESÍA ===== --}}
    <div class="modal-membership" id="membershipModal" aria-hidden="true">
        <div class="modal-membership-backdrop"></div>
        <div class="modal-membership-card" role="dialog" aria-modal="true" aria-labelledby="membershipModalTitle">
            <button class="modal-membership-close" id="closeMembershipModalBtn" aria-label="{{ __('Close') }}">
                <i class="fa-regular fa-circle-xmark"></i>
            </button>
            <div class="modal-membership-icon"><i class="fa-regular fa-id-card"></i></div>
            <h3 id="membershipModalTitle">{{ __('Need more information?') }}</h3>
            <p>{{ __('To get more information about memberships and receive personalized assistance, contact us directly by phone or send a message to our WhatsApp number.') }}
            </p>
            <div class="modal-membership-actions">
                <a href="tel:+524427169793" class="btn-contact btn-call"><i class="fa-solid fa-phone"></i>
                    {{ __('Call us') }}</a>
                <a href="https://wa.me/5214427169793" target="_blank" rel="noopener" class="btn-contact btn-whatsapp">
                    <i class="fa-brands fa-whatsapp"></i> WhatsApp
                </a>
            </div>
            <p class="modal-membership-phone">
                <i class="fa-regular fa-clock"></i> {{ __('Monday to Sunday | 8:00 AM - 10:00 PM') }}
            </p>
        </div>
    </div>

    {{-- ===== BURBUJA REDES SOCIALES ===== --}}
    <div class="social-fab" id="socialFab">
        <button class="fab-main" id="fabMain" type="button" aria-label="{{ __('Social media') }}"
            aria-expanded="false">
            <i class="fa-solid fa-share-nodes"></i>
        </button>
        <a class="fab-item fab-wp" href="https://wa.me/5214427169793" target="_blank" rel="noopener"
            aria-label="WhatsApp">
            <i class="fa-brands fa-whatsapp"></i>
        </a>
        <a class="fab-item fab-fb" href="https://www.facebook.com/ViajeroCarRentalQueretaro?locale=es_LA" target="_blank"
            rel="noopener" aria-label="Facebook">
            <i class="fa-brands fa-facebook-f"></i>
        </a>
        <a class="fab-item fab-ig" href="https://www.instagram.com/viajerocarental/" target="_blank" rel="noopener"
            aria-label="Instagram">
            <i class="fa-brands fa-instagram"></i>
        </a>
    </div>

    {{-- ===== MODAL BIENVENIDA ===== --}}
    <div class="modal" id="welcomeModal" aria-hidden="true">
        <div class="modal-backdrop"></div>
        <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="wmTitle">
            <button class="modal-close" id="wmClose" aria-label="{{ __('Close') }}">
                <i class="fa-regular fa-circle-xmark"></i>
            </button>
            <h3 id="wmTitle">
                <i class="fa-regular fa-hand-peace"></i> {{ __('Welcome') }},
                <span id="wmName">{{ __('Traveler') }}</span>!
            </h3>
            <p>{{ __('Your account is ready. Do you want to go directly to your reservation?') }}</p>
            <div class="modal-actions">
                <a href="{{ route('rutaReservaciones') }}" class="btn btn-primary">
                    <i class="fa-regular fa-calendar-check"></i> {{ __('Go to my reservation') }}
                </a>
                <button class="btn btn-ghost" id="wmOk" type="button">{{ __('Stay on homepage') }}</button>
            </div>
        </div>
    </div>

@endsection

@section('js-vistaHome')

    {{-- ✅ jQuery  Permite que Select2 funcione --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    {{-- ✅ Swiper JS --}}
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

    {{-- ✅ Flatpickr core + locale ES + rangePlugin --}}
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/rangePlugin.js"></script>

    {{-- ✅ Select2 JS  Convierte el select en avanzado --}}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    {{-- ✅ Inicializar Select2 básico --}}
    <script>
        $(document).ready(function() {
            $('#pickupPlace').select2({
                width: '100%'
            });
            $('#dropoffPlace').select2({
                width: '100%'
            });
        });
    </script>

    <script src="{{ asset('js/iconos-lugar.js') }}"></script>
    <script src="{{ asset('js/home.js') }}"></script>

    {{-- ✅ (Tu inline script de fleet infinito - lo dejo tal cual) --}}

    <!-- ===== TOAST DE RESERVAS BILINGÜE ===== -->
    <script>
        (function() {
                // Detectar idioma actual
                const idiomaActual = localStorage.getItem('idiomaPreferido') || 'es';

                // SECUENCIA DE MENSAJES EN ESPAÑOL E INGLÉS
                const SEQ = {
                    'es': [{
                            title: "Buscando reserva",
                            text: "Alguien más está buscando reserva en este momento"
                        },
                        {
                            title: "Otra reserva",
                            text: "Un cliente acaba de reservar en el Aeropuerto de Querétaro (AIQ)"
                        },
                        {
                            title: "Buscando reserva",
                            text: "Alguien más está buscando reserva en este momento"
                        },
                        {
                            title: "Otra reserva",
                            text: "Un cliente acaba de reservar en la Central de Autobuses de Querétaro (TAQ)"
                        },
                        {
                            title: "Buscando reserva",
                            text: "Alguien más está buscando reserva en este momento"
                        },
                        {
                            title: "Otra reserva",
                            text: "Un cliente acaba de reservar en la Plaza de Central Park Querétaro"
                        }
                    ],
                    'en': [{
                            title: "Searching for booking",
                            text: "Someone else is looking for a reservation right now"
                        },
                        {
                            title: "Another booking",
                            text: "A customer just booked at Querétaro Airport (AIQ)"
                        },
                        {
                            title: "Searching for booking",
                            text: "Someone else is looking for a reservation right now"
                        },
                        {
                            title: "Another booking",
                            text: "A customer just booked at Querétaro Bus Station (TAQ)"
                        },
                        {
                            title: "Searching for booking",
                            text: "Someone else is looking for a reservation right now"
                        },
                        {
                            title: "Another booking",
                            text: "A customer just booked at Plaza Central Park Querétaro"
                        }
                    ]
                };

                const SHOW_MS = 7000;
                const HIDE_MS = 25000;
                const INITIAL_DELAY_MS = 10000;
                const START_INDEX = 5;

                const banner = document.getElementById('rvBanner');
                const bar = document.getElementById('rvBar');
                const title = document.getElementById('rvTitle');
                const msg = document.getElementById('rvMsg');
                const close = document.getElementById('rvClose');

                let idx = START_INDEX,
                    loop = true,
                    hideT = null,
                    nextT = null,
                    startT = null;
                let paused = false,
                    startTs = 0,
                    remaining = SHOW_MS;

                // Función para obtener el mensaje según el idioma actual
                function getMensajeActual() {
                    const idioma = localStorage.getItem('idiomaPreferido') || 'es';
                    return SEQ[idioma] || SEQ['es'];
                }

                function setBar(ms) {
                    if (!bar) return;
                    bar.style.transition = 'none';
                    bar.style.width = '0%';

                    requestAnimationFrame(() => {
                        requestAnimationFrame(() => {
                            bar.style.transition = `width ${ms}ms linear`;
                            bar.style.width = '100%';
                        });
                    });
                }

                function showOnce() {
                    if (!banner || !title || !msg) return;

                    const mensajes = getMensajeActual();
                    const item = mensajes[idx];
                    idx = (idx + 1) % mensajes.length;

                    title.textContent = item.title;
                    msg.textContent = item.text;

                    banner.style.display = 'block';
                    banner.classList.remove('rv-out');
                    banner.classList.add('rv-in');

                    remaining = SHOW_MS;
                    startTs = performance.now();
                    setBar(SHOW_MS);

                    if (hideT) clearTimeout(hideT);
                    hideT = setTimeout(hide, SHOW_MS);
                }

                function hide() {
                    if (!banner) return;

                    banner.classList.remove('rv-in');
                    banner.classList.add('rv-out');

                    setTimeout(() => {
                        banner.style.display = 'none';
                        if (loop) {
                            nextT = setTimeout(showOnce, HIDE_MS);
                        }
                    }, 260);
                }

                if (banner) {
                    banner.addEventListener('mouseenter', () => {
                        paused = true;
                        const elapsed = performance.now() - startTs;
                        remaining = Math.max(0, SHOW_MS - elapsed);

                        if (hideT) {
                            clearTimeout(hideT);
                            hideT = null;
                        }

                        if (bar) {
                            const progress = ((SHOW_MS - remaining) / SHOW_MS) * 100;
                            bar.style.transition = 'none';
                            bar.style.width = `${progress}%`;
                        }
                    });

                    banner.addEventListener('mouseleave', () => {
                        if (!paused) return;
                        paused = false;

                        setTimeout(() => {
                            setBar(remaining);
                            hideT = setTimeout(hide, remaining);
                            startTs = performance.now() - (SHOW_MS - remaining);
                        }, 30);
                    });
                }

                if (close) {
                    close.addEventListener('click', () => {
                        loop = false;

                        if (hideT) clearTimeout(hideT);
                        if (nextT) clearTimeout(nextT);
                        if (startT) clearTimeout(startT);

                        banner.style.display = 'none';
                    });
                }

                // Escuchar cambios de idioma para actualizar las notificaciones
                window.addEventListener('storage', function(e) {
                        if (e.key === 'idiomaPreferido') {
                            // Reiniciar el ciclo con el nuevo idioma
                            loop = true;
                            if (hideT) clearTimeout(hideT);
                            if (nextT) clearTimeout(nextT);
                            showOnce();
                        }
                    }
                });

            // Iniciar
            document.addEventListener('DOMContentLoaded', () => {
                startT = setTimeout(showOnce, INITIAL_DELAY_MS);
            });

        })();
    </script>
    <script>
        window.iconosPorId = {
            @foreach ($ciudades as $ciudad)
                @foreach ($ciudad->sucursalesActivas as $suc)
                    @php
                        $name = strtolower($suc->nombre);
                        $icon = 'fa-building';

                        if (str_contains($name, 'aeropuerto')) {
                            $icon = 'fa-plane-departure';
                        } elseif (str_contains($name, 'central') && !str_contains($name, 'plaza central park')) {
                            $icon = 'fa-bus';
                        } elseif (str_contains($name, 'terminal')) {
                            $icon = 'fa-bus';
                        } else {
                            $icon = 'fa-building';
                        }
                    @endphp
                    {{ $suc->id_sucursal }}: '{{ $icon }}',
                @endforeach
            @endforeach
        };
    </script>

    <script>
        /* ============================================================
               CONVERSIÓN DE MONEDA CON INDICADORES MXN/USD
            ============================================================ */
        (function() {
            "use strict";

            const EXCHANGE_RATE = 20;

            function getCurrentLanguage() {
                return localStorage.getItem('idiomaPreferido') || 'es';
            }

            function getCurrencyCode(language) {
                return language === 'en' ? 'USD' : 'MXN';
            }

            function formatAmount(amount, currencyCode) {
                if (currencyCode === 'USD') {
                    return amount.toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                } else {
                    return amount.toLocaleString('es-MX', {
                        minimumFractionDigits: 0,
                        maximumFractionDigits: 0
                    });
                }
            }

            function convertPrices() {
                const language = getCurrentLanguage();
                const currencyCode = getCurrencyCode(language);

                console.log('🔄 Convirtiendo precios a:', currencyCode);

                const carCards = document.querySelectorAll('.car-card');

                carCards.forEach((card) => {
                    const priceMXN = parseFloat(card.dataset.priceMxn);
                    const oldPriceMXN = parseFloat(card.dataset.oldPriceMxn);

                    if (isNaN(priceMXN)) return;

                    let displayPrice, displayOldPrice;

                    if (currencyCode === 'USD') {
                        displayPrice = priceMXN / EXCHANGE_RATE;
                        if (!isNaN(oldPriceMXN)) {
                            displayOldPrice = oldPriceMXN / EXCHANGE_RATE;
                        }
                    } else {
                        displayPrice = priceMXN;
                        displayOldPrice = oldPriceMXN;
                    }

                    const formattedPrice = formatAmount(displayPrice, currencyCode);
                    const formattedOldPrice = formatAmount(displayOldPrice, currencyCode);

                    const priceNowSpan = card.querySelector('.price-now');
                    const priceOldSpan = card.querySelector('.price-old');
                    const currencyCodeSpan = card.querySelector('.currency-code');
                    const currencyCodeOldSpan = card.querySelector('.currency-code-old');

                    if (priceNowSpan) {
                        priceNowSpan.textContent = formattedPrice;
                    }
                    if (priceOldSpan && !isNaN(displayOldPrice)) {
                        priceOldSpan.textContent = formattedOldPrice;
                    }
                    if (currencyCodeSpan) {
                        currencyCodeSpan.textContent = currencyCode;
                    }
                    if (currencyCodeOldSpan) {
                        currencyCodeOldSpan.textContent = currencyCode;
                    }
                });

                console.log('💰 Moneda actual:', currencyCode);
            }

            function initCurrencyConversion() {
                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', convertPrices);
                } else {
                    convertPrices();
                }

                window.addEventListener('storage', function(e) {
                    if (e.key === 'idiomaPreferido') {
                        setTimeout(convertPrices, 100);
                    }
                });

                document.addEventListener('click', function(e) {
                    const langBtn = e.target.closest('.lang-btn, .dropdown-item[href*="/lang/"]');
                    if (langBtn) {
                        setTimeout(convertPrices, 300);
                    }
                });

                const observer = new MutationObserver(function() {
                    const lang = document.documentElement.lang;
                    if (lang === 'en' || lang === 'es') {
                        convertPrices();
                    }
                });
                observer.observe(document.documentElement, {
                    attributes: true,
                    attributeFilter: ['lang']
                });
            }

            initCurrencyConversion();
        })();
    </script>

    <script>
        /* ============================================================
               MODAL MEMBRESÍA
            ============================================================ */
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('membershipModal');
            const openBtn = document.getElementById('openMembershipModalBtn');
            const openBtnMain = document.getElementById('openMembershipModalFromBtn');
            const closeBtn = document.getElementById('closeMembershipModalBtn');

            if (!modal || !closeBtn) return;

            function openModal() {
                modal.classList.add('show');
                document.body.style.overflow = 'hidden';
            }

            function closeModal() {
                modal.classList.remove('show');
                document.body.style.overflow = '';
            }

            if (openBtn) openBtn.addEventListener('click', openModal);
            if (openBtnMain) openBtnMain.addEventListener('click', openModal);
            if (closeBtn) closeBtn.addEventListener('click', closeModal);

            modal.addEventListener('click', function(event) {
                if (event.target === modal || event.target.classList.contains(
                        'modal-membership-backdrop')) {
                    closeModal();
                }
            });

            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape' && modal.classList.contains('show')) {
                    closeModal();
                }
            });
        });
    </script>

@endsection
