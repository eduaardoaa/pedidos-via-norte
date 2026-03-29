<?php

namespace App\Http\Controllers;

use App\Models\MaterialRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminMaterialRequestController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeAdmin();

        $query = MaterialRequest::with([
            'user',
            'route',
            'location',
            'approver',
            'items.product',
            'items.variant',
        ])->latest('id');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('scope')) {
            $query->where('scope', $request->scope);
        }

        if ($request->filled('requester_role')) {
            $query->where('requester_role', $request->requester_role);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $requests = $query->paginate(20)->withQueryString();

        $users = User::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.material-requests.index', compact('requests', 'users'));
    }

    public function show(MaterialRequest $materialRequest): View
    {
        $this->authorizeAdmin();

        $materialRequest->load([
            'user',
            'route',
            'location',
            'approver',
            'items.product',
            'items.variant',
        ]);

        return view('admin.material-requests.show', compact('materialRequest'));
    }

    public function approve(Request $request, MaterialRequest $materialRequest): RedirectResponse
    {
        $this->authorizeAdmin();

        if ($materialRequest->status !== 'pending') {
            return back()->with('error', 'Somente solicitações pendentes podem ser aprovadas.');
        }

        $materialRequest->update([
            'status' => 'approved',
            'admin_notes' => $request->admin_notes,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Solicitação aprovada com sucesso.');
    }

    public function reject(Request $request, MaterialRequest $materialRequest): RedirectResponse
    {
        $this->authorizeAdmin();

        if ($materialRequest->status !== 'pending') {
            return back()->with('error', 'Somente solicitações pendentes podem ser recusadas.');
        }

        $materialRequest->update([
            'status' => 'rejected',
            'admin_notes' => $request->admin_notes,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Solicitação recusada com sucesso.');
    }

    public function redirectToOrder(MaterialRequest $materialRequest): RedirectResponse
    {
        $this->authorizeAdmin();

        if ($materialRequest->status !== 'pending') {
            return back()->with('error', 'Somente solicitações pendentes podem gerar pedido.');
        }

        return redirect()->route('orders.create', [
            'material_request_id' => $materialRequest->id,
        ]);
    }

    protected function authorizeAdmin(): void
    {
        $user = Auth::user();

        if (!$user || (($user->cargo->codigo ?? null) !== 'admin')) {
            abort(403);
        }
    }
}