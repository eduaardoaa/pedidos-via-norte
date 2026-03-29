<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Vianorte')</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ asset('assets/imgs/LOGO VIA NORTE.png') }}" type="image/x-icon">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        html,
        body{
            height:100%;
            margin:0;
            overflow:hidden;
        }

        body{
            transition:opacity .14s ease;
        }

        body.page-loading{
            opacity:.985;
        }

        .app-shell{
            height:100vh;
            height:100dvh;
            overflow:hidden;
        }

        .sidebar{
            height:100vh;
            height:100dvh;
            display:flex;
            flex-direction:column;
            overflow:hidden;
            will-change:transform;
            transition:transform .28s ease, width .28s ease, opacity .28s ease;
            flex-shrink:0;
        }

        .sidebar-brand{
            flex-shrink:0;
            position:sticky;
            top:0;
            z-index:20;
            background:inherit;
        }

        .sidebar-nav{
            flex:1;
            min-height:0;
            overflow-y:auto;
            overflow-x:hidden;
            padding-right:4px;
        }

        .sidebar-bottom{
            flex-shrink:0;
            position:sticky;
            bottom:0;
            z-index:20;
            background:inherit;
        }

        .main-content{
            height:100vh;
            height:100dvh;
            min-width:0;
            overflow-y:auto;
            overflow-x:hidden;
            -webkit-overflow-scrolling:touch;
        }

        .main-content::-webkit-scrollbar,
        .sidebar-nav::-webkit-scrollbar{
            width:6px;
            height:6px;
        }

        .main-content::-webkit-scrollbar-thumb,
        .sidebar-nav::-webkit-scrollbar-thumb{
            background:rgba(255,255,255,.18);
            border-radius:999px;
        }

        .sidebar-close-mobile{
            display:none;
            align-items:center;
            justify-content:center;
            border:none;
            background:transparent;
            cursor:pointer;
            font-size:1.25rem;
            color:inherit;
            width:42px;
            height:42px;
            border-radius:10px;
            flex-shrink:0;
        }

        .sidebar-close-mobile:hover{
            background:rgba(255,255,255,.08);
        }

        .sidebar-overlay{
            transition:opacity .28s ease, visibility .28s ease;
        }

        .sidebar-toggle,
        .sidebar-close-mobile,
        .sidebar-overlay{
            -webkit-tap-highlight-color:transparent;
            touch-action:manipulation;
        }

        body.sidebar-open-body{
            overflow:hidden;
        }

        .page-content{
            min-height:120px;
        }

        .page-content.is-loading{
            opacity:.75;
            pointer-events:none;
            transition:opacity .16s ease;
        }

        /* MOBILE */
        @media (max-width: 991px){
            html,
            body{
                overflow:auto;
            }

            .main-content{
                height:auto;
                min-height:100vh;
                min-height:100dvh;
                overflow-y:auto;
            }

            .sidebar-close-mobile{
                display:inline-flex;
            }

            .sidebar{
                max-height:100vh;
                max-height:100dvh;
                position:fixed;
                top:0;
                left:0;
                z-index:1001;
                width:280px;
                max-width:85vw;
                transform:translateX(-100%);
                opacity:1;
                pointer-events:none;
            }

            .sidebar-overlay{
                position:fixed;
                inset:0;
                background:rgba(0,0,0,.45);
                opacity:0;
                visibility:hidden;
                pointer-events:none;
                z-index:1000;
            }

            .app-shell.sidebar-mobile-open .sidebar{
                transform:translateX(0);
                pointer-events:auto;
            }

            .app-shell.sidebar-mobile-open .sidebar-overlay{
                opacity:1;
                visibility:visible;
                pointer-events:auto;
            }

            .app-shell.sidebar-collapsed .sidebar{
                transform:translateX(-100%);
            }
        }

        /* DESKTOP */
        @media (min-width: 992px){
            .sidebar{
                position:sticky;
                top:0;
                left:auto;
                transform:none;
                opacity:1;
                pointer-events:auto;
                align-self:flex-start;
            }

            .sidebar-overlay{
                display:none;
            }
        }
        @media (max-width: 991px){
    html,
    body{
        height:auto;
        min-height:100%;
        overflow-x:hidden;
        overflow-y:auto;
    }

    .app-shell{
        height:auto;
        min-height:100vh;
        min-height:100dvh;
        overflow:visible;
    }

    .main-content{
        height:auto;
        min-height:100vh;
        min-height:100dvh;
        overflow-x:hidden;
        overflow-y:visible;
        -webkit-overflow-scrolling:touch;
    }

    .sidebar-close-mobile{
        display:inline-flex;
    }

    .sidebar{
        height:100vh;
        height:100dvh;
        max-height:100vh;
        max-height:100dvh;
        position:fixed;
        top:0;
        left:0;
        z-index:1001;
        width:280px;
        max-width:85vw;
        transform:translateX(-100%);
        opacity:1;
        pointer-events:none;
    }

    .sidebar-overlay{
        position:fixed;
        inset:0;
        background:rgba(0,0,0,.45);
        opacity:0;
        visibility:hidden;
        pointer-events:none;
        z-index:1000;
    }

    .app-shell.sidebar-mobile-open .sidebar{
        transform:translateX(0);
        pointer-events:auto;
    }

    .app-shell.sidebar-mobile-open .sidebar-overlay{
        opacity:1;
        visibility:visible;
        pointer-events:auto;
    }

    .app-shell.sidebar-collapsed .sidebar{
        transform:translateX(-100%);
    }
}
    </style>
