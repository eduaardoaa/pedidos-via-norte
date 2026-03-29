<?php

namespace App\Http\Controllers;

use App\Models\MaterialRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SupervisorDashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        $cargoCodigo = $user->cargo->codigo ?? null;

        if ($cargoCodigo !== 'supervisor') {
            abort(403);
        }

        $baseQuery = MaterialRequest::query()
            ->where('user_id', $user->id)
            ->where('requester_role', 'supervisor');

        $totalRequests = (clone $baseQuery)->count();
        $pendingRequests = (clone $baseQuery)->where('status', 'pending')->count();
        $approvedRequests = (clone $baseQuery)->where('status', 'approved')->count();
        $rejectedRequests = (clone $baseQuery)->where('status', 'rejected')->count();

        $latestRequests = (clone $baseQuery)
            ->with(['location'])
            ->latest('id')
            ->limit(5)
            ->get();

        return view('supervisor.dashboard', compact(
            'totalRequests',
            'pendingRequests',
            'approvedRequests',
            'rejectedRequests',
            'latestRequests'
        ));
    }
}