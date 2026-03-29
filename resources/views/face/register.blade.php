<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Cadastro Facial - Vianorte</title>

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

        *{ box-sizing:border-box; }

        body{
            margin:0;
            min-height:100vh;
            font-family:Inter, Arial, Helvetica, sans-serif;
            color:var(--text);
            background:
                radial-gradient(circle at top right, rgba(16,185,129,.10), transparent 22%),
                linear-gradient(180deg, var(--bg-1) 0%, #07101b 100%);
        }

        .face-page{
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            padding:32px 20px;
        }

        .face-shell{
            width:100%;
            max-width:780px;
        }

        .face-title{
            margin:0 0 10px;
            font-size:2rem;
            font-weight:800;
        }

        .face-subtitle{
            margin:0 0 20px;
            color:var(--muted);
            line-height:1.6;
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

        .face-card{
            background:linear-gradient(180deg, var(--card) 0%, var(--card-2) 100%);
            border:1px solid var(--border);
            border-radius:24px;
            box-shadow:0 20px 60px rgba(0,0,0,.28);
            overflow:hidden;
        }

        .face-card-body{
            padding:24px;
        }

        .face-video{
            width:100%;
            min-height:360px;
            max-height:460px;
            border-radius:16px;
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
            flex-wrap:wrap;
            margin-top:16px;
        }

        .btn{
            border:none;
            border-radius:14px;
            min-height:46px;
            padding:12px 18px;
            font-size:.96rem;
            font-weight:700;
            cursor:pointer;
            transition:.2s ease;
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
            font-size:.95rem;
            display:block;
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

        @media (max-width: 640px){
            .face-card-body{
                padding:16px;
            }

            .face-title{
                font-size:1.45rem;
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
<div class="face-page">
    <div class="face-shell">
        <h1 class="face-title">
            {{ ($mode ?? 'create') === 'update' ? 'Atualizar Validação Facial' : 'Cadastro Facial Obrigatório' }}
        </h1>

        <p class="face-subtitle">
            {{ ($mode ?? 'create') === 'update'
                ? 'Abra a câmera e capture uma nova foto para atualizar sua validação facial.'
                : 'Para acessar o sistema, você precisa cadastrar seu rosto. Clique em abrir câmera e depois em capturar foto.' }}
        </p>

        @if ($errors->any())
            <div class="alert-box error">
                <ul style="margin:0;padding-left:18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="face-card">
            <div class="face-card-body">
                <video id="faceVideo" class="face-video" autoplay playsinline muted></video>

                <div class="face-actions">
                    <button type="button" class="btn btn-dark" id="startCameraBtn">
                        Abrir câmera
                    </button>

                    <button type="button" class="btn btn-green" id="captureFaceBtn">
                        Capturar foto
                    </button>
                </div>

                <div id="faceStatus" class="face-status">
                    Preparando reconhecimento facial...
                </div>

                <form method="POST" action="{{ route('face.register.store') }}" id="faceRegisterForm">
                    @csrf
                    <input type="hidden" name="face_image" id="face_image">
                    <input type="hidden" name="face_descriptor" id="face_descriptor">
                </form>

                <canvas id="faceCanvas" style="display:none;"></canvas>
            </div>
        </div>
    </div>
</div>

<script defer src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
    const faceVideo = document.getElementById('faceVideo');
    const faceCanvas = document.getElementById('faceCanvas');
    const faceStatus = document.getElementById('faceStatus');
    const startCameraBtn = document.getElementById('startCameraBtn');
    const captureFaceBtn = document.getElementById('captureFaceBtn');
    const faceImageInput = document.getElementById('face_image');
    const faceDescriptorInput = document.getElementById('face_descriptor');
    const faceRegisterForm = document.getElementById('faceRegisterForm');

    let stream = null;
    let modelsLoaded = false;
    let cameraStarted = false;
    let enviandoCadastro = false;
    let booting = false;

    function setStatus(message, type = 'info') {
        faceStatus.className = 'face-status' + (type !== 'info' ? ' ' + type : '');
        faceStatus.innerHTML = message;
    }

    async function loadModels() {
        if (modelsLoaded) {
            return true;
        }

        try {
            setStatus('Carregando reconhecimento facial...', 'info');

            await Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri('/face-api/models'),
                faceapi.nets.faceLandmark68Net.loadFromUri('/face-api/models'),
                faceapi.nets.faceRecognitionNet.loadFromUri('/face-api/models')
            ]);

            modelsLoaded = true;
            return true;
        } catch (error) {
            console.error(error);
            setStatus('Os modelos faciais não foram encontrados em /public/face-api/models.', 'error');
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
            setStatus('A câmera já está pronta. Agora clique em capturar foto.', 'success');
            return true;
        }

        if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
            setStatus('Seu navegador não suporta acesso à câmera.', 'error');
            return false;
        }

        try {
            const okModels = await loadModels();

            if (!okModels) {
                return false;
            }

            setStatus('Solicitando permissão da câmera...', 'info');

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
            setStatus('Câmera liberada com sucesso. Agora clique em capturar foto.', 'success');
            return true;
        } catch (error) {
            console.error(error);
            setStatus('Não foi possível acessar a câmera. Permita o uso da câmera no navegador e tente novamente.', 'error');
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

    async function captureFaceAndSubmit() {
        if (enviandoCadastro) {
            return;
        }

        if (!stream) {
            setStatus('Abra a câmera antes de capturar a foto.', 'error');
            return;
        }

        const okModels = await loadModels();

        if (!okModels) {
            return;
        }

        const videoWidth = faceVideo.videoWidth;
        const videoHeight = faceVideo.videoHeight;

        if (!videoWidth || !videoHeight) {
            setStatus('A câmera ainda não está pronta. Aguarde um instante e tente novamente.', 'error');
            return;
        }

        drawMirroredVideoToCanvas();
        setStatus('Processando rosto...', 'info');

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
                faceDescriptorInput.value = '';
                setStatus('Nenhum rosto foi detectado. Tente novamente centralizando melhor o rosto.', 'error');
                return;
            }

            faceImageInput.value = faceCanvas.toDataURL('image/jpeg', 0.72);
            faceDescriptorInput.value = JSON.stringify(Array.from(detection.descriptor));

            enviandoCadastro = true;
            startCameraBtn.disabled = true;
            captureFaceBtn.disabled = true;

            setStatus('Foto capturada com sucesso. Enviando cadastro facial...', 'success');
            faceRegisterForm.submit();
        } catch (error) {
            console.error(error);
            setStatus('Erro ao processar o rosto. Tente novamente.', 'error');
        }
    }

    async function bootstrapRecognition() {
        if (booting) {
            return;
        }

        booting = true;
        startCameraBtn.disabled = true;
        captureFaceBtn.disabled = true;

        try {
            const okModels = await loadModels();

            if (!okModels) {
                startCameraBtn.disabled = false;
                captureFaceBtn.disabled = false;
                return;
            }

            setStatus('Reconhecimento pronto. Clique em abrir câmera.', 'success');
            startCameraBtn.disabled = false;
            captureFaceBtn.disabled = false;
        } catch (error) {
            console.error(error);
            setStatus('Não foi possível iniciar o reconhecimento facial.', 'error');
            startCameraBtn.disabled = false;
            captureFaceBtn.disabled = false;
        } finally {
            booting = false;
        }
    }

    startCameraBtn.addEventListener('click', async function () {
        startCameraBtn.disabled = true;
        await startCamera();
        startCameraBtn.disabled = false;
    });

    captureFaceBtn.addEventListener('click', captureFaceAndSubmit);

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