</head>
<body>
    @php
        $user = auth()->user();
        $cargoCodigo = $user->cargo->codigo ?? null;

        $isAdmin = $cargoCodigo === 'admin';
        $isCaboTurma = $cargoCodigo === 'cabo de turma';
        $isSupervisor = $cargoCodigo === 'supervisor';

        $showSidebar = $isAdmin || $isCaboTurma || $isSupervisor;
    @endphp

    <div class="app-shell {{ $showSidebar ? 'sidebar-collapsed' : 'app-shell-no-sidebar' }}" id="appShell">
        @if($showSidebar)
            <div class="sidebar-overlay" id="sidebarOverlay"></div>

            <aside class="sidebar" id="sidebar">
                <div class="sidebar-brand">
                    <img src="{{ asset('assets/imgs/LOGO VIA NORTE.png') }}" alt="Logo Via Norte">

                    <div class="sidebar-brand-text">
                        <h2>{{ $user->name ?? 'Usuário' }}</h2>
                        <p>{{ $user->cargo?->nome ?? 'Sem cargo' }}</p>
                    </div>

                    <button type="button" class="sidebar-close-mobile" id="sidebarClose" aria-label="Fechar menu">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>

                <nav class="sidebar-nav">
                    @if($isAdmin)
                        <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <span class="sidebar-link-icon"><i class="bi bi-grid-1x2-fill"></i></span>
                            <span class="sidebar-link-text">Dashboard</span>
                        </a>

                        <a href="{{ route('admin.material-requests.index') }}" class="sidebar-link {{ request()->routeIs('admin.material-requests.*') ? 'active' : '' }}">
                            <span class="sidebar-link-icon"><i class="bi bi-box2-heart"></i></span>
                            <span class="sidebar-link-text">Solicitações de Materiais</span>
                        </a>

                        <a href="{{ route('admin.visits.index') }}" class="sidebar-link {{ request()->routeIs('admin.visits.*') ? 'active' : '' }}">
                            <span class="sidebar-link-icon"><i class="bi bi-geo-alt-fill"></i></span>
                            <span class="sidebar-link-text">Gerenciar Visitas</span>
                        </a>

                        <a href="{{ route('orders.index') }}" class="sidebar-link {{ request()->routeIs('orders.*') ? 'active' : '' }}">
                            <span class="sidebar-link-icon"><i class="bi bi-receipt-cutoff"></i></span>
                            <span class="sidebar-link-text">Pedidos</span>
                        </a>

                        <a href="{{ route('products.index') }}" class="sidebar-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
                            <span class="sidebar-link-icon"><i class="bi bi-box-seam"></i></span>
                            <span class="sidebar-link-text">Produtos e Estoque</span>
                        </a>

                        <a href="{{ route('rotas.index') }}" class="sidebar-link {{ request()->routeIs('rotas.*') ? 'active' : '' }}">
                            <span class="sidebar-link-icon"><i class="bi bi-signpost-split"></i></span>
                            <span class="sidebar-link-text">Rotas</span>
                        </a>

                        <a href="{{ route('locais.index') }}" class="sidebar-link {{ request()->routeIs('locais.*') ? 'active' : '' }}">
                            <span class="sidebar-link-icon"><i class="bi bi-geo-alt"></i></span>
                            <span class="sidebar-link-text">Locais</span>
                        </a>

                        <a href="{{ route('epi.index') }}" class="sidebar-link {{ request()->routeIs('epi.*') || request()->routeIs('epi-deliveries.*') ? 'active' : '' }}">
                            <span class="sidebar-link-icon"><i class="bi bi-shield-check"></i></span>
                            <span class="sidebar-link-text">EPI / Segurança</span>
                        </a>

                        <a href="{{ route('usuarios.index') }}" class="sidebar-link {{ request()->routeIs('usuarios.*') ? 'active' : '' }}">
                            <span class="sidebar-link-icon"><i class="bi bi-people"></i></span>
                            <span class="sidebar-link-text">Usuários</span>
                        </a>

                        <a href="{{ route('cargos.index') }}" class="sidebar-link {{ request()->routeIs('cargos.*') ? 'active' : '' }}">
                            <span class="sidebar-link-icon"><i class="bi bi-person-badge"></i></span>
                            <span class="sidebar-link-text">Cargos</span>
                        </a>
                    @endif

                    @if($isCaboTurma)
                        <a href="{{ route('cabo.dashboard') }}" class="sidebar-link {{ request()->routeIs('cabo.dashboard') ? 'active' : '' }}">
                            <span class="sidebar-link-icon"><i class="bi bi-grid-1x2-fill"></i></span>
                            <span class="sidebar-link-text">Dashboard</span>
                        </a>

                        <a href="{{ route('cabo.requests.create') }}" class="sidebar-link {{ request()->routeIs('cabo.requests.create') ? 'active' : '' }}">
                            <span class="sidebar-link-icon"><i class="bi bi-plus-circle"></i></span>
                            <span class="sidebar-link-text">Nova Solicitação</span>
                        </a>

                        <a href="{{ route('cabo.requests.index') }}" class="sidebar-link {{ request()->routeIs('cabo.requests.index') || request()->routeIs('cabo.requests.show') || request()->routeIs('cabo.requests.edit') || request()->routeIs('cabo.requests.redo') || request()->routeIs('cabo.requests.quick-view') ? 'active' : '' }}">
                            <span class="sidebar-link-icon"><i class="bi bi-list-check"></i></span>
                            <span class="sidebar-link-text">Minhas Solicitações</span>
                        </a>

                        <a href="{{ route('cabo.visits.create') }}" class="sidebar-link {{ request()->routeIs('cabo.visits.create') ? 'active' : '' }}">
                            <span class="sidebar-link-icon"><i class="bi bi-geo-alt-fill"></i></span>
                            <span class="sidebar-link-text">Registrar Visita</span>
                        </a>

                        <a href="{{ route('cabo.visits.index') }}" class="sidebar-link {{ request()->routeIs('cabo.visits.index') ? 'active' : '' }}">
                            <span class="sidebar-link-icon"><i class="bi bi-clock-history"></i></span>
                            <span class="sidebar-link-text">Minhas Visitas</span>
                        </a>
                    @endif

                    @if($isSupervisor)
                        <a href="{{ route('supervisor.dashboard') }}" class="sidebar-link {{ request()->routeIs('supervisor.dashboard') ? 'active' : '' }}">
                            <span class="sidebar-link-icon"><i class="bi bi-grid-1x2-fill"></i></span>
                            <span class="sidebar-link-text">Dashboard</span>
                        </a>

                        <a href="{{ route('supervisor.requests.create') }}" class="sidebar-link {{ request()->routeIs('supervisor.requests.create') ? 'active' : '' }}">
                            <span class="sidebar-link-icon"><i class="bi bi-plus-circle"></i></span>
                            <span class="sidebar-link-text">Nova Solicitação</span>
                        </a>

                        <a href="{{ route('supervisor.requests.index') }}" class="sidebar-link {{ request()->routeIs('supervisor.requests.index') || request()->routeIs('supervisor.requests.show') || request()->routeIs('supervisor.requests.edit') || request()->routeIs('supervisor.requests.redo') || request()->routeIs('supervisor.requests.quick-view') ? 'active' : '' }}">
                            <span class="sidebar-link-icon"><i class="bi bi-list-check"></i></span>
                            <span class="sidebar-link-text">Minhas Solicitações</span>
                        </a>
                    @endif
                </nav>

                <div class="sidebar-bottom">
                    <a href="{{ route('profile.edit') }}" class="sidebar-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                        <span class="sidebar-link-icon"><i class="bi bi-person-circle"></i></span>
                        <span class="sidebar-link-text">Editar perfil</span>
                    </a>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn btn-logout">
                            <i class="bi bi-box-arrow-right"></i>
                            <span class="sidebar-link-text">Sair do sistema</span>
                        </button>
                    </form>
                </div>
            </aside>
        @endif

        <main class="main-content" id="mainContent">
            <header class="topbar">
                <div class="topbar-left">
                    @if($showSidebar)
                        <button type="button" class="sidebar-toggle" id="sidebarToggle" aria-label="Abrir menu">
                            <i class="bi bi-list"></i>
                        </button>
                    @endif

                    <div>
                        <h1 id="pageTopbarTitle">@yield('pageTitle', 'Painel')</h1>
                        <p id="pageTopbarDescription">@yield('pageDescription', 'Gerencie as informações do sistema.')</p>
                    </div>
                </div>

                <div class="topbar-badge">
                    <span><i class="bi bi-circle-fill"></i></span>
                    <span>Ambiente interno</span>
                </div>
            </header>

            <section class="page-content" id="pageContent">
                @yield('content')
            </section>

            @include('partials.footer')
        </main>
    </div>

    <script>
        const appShell = document.getElementById('appShell');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebarClose = document.getElementById('sidebarClose');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const mainContent = document.getElementById('mainContent');
        const pageContent = document.getElementById('pageContent');
        const pageTopbarTitle = document.getElementById('pageTopbarTitle');
        const pageTopbarDescription = document.getElementById('pageTopbarDescription');
        const mobileBreakpoint = 991;

        let isAnimatingSidebar = false;
        let partialNavigationInProgress = false;

        function isMobile() {
            return window.innerWidth <= mobileBreakpoint;
        }

        function lockSidebarAnimation() {
            isAnimatingSidebar = true;
            setTimeout(() => {
                isAnimatingSidebar = false;
            }, 280);
        }

        function openMobileSidebar() {
            if (!appShell || isAnimatingSidebar) return;
            lockSidebarAnimation();
            appShell.classList.add('sidebar-mobile-open');
            document.body.classList.add('sidebar-open-body');
        }

        function closeMobileSidebar() {
            if (!appShell || isAnimatingSidebar) return;
            lockSidebarAnimation();
            appShell.classList.remove('sidebar-mobile-open');
            document.body.classList.remove('sidebar-open-body');
        }

        function toggleMobileSidebar() {
            if (!appShell || isAnimatingSidebar) return;

            if (appShell.classList.contains('sidebar-mobile-open')) {
                closeMobileSidebar();
            } else {
                openMobileSidebar();
            }
        }

        function openDesktopSidebar() {
            if (!appShell) return;
            appShell.classList.remove('sidebar-collapsed');
            localStorage.setItem('sidebar-collapsed', 'false');
        }

        function closeDesktopSidebar() {
            if (!appShell) return;
            appShell.classList.add('sidebar-collapsed');
            localStorage.setItem('sidebar-collapsed', 'true');
        }

        function toggleDesktopSidebar() {
            if (!appShell || isAnimatingSidebar) return;
            lockSidebarAnimation();

            if (appShell.classList.contains('sidebar-collapsed')) {
                openDesktopSidebar();
            } else {
                closeDesktopSidebar();
            }
        }

        function applyInitialSidebarState() {
            if (!appShell) return;

            if (localStorage.getItem('sidebar-collapsed') === null) {
                localStorage.setItem('sidebar-collapsed', 'true');
            }

            if (isMobile()) {
                appShell.classList.remove('sidebar-collapsed');
                appShell.classList.remove('sidebar-mobile-open');
                document.body.classList.remove('sidebar-open-body');
            } else {
                appShell.classList.remove('sidebar-mobile-open');
                document.body.classList.remove('sidebar-open-body');

                if (localStorage.getItem('sidebar-collapsed') === 'true') {
                    appShell.classList.add('sidebar-collapsed');
                } else {
                    appShell.classList.remove('sidebar-collapsed');
                }
            }
        }

        function shouldInterceptLink(link, event) {
            if (!link) return false;
            if (partialNavigationInProgress) return false;
            if (link.target === '_blank') return false;
            if (link.hasAttribute('download')) return false;
            if (link.dataset.noAjax === 'true') return false;
            if (event.defaultPrevented) return false;
            if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return false;

            const href = link.getAttribute('href');
            if (!href || href.startsWith('#') || href.startsWith('javascript:')) return false;

            const url = new URL(link.href, window.location.origin);

            if (url.origin !== window.location.origin) return false;
            if (['POST', 'PUT', 'PATCH', 'DELETE'].includes((link.dataset.method || 'GET').toUpperCase())) return false;

            return true;
        }

        function setLoadingState(isLoading) {
            if (isLoading) {
                document.body.classList.add('page-loading');
                pageContent?.classList.add('is-loading');
            } else {
                document.body.classList.remove('page-loading');
                pageContent?.classList.remove('is-loading');
            }
        }

        function updateActiveSidebarLink(urlString) {
            const currentUrl = new URL(urlString, window.location.origin);
            const sidebarLinks = document.querySelectorAll('.sidebar a.sidebar-link[href]');

            sidebarLinks.forEach(link => {
                const linkUrl = new URL(link.href, window.location.origin);
                const samePath = linkUrl.pathname === currentUrl.pathname;
                link.classList.toggle('active', samePath);
            });
        }

        function executeScriptsFromContainer(container) {
            const scripts = container.querySelectorAll('script');

            scripts.forEach(oldScript => {
                const newScript = document.createElement('script');

                Array.from(oldScript.attributes).forEach(attr => {
                    newScript.setAttribute(attr.name, attr.value);
                });

                if (oldScript.src) {
                    const existing = Array.from(document.scripts).some(script => script.src === oldScript.src);

                    if (!existing) {
                        newScript.src = oldScript.src;
                        document.body.appendChild(newScript);
                    }
                } else {
                    newScript.textContent = oldScript.textContent;
                    document.body.appendChild(newScript);
                }
            });
        }

        async function navigatePartial(url, pushState = true) {
            if (!pageContent || !mainContent) {
                window.location.href = url;
                return;
            }

            partialNavigationInProgress = true;
            setLoadingState(true);

            try {
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-Partial-Request': 'true',
                    },
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    window.location.href = url;
                    return;
                }

                const html = await response.text();
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');

                const newPageContent = doc.querySelector('#pageContent, .page-content');
                const newTopbarTitle = doc.querySelector('#pageTopbarTitle, .topbar h1');
                const newTopbarDescription = doc.querySelector('#pageTopbarDescription, .topbar p');
                const newTitle = doc.querySelector('title');

                if (!newPageContent) {
                    window.location.href = url;
                    return;
                }

                pageContent.innerHTML = newPageContent.innerHTML;

                if (newTopbarTitle && pageTopbarTitle) {
                    pageTopbarTitle.innerHTML = newTopbarTitle.innerHTML;
                }

                if (newTopbarDescription && pageTopbarDescription) {
                    pageTopbarDescription.innerHTML = newTopbarDescription.innerHTML;
                }

                if (newTitle) {
                    document.title = newTitle.textContent || 'Vianorte';
                }

                if (pushState) {
                    window.history.pushState({ url }, '', url);
                }

                updateActiveSidebarLink(url);
                mainContent.scrollTo({ top: 0, behavior: 'auto' });

                if (isMobile()) {
                    closeMobileSidebar();
                }

                executeScriptsFromContainer(pageContent);
            } catch (error) {
                console.error('Erro na navegação parcial:', error);
                window.location.href = url;
            } finally {
                partialNavigationInProgress = false;
                setLoadingState(false);
            }
        }

        applyInitialSidebarState();
        updateActiveSidebarLink(window.location.href);

        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function () {
                if (isMobile()) {
                    toggleMobileSidebar();
                } else {
                    toggleDesktopSidebar();
                }
            });
        }

        if (sidebarClose) {
            sidebarClose.addEventListener('click', function () {
                closeMobileSidebar();
            });
        }

        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', function () {
                closeMobileSidebar();
            });
        }

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                closeMobileSidebar();
            }
        });

        document.addEventListener('click', function (e) {
            const link = e.target.closest('a[href]');

            if (!shouldInterceptLink(link, e)) {
                return;
            }

            const url = new URL(link.href, window.location.origin);

            if (url.href === window.location.href) {
                return;
            }

            e.preventDefault();
            navigatePartial(url.href, true);
        });

        window.addEventListener('popstate', function () {
            navigatePartial(window.location.href, false);
        });

        let resizeTimeout;

        window.addEventListener('resize', function () {
            clearTimeout(resizeTimeout);

            resizeTimeout = setTimeout(() => {
                applyInitialSidebarState();
            }, 120);
        });
    </script>
</body>
</html>