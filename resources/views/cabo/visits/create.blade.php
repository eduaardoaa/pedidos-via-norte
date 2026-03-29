@extends('layouts.app')

@section('title', 'Registrar Visita')
@section('pageTitle', 'Registrar Visita')
@section('pageDescription', 'Registre sua presença no local usando a localização atual.')

@section('content')
<style>
    .visit-register-wrapper{
        max-width: 760px;
        margin: 0 auto;
    }

    .visit-register-card{
        padding: 24px;
    }

    .visit-status-box{
        margin-top: 16px;
        padding: 14px 16px;
        border-radius: 12px;
        background: #f5f7fb;
        color: #334155;
        font-size: .96rem;
        line-height: 1.5;
    }

    .visit-status-box.error{
        background: #fef2f2;
        color: #991b1b;
    }

    .visit-status-box.success{
        background: #ecfdf5;
        color: #065f46;
    }

    .visit-actions{
        display:flex;
        gap:12px;
        flex-wrap:wrap;
        margin-top:18px;
    }

    .visit-coords{
        margin-top: 12px;
        font-size: .95rem;
        color: var(--muted);
    }

    .visit-submit-btn{
        min-width: 220px;
        transition: .2s ease;
    }

    .visit-submit-btn:disabled{
        opacity:.65;
        cursor:not-allowed;
    }

    .visit-field{
        margin-top: 18px;
        position: relative;
        z-index: 1;
    }

    .visit-field label{
        display:block;
        font-weight:800;
        margin-bottom:8px;
        color:#fff;
    }

    .visit-textarea{
        display:block;
        width:100%;
        min-height:140px;
        resize:vertical;
        padding:14px 16px;
        border-radius:14px;
        border:1px solid rgba(255,255,255,.12);
        background:rgba(255,255,255,.04);
        color:#fff;
        font-size:.96rem;
        line-height:1.5;
        outline:none;
        box-sizing:border-box;
        appearance:none;
        -webkit-appearance:none;
        position:relative;
        z-index:3;
        pointer-events:auto;
        user-select:text;
    }

    .visit-textarea::placeholder{
        color:rgba(255,255,255,.45);
    }

    .visit-textarea:focus{
        border-color:#3b82f6;
        box-shadow:0 0 0 3px rgba(59,130,246,.18);
        background:rgba(255,255,255,.06);
    }

    .visit-field-help{
        margin-top:8px;
        font-size:.9rem;
        color:var(--muted);
        display:flex;
        justify-content:space-between;
        gap:12px;
        flex-wrap:wrap;
    }

    @media (max-width: 768px){
        .visit-register-card{
            padding: 18px;
        }

        .visit-submit-btn{
            width:100%;
        }

        .visit-field-help{
            flex-direction:column;
            align-items:flex-start;
        }
    }
</style>

<div class="visit-register-wrapper">
    @if(session('success'))
        <div class="alert-success-box" style="margin-bottom:16px;">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert-error-box" style="margin-bottom:16px;">
            <ul style="margin:0; padding-left:18px;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card visit-register-card">
        <div class="card-body">
            <h3 style="margin-top:0; margin-bottom:10px;">Registrar visita com GPS</h3>
            <p style="margin-top:0; color:var(--muted);">
                Antes de registrar, descreva obrigatoriamente o que foi feito na visita. Depois disso, o sistema irá capturar sua localização e salvar o registro.
            </p>

            <form method="POST" action="{{ route('cabo.visits.store') }}" id="visitForm" novalidate>
                @csrf

                <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
                <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">

                <div class="visit-field">
                    <label for="service_report">
                        O que foi feito na visita? <span style="color:#ef4444;">*</span>
                    </label>

                    <textarea
    name="service_report"
    id="service_report"
    class="visit-textarea"
    placeholder="Ex.: Realizei vistoria do local, conversei com o responsável, conferi a situação da equipe e repassei as orientações necessárias..."
    required
    maxlength="5000"
    autocomplete="off"
    spellcheck="true"
    oninput="updateVisitCounter()"
