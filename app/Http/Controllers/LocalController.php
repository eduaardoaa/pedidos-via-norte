<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Route as RouteModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class LocalController extends Controller
{
    public function index(Request $request)
    {
        $routeFilter = $request->get('route_id');
        $scopeFilter = $request->get('scope');

        $rotas = RouteModel::orderBy('name')->get();

        $locais = Location::with('route')
            ->when($scopeFilter, function ($query) use ($scopeFilter) {
                $query->where('scope', $scopeFilter);
            })
            ->when($routeFilter, function ($query) use ($routeFilter) {
                $query->where('route_id', $routeFilter);
            })
            ->orderBy('name')
            ->get();

        return view('locais.index', compact('locais', 'rotas', 'routeFilter', 'scopeFilter'));
    }

    public function pdf(Request $request)
    {
        $routeFilter = $request->get('route_id');
        $scopeFilter = $request->get('scope');

        $locais = Location::with('route')
            ->when($scopeFilter, function ($query) use ($scopeFilter) {
                $query->where('scope', $scopeFilter);
            })
            ->when($routeFilter, function ($query) use ($routeFilter) {
                $query->where('route_id', $routeFilter);
            })
            ->orderBy('name')
            ->get();

        $tcpdfPath = $this->getTcpdfPath();

        if (! $tcpdfPath) {
            abort(500, 'TCPDF não encontrado. Coloque a biblioteca em lib/TCPDF/tcpdf.php, tcpdf/tcpdf.php ou vendor/tecnickcom/tcpdf/tcpdf.php.');
        }

        require_once $tcpdfPath;

        $pdf = new \TCPDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);
        $pdf->SetCreator(config('app.name'));
        $pdf->SetAuthor(config('app.name'));
        $pdf->SetTitle('Lista de Locais');
        $pdf->SetMargins(10, 12, 10);
        $pdf->SetHeaderMargin(0);
        $pdf->SetFooterMargin(8);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(true);
        $pdf->setFontSubsetting(true);
        $pdf->SetAutoPageBreak(true, 12);

        $pdf->AddPage();

        $logo = $this->getLogoPath();

        if ($logo) {
            $pdf->Image($logo, 10, 8, 34, '', '', '', 'T', false, 300);
        }

        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->SetXY(50, 10);
        $pdf->Cell(0, 8, 'Lista de Locais', 0, 1, 'L');

        $pdf->SetFont('helvetica', '', 10);

        $tipoTexto = match ($scopeFilter) {
            'rota' => 'Rota',
            'almoxarifado' => 'Almoxarifado',
            'centro_custo' => 'Centro de Custo',
            default => 'Todos',
        };

        $rotaTexto = 'Todas as rotas';
        if ($routeFilter) {
            $rota = RouteModel::find($routeFilter);
            $rotaTexto = $rota?->name ?? 'Rota não encontrada';
        }

        $pdf->SetXY(50, 18);
        $pdf->Cell(0, 6, 'Tipo: ' . $tipoTexto, 0, 1, 'L');

        $pdf->SetX(50);
        $pdf->Cell(0, 6, 'Rota: ' . $rotaTexto, 0, 1, 'L');

        $pdf->SetX(50);
        $pdf->Cell(0, 6, 'Gerado em: ' . now()->format('d/m/Y H:i'), 0, 1, 'L');

        $pdf->Ln(8);

        $html = '';
        $html .= '<table cellpadding="6" cellspacing="0" border="1" width="100%">';
        $html .= '<thead>';
        $html .= '<tr style="background-color:#e5e7eb; font-weight:bold;">';
        $html .= '<th width="6%" align="center"><strong>ID</strong></th>';
        $html .= '<th width="23%"><strong>Local</strong></th>';
        $html .= '<th width="14%"><strong>Tipo</strong></th>';
        $html .= '<th width="18%"><strong>Rota</strong></th>';
        $html .= '<th width="27%"><strong>Endereço</strong></th>';
        $html .= '<th width="12%" align="center"><strong>Status</strong></th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';

        if ($locais->isEmpty()) {
            $html .= '<tr>';
            $html .= '<td colspan="6" align="center">Nenhum local encontrado.</td>';
            $html .= '</tr>';
        } else {
            foreach ($locais as $local) {
                $tipo = match ($local->scope) {
                    'almoxarifado' => 'Almoxarifado',
                    'centro_custo' => 'Centro de Custo',
                    default => 'Rota',
                };

                $rota = $local->route?->name ?? '-';
                $endereco = trim((string) ($local->address ?? '')) !== '' ? $local->address : 'Sem endereço';
                $status = $local->active ? 'Ativo' : 'Inativo';

                $html .= '<tr>';
                $html .= '<td width="6%" align="center">' . e((string) $local->id) . '</td>';
                $html .= '<td width="23%">' . e($this->cleanPdfText($local->name)) . '</td>';
                $html .= '<td width="14%">' . e($tipo) . '</td>';
                $html .= '<td width="18%">' . e($this->cleanPdfText($rota)) . '</td>';
                $html .= '<td width="27%">' . e($this->cleanPdfText($endereco)) . '</td>';
                $html .= '<td width="12%" align="center">' . e($status) . '</td>';
                $html .= '</tr>';
            }
        }

        $html .= '</tbody>';
        $html .= '</table>';

        $html .= '<br><p style="font-size:10pt;"><strong>Total de locais:</strong> ' . $locais->count() . '</p>';

        $pdf->writeHTML($html, true, false, true, false, '');

        $fileName = 'Locais_' . now()->format('d-m-Y_H-i') . '.pdf';
        $content = $pdf->Output($fileName, 'S');

        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'scope' => ['required', Rule::in(['rota', 'almoxarifado', 'centro_custo'])],
            'route_id' => ['nullable', 'exists:routes,id'],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'active' => ['nullable', 'boolean'],
        ], [
            'scope.required' => 'Selecione o tipo do local.',
            'scope.in' => 'Tipo de local inválido.',
            'route_id.exists' => 'Rota inválida.',
            'name.required' => 'Informe o nome do local.',
        ]);

        $validator->after(function ($validator) use ($request) {
            if ($request->scope === 'rota' && ! $request->route_id) {
                $validator->errors()->add('route_id', 'Selecione a rota para um local de rota.');
            }
        });

        if ($validator->fails()) {
            return redirect()
                ->route('locais.index', [
                    'route_id' => $request->route_id,
                    'scope' => $request->scope,
                ])
                ->withErrors($validator)
                ->withInput()
                ->with('open_modal', 'create');
        }

        $data = $validator->validated();
        $data['active'] = $request->boolean('active', true);

        if (in_array($data['scope'], ['almoxarifado', 'centro_custo'], true)) {
            $data['route_id'] = null;
        }

        $coordinates = $this->geocodeAddress($data['address'] ?? null);

        $data['latitude'] = $coordinates['latitude'] ?? null;
        $data['longitude'] = $coordinates['longitude'] ?? null;
        $data['geocoded_at'] = $coordinates ? now() : null;

        Location::create($data);

        return redirect()
            ->route('locais.index', [
                'route_id' => $request->route_id,
                'scope' => $request->scope,
            ])
            ->with('success', 'Local criado com sucesso.');
    }

    public function update(Request $request, Location $local)
    {
        $validator = Validator::make($request->all(), [
            'scope' => ['required', Rule::in(['rota', 'almoxarifado', 'centro_custo'])],
            'route_id' => ['nullable', 'exists:routes,id'],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'active' => ['nullable', 'boolean'],
        ], [
            'scope.required' => 'Selecione o tipo do local.',
            'scope.in' => 'Tipo de local inválido.',
            'route_id.exists' => 'Rota inválida.',
            'name.required' => 'Informe o nome do local.',
        ]);

        $validator->after(function ($validator) use ($request) {
            if ($request->scope === 'rota' && ! $request->route_id) {
                $validator->errors()->add('route_id', 'Selecione a rota para um local de rota.');
            }
        });

        if ($validator->fails()) {
            return redirect()
                ->route('locais.index', [
                    'route_id' => $request->route_id ?: $local->route_id,
                    'scope' => $request->scope ?: $local->scope,
                ])
                ->withErrors($validator)
                ->withInput()
                ->with('open_modal', 'edit_' . $local->id);
        }

        $data = $validator->validated();
        $data['active'] = $request->boolean('active');

        if (in_array($data['scope'], ['almoxarifado', 'centro_custo'], true)) {
            $data['route_id'] = null;
        }

        $addressChanged = trim((string) ($data['address'] ?? '')) !== trim((string) ($local->address ?? ''));

        if ($addressChanged) {
            $coordinates = $this->geocodeAddress($data['address'] ?? null);

            $data['latitude'] = $coordinates['latitude'] ?? null;
            $data['longitude'] = $coordinates['longitude'] ?? null;
            $data['geocoded_at'] = $coordinates ? now() : null;
        }

        $local->update($data);

        return redirect()
            ->route('locais.index', [
                'route_id' => $request->route_id,
                'scope' => $request->scope,
            ])
            ->with('success', 'Local atualizado com sucesso.');
    }

    public function toggle(Location $local)
    {
        $local->update([
            'active' => ! $local->active,
        ]);

        return redirect()
            ->route('locais.index', [
                'route_id' => request('route_id') ?: $local->route_id,
                'scope' => request('scope') ?: $local->scope,
            ])
            ->with('success', $local->active ? 'Local ativado com sucesso.' : 'Local inativado com sucesso.');
    }

    private function geocodeAddress(?string $address): ?array
    {
        if (blank($address)) {
            return null;
        }

        $queries = array_values(array_unique(array_filter([
            trim($address),
            trim($address . ', Aracaju, SE, Brasil'),
            trim($address . ', Sergipe, Brasil'),
            trim($address . ', Brasil'),
        ])));

        foreach ($queries as $query) {
            try {
                $response = Http::acceptJson()
                    ->withHeaders([
                        'User-Agent' => 'Vianorte/1.0 (contato-interno)',
                    ])
                    ->connectTimeout(3)
                    ->timeout(6)
                    ->get('https://nominatim.openstreetmap.org/search', [
                        'q' => $query,
                        'format' => 'jsonv2',
                        'limit' => 1,
                        'addressdetails' => 1,
                        'countrycodes' => 'br',
                    ]);

                if (! $response->successful()) {
                    continue;
                }

                $json = $response->json();

                if (! is_array($json) || empty($json[0])) {
                    continue;
                }

                $latitude = isset($json[0]['lat']) ? (float) $json[0]['lat'] : null;
                $longitude = isset($json[0]['lon']) ? (float) $json[0]['lon'] : null;

                if ($latitude === null || $longitude === null) {
                    continue;
                }

                return [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                ];
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return null;
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

    private function cleanPdfText(?string $value): string
    {
        $value = trim((string) $value);
        return preg_replace('/\s+/', ' ', $value);
    }
}