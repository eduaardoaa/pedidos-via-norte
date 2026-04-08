<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Validação Facial - Vianorte</title>

    <link rel="preconnect" href="https://cdn.jsdelivr.net">
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
    <link rel="preload" as="script" href="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js">

    <style>
        :root{
            --bg-1:#08111f;
            --bg-2:#0d1728;
            --card:#16202d;
            --card-2:#1b2635;
            --border:rgba(255,255,255,.08);
            --text:#f3f4f6;
            --muted:#9ca3af;
            --green:#27c88a;
            --green-dark:#1fa06f;
            --blue-soft:rgba(59,130,246,.12);
            --blue-border:rgba(59,130,246,.25);
            --blue-text:#bfdbfe;
            --red-soft:rgba(239,68,68,.12);
            --red-border:rgba(239,68,68,.25);
            --red-text:#fecaca;
            --ok-soft:rgba(34,197,94,.12);
            --ok-border:rgba(34,197,94,.25);
            --ok-text:#bbf7d0;
        }

        *{
            box-sizing:border-box;
        }

        body{
            margin:0;
            min-height:100vh;
            font-family:Inter, Arial, Helvetica, sans-serif;
            color:var(--text);
            background:
                radial-gradient(circle at top right, rgba(16,185,129,.10), transparent 22%),
                linear-gradient(180deg, var(--bg-1) 0%, #07101b 100%);
        }

        .face-login-page{
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:24px 18px;
        }

        .face-login-shell{
            width:100%;
            max-width:760px;
        }

        .face-login-card{
            background:linear-gradient(180deg, var(--card) 0%, var(--card-2) 100%);
            border:1px solid var(--border);
            border-radius:24px;
            box-shadow:0 20px 60px rgba(0,0,0,.28);
            overflow:hidden;
        }

        .face-login-card-body{
            padding:24px;
        }

        .face-login-title{
            margin:0 0 18px;
            font-size:1.7rem;
            font-weight:800;
            text-align:center;
        }

        .face-camera-wrap{
            border-radius:20px;
            background:rgba(255,255,255,.025);
            border:1px solid var(--border);
            padding:18px;
        }

        .face-video{
            width:100%;
            min-height:420px;
            max-height:520px;
            border-radius:18px;
            background:#000;
            object-fit:cover;
            display:block;
            transform:scaleX(-1);
            -webkit-transform:scaleX(-1);
            backface-visibility:hidden;
        }

        .face-actions{
            display:flex;
            gap:12px;
            justify-content:center;
            flex-wrap:wrap;
            margin-top:16px;
        }

        .btn{
            border:none;
            border-radius:14px;
            min-height:48px;
            padding:12px 20px;
            font-size:1rem;
            font-weight:700;
            cursor:pointer;
            transition:.2s ease;
            text-decoration:none;
            display:inline-flex;
            align-items:center;
            justify-content:center;
        }

        .btn-dark{
            background:#1b2a3d;
            color:#fff;
            border:1px solid rgba(255,255,255,.08);
        }

        .btn-dark:hover{
            filter:brightness(1.08);
        }

        .btn-green{
            background:var(--green);
            color:#fff;
        }

        .btn-green:hover{
            background:var(--green-dark);
        }

        .btn:disabled{
            opacity:.7;
            cursor:not-allowed;
        }

        .face-status{
            margin-top:14px;
            padding:13px 14px;
            border-radius:14px;
            font-size:.96rem;
            display:block;
            text-align:center;
            background:var(--blue-soft);
            border:1px solid var(--blue-border);
            color:var(--blue-text);
            line-height:1.5;
        }

        .face-status.error{
            background:var(--red-soft);
            border-color:var(--red-border);
            color:var(--red-text);
        }

        .face-status.success{
            background:var(--ok-soft);
            border-color:var(--ok-border);
            color:var(--ok-text);
        }

        .alert-box{
            border-radius:14px;
            padding:14px 16px;
            margin-bottom:16px;
            border:1px solid;
        }

        .alert-box.error{
            background:var(--red-soft);
            border-color:var(--red-border);
            color:var(--red-text);
        }

        .face-hidden{
            display:none !important;
        }

        @media (max-width: 640px){
            .face-login-card-body{
                padding:16px;
            }

            .face-login-title{
                font-size:1.35rem;
            }

            .face-video{
                min-height:260px;
            }

            .face-actions{
                flex-direction:column;
            }

            .face-actions .btn{
                width:100%;
            }
        }
    </style>
</head>
<body>
<div class="face-login-page">
    <div class="face-login-shell">
        @if(session('error'))
            <div class="alert-box error">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert-box error">
                <ul style="margin:0;padding-left:18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="face-login-card">
            <div class="face-login-card-body">
                <h1 class="face-login-title">Validação Facial</h1>

                <div class="face-camera-wrap">
                    <video id="faceVideo" class="face-video" autoplay playsinline muted></video>

                    <div class="face-actions">
                        <button type="button" class="btn btn-green" id="validateFaceBtn">
                            Iniciar validação
                        </button>

                        <a href="{{ route('login') }}" class="btn btn-dark">
                            Voltar
                        </a>
                    </div>

                    <div id="faceStatus" class="face-status">
                        Preparando reconhecimento facial...
                    </div>

                    <form method="POST" action="{{ route('login.face.verify') }}" id="faceLoginForm" class="face-hidden">
                        @csrf
                        <input type="hidden" name="face_image" id="face_image">
                        <input type="hidden" name="face_distance" id="face_distance">
                    </form>

                    <canvas id="faceCanvas" class="face-hidden"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script defer src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
    const referenceDescriptorRaw = @json($pendingFaceDescriptor ?? null);

    const faceVideo = document.getElementById('faceVideo');
    const faceCanvas = document.getElementById('faceCanvas');
    const faceStatus = document.getElementById('faceStatus');
    const validateFaceBtn = document.getElementById('validateFaceBtn');
    const faceImageInput = document.getElementById('face_image');
    const faceDistanceInput = document.getElementById('face_distance');
    const faceLoginForm = document.getElementById('faceLoginForm');

    let stream = null;
    let modelsLoaded = false;
    let referenceDescriptor = null;
    let cameraStarted = false;
    let validatingNow = false;
    let booting = false;

    function setStatus(message, type = 'info') {
        faceStatus.className = 'face-status' + (type !== 'info' ? ' ' + type : '');
        faceStatus.textContent = message;
    }

    function loadReferenceDescriptorFromView() {
        if (!referenceDescriptorRaw) {
            throw new Error('Referência facial não encontrada.');
        }

        let parsed = referenceDescriptorRaw;

        if (typeof parsed === 'string') {
            parsed = JSON.parse(parsed);
        }

        if (!Array.isArray(parsed)) {
            throw new Error('Referência facial inválida.');
        }

        referenceDescriptor = new Float32Array(parsed);
    }

    async function loadModels() {
        if (modelsLoaded) {
            return true;
        }

        try {
            setStatus('Carregando reconhecimento facial...', 'info');

            await Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri('/public/face-api/models'),
                faceapi.nets.faceLandmark68Net.loadFromUri('/public/face-api/models'),
                faceapi.nets.faceRecognitionNet.loadFromUri('/public/face-api/models')
            ]);

            modelsLoaded = true;
            return true;
        } catch (error) {
            console.error(error);
            setStatus('Erro ao carregar reconhecimento facial', 'error');
            return false;
        }
    }

    async function waitVideoReady(video) {
        if (video.readyState >= 2 && video.videoWidth > 0 && video.videoHeight > 0) {
            return true;
        }

        return new Promise((resolve) => {
            const done = () => {
                video.removeEventListener('loadedmetadata', done);
                video.removeEventListener('canplay', done);
                resolve(true);
            };

            video.addEventListener('loadedmetadata', done, { once: true });
            video.addEventListener('canplay', done, { once: true });
        });
    }

    async function startCamera() {
        if (stream) {
            cameraStarted = true;
            validateFaceBtn.textContent = 'Tirar foto';
            setStatus('Clique em tirar foto', 'success');
            return true;
        }

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            setStatus('Câmera não suportada neste dispositivo', 'error');
            return false;
        }

        try {
            const okModels = await loadModels();

            if (!okModels) {
                return false;
            }

            if (!referenceDescriptor) {
                loadReferenceDescriptorFromView();
            }

            setStatus('Abrindo câmera...', 'info');

            stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: 'user',
                    width: { ideal: 640 },
                    height: { ideal: 480 }
                },
                audio: false
            });

            faceVideo.srcObject = stream;
            await waitVideoReady(faceVideo);

            try {
                await faceVideo.play();
            } catch (e) {
                console.warn('Falha ao dar play automático no vídeo.', e);
            }

            cameraStarted = true;
            validateFaceBtn.textContent = 'Tirar foto';
            setStatus('Clique em tirar foto', 'success');

            return true;
        } catch (error) {
            console.error(error);
            setStatus('Não foi possível abrir a câmera', 'error');
            return false;
        }
    }

    function drawMirroredVideoToCanvas() {
        const videoWidth = faceVideo.videoWidth;
        const videoHeight = faceVideo.videoHeight;

        faceCanvas.width = videoWidth;
        faceCanvas.height = videoHeight;

        const ctx = faceCanvas.getContext('2d');

        ctx.save();
        ctx.translate(videoWidth, 0);
        ctx.scale(-1, 1);
        ctx.drawImage(faceVideo, 0, 0, videoWidth, videoHeight);
        ctx.restore();
    }

    async function captureAndValidateFace() {
        if (validatingNow) {
            return;
        }

        if (!stream) {
            setStatus('Abra a câmera primeiro', 'error');
            return;
        }

        if (!referenceDescriptor) {
            setStatus('Referência facial inválida', 'error');
            return;
        }

        const videoWidth = faceVideo.videoWidth;
        const videoHeight = faceVideo.videoHeight;

        if (!videoWidth || !videoHeight) {
            setStatus('Câmera ainda não está pronta', 'error');
            return;
        }

        validatingNow = true;
        validateFaceBtn.disabled = true;
        validateFaceBtn.textContent = 'Validando...';

        drawMirroredVideoToCanvas();

        try {
            const detection = await faceapi
                .detectSingleFace(
                    faceCanvas,
                    new faceapi.TinyFaceDetectorOptions({
                        inputSize: 320,
                        scoreThreshold: 0.45
                    })
                )
                .withFaceLandmarks()
                .withFaceDescriptor();

            if (!detection) {
                faceImageInput.value = '';
                faceDistanceInput.value = '';
                validatingNow = false;
                validateFaceBtn.disabled = false;
                validateFaceBtn.textContent = 'Tirar foto';
                setStatus('Nenhum rosto detectado. Centralize melhor o rosto.', 'error');
                return;
            }

            const currentDescriptor = detection.descriptor;
            const distance = faceapi.euclideanDistance(referenceDescriptor, currentDescriptor);

            faceImageInput.value = faceCanvas.toDataURL('image/jpeg', 0.72);
            faceDistanceInput.value = Number(distance).toFixed(6);

            if (distance <= 0.58) {
                setStatus('Rosto validado. Entrando...', 'success');
                faceLoginForm.submit();
                return;
            }

            faceImageInput.value = '';
            faceDistanceInput.value = '';
            validatingNow = false;
            validateFaceBtn.disabled = false;
            validateFaceBtn.textContent = 'Tirar foto';
            setStatus('Rosto não condizente com o cadastro', 'error');
        } catch (error) {
            console.error(error);
            validatingNow = false;
            validateFaceBtn.disabled = false;
            validateFaceBtn.textContent = 'Tirar foto';
            setStatus('Erro ao validar rosto', 'error');
        }
    }

    async function bootstrapRecognition() {
        if (booting) {
            return;
        }

        booting = true;
        validateFaceBtn.disabled = true;

        try {
            loadReferenceDescriptorFromView();

            const okModels = await loadModels();

            if (!okModels) {
                validateFaceBtn.disabled = false;
                return;
            }

            setStatus('Reconhecimento pronto. Clique para abrir a câmera.', 'success');
            validateFaceBtn.disabled = false;
            validateFaceBtn.textContent = 'Iniciar validação';
        } catch (error) {
            console.error(error);
            setStatus('Não foi possível iniciar o reconhecimento facial', 'error');
            validateFaceBtn.disabled = false;
        } finally {
            booting = false;
        }
    }

    validateFaceBtn.addEventListener('click', async function () {
        if (!cameraStarted) {
            validateFaceBtn.disabled = true;
            const started = await startCamera();
            validateFaceBtn.disabled = false;

            if (!started) {
                validateFaceBtn.textContent = 'Tentar novamente';
            }

            return;
        }

        await captureAndValidateFace();
    });

    window.addEventListener('load', function () {
        bootstrapRecognition();
    });

    window.addEventListener('beforeunload', function () {
        if (stream) {
            stream.getTracks().forEach(track => track.stop());
        }
    });
</script>
</body>
</html>