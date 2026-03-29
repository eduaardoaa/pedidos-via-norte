<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Route as UserRoute;
use App\Models\User;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\View as ViewFacade;
use Illuminate\View\View;

class VisitController extends Controller
{
    public function create(): View
    {
        return view('cabo.visits.create');
    }

    public function store(Request $request): RedirectResponse
{
    $request->validate([
        'latitude' => ['required', 'numeric'],
        'longitude' => ['required', 'numeric'],
        'service_report' => ['required', 'string', 'min:1', 'max:5000'],
    ], [
        'latitude.required' => 'Não foi possível obter a latitude.',
        'longitude.required' => 'Não foi possível obter a longitude.',
        'service_report.required' => 'Informe o que foi feito na visita.',
        'service_report.min' => 'Descreva melhor o que foi feito na visita.',
        'service_report.max' => 'O relato da visita pode ter no máximo 5000 caracteres.',
    ]);

    $latitude = (float) $request->latitude;
    $longitude = (float) $request->longitude;
    $serviceReport = trim((string) $request->service_report);

    $address = $this->reverseGeocode($latitude, $longitude);

    $matchedLocation = $this->findNearestLocation($latitude, $longitude, 1000);

    if (! $matchedLocation) {
        $matchedLocation = $this->findMatchingLocation($address);
    }

    Visit::create([
        'user_id' => Auth::id(),
        'location_id' => $matchedLocation?->id,
        'visited_at' => now(),
        'latitude' => $latitude,
        'longitude' => $longitude,
        'address' => $address,
        'display_name' => $matchedLocation?->name,
        'service_report' => $serviceReport,
    ]);

    return redirect()
        ->route('cabo.visits.index')
        ->with('success', 'Visita registrada com sucesso.');
}

    public function caboIndex(Request $request): View
    {
        $baseQuery = Visit::query()
            ->with([
                'user:id,name',
                'location.route:id,name',
            ])
            ->where('user_id', Auth::id());

        $selectedMonth = $request->filled('month')
            ? Carbon::createFromFormat('Y-m', $request->month)->startOfMonth()
            : now()->startOfMonth();

        $monthStart = $selectedMonth->copy()->startOfMonth();
        $monthEnd = $selectedMonth->copy()->endOfMonth();

        $visitsQuery = clone $baseQuery;

        if ($request->filled('date')) {
            $visitsQuery->whereDate('visited_at', $request->date);
        } elseif ($request->filled('month')) {
            $visitsQuery->whereBetween('visited_at', [$monthStart, $monthEnd]);
        }

        if ($request->filled('route_id')) {
            $visitsQuery->whereHas('location', function ($locationQuery) use ($request) {
                $locationQuery->where('route_id', $request->route_id);
            });
        }

        $visits = $visitsQuery
            ->orderByDesc('visited_at')
            ->paginate(15)
            ->withQueryString();

        $routes = UserRoute::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        $kpiQuery = clone $baseQuery;

        if ($request->filled('date')) {
            $kpiQuery->whereDate('visited_at', $request->date);
        } else {
            $kpiQuery->whereBetween('visited_at', [$monthStart, $monthEnd]);
        }

        if ($request->filled('route_id')) {
            $kpiQuery->whereHas('location', function ($locationQuery) use ($request) {
                $locationQuery->where('route_id', $request->route_id);
            });
        }

        $kpiVisits = $kpiQuery
            ->with(['location.route:id,name'])
            ->get();

        $visitedDays = $kpiVisits
            ->filter(fn ($visit) => ! empty($visit->visited_at))
            ->groupBy(fn ($visit) => $visit->visited_at->format('Y-m-d'))
            ->count();

        $uniqueLocations = $kpiVisits
            ->map(function ($visit) {
                if (! empty($visit->location_id)) {
                    return 'location_' . $visit->location_id;
                }

                return 'fallback_' . md5(
                    (string) ($visit->display_name ?? '') . '|' . (string) ($visit->address ?? '')
                );
            })
            ->unique()
            ->count();

        $uniqueRoutes = $kpiVisits
            ->map(fn ($visit) => $visit->location?->route?->id)
            ->filter()
            ->unique()
            ->count();

        $mostVisitedLocation = $kpiVisits
            ->groupBy(function ($visit) {
                return $visit->location_id
                    ? 'location_' . $visit->location_id
                    : 'fallback_' . md5((string) ($visit->display_name ?? '') . '|' . (string) ($visit->address ?? ''));
            })
            ->map(function ($group) {
                $first = $group->first();

                return [
                    'location_name' => $first->location?->name ?: ($first->display_name ?: 'Local não identificado'),
                    'total' => $group->count(),
                ];
            })
            ->sortByDesc('total')
            ->first();

        $periodLabel = $request->filled('date')
            ? Carbon::parse($request->date)->format('d/m/Y')
            : ucfirst($selectedMonth->copy()->locale('pt_BR')->translatedFormat('F/Y'));

        return view('cabo.visits.index', [
            'visits' => $visits,
            'routes' => $routes,
            'filters' => [
                'date' => $request->date,
                'route_id' => $request->route_id,
                'month' => $request->month ?: $selectedMonth->format('Y-m'),
            ],
            'kpis' => [
                'total_visits' => $kpiVisits->count(),
                'unique_locations' => $uniqueLocations,
                'visited_days' => $visitedDays,
                'unique_routes' => $uniqueRoutes,
                'most_visited_location' => $mostVisitedLocation['location_name'] ?? 'Nenhum local',
                'most_visited_location_total' => $mostVisitedLocation['total'] ?? 0,
                'period_label' => $periodLabel,
            ],
        ]);
    }

