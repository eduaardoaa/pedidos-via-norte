<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\StockLocation;
use App\Services\StockService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        $locationFilter = request('local');
        $search = trim((string) request('search'));
        $status = request('status');

        $products = Product::with(['unit', 'locations', 'variants.movements'])
            ->when($locationFilter, function ($query) use ($locationFilter) {
                $query->whereHas('locations', function ($subQuery) use ($locationFilter) {
                    $subQuery->where('slug', $locationFilter);
                });
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', '%' . $search . '%')
                        ->orWhere('sku', 'like', '%' . $search . '%');
                });
            })
            ->when($status !== null && $status !== '', function ($query) use ($status) {
                $query->where('active', $status === 'ativo');
            })
            ->orderBy('name', 'asc')
            ->get();

        $units = ProductUnit::where('active', true)->orderBy('name')->get();
        $locations = StockLocation::where('active', true)->orderBy('name')->get();

        $stats = [
            'total' => $products->count(),
            'with_variants' => $products->where('uses_variants', true)->count(),
            'inactive' => $products->where('active', false)->count(),
            'out_of_stock' => $products->filter(function ($product) {
                if ($product->uses_variants) {
                    return $product->variants->sum(fn ($v) => (float) $v->current_stock) <= 0;
                }

                return (float) $product->current_stock <= 0;
            })->count(),
        ];

        return view('produtos.index', compact(
            'products',
            'units',
            'locations',
            'locationFilter',
            'search',
            'status',
            'stats'
        ));
    }

    public function stockPdf(Request $request)
{
    $locationFilter = $request->get('local');
    $search = trim((string) $request->get('search'));
    $status = $request->get('status');

    $products = Product::with(['unit', 'locations', 'variants'])
        ->when($locationFilter, function ($query) use ($locationFilter) {
            $query->whereHas('locations', function ($subQuery) use ($locationFilter) {
                $subQuery->where('slug', $locationFilter);
            });
        })
        ->when($search !== '', function ($query) use ($search) {
            $query->where(function ($subQuery) use ($search) {
                $subQuery->where('name', 'like', '%' . $search . '%')
                    ->orWhere('sku', 'like', '%' . $search . '%');
            });
        })
        ->when($status !== null && $status !== '', function ($query) use ($status) {
            $query->where('active', $status === 'ativo');
        })
        ->orderBy('name', 'asc')
        ->get();

    $tcpdfPath = $this->getTcpdfPath();

    if (!$tcpdfPath) {
        abort(500, 'TCPDF não encontrado. Biblioteca não localizada.');
    }

    require_once $tcpdfPath;

    $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    $pdf->SetCreator(config('app.name'));
    $pdf->SetAuthor(config('app.name'));
    $pdf->SetTitle('Relatório de Estoque');
    $pdf->SetMargins(12, 14, 12);
    $pdf->SetHeaderMargin(0);
    $pdf->SetFooterMargin(10);
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(true);
    $pdf->setFontSubsetting(true);
    $pdf->SetAutoPageBreak(true, 15);

    $logo = $this->getLogoPath();

    $pdf->AddPage();

    if ($logo) {
        $pdf->Image($logo, 12, 10, 40, '', '', '', 'T', false, 300);
    }

    $pdf->SetFont('helvetica', '', 12);
    $pdf->SetXY(120, 12);
    $pdf->Cell(75, 8, 'Relatório de Estoque', 0, 1, 'R');

    $pdf->Ln(12);

    $filtroLocalTexto = match ($locationFilter) {
        'rota' => 'Rota',
        'almoxarifado' => 'Almoxarifado',
        default => 'Todos',
    };

    $html = '';
    $html .= '<h1 style="font-size:17pt;">Estoque de Produtos</h1>';
    $html .= '<p style="font-size:11.5pt;"><strong>Gerado em:</strong> ' . e(now()->format('d/m/Y H:i')) . '</p>';
    $html .= '<p style="font-size:11.5pt;"><strong>Filtro:</strong> ' . e($filtroLocalTexto) . '</p>';

    if ($search !== '') {
        $html .= '<p style="font-size:11.5pt;"><strong>Busca:</strong> ' . e($search) . '</p>';
    }

    $html .= '<p style="font-size:11.5pt;"><strong>Total de produtos:</strong> ' . e((string) $products->count()) . '</p>';
    $html .= '<br>';

    if ($products->isEmpty()) {
        $html .= '<p style="font-size:12pt;">Nenhum produto encontrado para os filtros informados.</p>';
    } else {
        foreach ($products as $product) {
            $nome = $this->normalizeLabel($product->name);
            $locais = $product->locations->pluck('name')->filter()->implode(', ');
            $locais = $locais !== '' ? $locais : 'Sem local';
            $unidade = $product->unit?->name ?? '-';

            $html .= '
                <div style="
                    border:1px solid #d8dee8;
                    border-radius:10px;
                    padding:12px 14px;
                    margin-bottom:12px;
                    background-color:#fafafa;
                ">
                    <h2 style="font-size:14pt; margin:0 0 8px 0; color:#111827;">
                        ' . e($nome) . '
                    </h2>

                    <table cellpadding="4" cellspacing="0" border="0" width="100%">
                        <tr>
                            <td width="18%"><strong>Locais:</strong></td>
                            <td width="82%">' . e($locais) . '</td>
                        </tr>
                        <tr>
                            <td width="18%"><strong>Unidade:</strong></td>
                            <td width="82%">' . e($unidade) . '</td>
                        </tr>
                    </table>
            ';

            if ($product->uses_variants && $product->variants->count()) {
                $html .= '
                    <div style="margin-top:8px;">
                        <strong style="font-size:11.5pt;">Variações e quantidades</strong>
                        <div style="margin-top:6px;">
                ';

                foreach ($product->variants as $variant) {
                    $variantName = $this->normalizeLabel($variant->name);
                    $variantStock = $this->formatNumber($variant->current_stock ?? 0);

                    $html .= '
                        <div style="
                            padding:6px 8px;
                            margin-bottom:4px;
                            border-bottom:1px solid #e5e7eb;
                        ">
                            <span><strong>' . e($variantName) . ':</strong> ' . e($variantStock) . '</span>
                        </div>
                    ';
                }

                $html .= '
                        </div>
                    </div>
                ';
            } else {
                $quantidade = $this->formatNumber($product->current_stock ?? 0);

                $html .= '
                    <div style="margin-top:10px; font-size:11.5pt;">
                        <strong>Quantidade em estoque:</strong> ' . e($quantidade) . '
                    </div>
                ';
            }

            $html .= '</div>';
        }
    }

    $pdf->writeHTML($html, true, false, true, false, '');

    $fileName = 'Estoque_Produtos';

    if ($locationFilter) {
        $fileName .= '_' . ucfirst($locationFilter);
    }

    $fileName .= '_' . now()->format('d-m-Y') . '.pdf';

    $content = $pdf->Output($fileName, 'S');

    return response($content, 200, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
    ]);
}

    public function store(StoreProductRequest $request, StockService $stockService): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $stockService) {
            $product = Product::create([
                'name' => $validated['name'],
                'sku' => $validated['sku'] ?? null,
                'description' => $validated['description'] ?? null,
                'product_unit_id' => $validated['product_unit_id'],
                'uses_variants' => $validated['uses_variants'],
                'current_stock' => 0,
                'active' => $validated['active'],
            ]);

            $product->locations()->sync($validated['stock_locations']);

            if ($product->uses_variants) {
                $variants = collect($validated['variants'] ?? [])
                    ->filter(fn ($item) => filled($item['name'] ?? null))
                    ->values();

                foreach ($variants as $index => $item) {
                    $variant = $product->variants()->create([
                        'name' => trim((string) $item['name']),
                        'sku' => $item['sku'] ?? null,
                        'current_stock' => 0,
                        'sort_order' => $index,
                        'active' => isset($item['active']) ? (bool) $item['active'] : true,
                    ]);

                    $stockService->createInitialForVariant(
                        $product,
                        $variant,
                        (float) ($item['initial_stock'] ?? 0)
                    );
                }
            } else {
                $stockService->createInitialForProduct(
                    $product,
                    (float) ($validated['initial_stock'] ?? 0)
                );
            }
        });

        return redirect()
            ->route('products.index')
            ->with('success', 'Produto cadastrado com sucesso.');
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated, $product) {
            $product->update([
                'name' => $validated['name'],
                'sku' => $validated['sku'] ?? null,
                'description' => $validated['description'] ?? null,
                'product_unit_id' => $validated['product_unit_id'],
                'active' => (bool) $validated['active'],
            ]);

            $product->locations()->sync($validated['stock_locations']);

            if ($product->uses_variants) {
                $existingVariants = $product->variants()->get()->keyBy('id');

                foreach (($validated['variants'] ?? []) as $index => $item) {
                    $remove = filter_var($item['remove'] ?? false, FILTER_VALIDATE_BOOLEAN);
                    $variantId = $item['id'] ?? null;
                    $name = trim((string) ($item['name'] ?? ''));
                    $sku = isset($item['sku']) && $item['sku'] !== '' ? trim((string) $item['sku']) : null;

                    if ($variantId && $existingVariants->has((int) $variantId)) {
                        $variant = $existingVariants[(int) $variantId];

                        if ($remove) {
                            if ($variant->movements()->exists()) {
                                $variant->update(['active' => false]);
                            } else {
                                $variant->delete();
                            }
                            continue;
                        }

                        if ($name === '') {
                            continue;
                        }

                        $variant->update([
                            'name' => $name,
                            'sku' => $sku,
                            'sort_order' => $index,
                            'active' => isset($item['active']) ? (bool) $item['active'] : true,
                        ]);
                    } else {
                        if ($remove || $name === '') {
                            continue;
                        }

                        $product->variants()->create([
                            'name' => $name,
                            'sku' => $sku,
                            'current_stock' => 0,
                            'sort_order' => $index,
                            'active' => isset($item['active']) ? (bool) $item['active'] : true,
                        ]);
                    }
                }
            }
        });

        return redirect()
            ->route('products.index')
            ->with('success', 'Produto atualizado com sucesso.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->update([
            'active' => false,
        ]);

        return redirect()
            ->route('products.index')
            ->with('success', 'Produto inativado com sucesso.');
    }

    private function normalizeLabel(?string $value): string
    {
        $value = trim((string) $value);
        $value = preg_replace('/\s+/', ' ', $value);

        return $value;
    }

    private function formatNumber($value): string
    {
        $value = (float) $value;

        if (fmod($value, 1.0) === 0.0) {
            return (string) (int) $value;
        }

        return rtrim(rtrim(number_format($value, 3, '.', ''), '0'), '.');
    }

    private function getTcpdfPath(): ?string
    {
        $paths = [
            base_path('lib/TCPDF/tcpdf.php'),
            base_path('tcpdf/tcpdf.php'),
            base_path('vendor/tecnickcom/tcpdf/tcpdf.php'),
        ];

        foreach ($paths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }

    private function getLogoPath(): ?string
    {
        $paths = [
            public_path('assets/imgs/LOGO VIA NORTE.jpg'),
            public_path('assets/imgs/LOGO VIA NORTE.png'),
            public_path('imgs/LOGO VIA NORTE.jpg'),
            public_path('imgs/LOGO VIA NORTE.png'),
        ];

        foreach ($paths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }

        return null;
    }
}