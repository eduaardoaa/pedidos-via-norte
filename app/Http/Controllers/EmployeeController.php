<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Cargo;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with('cargo');

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

        $employees = $query
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $cargos = Cargo::where('ativo', 1)
            ->orderBy('id')
            ->get();

        return view('employees.index', compact('employees', 'cargos'));
    }

    public function create()
    {
        $cargos = Cargo::where('ativo', 1)
            ->orderBy('id')
            ->get();

        return view('employees.create', compact('cargos'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'cpf' => ['required', 'string', 'max:14', 'unique:employees,cpf'],
            'registration' => ['required', 'string', 'max:255', 'unique:employees,registration'],
            'hired_at' => ['required', 'date'],
            'cargo_id' => ['required', 'exists:cargos,id'],
            'active' => ['nullable', 'boolean'],
        ]);

        Employee::create([
            'name' => $validated['name'],
            'cpf' => $validated['cpf'],
            'registration' => $validated['registration'],
            'hired_at' => $validated['hired_at'],
            'cargo_id' => $validated['cargo_id'],
            'active' => $request->boolean('active', true),
        ]);

        return redirect()
            ->route('employees.index')
            ->with('success', 'Funcionário cadastrado com sucesso!');
    }

    public function edit(Employee $employee)
    {
        $cargos = Cargo::where('ativo', 1)
            ->orWhere('id', $employee->cargo_id)
            ->orderBy('id')
            ->get();

        return view('employees.edit', compact('employee', 'cargos'));
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'cpf' => ['required', 'string', 'max:14', 'unique:employees,cpf,' . $employee->id],
            'registration' => ['required', 'string', 'max:255', 'unique:employees,registration,' . $employee->id],
            'hired_at' => ['required', 'date'],
            'cargo_id' => ['required', 'exists:cargos,id'],
            'active' => ['nullable', 'boolean'],
        ]);

        $employee->update([
            'name' => $validated['name'],
            'cpf' => $validated['cpf'],
            'registration' => $validated['registration'],
            'hired_at' => $validated['hired_at'],
            'cargo_id' => $validated['cargo_id'],
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
}