    public function index(Request $request): View
    {
        $query = Visit::query()
            ->with([
                'user:id,name',
                'location.route:id,name',
            ]);

        if ($request->filled('date')) {
            $query->whereDate('visited_at', $request->date);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $visits = $query
            ->orderByDesc('visited_at')
            ->paginate(15)
            ->withQueryString();

        $users = User::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.visits.index', [
            'visits' => $visits,
            'users' => $users,
            'filters' => [
                'date' => $request->date,
                'user_id' => $request->user_id,
            ],
        ]);
    }

    public function summarySelector(): View
    {
        $users = User::query()
            ->whereHas('cargo', function ($query) {
                $query->whereRaw('LOWER(codigo) = ?', ['cabo de turma']);
            })
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.visits.summary-selector', [
            'users' => $users,
        ]);
    }

    public function summaryRedirect(Request $request): RedirectResponse
    {
        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ], [
            'user_id.required' => 'Selecione um cabo de turma.',
            'user_id.exists' => 'Usuário inválido.',
        ]);

        return redirect()->route('admin.visits.user-summary', $request->user_id);
    }

    public function userSummary(Request $request, User $user): View
    {
        [$selectedMonth, $currentMonthStart, $currentMonthEnd] = $this->resolveSelectedMonth($request->month);

        $previousMonthStart = $selectedMonth->copy()->subMonth()->startOfMonth();
        $previousMonthEnd = $selectedMonth->copy()->subMonth()->endOfMonth();

        $currentMonthVisitsQuery = Visit::query()
            ->where('user_id', $user->id)
            ->whereBetween('visited_at', [$currentMonthStart, $currentMonthEnd]);

        $previousMonthVisitsQuery = Visit::query()
            ->where('user_id', $user->id)
            ->whereBetween('visited_at', [$previousMonthStart, $previousMonthEnd]);

        $currentMonthVisits = (clone $currentMonthVisitsQuery)->count();
        $previousMonthVisits = (clone $previousMonthVisitsQuery)->count();

        $currentMonthUniqueLocations = $this->countUniqueLocations(clone $currentMonthVisitsQuery);
        $previousMonthUniqueLocations = $this->countUniqueLocations(clone $previousMonthVisitsQuery);

        $visitsQuery = Visit::query()
            ->with([
                'location.route:id,name',
            ])
            ->where('user_id', $user->id);

        if ($request->filled('date')) {
            $visitsQuery->whereDate('visited_at', $request->date);
        } else {
            $visitsQuery->whereBetween('visited_at', [$currentMonthStart, $currentMonthEnd]);
        }

        $visits = $visitsQuery
            ->orderByDesc('visited_at')
            ->paginate(15)
            ->withQueryString();

        $monthlyVisits = Visit::query()
            ->with([
                'location.route:id,name',
            ])
            ->where('user_id', $user->id)
            ->whereBetween('visited_at', [$currentMonthStart, $currentMonthEnd])
            ->orderBy('visited_at')
            ->get();

        $monthlyLocationSummary = $this->buildMonthlyLocationSummary($monthlyVisits);
        $monthlyReport = $this->buildMonthlyReport($monthlyVisits, $monthlyLocationSummary, $selectedMonth);

        return view('admin.visits.user-summary', [
            'user' => $user,
            'visits' => $visits,
            'monthlyLocationSummary' => $monthlyLocationSummary,
            'monthlyReport' => $monthlyReport,
            'kpis' => [
                'current_month_visits' => $currentMonthVisits,
                'previous_month_visits' => $previousMonthVisits,
                'current_month_unique_locations' => $currentMonthUniqueLocations,
                'previous_month_unique_locations' => $previousMonthUniqueLocations,
                'visits_diff' => $currentMonthVisits - $previousMonthVisits,
                'locations_diff' => $currentMonthUniqueLocations - $previousMonthUniqueLocations,
            ],
            'filters' => [
                'date' => $request->date,
                'month' => $selectedMonth->format('Y-m'),
            ],
        ]);
    }

    public function downloadUserSummaryPdf(Request $request, User $user)
    {
        [$selectedMonth, $currentMonthStart, $currentMonthEnd] = $this->resolveSelectedMonth($request->month);

        $reportVisitsQuery = Visit::query()
            ->with([
                'location.route:id,name',
            ])
            ->where('user_id', $user->id);

        if ($request->filled('date')) {
            $reportVisitsQuery->whereDate('visited_at', $request->date);
        } else {
            $reportVisitsQuery->whereBetween('visited_at', [$currentMonthStart, $currentMonthEnd]);
        }

        $reportVisits = $reportVisitsQuery
            ->orderByDesc('visited_at')
            ->get();

        $monthlyVisits = Visit::query()
            ->with([
                'location.route:id,name',
            ])
            ->where('user_id', $user->id)
            ->whereBetween('visited_at', [$currentMonthStart, $currentMonthEnd])
            ->orderBy('visited_at')
            ->get();

        $monthlyLocationSummary = $this->buildMonthlyLocationSummary($monthlyVisits);
        $monthlyReport = $this->buildMonthlyReport($monthlyVisits, $monthlyLocationSummary, $selectedMonth);

        $html = ViewFacade::make('admin.visits.user-summary-pdf', [
            'user' => $user,
            'filters' => [
                'date' => $request->date,
                'month' => $selectedMonth->format('Y-m'),
            ],
            'monthlyReport' => $monthlyReport,
            'monthlyLocationSummary' => $monthlyLocationSummary,
            'visits' => $reportVisits,
            'generatedAt' => now()->format('d/m/Y H:i:s'),
        ])->render();

        $tcpdfPath = $this->getTcpdfPath();

        if (! $tcpdfPath) {
            abort(500, 'TCPDF não encontrado. Coloque a biblioteca em lib/TCPDF/tcpdf.php, tcpdf/tcpdf.php ou instale via Composer.');
        }

        require_once $tcpdfPath;

        $pdf = new \TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf->SetCreator('Vianorte');
        $pdf->SetAuthor('Vianorte');
        $pdf->SetTitle('Relatório de Visitas - ' . $user->name);
        $pdf->SetSubject('Relatório detalhado de visitas');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(true, 12);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->AddPage();
        $pdf->writeHTML($html, true, false, true, false, '');

        $filename = 'relatorio-visitas-' . str($user->name)->slug('-') . '-' . now()->format('Ymd_His') . '.pdf';

        return response($pdf->Output($filename, 'S'), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    private function resolveSelectedMonth(?string $month): array
    {
        $selectedMonth = ! empty($month)
            ? Carbon::createFromFormat('Y-m', $month)->startOfMonth()
            : now()->startOfMonth();

        return [
            $selectedMonth,
            $selectedMonth->copy()->startOfMonth(),
            $selectedMonth->copy()->endOfMonth(),
        ];
    }

    private function countUniqueLocations($query): int
    {
        return $query
            ->get(['location_id', 'display_name', 'address'])
            ->map(function ($visit) {
                if (! empty($visit->location_id)) {
                    return 'location_' . $visit->location_id;
                }

                return 'fallback_' . md5(
                    (string) ($visit->display_name ?? '') . '|' . (string) ($visit->address ?? '')
                );
            })
            ->unique()
            ->count();
    }

    private function buildMonthlyLocationSummary(Collection $monthlyVisits): Collection
    {
        return $monthlyVisits
            ->groupBy(function ($visit) {
                return $visit->location_id
                    ? 'location_' . $visit->location_id
                    : 'no-location-' . md5((string) $visit->display_name . '|' . (string) $visit->address);
            })
            ->map(function ($group) {
                $first = $group->first();
                $lastVisit = $group->sortByDesc('visited_at')->first();

                return [
                    'location_name' => $first->location?->name ?: ($first->display_name ?: 'Local não identificado'),
                    'route_name' => $first->location?->route?->name,
                    'address' => $first->address ?: ($first->location?->address ?? null),
                    'total_visits' => $group->count(),
                    'last_visit' => $lastVisit?->visited_at?->format('d/m/Y H:i:s'),
                ];
            })
            ->sortByDesc('total_visits')
            ->values();
    }

    private function buildMonthlyReport(Collection $monthlyVisits, Collection $monthlyLocationSummary, Carbon $selectedMonth): array
    {
        $selectedMonth = $selectedMonth->copy()->locale('pt_BR');

        $visitedDays = $monthlyVisits
            ->filter(fn ($visit) => ! empty($visit->visited_at))
            ->groupBy(fn ($visit) => $visit->visited_at->format('Y-m-d'))
            ->count();

        $mostVisitedLocation = $monthlyLocationSummary->first();

        return [
            'reference_month' => ucfirst($selectedMonth->translatedFormat('F/Y')),
            'reference_month_label' => ucfirst($selectedMonth->translatedFormat('F/Y')),
            'total_visits' => $monthlyVisits->count(),
            'unique_locations' => $monthlyLocationSummary->count(),
            'visited_days' => $visitedDays,
            'first_visit' => $monthlyVisits->first()?->visited_at?->format('d/m/Y H:i:s'),
            'last_visit' => $monthlyVisits->last()?->visited_at?->format('d/m/Y H:i:s'),
            'most_visited_location' => $mostVisitedLocation ? [
                'location_name' => $mostVisitedLocation['location_name'],
                'total_visits' => $mostVisitedLocation['total_visits'],
            ] : null,
            'average_per_visited_day' => $visitedDays > 0
                ? number_format($monthlyVisits->count() / $visitedDays, 1, ',', '.')
                : '0,0',
        ];
    }

    private function reverseGeocode(float $latitude, float $longitude): ?string
    {
        try {
            $response = Http::withHeaders([
                'User-Agent' => 'Vianorte Laravel System',
            ])->timeout(10)->get('https://nominatim.openstreetmap.org/reverse', [
                'format' => 'jsonv2',
                'lat' => $latitude,
                'lon' => $longitude,
                'addressdetails' => 1,
            ]);

            if (! $response->successful()) {
                return null;
            }

            $data = $response->json();
            $address = $data['address'] ?? [];

            $road = $address['road'] ?? null;
            $houseNumber = $address['house_number'] ?? null;
            $street = trim(collect([$road, $houseNumber])->filter()->implode(', '));

            $neighbourhood = $address['suburb']
                ?? $address['neighbourhood']
                ?? $address['quarter']
                ?? null;

            $city = $address['city']
                ?? $address['town']
                ?? $address['village']
                ?? $address['municipality']
                ?? null;

            $state = $address['state'] ?? null;
            $postcode = $address['postcode'] ?? null;

            $parts = array_filter([
                $street ?: null,
                $neighbourhood,
                $city,
                $state,
                $postcode,
            ], fn ($value) => ! empty($value));

            if (empty($parts)) {
                return $data['display_name'] ?? null;
            }

            return implode(' - ', $parts);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function findNearestLocation(
    float $latitude,
    float $longitude,
    float $maxDistanceMeters = 1000
): ?Location {
    $locations = Location::query()
        ->where('active', true)
        ->whereNotNull('latitude')
        ->whereNotNull('longitude')
        ->get();

    $nearestLocation = null;
    $nearestDistance = null;

    foreach ($locations as $location) {
        $distance = $this->calculateDistanceMeters(
            $latitude,
            $longitude,
            (float) $location->latitude,
            (float) $location->longitude
        );

        if ($distance > $maxDistanceMeters) {
            continue;
        }

        if ($nearestDistance === null || $distance < $nearestDistance) {
            $nearestDistance = $distance;
            $nearestLocation = $location;
        }
    }

    return $nearestLocation;
}

    private function calculateLocationConfidenceScore(?float $distance, int $addressScore): int
{
    $score = 0;

    if ($distance !== null) {
        if ($distance <= 50) {
            $score += 120;
        } elseif ($distance <= 120) {
            $score += 100;
        } elseif ($distance <= 200) {
            $score += 80;
        } elseif ($distance <= 300) {
            $score += 60;
        } elseif ($distance <= 500) {
            $score += 35;
        }
    }

    $score += $addressScore;

    return $score;
}

    private function calculateDistanceMeters(
        float $lat1,
        float $lon1,
        float $lat2,
        float $lon2
    ): float {
        $earthRadius = 6371000;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2))
            * sin($dLon / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    private function calculateAddressMatchScore(?string $capturedAddress, ?string $locationAddress): int
    {
        $capturedNormalized = $this->normalizeAddressForMatch($capturedAddress);
        $locationNormalized = $this->normalizeAddressForMatch($locationAddress);

        if ($capturedNormalized === '' || $locationNormalized === '') {
            return 0;
        }

        if ($capturedNormalized === $locationNormalized) {
            return 100;
        }

        $score = 0;

        $capturedStreet = $this->extractStreetName($capturedNormalized);
        $locationStreet = $this->extractStreetName($locationNormalized);

        $capturedNumber = $this->extractStreetNumber($capturedAddress);
        $locationNumber = $this->extractStreetNumber($locationAddress);

        $capturedTokens = $this->tokenizeAddress($capturedNormalized);
        $locationTokens = $this->tokenizeAddress($locationNormalized);

        if ($capturedStreet !== '' && $locationStreet !== '') {
            if ($capturedStreet === $locationStreet) {
                $score += 65;
            } else {
                similar_text($capturedStreet, $locationStreet, $streetPercent);

                if ($streetPercent >= 92) {
                    $score += 58;
                } elseif ($streetPercent >= 85) {
                    $score += 48;
                } elseif (
                    str_contains($capturedStreet, $locationStreet) ||
                    str_contains($locationStreet, $capturedStreet)
                ) {
                    $score += 44;
                }
            }
        }

        if ($capturedNumber !== null && $locationNumber !== null) {
            if ($capturedNumber === $locationNumber) {
                $score += 18;
            }
        } elseif ($locationNumber === null || $capturedNumber === null) {
            $score += 8;
        }

        $commonTokens = array_intersect($capturedTokens, $locationTokens);
        $score += min(count($commonTokens) * 6, 24);

        similar_text($capturedNormalized, $locationNormalized, $fullPercent);
        $score += (int) round($fullPercent * 0.18);

        if (
            str_contains($capturedNormalized, $locationNormalized) ||
            str_contains($locationNormalized, $capturedNormalized)
        ) {
            $score += 15;
        }

        return min($score, 100);
    }

    private function extractStreetName(?string $address): string
    {
        $normalized = $this->normalizeAddressForMatch($address);

        if ($normalized === '') {
            return '';
        }

        $parts = preg_split('/\s*-\s*/', $normalized);
        $streetPart = trim((string) ($parts[0] ?? ''));

        $streetPart = preg_replace('/\b\d+\b/u', ' ', $streetPart);
        $streetPart = preg_replace('/\s+/', ' ', $streetPart);

        return trim($streetPart);
    }

    private function extractStreetNumber(?string $address): ?string
    {
        if (blank($address)) {
            return null;
        }

        if (preg_match('/,\s*(\d{1,10})\b/u', (string) $address, $matches)) {
            return $matches[1];
        }

        if (preg_match('/\b(\d{1,10})\b/u', (string) $address, $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function tokenizeAddress(string $address): array
    {
        if ($address === '') {
            return [];
        }

        $tokens = preg_split('/[\s\-]+/u', $address);

        $tokens = array_filter($tokens, function ($token) {
            $token = trim((string) $token);

            if ($token === '') {
                return false;
            }

            if (mb_strlen($token, 'UTF-8') <= 2) {
                return false;
            }

            return ! in_array($token, [
                'rua',
                'avenida',
                'travessa',
                'rodovia',
                'alameda',
                'praca',
                'bairro',
                'conjunto',
            ], true);
        });

        return array_values(array_unique($tokens));
    }

    private function findMatchingLocation(?string $address): ?Location
    {
        if (empty($address)) {
            return null;
        }

        $locations = Location::query()
            ->where('active', true)
            ->get();

        $bestLocation = null;
        $bestScore = 0;

        foreach ($locations as $location) {
            $score = $this->calculateAddressMatchScore($address, $location->address);

            if ($score > $bestScore) {
                $bestScore = $score;
                $bestLocation = $location;
            }
        }

        return $bestScore >= 78 ? $bestLocation : null;
    }

    private function normalizeAddressForMatch(?string $text): string
    {
        $text = $this->normalizeText($text);

        if (empty($text)) {
            return '';
        }

        $text = preg_replace('/\s*,\s*/u', ' ', $text);
        $text = preg_replace('/\s*-\s*/u', ' - ', $text);
        $text = preg_replace('/\s+/', ' ', $text);

        $parts = array_map('trim', explode('-', $text));
        $parts = array_values(array_filter($parts, fn ($part) => $part !== ''));

        if (empty($parts)) {
            return '';
        }

        return implode(' - ', $parts);
    }

    private function removeNumbersFromStreet(string $text): string
    {
        $text = preg_replace('/\b\d+\b/u', ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }

    private function normalizeText(?string $text): string
    {
        $text = mb_strtolower((string) $text, 'UTF-8');

        $replacements = [
            'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a',
            'é' => 'e', 'ê' => 'e',
            'í' => 'i',
            'ó' => 'o', 'ô' => 'o', 'õ' => 'o',
            'ú' => 'u',
            'ç' => 'c',
        ];

        $text = strtr($text, $replacements);

        $text = preg_replace('/\b(r|r\.)\b/u', 'rua', $text);
        $text = preg_replace('/\b(av|av\.)\b/u', 'avenida', $text);
        $text = preg_replace('/\b(tv|tv\.)\b/u', 'travessa', $text);
        $text = preg_replace('/\b(dep|dep\.)\b/u', 'deputado', $text);

        $text = preg_replace('/[^\p{L}\p{N}\s\-,]/u', ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
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
}