>{{ old('service_report') }}</textarea>

                    <div class="visit-field-help">
                        <span>Esse campo é obrigatório para liberar o botão de registro.</span>
                        <span id="reportCounter">0 / 5000</span>
                    </div>
                </div>

                <div class="visit-actions">
                    <button
    type="button"
    class="btn btn-green visit-submit-btn"
    id="registerVisitBtn"
    onclick="handleVisitRegister()"
>
    Registrar visita
</button>
                </div>
            </form>

            <div id="visitStatus" class="visit-status-box" style="display:none;"></div>
            <div class="visit-coords" id="coordsPreview" style="display:none;"></div>
        </div>
    </div>
</div>
<script>
    let visitRequestingLocation = false;

    function getVisitEls() {
        return {
            form: document.getElementById('visitForm'),
            button: document.getElementById('registerVisitBtn'),
            latitude: document.getElementById('latitude'),
            longitude: document.getElementById('longitude'),
            status: document.getElementById('visitStatus'),
            coords: document.getElementById('coordsPreview'),
            report: document.getElementById('service_report'),
            counter: document.getElementById('reportCounter')
        };
    }

    function setVisitStatus(message, type = '') {
        const { status } = getVisitEls();

        status.style.display = 'block';
        status.className = 'visit-status-box' + (type ? ' ' + type : '');
        status.textContent = message;
    }

    function clearVisitStatus() {
        const { status } = getVisitEls();

        status.style.display = 'none';
        status.className = 'visit-status-box';
        status.textContent = '';
    }

    function updateVisitCounter() {
        const { report, counter } = getVisitEls();

        if (!report || !counter) {
            return;
        }

        counter.textContent = `${report.value.length} / 5000`;

        if (report.value.trim().length > 0) {
            clearVisitStatus();
        }
    }

    function resetVisitLocationFields() {
        const { latitude, longitude, coords } = getVisitEls();

        latitude.value = '';
        longitude.value = '';
        coords.style.display = 'none';
        coords.innerHTML = '';
    }

    function setVisitButtonLoading(loading) {
        const { button } = getVisitEls();

        visitRequestingLocation = loading;
        button.disabled = loading;
        button.textContent = loading ? 'Obtendo localização...' : 'Registrar visita';
    }

    function showVisitCoords(lat, lon) {
        const { coords } = getVisitEls();

        coords.style.display = 'block';
        coords.innerHTML = `
            <strong>Latitude:</strong> ${lat}<br>
            <strong>Longitude:</strong> ${lon}
        `;
    }

    function handleVisitRegister() {
        const { form, report, latitude, longitude } = getVisitEls();

        if (visitRequestingLocation) {
            return;
        }

        if (!report.value.trim()) {
            setVisitStatus('Você precisa escrever o que foi feito na visita antes de registrar.', 'error');
            report.focus();
            return;
        }

        if (!navigator.geolocation) {
            setVisitStatus('Seu dispositivo ou navegador não suporta geolocalização.', 'error');
            return;
        }

        resetVisitLocationFields();
        setVisitButtonLoading(true);
        setVisitStatus('Solicitando sua localização...');

        navigator.geolocation.getCurrentPosition(
            function (position) {
                const lat = position.coords.latitude;
                const lon = position.coords.longitude;

                latitude.value = lat;
                longitude.value = lon;

                showVisitCoords(lat, lon);
                setVisitStatus('Localização obtida com sucesso. Registrando visita...', 'success');

                form.submit();
            },
            function (error) {
                resetVisitLocationFields();

                let message = 'Não foi possível obter sua localização.';

                if (error && error.code === 1) {
                    message = 'Permissão de localização negada. Autorize a localização para registrar a visita.';
                } else if (error && error.code === 2) {
                    message = 'A localização não está disponível no momento.';
                } else if (error && error.code === 3) {
                    message = 'O tempo para obter a localização expirou. Tente novamente.';
                }

                setVisitStatus(message, 'error');
                setVisitButtonLoading(false);
            },
            {
                enableHighAccuracy: true,
                timeout: 20000,
                maximumAge: 0
            }
        );
    }

    function resetVisitScreen() {
        setVisitButtonLoading(false);
        resetVisitLocationFields();
        updateVisitCounter();
    }

    window.addEventListener('load', function () {
        resetVisitScreen();
    });

    window.addEventListener('pageshow', function () {
        resetVisitScreen();
    });
</script>
@endsection