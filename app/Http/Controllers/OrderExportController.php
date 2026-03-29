<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class OrderExportController extends Controller
{
    public function pdfSingle(Order $order)
    {
        $order->load([
            'location.route',
            'route',
            'user',
            'items.product.unit',
            'items.variant',
        ]);

        return $this->generatePdfResponse(
            collect([$order]),
            'Pedido_' . $order->id . '_' . now()->format('d-m-Y') . '.pdf'
        );
    }

    public function pdfBatch(Request $request)
    {
        $data = $request->validate([
            'order_ids' => ['required', 'array', 'min:1'],
            'order_ids.*' => ['integer', 'exists:orders,id'],
        ]);

        $orders = Order::with([
            'location.route',
            'route',
            'user',
            'items.product.unit',
            'items.variant',
        ])
            ->whereIn('id', $data['order_ids'])
            ->orderBy('id')
            ->get();

        if ($orders->isEmpty()) {
            return back()->with('error', 'Nenhum pedido válido foi selecionado.');
        }

        return $this->generatePdfResponse($orders, 'Pedidos_' . now()->format('d-m-Y') . '.pdf');
    }

    public function excelSingle(Order $order)
    {
        $order->load([
            'items.product.unit',
            'items.variant',
        ]);

        return $this->generateExcelResponse(
            collect([$order]),
            'Contagem_Pedido_' . $order->id . '_' . now()->format('d-m-Y') . '.xlsx'
        );
    }

    public function excelBatch(Request $request)
    {
        $data = $request->validate([
            'order_ids' => ['required', 'array', 'min:1'],
            'order_ids.*' => ['integer', 'exists:orders,id'],
        ]);

        $orders = Order::with([
            'items.product.unit',
            'items.variant',
        ])
            ->whereIn('id', $data['order_ids'])
            ->orderBy('id')
            ->get();

        if ($orders->isEmpty()) {
            return back()->with('error', 'Nenhum pedido válido foi selecionado.');
        }

        return $this->generateExcelResponse(
            $orders,
            'Contagem_Pedidos_' . now()->format('d-m-Y') . '.xlsx'
        );
    }

    private function generatePdfResponse($orders, string $fileName)
    {
        $tcpdfPath = $this->getTcpdfPath();

        if (!$tcpdfPath) {
            abort(500, 'TCPDF não encontrado. Coloque a biblioteca em lib/TCPDF/tcpdf.php ou me peça para adaptar para outra biblioteca.');
        }

        require_once $tcpdfPath;

        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(config('app.name'));
        $pdf->SetAuthor(config('app.name'));
        $pdf->SetTitle('Requisições de Pedidos');
        $pdf->SetMargins(12, 14, 12);
        $pdf->SetHeaderMargin(0);
        $pdf->SetFooterMargin(10);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(true);
        $pdf->setFontSubsetting(true);

        $logo = $this->getLogoPath();

        foreach ($orders as $order) {
            $pdf->AddPage();

            if ($logo) {
                $pdf->Image($logo, 12, 10, 40, '', '', '', 'T', false, 300);
            }

            $numeroPedido = 'Número do Pedido: ' . $order->id;
            $pdf->SetFont('helvetica', '', 12);
            $pdf->SetXY(120, 12);
            $pdf->Cell(75, 8, $numeroPedido, 0, 1, 'R');

            $pdf->Ln(12);

            $tipo = ($order->location->scope ?? null) === 'almoxarifado' ? 'Almoxarifado' : 'Rota';
            $local = $order->location->name ?? '-';
            $rota = $order->route->name ?? ($order->location->route->name ?? '-');
            $usuario = $order->user->name ?? '-';
            $data = optional($order->order_date)->format('d/m/Y');

            $html = '';
            $html .= '<h1 style="font-size:17pt;">Detalhes do Pedido</h1>';
            $html .= '<p style="font-size:12pt;"><strong>Tipo:</strong> ' . e($tipo) . '</p>';
            $html .= '<p style="font-size:12pt;"><strong>Local:</strong> ' . e($local) . '</p>';
            $html .= '<p style="font-size:12pt;"><strong>Rota:</strong> ' . e($rota) . '</p>';
            $html .= '<p style="font-size:12pt;"><strong>Usuário:</strong> ' . e($usuario) . '</p>';
            $html .= '<p style="font-size:12pt;"><strong>Data:</strong> ' . e($data) . '</p>';

            $html .= '<h2 style="font-size:14pt; margin-top:10px;">Itens do Pedido</h2>';
            $html .= '<ul style="font-size:11.5pt;">';

            $itemsOrdenados = $order->items->sortBy(function ($item) {
                $nome = $item->product_name_snapshot ?: ($item->product->name ?? 'Item');
                return $this->normalizeForSort($nome);
            });

            foreach ($itemsOrdenados as $item) {
                if ((float) $item->quantity <= 0) {
                    continue;
                }

                $nome = $this->normalizeLabel(
                    $item->product_name_snapshot ?: ($item->product->name ?? 'Item')
                );

                $quantidade = $this->formatNumber($item->quantity);

                $unidade = $this->formatUnit(
                    $this->normalizeUnitLabel(
                        $item->unit_snapshot ?? ($item->product->unit->name ?? '')
                    )
                );

                $linha = '<li><strong>' . e($nome) . '</strong> - ' . e($quantidade);

                if ($unidade !== '') {
                    $linha .= ' ' . e($unidade);
                }

                $linha .= '</li>';

                $html .= $linha;
            }

            $html .= '</ul>';

            $pdf->writeHTML($html, true, false, true, false, '');
        }

        $content = $pdf->Output($fileName, 'S');

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    private function generateExcelResponse($orders, string $fileName)
    {
        $totais = [];

        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $quantidade = (float) $item->quantity;

                if ($quantidade <= 0) {
                    continue;
                }

                $nomeOriginal = $item->product_name_snapshot ?: ($item->product->name ?? 'Item');
                $unidadeOriginal = $item->unit_snapshot ?? ($item->product->unit->name ?? '');

                $nome = $this->normalizeLabel($nomeOriginal);
                $unidade = $this->normalizeUnitLabel($unidadeOriginal);

                $chave = $this->normalizeForSort($nome) . '||' . $this->normalizeForSort($unidade);

                if (!isset($totais[$chave])) {
                    $totais[$chave] = [
                        'produto' => $nome,
                        'quantidade_total' => 0,
                        'unidade' => $unidade,
                    ];
                }

                $totais[$chave]['quantidade_total'] += $quantidade;
            }
        }

        uasort($totais, function ($a, $b) {
            return strcmp(
                $this->normalizeForSort($a['produto']),
                $this->normalizeForSort($b['produto'])
            );
        });

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Contagem');

        $sheet->setCellValue('A1', 'Produto');
        $sheet->setCellValue('B1', 'Quantidade Total');
        $sheet->setCellValue('C1', 'Unidade');

        $sheet->getColumnDimension('A')->setWidth(50);
        $sheet->getColumnDimension('B')->setWidth(20);
        $sheet->getColumnDimension('C')->setWidth(18);

        $row = 2;
        foreach ($totais as $item) {
            $sheet->setCellValue('A' . $row, $item['produto']);
            $sheet->setCellValue('B' . $row, $this->formatNumber($item['quantidade_total']));
            $sheet->setCellValue('C' . $row, $item['unidade']);
            $row++;
        }

        $writer = new Xlsx($spreadsheet);

        ob_start();
        $writer->save('php://output');
        $excelContent = ob_get_clean();

        return response($excelContent, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    private function normalizeLabel(?string $value): string
    {
        $value = trim((string) $value);
        $value = preg_replace('/\s+/', ' ', $value);

        return $value;
    }

    private function normalizeUnitLabel(?string $unit): string
    {
        $unit = trim((string) $unit);
        $unit = preg_replace('/\s+/', ' ', $unit);

        if ($unit === '') {
            return '';
        }

        $lower = mb_strtolower($unit, 'UTF-8');

        return match ($lower) {
            'unidade', 'unidades' => 'unidade',
            'litro', 'litros' => 'litro',
            'rolo', 'rolos' => 'rolo',
            default => $unit,
        };
    }

    private function formatUnit(?string $unit): string
    {
        $unit = trim((string) $unit);

        if ($unit === '') {
            return '';
        }

        $lower = mb_strtolower($unit);

        return match ($lower) {
            'unidade' => 'unidades',
            'rolo' => 'rolos',
            'litro' => 'litros',
            default => $unit,
        };
    }

    private function formatNumber($value): string
    {
        $value = (float) $value;

        if (fmod($value, 1.0) === 0.0) {
            return (string) (int) $value;
        }

        return rtrim(rtrim(number_format($value, 3, '.', ''), '0'), '.');
    }

    private function normalizeForSort(?string $value): string
    {
        $value = trim((string) $value);
        $value = mb_strtolower($value, 'UTF-8');

        if (function_exists('iconv')) {
            $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
            if ($converted !== false) {
                $value = $converted;
            }
        }

        return $value;
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