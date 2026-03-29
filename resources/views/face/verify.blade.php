@extends('layouts.app')

@section('title', 'Validação Facial')

@section('content')
<style>
    .face-card{
        max-width:900px;
        margin:0 auto;
    }

    .face-grid{
        display:grid;
        grid-template-columns:1.2fr .8fr;
        gap:20px;
        align-items:start;
    }

    .face-camera-box,
    .face-preview-box{
        border-radius:18px;
        overflow:hidden;
        background:rgba(255,255,255,.03);
        border:1px solid rgba(255,255,255,.08);
        padding:16px;
    }

    .face-video,
    .face-canvas-preview{
        width:100%;
        max-height:460px;
        border-radius:14px;
        background:#000;
        object-fit:cover;
    }

    .face-preview-placeholder{
        min-height:320px;
        display:flex;
        align-items:center;
        justify-content:center;
        text-align:center;
        color:var(--muted);
        padding:20px;
        border-radius:14px;
        background:rgba(255,255,255,.025);
        border:1px dashed rgba(255,255,255,.08);
    }

    .face-actions{
        display:flex;
        gap:12px;
        flex-wrap:wrap;
        margin-top:18px;
    }

    .face-status{
        margin-top:14px;
        padding:12px 14px;
        border-radius:12px;
        font-size:.95rem;
        display:none;
    }

    .face-status.info{
        display:block;
        background:rgba(59,130,246,.12);
        color:#bfdbfe;
        border:1px solid rgba(59,130,246,.25);
    }

    .face-status.error{
        display:block;
        background:rgba(239,68,68,.12);
        color:#fecaca;
        border:1px solid rgba(239,68,68,.25);
    }

    .face-status.success{
        display:block;
        background:rgba(34,197,94,.12);
        color:#bbf7d0;
        border:1px solid rgba(34,197,94,.25);
    }

    .face-hidden{
        display:none;
    }

    @media (max-width: 900px){
        .face-grid{
            grid-template-columns:1fr;
        }
    }
</style>

<div class="page-head">
    <div>
        <h2>Validação Facial</h2>
        <p>Faça a validação do seu rosto para acessar o sistema.</p>
    </div>
</div>

@if ($errors->any())
    <div class="alert-error-box">
        <ul style="margin:0;padding-left:18px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card face-card">
    <div class="card-body">
        <div class="face-grid">
            <div class="face-camera-box">
                <div class="card-title" style="margin-bottom:12px;">Câmera</div>
                <video id="faceVideo" class="face-video" autoplay playsinline muted></video>

                <div class="face-actions">
                    <button type="button" class="btn btn-dark" id="startCameraBtn">
                        Abrir câmera
                    </button>

                    <button type="button" class="btn btn-green" id="captureFaceBtn">
                        Validar rosto
                    </button>
                </div>

                <div id="faceStatus" class="face-status info">
                    Abra a câmera e faça a validação facial.
                </div>
            </div>

            <div class="face-preview-box">
                <div class="card-title" style="margin-bottom:12px;">Pré-visualização</div>

                <div id="previewPlaceholder" class="face-preview-placeholder">
                    Nenhuma captura realizada ainda.
                </div>

                <canvas id="faceCanvas" class="face-canvas-preview face-hidden"></canvas>
            </div>
        </div>

        <form method="POST" action="{{ route('face.verify.submit') }}" id="faceVerifyForm" style="margin-top:20px;">
            @csrf

            <input type="hidden" name="face_image" id="face_image">
            <input type="hidden" name="face_distance" id="face_distance">

            <div class="actions-inline" style="margin-top:18px;">
                <button type="submit" class="btn btn-green" id="submitVerifyBtn">
                    Confirmar validação
                </button>
            </div>
        </form>
    </div>
</div>

