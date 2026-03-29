<?php

namespace App\Http\Controllers;

use App\Models\MaterialRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CaboDashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        $cargoCodigo = $user->cargo->codigo ?? null;

        if ($cargoCodigo !== 'cabo de turma') {
            abort(403);
        }

        $baseQuery = MaterialRequest::query()
            ->where('user_id', $user->id)
            ->where('requester_role', 'cabo_turma');

        $totalRequests = (clone $baseQuery)->count();
        $pendingRequests = (clone $baseQuery)->where('status', 'pending')->count();
        $approvedRequests = (clone $baseQuery)->where('status', 'approved')->count();
        $rejectedRequests = (clone $baseQuery)->where('status', 'rejected')->count();

        $latestRequests = (clone $baseQuery)
            ->with(['route', 'location'])
            ->latest('id')
            ->limit(5)
            ->get();

        return view('cabo.dashboard', compact(
            'totalRequests',
            'pendingRequests',
            'approvedRequests',
            'rejectedRequests',
            'latestRequests'
        ));
    }
}