<?php

use App\Http\Controllers\AdminMaterialRequestController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CaboDashboardController;
use App\Http\Controllers\CargoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\EpiDeliveryController;
use App\Http\Controllers\FaceRegistrationController;
use App\Http\Controllers\FaceVerificationController;
use App\Http\Controllers\FirstAccessPasswordController;
use App\Http\Controllers\GeolocationController;
use App\Http\Controllers\LocalController;
use App\Http\Controllers\MaterialRequestController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderExportController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductStockController;
use App\Http\Controllers\ProductVariantController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RotaController;
use App\Http\Controllers\StockEntryController;
use App\Http\Controllers\StockHistoryController;
use App\Http\Controllers\StockReplenishmentController;
use App\Http\Controllers\SupervisorDashboardController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\VisitController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');

    Route::get('/login/face', [FaceVerificationController::class, 'loginFaceForm'])
        ->name('login.face');

    Route::post('/login/face', [FaceVerificationController::class, 'loginFaceVerify'])
        ->name('login.face.verify');
});

Route::middleware('auth')->group(function () {
    Route::get('/primeiro-acesso/senha', [FirstAccessPasswordController::class, 'edit'])
        ->name('password.first_access');

    Route::post('/primeiro-acesso/senha', [FirstAccessPasswordController::class, 'update'])
        ->name('password.first_access.update');

    Route::middleware('force.password.change')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

        /*
        |--------------------------------------------------------------------------
        | Perfil
        |--------------------------------------------------------------------------
        */
        Route::get('/perfil/editar', [ProfileController::class, 'edit'])
            ->name('profile.edit');

        Route::put('/perfil', [ProfileController::class, 'update'])
            ->name('profile.update');

        /*
        |--------------------------------------------------------------------------
        | Dashboard principal
        |--------------------------------------------------------------------------
        */
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');

        /*
        |--------------------------------------------------------------------------
        | Geolocalização
        |--------------------------------------------------------------------------
        */
        Route::post('/reverse-geocode', [GeolocationController::class, 'reverseGeocode'])
            ->name('reverse-geocode');

        /*
        |--------------------------------------------------------------------------
        | Cadastro e validação facial
        |--------------------------------------------------------------------------
        */
        Route::get('/face/register', [FaceRegistrationController::class, 'create'])
            ->name('face.register');

        Route::post('/face/register', [FaceRegistrationController::class, 'store'])
            ->name('face.register.store');

        Route::get('/face/verify', [FaceVerificationController::class, 'show'])
            ->name('face.verify');

        Route::post('/face/verify', [FaceVerificationController::class, 'verify'])
            ->name('face.verify.submit');

        Route::get('/face/update', [FaceRegistrationController::class, 'edit'])
            ->name('face.update');

        /*
        |--------------------------------------------------------------------------
        | Cabo de turma
        |--------------------------------------------------------------------------
        */
        Route::get('/cabo-turma/dashboard', [CaboDashboardController::class, 'index'])
            ->name('cabo.dashboard');

        Route::get('/cabo-turma/solicitacoes', [MaterialRequestController::class, 'caboIndex'])
            ->name('cabo.requests.index');

        Route::get('/cabo-turma/solicitacoes/create', [MaterialRequestController::class, 'createCabo'])
            ->name('cabo.requests.create');

        Route::post('/cabo-turma/solicitacoes', [MaterialRequestController::class, 'storeCabo'])
            ->name('cabo.requests.store');

        Route::get('/cabo-turma/solicitacoes/{materialRequest}', [MaterialRequestController::class, 'caboShow'])
            ->name('cabo.requests.show');

        Route::get('/cabo-turma/solicitacoes/{materialRequest}/quick-view', [MaterialRequestController::class, 'caboQuickView'])
            ->name('cabo.requests.quick-view');

        Route::get('/cabo-turma/solicitacoes/{materialRequest}/editar', [MaterialRequestController::class, 'caboEdit'])
            ->name('cabo.requests.edit');

        Route::put('/cabo-turma/solicitacoes/{materialRequest}', [MaterialRequestController::class, 'caboUpdate'])
            ->name('cabo.requests.update');

        Route::delete('/cabo-turma/solicitacoes/{materialRequest}', [MaterialRequestController::class, 'caboDestroy'])
            ->name('cabo.requests.destroy');

        Route::get('/cabo-turma/solicitacoes/{materialRequest}/refazer', [MaterialRequestController::class, 'caboRedo'])
            ->name('cabo.requests.redo');

        /*
        |--------------------------------------------------------------------------
        | Visitas - Cabo de turma
        |--------------------------------------------------------------------------
        */
        Route::get('/cabo-turma/visitas', [VisitController::class, 'caboIndex'])
            ->name('cabo.visits.index');

        Route::get('/cabo-turma/visitas/registrar', [VisitController::class, 'create'])
            ->name('cabo.visits.create');

        Route::post('/cabo-turma/visitas', [VisitController::class, 'store'])
            ->name('cabo.visits.store');

        /*
        |--------------------------------------------------------------------------
        | Supervisor
        |--------------------------------------------------------------------------
        */
        Route::get('/supervisor/dashboard', [SupervisorDashboardController::class, 'index'])
            ->name('supervisor.dashboard');

        Route::get('/supervisor/solicitacoes', [MaterialRequestController::class, 'supervisorIndex'])
            ->name('supervisor.requests.index');

        Route::get('/supervisor/solicitacoes/create', [MaterialRequestController::class, 'createSupervisor'])
            ->name('supervisor.requests.create');

        Route::post('/supervisor/solicitacoes', [MaterialRequestController::class, 'storeSupervisor'])
            ->name('supervisor.requests.store');

        Route::get('/supervisor/solicitacoes/{materialRequest}', [MaterialRequestController::class, 'supervisorShow'])
            ->name('supervisor.requests.show');

        Route::get('/supervisor/solicitacoes/{materialRequest}/quick-view', [MaterialRequestController::class, 'supervisorQuickView'])
            ->name('supervisor.requests.quick-view');

        Route::get('/supervisor/solicitacoes/{materialRequest}/editar', [MaterialRequestController::class, 'supervisorEdit'])
            ->name('supervisor.requests.edit');

        Route::put('/supervisor/solicitacoes/{materialRequest}', [MaterialRequestController::class, 'supervisorUpdate'])
            ->name('supervisor.requests.update');

        Route::delete('/supervisor/solicitacoes/{materialRequest}', [MaterialRequestController::class, 'supervisorDestroy'])
            ->name('supervisor.requests.destroy');

        Route::get('/supervisor/solicitacoes/{materialRequest}/refazer', [MaterialRequestController::class, 'supervisorRedo'])
            ->name('supervisor.requests.redo');

        /*
        |--------------------------------------------------------------------------
        | Admin
        |--------------------------------------------------------------------------
        */
        Route::middleware('admin')->group(function () {
            /*
            |--------------------------------------------------------------------------
            | Solicitações de materiais - admin
            |--------------------------------------------------------------------------
            */
            Route::get('/admin/solicitacoes-materiais', [AdminMaterialRequestController::class, 'index'])
                ->name('admin.material-requests.index');

            Route::get('/admin/solicitacoes-materiais/{materialRequest}', [AdminMaterialRequestController::class, 'show'])
                ->name('admin.material-requests.show');

            Route::post('/admin/solicitacoes-materiais/{materialRequest}/approve', [AdminMaterialRequestController::class, 'approve'])
                ->name('admin.material-requests.approve');

            Route::post('/admin/solicitacoes-materiais/{materialRequest}/reject', [AdminMaterialRequestController::class, 'reject'])
                ->name('admin.material-requests.reject');

            Route::get('/admin/solicitacoes-materiais/{materialRequest}/aprovar', [AdminMaterialRequestController::class, 'redirectToOrder'])
                ->name('admin.material-requests.redirect-to-order');

            /*
            |--------------------------------------------------------------------------
            | Visitas - Admin
            |--------------------------------------------------------------------------
            */
            Route::get('/admin/visitas', [VisitController::class, 'index'])
                ->name('admin.visits.index');

            Route::get('/admin/visitas/detalhado', [VisitController::class, 'summarySelector'])
                ->name('admin.visits.summary-selector');

            Route::get('/admin/visitas/detalhado/abrir', [VisitController::class, 'summaryRedirect'])
                ->name('admin.visits.summary-redirect');

            Route::get('/admin/visitas/usuario/{user}', [VisitController::class, 'userSummary'])
                ->name('admin.visits.user-summary');

            Route::get('/admin/visitas/usuario/{user}/pdf', [VisitController::class, 'downloadUserSummaryPdf'])
                ->name('admin.visits.user-summary.pdf');

            /*
            |--------------------------------------------------------------------------
            | Cargos
            |--------------------------------------------------------------------------
            */
            Route::get('/cargos', [CargoController::class, 'index'])
                ->name('cargos.index');

            Route::post('/cargos', [CargoController::class, 'store'])
                ->name('cargos.store');

            Route::put('/cargos/{cargo}', [CargoController::class, 'update'])
                ->name('cargos.update');

            Route::patch('/cargos/{cargo}/toggle', [CargoController::class, 'toggle'])
                ->name('cargos.toggle');

            /*
            |--------------------------------------------------------------------------
            | Usuários
            |--------------------------------------------------------------------------
            */
            Route::get('/usuarios', [UsuarioController::class, 'index'])
                ->name('usuarios.index');

            Route::post('/usuarios', [UsuarioController::class, 'store'])
                ->name('usuarios.store');

            Route::put('/usuarios/{user}', [UsuarioController::class, 'update'])
                ->name('usuarios.update');

            Route::patch('/usuarios/{user}/toggle', [UsuarioController::class, 'toggle'])
                ->name('usuarios.toggle');

            Route::patch('/usuarios/{user}/reset-senha', [UsuarioController::class, 'resetPassword'])
                ->name('usuarios.reset-password');

            Route::delete('/usuarios/{user}', [UsuarioController::class, 'destroy'])
                ->name('usuarios.destroy');

            Route::patch('/usuarios/{user}/reset-facial', [UsuarioController::class, 'resetFace'])
                ->name('usuarios.reset-face');

            /*
            |--------------------------------------------------------------------------
            | Rotas
            |--------------------------------------------------------------------------
            */
            Route::get('/rotas', [RotaController::class, 'index'])
                ->name('rotas.index');

            Route::post('/rotas', [RotaController::class, 'store'])
                ->name('rotas.store');

            Route::put('/rotas/{rota}', [RotaController::class, 'update'])
                ->name('rotas.update');

            Route::patch('/rotas/{rota}/toggle', [RotaController::class, 'toggle'])
                ->name('rotas.toggle');

            /*
|--------------------------------------------------------------------------
| Locais
|--------------------------------------------------------------------------
*/
Route::get('/locais', [LocalController::class, 'index'])
    ->name('locais.index');

Route::get('/locais/pdf', [LocalController::class, 'pdf'])
    ->name('locais.pdf');

Route::post('/locais', [LocalController::class, 'store'])
    ->name('locais.store');

Route::put('/locais/{local}', [LocalController::class, 'update'])
    ->name('locais.update');

Route::patch('/locais/{local}/toggle', [LocalController::class, 'toggle'])
    ->name('locais.toggle');
            /*
            |--------------------------------------------------------------------------
            | Produtos
            |--------------------------------------------------------------------------
            */
            Route::get('/produtos', [ProductController::class, 'index'])
                ->name('products.index');

            Route::post('/produtos', [ProductController::class, 'store'])
                ->name('products.store');

            Route::put('/produtos/{product}', [ProductController::class, 'update'])
                ->name('products.update');

            Route::delete('/produtos/{product}', [ProductController::class, 'destroy'])
                ->name('products.destroy');

            Route::get('/produtos/estoque/pdf', [ProductController::class, 'stockPdf'])
                ->name('products.stock.pdf');

            /*
            |--------------------------------------------------------------------------
            | Variações de produtos
            |--------------------------------------------------------------------------
            */
            Route::post('/product-variants', [ProductVariantController::class, 'store'])
                ->name('product-variants.store');

            Route::put('/product-variants/{productVariant}', [ProductVariantController::class, 'update'])
                ->name('product-variants.update');

            Route::delete('/product-variants/{productVariant}', [ProductVariantController::class, 'destroy'])
                ->name('product-variants.destroy');

            /*
            |--------------------------------------------------------------------------
            | Funcionários
            |--------------------------------------------------------------------------
            */
            Route::get('/funcionarios', [EmployeeController::class, 'index'])
                ->name('employees.index');

            Route::get('/funcionarios/novo', [EmployeeController::class, 'create'])
                ->name('employees.create');

            Route::post('/funcionarios', [EmployeeController::class, 'store'])
                ->name('employees.store');

            Route::get('/funcionarios/{employee}/editar', [EmployeeController::class, 'edit'])
                ->name('employees.edit');

            Route::put('/funcionarios/{employee}', [EmployeeController::class, 'update'])
                ->name('employees.update');

            Route::delete('/funcionarios/{employee}', [EmployeeController::class, 'destroy'])
                ->name('employees.destroy');
            Route::get('/employees/pdf', [EmployeeController::class, 'pdf'])->name('employees.pdf');

            /*
            |--------------------------------------------------------------------------
            | EPI
            |--------------------------------------------------------------------------
            */
            Route::get('/epi', [EpiDeliveryController::class, 'dashboard'])
                ->name('epi.index');

            Route::get('/epi/entregas', [EpiDeliveryController::class, 'index'])
                ->name('epi-deliveries.index');

            Route::get('/epi/entregas/nova', [EpiDeliveryController::class, 'create'])
                ->name('epi-deliveries.create');

            Route::post('/epi/entregas', [EpiDeliveryController::class, 'store'])
                ->name('epi-deliveries.store');

            Route::get('/epi/entregas/{epiDelivery}', [EpiDeliveryController::class, 'show'])
                ->name('epi-deliveries.show');

            Route::get('/epi/funcionarios/{employee}', [EpiDeliveryController::class, 'employeeHistory'])
                ->name('epi.employee-history');

            Route::get('/epi-deliveries/{delivery}/quick-view', [EpiDeliveryController::class, 'quickView'])
                ->name('epi-deliveries.quick-view');

            /*
            |--------------------------------------------------------------------------
            | Pedidos
            |--------------------------------------------------------------------------
            */
            Route::get('/pedidos', [OrderController::class, 'index'])
                ->name('orders.index');

            Route::get('/pedidos/novo', [OrderController::class, 'create'])
                ->name('orders.create');

            Route::post('/pedidos', [OrderController::class, 'store'])
                ->name('orders.store');

            Route::get('/pedidos/{order}', [OrderController::class, 'show'])
                ->name('orders.show');

            Route::get('/pedidos/{order}/editar', [OrderController::class, 'edit'])
                ->name('orders.edit');

            Route::put('/pedidos/{order}', [OrderController::class, 'update'])
                ->name('orders.update');

            Route::delete('/pedidos/{order}', [OrderController::class, 'destroy'])
                ->name('orders.destroy');

            Route::get('/pedidos/{order}/pdf', [OrderExportController::class, 'pdfSingle'])
                ->name('orders.pdf.single');

            Route::post('/pedidos/pdf-lote', [OrderExportController::class, 'pdfBatch'])
                ->name('orders.pdf.batch');

            Route::get('/pedidos/{order}/excel', [OrderExportController::class, 'excelSingle'])
                ->name('orders.excel.single');

            Route::post('/pedidos/excel-lote', [OrderExportController::class, 'excelBatch'])
                ->name('orders.excel.batch');

            Route::get('/pedidos/{order}/repetir', [OrderController::class, 'repeatForm'])
                ->name('orders.repeat');

            Route::get('/pedidos/{order}/quick-view', [OrderController::class, 'quickView'])
                ->name('orders.quick-view');

            /*
            |--------------------------------------------------------------------------
            | Estoque
            |--------------------------------------------------------------------------
            */
            Route::post('/product-stock/movements', [ProductStockController::class, 'store'])
                ->name('product-stock.movements.store');

            Route::get('/estoque/entradas', [StockEntryController::class, 'index'])
                ->name('stock-entries.index');

            Route::post('/estoque/entradas', [StockEntryController::class, 'store'])
                ->name('stock-entries.store');

            Route::get('/estoque/reposicao', [StockReplenishmentController::class, 'index'])
                ->name('stock-replenishment.index');

            Route::post('/estoque/reposicao', [StockReplenishmentController::class, 'store'])
                ->name('stock-replenishment.store');

            Route::get('/estoque/movimentacoes', [StockHistoryController::class, 'index'])
                ->name('stock-history.index');

            Route::get('/estoque/movimentacoes/resumo-diario', [StockHistoryController::class, 'daily'])
                ->name('stock-history.daily');

            Route::get('/stock-entries', [StockEntryController::class, 'index'])
                ->name('stock-entries.index.alt');

            Route::post('/stock-entries', [StockEntryController::class, 'store'])
                ->name('stock-entries.store.alt');

            Route::get('/stock-history', [StockHistoryController::class, 'index'])
                ->name('stock-history.index.alt');

            Route::get('/stock-history/daily', [StockHistoryController::class, 'daily'])
                ->name('stock-history.daily.alt');
        });
    });
});