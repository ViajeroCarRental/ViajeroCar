<style>
    @page {
        margin:  100px 42px 65px 42px;
    }

    {
        box-sizing: border-box;
    }

    body {
        margin: 0;
        color: #1f2937;
        font-family: DejaVu Sans, sans-serif;
        font-size: 10.5px;
        line-height: 1.45;
    }

    .pdf-header {
        position: fixed;
        top: -72px;
        left: 0;
        right: 0;
        height: 68px;
        border-bottom: 2px solid #b91c1c;
        padding-bottom: 3.5px;
    }

    .header-table,
    .header-table tr,
    .header-table td {
        border: none;
        padding: 0;
    }

    .header-logo {
        width: 18%;
        vertical-align: middle;
    }

    .logo-img {
        width: 120px;
        height: auto;
    }

    .header-info {
        width: 62%;
        vertical-align: middle;
    }

    .pdf-title {
        color: #b91c1c;
        font-size: 16px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: .2px;
        line-height: 1;
    }

    .pdf-subtitle {
        margin-top: 3px;
        color: #111827;
        font-size: 9px;
        font-weight: bold;
        text-transform: uppercase;
        line-height: 1;
    }

    .company-data {
        margin-top: 3px;
        color: #4b5563;
        font-size: 7.8px;
        line-height: 1.05;
    }

    .header-folio {
        width: 20%;
        text-align: right;
        vertical-align: top;
    }

    .folio-label {
        color: #6b7280;
        font-size: 10px;
        text-transform: uppercase;
        line-height: 1;
    }

    .folio-number {
        margin-top: 4px;
        color: #b91c1c;
        font-size: 16px;
        font-weight: bold;
        line-height: 1;
    }

    .folio-date {
        margin-top: 8px;
        color: #374151;
        font-size: 10px;
        line-height: 1;
    }
    .brand-name {
        color: #111827;
        font-weight: bold;
    }

    .section {
        margin-bottom: 14px;
        page-break-inside: avoid;
    }

    .section-title {
        margin: 0 0 8px;
        padding: 7px 10px;
        color: #ffffff;
        background: #b91c1c;
        font-size: 11px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: .3px;
    }

    .box {
        border: 1px solid #d1d5db;
        padding: 10px;
        background: #ffffff;
    }

    .muted {
        color: #6b7280;
    }

    .small {
        font-size: 9px;
    }

    .text-center {
        text-align: center;
    }

    .text-right {
        text-align: right;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th {
        padding: 7px 6px;
        color: #ffffff;
        background: #991b1b;
        border: 1px solid #7f1d1d;
        font-size: 9px;
        text-transform: uppercase;
    }

    td {
        padding: 6px;
        border: 1px solid #d1d5db;
        vertical-align: top;
    }

    .info-table td:first-child {
        width: 28%;
        color: #374151;
        background: #f3f4f6;
        font-weight: bold;
    }

    .clause {
        margin-bottom: 8px;
        text-align: justify;
    }

    .clause-number {
        color: #b91c1c;
        font-weight: bold;
    }

    .signature-grid {
        width: 100%;
        margin-top: 34px;
    }

    .signature-cell {
        width: 33.33%;
        padding: 0 8px;
        text-align: center;
        vertical-align: bottom;
    }

    .signature-img{
    display:block;
    width:170px;
    height:70px;
    object-fit:contain;
    margin:0 auto 8px;
    }

    .signature-box{
        height:78px;
        display:flex;
        align-items:flex-end;
        justify-content:center;
    }

    .signature-line{
        border-top:1px solid #111827;
        padding-top:6px;
        margin-top:6px;
        font-size:9px;
        font-weight:bold;
    }

    .signature-role{
        font-size:8px;
        color:#6b7280;
        margin-top:2px;
    }

    .signature-line {
        border-top: 1px solid #111827;
        padding-top: 5px;
        font-size: 9px;
        font-weight: bold;
    }

    .pdf-footer {
        position: fixed;
        bottom: -48px;
        left: 0;
        right: 0;
        height: 34px;
        border-top: 1px solid #d1d5db;
        color: #6b7280;
        font-size: 7.8px;
        padding-top: 7px;
    }

    .footer-table,
    .footer-table tr,
    .footer-table td {
        border: none;
        padding: 0;
    }

    .footer-left {
        width: 60%;
        text-align: left;
    }

    .footer-right {
        width: 40%;
        text-align: right;
    }

    .page-break {
        page-break-after: always;
    }
    .client-table th {
        background: #b91c1c;
        color: #ffffff;
        font-size: 9px;
        text-align: center;
        letter-spacing: .4px;
    }

    .client-table td {
        font-size: 9px;
        padding: 6px 7px;
    }

    .client-table .label {
        width: 16%;
        background: #f3f4f6;
        color: #374151;
        font-weight: bold;
    }

    .client-table td:nth-child(2) {
        width: 38%;
    }

    .client-table td:nth-child(4) {
        width: 30%;
    }
    .rate-pdf-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .rate-pdf-table th {
        background: #b91c1c;
        color: #ffffff;
        border: 1px solid #7f1d1d;
        padding: 7px 5px;
        font-size: 8.5px;
        text-align: center;
        text-transform: uppercase;
    }

    .rate-pdf-table td {
        border: 1px solid #d1d5db;
        padding: 6px 5px;
        font-size: 8.7px;
        vertical-align: middle;
    }

    .rate-pdf-table tbody tr:nth-child(even) td {
        background: #f9fafb;
    }

    .rate-pdf-table .col-category {
        width: 24%;
        font-weight: bold;
    }

    .rate-pdf-table .col-money {
        width: 14%;
        text-align: right;
    }

    .rate-pdf-table .col-protection {
        width: 20%;
    }

    .rate-pdf-table .col-total {
        width: 14%;
        text-align: right;
        font-weight: bold;
        color: #991b1b;
    }

    .rate-note {
        margin-top: 8px;
        color: #6b7280;
        font-size: 8px;
        text-align: justify;
    }
    .rate-pdf-table {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .rate-pdf-table th {
        background: #b91c1c;
        color: #ffffff;
        border: 1px solid #7f1d1d;
        padding: 7px 5px;
        font-size: 8.5px;
        text-align: center;
        text-transform: uppercase;
    }

    .rate-pdf-table td {
        border: 1px solid #d1d5db;
        padding: 6px 5px;
        font-size: 8.7px;
        vertical-align: middle;
    }

    .rate-pdf-table tbody tr:nth-child(even) td {
        background: #f9fafb;
    }

    .rate-pdf-table .col-category {
        width: 24%;
        font-weight: bold;
    }

    .rate-pdf-table .col-money {
        width: 14%;
        text-align: right;
    }

    .rate-pdf-table .col-protection {
        width: 20%;
    }

    .rate-pdf-table .col-total {
        width: 14%;
        text-align: right;
        font-weight: bold;
        color: #991b1b;
    }

    .rate-note {
        margin-top: 8px;
        color: #6b7280;
        font-size: 8px;
        text-align: justify;
    }

</style>