<script defer src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
    const referenceDescriptor = @json(optional(auth()->user())->face_descriptor);

    const faceVideo = document.getElementById('faceVideo');
    const faceCanvas = document.getElementById('faceCanvas');
    const previewPlaceholder = document.getElementById('previewPlaceholder');
    const faceStatus = document.getElementById('faceStatus');
    const startCameraBtn = document.getElementById('startCameraBtn');
    const captureFaceBtn = document.getElementById('captureFaceBtn');
    const submitVerifyBtn = document.getElementById('submitVerifyBtn');
    const faceImageInput = document.getElementById('face_image');
    const faceDistanceInput = document.getElementById('face_distance');
    const faceVerifyForm = document.getElementById('faceVerifyForm');

    let stream = null;
    let modelsLoaded = false;

    function setStatus(message, type = 'info') {
        faceStatus.className = 'face-status ' + type;
        faceStatus.textContent = message;
    }

    async function loadModels() {
        if (modelsLoaded) {
            return;
        }

        setStatus('Carregando recursos de reconhecimento facial...', 'info');

        await faceapi.nets.tinyFaceDetector.loadFromUri('/face-api/models');
        await faceapi.nets.faceLandmark68Net.loadFromUri('/face-api/models');
        await faceapi.nets.faceRecognitionNet.loadFromUri('/face-api/models');

        modelsLoaded = true;
        setStatus('Recursos carregados. Abra a câmera.', 'success');
    }

    async function startCamera() {
        await loadModels();

        if (stream) {
            return;
        }

        try {
            stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: 'user',
                    width: { ideal: 640 },
                    height: { ideal: 480 }
                },
                audio: false
            });

            faceVideo.srcObject = stream;
            setStatus('Câmera pronta. Faça a validação facial.', 'success');
        } catch (error) {
            console.error(error);
            setStatus('Não foi possível acessar a câmera. Permita o uso da câmera no navegador.', 'error');
        }
    }

    function parseReferenceDescriptor() {
        try {
            const parsed = JSON.parse(referenceDescriptor);
            return new Float32Array(parsed);
        } catch (error) {
            return null;
        }
    }

    async function validateFace() {
        if (!stream) {
            setStatus('Abra a câmera antes de validar.', 'error');
            return;
        }

        const storedDescriptor = parseReferenceDescriptor();

        if (!storedDescriptor) {
            setStatus('Não foi possível carregar a referência facial do usuário.', 'error');
            return;
        }

        const videoWidth = faceVideo.videoWidth;
        const videoHeight = faceVideo.videoHeight;

        if (!videoWidth || !videoHeight) {
            setStatus('A câmera ainda não está pronta. Tente novamente.', 'error');
            return;
        }

        faceCanvas.width = videoWidth;
        faceCanvas.height = videoHeight;

        const ctx = faceCanvas.getContext('2d');
        ctx.drawImage(faceVideo, 0, 0, videoWidth, videoHeight);

        const detection = await faceapi
    .detectSingleFace(faceCanvas, new faceapi.TinyFaceDetectorOptions({
        inputSize: 320,
        scoreThreshold: 0.5
    }))
            .withFaceLandmarks()
            .withFaceDescriptor();

        if (!detection) {
            faceImageInput.value = '';
            faceDistanceInput.value = '';
            previewPlaceholder.classList.remove('face-hidden');
            faceCanvas.classList.add('face-hidden');
            setStatus('Nenhum rosto foi detectado. Tente novamente.', 'error');
            return;
        }

        const currentDescriptor = detection.descriptor;
        const distance = faceapi.euclideanDistance(storedDescriptor, currentDescriptor);

        faceImageInput.value = faceCanvas.toDataURL('image/jpeg', 0.92);
        faceDistanceInput.value = distance;

        previewPlaceholder.classList.add('face-hidden');
        faceCanvas.classList.remove('face-hidden');

        if (distance <= 0.58) {
            setStatus('Rosto validado com sucesso. Agora confirme a validação.', 'success');
        } else {
            setStatus('O rosto capturado não corresponde ao cadastro. Tente novamente.', 'error');
        }
    }

    startCameraBtn.addEventListener('click', startCamera);
    captureFaceBtn.addEventListener('click', validateFace);

    faceVerifyForm.addEventListener('submit', function (event) {
        if (!faceImageInput.value || !faceDistanceInput.value) {
            event.preventDefault();
            setStatus('Faça a validação facial antes de continuar.', 'error');
            return;
        }

        submitVerifyBtn.disabled = true;
        submitVerifyBtn.innerHTML = 'Validando...';
    });

    window.addEventListener('beforeunload', function () {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }
    });
</script>
@endsection