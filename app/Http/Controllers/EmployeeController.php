<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Cargo;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;


class EmployeeController extends Controller
{
    public function index(Request $request)
{
    $query = Employee::with(['cargo', 'location']);

    if ($request->filled('search')) {
        $search = trim($request->search);

        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('cpf', 'like', "%{$search}%")
                ->orWhere('registration', 'like', "%{$search}%");
        });
    }

    if ($request->filled('cargo_id')) {
        $query->where('cargo_id', $request->cargo_id);
    }

    if ($request->filled('location_id')) {
        $query->where('location_id', $request->location_id);
    }

    $employees = $query
        ->orderBy('name')
        ->get();

    $cargos = Cargo::where('ativo', 1)
        ->orderBy('id')
        ->get();

    $centrosCusto = Location::query()
        ->where('scope', 'centro_custo')
        ->where('active', 1)
        ->orderBy('name')
        ->get();

    return view('employees.index', compact('employees', 'cargos', 'centrosCusto'));
}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'cpf' => ['required', 'string', 'max:14', 'unique:employees,cpf'],
            'registration' => ['required', 'string', 'max:255', 'unique:employees,registration'],
            'hired_at' => ['required', 'date'],
            'cargo_id' => ['required', 'exists:cargos,id'],
            'location_id' => ['required', 'exists:locations,id'],
            'active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Informe o nome do funcionário.',
            'cpf.required' => 'Informe o CPF.',
            'cpf.unique' => 'Este CPF já está cadastrado.',
            'registration.required' => 'Informe a matrícula.',
            'registration.unique' => 'Esta matrícula já está cadastrada.',
            'hired_at.required' => 'Informe a data de contratação.',
            'cargo_id.required' => 'Selecione o cargo.',
            'cargo_id.exists' => 'Cargo inválido.',
            'location_id.required' => 'Selecione o centro de custo.',
            'location_id.exists' => 'Centro de custo inválido.',
        ]);

        $location = Location::query()
            ->where('id', $validated['location_id'])
            ->where('scope', 'centro_custo')
            ->where('active', 1)
            ->first();

        if (! $location) {
            return redirect()
                ->route('employees.index')
                ->withErrors(['location_id' => 'Selecione um local válido do tipo Centro de Custo.'])
                ->withInput()
                ->with('open_modal', 'create');
        }

        Employee::create([
            'name' => $validated['name'],
            'cpf' => $validated['cpf'],
            'registration' => $validated['registration'],
            'hired_at' => $validated['hired_at'],
            'cargo_id' => $validated['cargo_id'],
            'location_id' => $validated['location_id'],
            'active' => $request->boolean('active', true),
        ]);

        return redirect()
            ->route('employees.index')
            ->with('success', 'Funcionário cadastrado com sucesso!');
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'cpf' => ['required', 'string', 'max:14', Rule::unique('employees', 'cpf')->ignore($employee->id)],
            'registration' => ['required', 'string', 'max:255', Rule::unique('employees', 'registration')->ignore($employee->id)],
            'hired_at' => ['required', 'date'],
            'cargo_id' => ['required', 'exists:cargos,id'],
            'location_id' => ['required', 'exists:locations,id'],
            'active' => ['nullable', 'boolean'],
        ], [
            'name.required' => 'Informe o nome do funcionário.',
            'cpf.required' => 'Informe o CPF.',
            'cpf.unique' => 'Este CPF já está cadastrado.',
            'registration.required' => 'Informe a matrícula.',
            'registration.unique' => 'Esta matrícula já está cadastrada.',
            'hired_at.required' => 'Informe a data de contratação.',
            'cargo_id.required' => 'Selecione o cargo.',
            'cargo_id.exists' => 'Cargo inválido.',
            'location_id.required' => 'Selecione o centro de custo.',
            'location_id.exists' => 'Centro de custo inválido.',
        ]);

        $location = Location::query()
            ->where('id', $validated['location_id'])
            ->where('scope', 'centro_custo')
            ->where('active', 1)
            ->first();

        if (! $location) {
            return redirect()
                ->route('employees.index')
                ->withErrors(['location_id' => 'Selecione um local válido do tipo Centro de Custo.'])
                ->withInput()
                ->with('open_modal', 'edit_' . $employee->id);
        }

        $employee->update([
            'name' => $validated['name'],
            'cpf' => $validated['cpf'],
            'registration' => $validated['registration'],
            'hired_at' => $validated['hired_at'],
            'cargo_id' => $validated['cargo_id'],
            'location_id' => $validated['location_id'],
            'active' => $request->boolean('active'),
        ]);

        return redirect()
            ->route('employees.index')
            ->with('success', 'Funcionário atualizado com sucesso!');
    }

    public function destroy(Employee $employee)
    {
        $employee->update([
            'active' => false,
        ]);

        return redirect()
            ->route('employees.index')
            ->with('success', 'Funcionário inativado com sucesso!');
    }
    public function pdf(Request $request)
{
    $search = trim((string) $request->get('search'));
    $cargoFilter = $request->get('cargo_id');
    $locationFilter = $request->get('location_id');

    $query = Employee::with(['cargo', 'location']);

    if ($search !== '') {
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('cpf', 'like', "%{$search}%")
                ->orWhere('registration', 'like', "%{$search}%");
        });
    }

    if ($cargoFilter) {
        $query->where('cargo_id', $cargoFilter);
    }

    if ($locationFilter) {
        $query->where('location_id', $locationFilter);
    }

    $employees = $query
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
    $pdf->SetTitle('Lista de Funcionários');
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
    $pdf->Cell(0, 8, 'Lista de Funcionários', 0, 1, 'L');

    $pdf->SetFont('helvetica', '', 10);

    $cargoTexto = 'Todos';
    if ($cargoFilter) {
        $cargo = Cargo::find($cargoFilter);
        $cargoTexto = $cargo->nome ?? $cargo->name ?? $cargo->descricao ?? $cargo->cargo ?? 'Cargo não encontrado';
    }

    $centroCustoTexto = 'Todos';
    if ($locationFilter) {
        $location = Location::find($locationFilter);
        $centroCustoTexto = $location?->name ?? 'Centro de custo não encontrado';
    }

    $buscaTexto = $search !== '' ? $search : 'Nenhuma';

    $pdf->SetXY(50, 18);
    $pdf->Cell(0, 6, 'Busca: ' . $this->cleanPdfText($buscaTexto), 0, 1, 'L');

    $pdf->SetX(50);
    $pdf->Cell(0, 6, 'Cargo: ' . $this->cleanPdfText($cargoTexto), 0, 1, 'L');

    $pdf->SetX(50);
    $pdf->Cell(0, 6, 'Centro de Custo: ' . $this->cleanPdfText($centroCustoTexto), 0, 1, 'L');

    $pdf->SetX(50);
    $pdf->Cell(0, 6, 'Gerado em: ' . now()->format('d/m/Y H:i'), 0, 1, 'L');

    $pdf->Ln(8);

    $html = '';
    $html .= '<table cellpadding="6" cellspacing="0" border="1" width="100%">';
    $html .= '<thead>';
    $html .= '<tr style="background-color:#e5e7eb; font-weight:bold;">';
    $html .= '<th width="20%"><strong>Nome</strong></th>';
    $html .= '<th width="12%"><strong>CPF</strong></th>';
    $html .= '<th width="12%"><strong>Matrícula</strong></th>';
    $html .= '<th width="16%"><strong>Cargo</strong></th>';
    $html .= '<th width="16%"><strong>Centro de Custo</strong></th>';
    $html .= '<th width="12%" align="center"><strong>Contratação</strong></th>';
    $html .= '<th width="12%" align="center"><strong>Status</strong></th>';
    $html .= '</tr>';
    $html .= '</thead>';
    $html .= '<tbody>';

    if ($employees->isEmpty()) {
        $html .= '<tr>';
        $html .= '<td colspan="7" align="center">Nenhum funcionário encontrado.</td>';
        $html .= '</tr>';
    } else {
        foreach ($employees as $employee) {
            $cargo = $employee->cargo->nome
                ?? $employee->cargo->name
                ?? $employee->cargo->descricao
                ?? $employee->cargo->cargo
                ?? '-';

            $centroCusto = $employee->location?->name ?? '-';
            $contratacao = optional($employee->hired_at)?->format('d/m/Y') ?: '-';
            $status = $employee->active ? 'Ativo' : 'Inativo';

            $html .= '<tr>';
            $html .= '<td width="20%">' . e($this->cleanPdfText($employee->name)) . '</td>';
            $html .= '<td width="12%">' . e($this->cleanPdfText($employee->cpf)) . '</td>';
            $html .= '<td width="12%">' . e($this->cleanPdfText($employee->registration)) . '</td>';
            $html .= '<td width="16%">' . e($this->cleanPdfText($cargo)) . '</td>';
            $html .= '<td width="16%">' . e($this->cleanPdfText($centroCusto)) . '</td>';
            $html .= '<td width="12%" align="center">' . e($contratacao) . '</td>';
            $html .= '<td width="12%" align="center">' . e($status) . '</td>';
            $html .= '</tr>';
        }
    }

    $html .= '</tbody>';
    $html .= '</table>';
    $html .= '<br><p style="font-size:10pt;"><strong>Total de funcionários:</strong> ' . $employees->count() . '</p>';

    $pdf->writeHTML($html, true, false, true, false, '');

    $fileName = 'Funcionarios_' . now()->format('d-m-Y_H-i') . '.pdf';
    $content = $pdf->Output($fileName, 'S');

    return response($content, 200, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
    ]);
}private function getTcpdfPath(): ?string
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