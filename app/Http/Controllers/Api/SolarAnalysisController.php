<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ProductListing;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SolarAnalysisController extends Controller
{
    public function analyze(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'polygon_coordinates' => ['required', 'array', 'min:3'],
            'polygon_coordinates.*.lat' => ['required', 'numeric'],
            'polygon_coordinates.*.lng' => ['required', 'numeric'],
            'grid_type' => ['required', 'in:on-grid,off-grid,hybrid'],
            'phase_type' => ['required', 'in:single,three'],
            'system_type' => ['required', 'in:residential,commercial'],
            'monthly_consumption_kwh' => ['nullable', 'numeric', 'min:0'],
            'location_name' => ['nullable', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid solar analysis request.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $coordinates = $request->input('polygon_coordinates');
        $roofArea = max($this->calculatePolygonArea($coordinates), $request->input('system_type') === 'commercial' ? 50 : 10);
        $panelCapacityKw = 0.615;
        $panelAreaSqm = 2.8;
        $usableRoofFactor = 0.82;
        $panelCount = max(1, (int) floor(($roofArea * $usableRoofFactor) / $panelAreaSqm));
        $systemSizeKw = round($panelCount * $panelCapacityKw, 2);
        $specificYield = $request->input('system_type') === 'commercial' ? 1725 : 1650;
        $annualProduction = round($systemSizeKw * $specificYield, 2);
        $center = $this->centerPoint($coordinates);

        $analysis = [
            'system_size_kw' => $systemSizeKw,
            'panel_count' => $panelCount,
            'roof_area_sqm' => round($roofArea, 2),
            'annual_production_kwh' => $annualProduction,
            'irec_potential' => round($annualProduction / 1000, 2),
            'carbon_offset_tons' => round($annualProduction * 0.00045, 2),
            'blueprint_base64' => $this->makeBlueprintDataUri($coordinates, $panelCount, $systemSizeKw),
            'pvgis_data' => [
                'lat' => $center['lat'],
                'lng' => $center['lng'],
                'irradiation' => 5.55,
                'optimal_tilt' => 24,
                'optimal_azimuth' => 180,
                'annual_pv_production' => $annualProduction,
            ],
            'location' => $center,
            'layout_geometry' => [],
            'panel_dimensions' => [
                'length_m' => 2.38,
                'width_m' => 1.13,
            ],
        ];

        return response()->json([
            'analysis' => $analysis,
            'recommendations' => $this->recommendations($systemSizeKw, $panelCount, $request->input('grid_type'), $request->input('phase_type')),
        ]);
    }

    public function products(Request $request)
    {
        $systemSizeKw = (float) $request->query('system_size_kw', 10);
        $panelCount = max(1, (int) ceil($systemSizeKw / 0.615));

        return response()->json([
            'recommendations' => $this->recommendations($systemSizeKw, $panelCount, $request->query('grid_type', 'on-grid'), $request->query('phase_type', 'single')),
        ]);
    }

    public function save(Request $request)
    {
        $user = $this->userFromRequest($request);
        if (!$user) {
            return response()->json(['message' => 'Authentication required.'], 401);
        }

        $validator = Validator::make($request->all(), [
            'system_type' => ['required', 'in:residential,commercial'],
            'grid_type' => ['required', 'in:on-grid,off-grid,hybrid'],
            'phase_type' => ['required', 'in:single,three'],
            'polygon_coordinates' => ['required', 'array', 'min:3'],
            'roof_area_sqm' => ['required', 'numeric'],
            'system_size_kw' => ['required', 'numeric'],
            'panel_count' => ['required', 'integer'],
            'annual_production_kwh' => ['required', 'numeric'],
            'selected_tier' => ['required', 'in:premium,recommended,value'],
            'selected_products' => ['required', 'array'],
            'hardware_cost' => ['required', 'numeric'],
            'bos_cost' => ['required', 'numeric'],
            'total_cost' => ['required', 'numeric'],
            'submit_for_approval' => ['nullable', 'boolean'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid project save request.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $status = $request->boolean('submit_for_approval') ? 'pending' : 'draft';
        $systemType = $request->input('system_type');
        $locationName = $request->input('location_name', '');
        $projectName = $request->input('project_name')
            ?: ucfirst($systemType) . ' Solar Project' . ($locationName ? " - {$locationName}" : '');

        $project = Project::create([
            'project_name' => $projectName,
            'customer_name' => $user->name ?? $user->email,
            'project_type' => $systemType,
            'capacity_kw' => (float) $request->input('system_size_kw'),
            'location' => $locationName,
            'description' => $request->input('project_description'),
            'submitted_by' => (string) $user->_id,
            'submitted_at' => now(),
            'status' => $status,
            'grid_type' => $request->input('grid_type'),
            'phase_type' => $request->input('phase_type'),
            'polygon_coordinates' => $request->input('polygon_coordinates'),
            'monthly_consumption_kwh' => $request->input('monthly_consumption_kwh'),
            'roof_area_sqm' => (float) $request->input('roof_area_sqm'),
            'system_size_kw' => (float) $request->input('system_size_kw'),
            'panel_count' => (int) $request->input('panel_count'),
            'annual_production_kwh' => (float) $request->input('annual_production_kwh'),
            'blueprint_base64' => $request->input('blueprint_base64'),
            'analysis_results' => $request->input('analysis_results', []),
            'selected_tier' => $request->input('selected_tier'),
            'selected_products' => $request->input('selected_products'),
            'hardware_cost' => (float) $request->input('hardware_cost'),
            'bos_cost' => (float) $request->input('bos_cost'),
            'total_cost' => (float) $request->input('total_cost'),
            'location_name' => $locationName,
            'latitude' => $request->input('latitude'),
            'longitude' => $request->input('longitude'),
            'layout_geometry' => $request->input('layout_geometry', []),
            'panel_dimensions' => $request->input('panel_dimensions', []),
            'quotes' => [],
        ]);

        return response()->json([
            'success' => true,
            'message' => $status === 'pending' ? 'Project submitted for approval.' : 'Project saved.',
            'project' => $this->projectPayload($project),
        ]);
    }

    public function projects(Request $request)
    {
        $user = $this->userFromRequest($request);
        if (!$user) {
            return response()->json(['message' => 'Authentication required.'], 401);
        }

        $projects = Project::where('submitted_by', (string) $user->_id)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Project $project) => $this->projectPayload($project))
            ->values();

        return response()->json(['data' => $projects]);
    }

    public function show(Request $request, string $project)
    {
        $found = Project::with(['submitter'])->findOrFail($project);

        return response()->json([
            'project' => $this->projectPayload($found),
        ]);
    }

    public function submit(Request $request, string $project)
    {
        $user = $this->userFromRequest($request);
        if (!$user) {
            return response()->json(['message' => 'Authentication required.'], 401);
        }

        $found = Project::findOrFail($project);
        $found->status = 'pending';
        $found->submitted_at = now();
        $found->save();

        return response()->json([
            'success' => true,
            'message' => 'Project submitted for approval.',
            'project' => $this->projectPayload($found),
        ]);
    }

    public function marketplace(Request $request)
    {
        $query = Project::with(['submitter'])->where('status', 'approved');

        if ($request->filled('system_type')) {
            $query->where('project_type', $request->query('system_type'));
        }

        $projects = $query->orderByDesc('created_at')
            ->paginate((int) $request->query('per_page', 12));

        return response()->json([
            'data' => collect($projects->items())->map(fn (Project $project) => $this->projectPayload($project, true))->values(),
            'meta' => [
                'current_page' => $projects->currentPage(),
                'last_page' => $projects->lastPage(),
                'total' => $projects->total(),
            ],
        ]);
    }

    public function marketplaceShow(Request $request, string $project)
    {
        $found = Project::with(['submitter'])
            ->where('status', 'approved')
            ->findOrFail($project);
        $user = $this->userFromRequest($request);

        return response()->json([
            'project' => $this->projectPayload($found, true, $user),
        ]);
    }

    public function submitQuote(Request $request)
    {
        $user = $this->userFromRequest($request);
        if (!$user) {
            return response()->json(['message' => 'Authentication required.'], 401);
        }

        $user->loadMissing('role');
        if (($user->role?->slug ?? null) !== 'epc-company') {
            return response()->json(['message' => 'Only EPC company users can submit quotes.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'project_id' => ['required', 'string'],
            'service_charge' => ['required', 'numeric', 'min:0'],
            'validity_days' => ['nullable', 'integer', 'min:1'],
            'scope_of_work' => ['nullable', 'string'],
            'terms_and_conditions' => ['nullable', 'string'],
            'epc_notes' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid quote request.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $project = Project::findOrFail($request->input('project_id'));
        if ($project->status !== 'approved') {
            return response()->json(['message' => 'Quotes can only be submitted for approved marketplace projects.'], 422);
        }

        $quotes = $project->quotes ?? [];
        $hasExistingQuote = collect($quotes)->contains(function ($quote) use ($user) {
            $quoteUserId = (string) data_get($quote, 'epc.id', '');
            $quoteEmail = strtolower((string) data_get($quote, 'epc.email', ''));

            return $quoteUserId === (string) $user->_id || $quoteEmail === strtolower((string) $user->email);
        });

        if ($hasExistingQuote) {
            return response()->json(['message' => 'You have already submitted a quote for this project.'], 409);
        }

        $quotes[] = [
            'id' => (string) Str::uuid(),
            'epc' => [
                'id' => (string) $user->_id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'service_charge' => (float) $request->input('service_charge'),
            'validity_days' => (int) $request->input('validity_days', 30),
            'scope_of_work' => $request->input('scope_of_work'),
            'terms_and_conditions' => $request->input('terms_and_conditions'),
            'epc_notes' => $request->input('epc_notes'),
            'admin_status' => 'pending',
            'created_at' => now()->toIso8601String(),
        ];

        $project->quotes = $quotes;
        $project->save();

        return response()->json([
            'success' => true,
            'message' => 'Quote submitted successfully.',
        ]);
    }

    public function quotes(Request $request, string $project)
    {
        $found = Project::findOrFail($project);
        $quotes = $this->visibleMarketplaceQuotes($found->quotes ?? [], $found, $this->userFromRequest($request));

        return response()->json([
            'quotes' => $quotes,
            'quotes_count' => count($quotes),
        ]);
    }

    public function pdf(string $project)
    {
        $found = Project::findOrFail($project);
        $pdf = $this->minimalPdf($found->project_name ?: 'Solar Analysis');

        return response($pdf, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="solar-analysis-' . $project . '.pdf"',
        ]);
    }

    public function pdfBase64(string $project)
    {
        $found = Project::findOrFail($project);

        return response()->json([
            'pdf_base64' => base64_encode($this->minimalPdf($found->project_name ?: 'Solar Analysis')),
        ]);
    }

    private function userFromRequest(Request $request): ?User
    {
        $header = $request->header('Authorization', '');
        if (!str_starts_with($header, 'Bearer ')) {
            return null;
        }

        $payload = $this->decodeJwtPayload(substr($header, 7));
        if (!$payload || empty($payload['user_id'])) {
            return null;
        }

        try {
            return User::find((string) $payload['user_id']);
        } catch (\Throwable) {
            return null;
        }
    }

    private function decodeJwtPayload(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        [$header, $payload, $signature] = $parts;
        $decodedHeader = json_decode($this->base64UrlDecode($header), true);
        if (($decodedHeader['alg'] ?? null) !== 'HS256') {
            return null;
        }

        $signedByKnownSecret = collect($this->jwtSecrets())->contains(function (string $secret) use ($header, $payload, $signature) {
            $expected = $this->base64UrlEncode(hash_hmac('sha256', "{$header}.{$payload}", $secret, true));
            return hash_equals($expected, $signature);
        });

        if (!$signedByKnownSecret) {
            return null;
        }

        $decodedPayload = json_decode($this->base64UrlDecode($payload), true);
        if (!is_array($decodedPayload)) {
            return null;
        }

        if (!empty($decodedPayload['exp']) && time() >= (int) $decodedPayload['exp']) {
            return null;
        }

        return $decodedPayload;
    }

    private function jwtSecrets(): array
    {
        $configuredSecrets = array_map(
            'trim',
            explode(',', (string) env('JWT_SECRETS', ''))
        );

        return array_values(array_unique(array_filter([
            env('JWT_SECRET', 'ironman'),
            env('PV_MARKET_API_JWT_SECRET', 'your-secret-key-change-in-production'),
            'your-secret-key-change-in-production',
            ...$configuredSecrets,
        ])));
    }

    private function recommendations(float $systemSizeKw, int $panelCount, string $gridType, string $phaseType): array
    {
        return [
            'premium' => $this->tierRecommendationForSolarListing('premium', 'Premium Mono PERC 650W', 'PVMarket Premium', 650, 195, $systemSizeKw, $panelCount, $gridType, $phaseType, 1),
            'recommended' => $this->tierRecommendationForSolarListing('recommended', 'High Efficiency Bifacial 615W', 'PVMarket Recommended', 615, 155, $systemSizeKw, $panelCount, $gridType, $phaseType, 2),
            'value' => $this->tierRecommendationForSolarListing('value', 'Value Solar Module 580W', 'PVMarket Value', 580, 125, $systemSizeKw, $panelCount, $gridType, $phaseType, 3),
        ];
    }

    private function tierRecommendationForSolarListing(
        string $tier,
        string $fallbackPanelName,
        string $fallbackBrand,
        int $fallbackWatt,
        float $fallbackPanelPrice,
        float $systemSizeKw,
        int $panelCount,
        string $gridType,
        string $phaseType,
        int $idOffset
    ): array {
        $panel = $this->panelRecommendationFromSolarListing($tier, $panelCount, $gridType, $phaseType, $fallbackPanelName, $fallbackBrand, $fallbackWatt, $fallbackPanelPrice);
        $inverter = $this->inverterRecommendationFromSolarListing($tier, $systemSizeKw, $gridType, $phaseType, $fallbackBrand, $idOffset);

        return $this->tierRecommendation($panel, $inverter, $systemSizeKw, $panelCount, $gridType, $idOffset);
    }

    private function panelRecommendationFromSolarListing(
        string $tier,
        int $panelCount,
        string $gridType,
        string $phaseType,
        string $fallbackPanelName,
        string $fallbackBrand,
        int $fallbackWatt,
        float $fallbackPanelPrice
    ): array {
        $listing = $this->solarListingForTier($tier, 'panel', $gridType, $phaseType);

        if (!$listing) {
            return [
                'id' => null,
                'name' => $fallbackPanelName,
                'brand' => $fallbackBrand,
                'model' => 'PV-' . $fallbackWatt,
                'capacity' => $fallbackWatt / 1000,
                'unit_price' => $fallbackPanelPrice,
                'tier' => $tier,
            ];
        }

        $product = $listing->product;
        $productName = $product?->product_name ?: $listing->sku_code ?: $fallbackPanelName;
        $watt = $this->extractPanelWatt($productName, $fallbackWatt);
        $unitPrice = $this->listingUnitPrice($listing, $panelCount, $fallbackPanelPrice);

        return [
            'id' => (string) $listing->_id,
            'name' => $productName,
            'brand' => $product?->brand_name ?: $product?->category_name ?: $fallbackBrand,
            'model' => $product?->sku_code ?: $listing->sku_code ?: 'PV-' . $watt,
            'capacity' => $watt / 1000,
            'unit_price' => $unitPrice,
            'tier' => $tier,
        ];
    }

    private function inverterRecommendationFromSolarListing(string $tier, float $systemSizeKw, string $gridType, string $phaseType, string $fallbackBrand, int $idOffset): array
    {
        $fallbackCapacity = max(3, (int) ceil($systemSizeKw));
        $fallbackUnitPrice = $fallbackCapacity * 220;
        $listing = $this->solarListingForTier($tier, 'inverter', $gridType, $phaseType);

        if (!$listing) {
            return [
                'id' => 2000 + $idOffset,
                'name' => 'Solar String Inverter',
                'brand' => $fallbackBrand,
                'model' => 'INV-' . $fallbackCapacity,
                'capacity' => $fallbackCapacity,
                'unit_price' => round($fallbackUnitPrice, 2),
                'quantity' => 1,
                'tier' => $tier,
            ];
        }

        $product = $listing->product;
        $productName = $product?->product_name ?: $listing->sku_code ?: 'Solar String Inverter';
        $capacity = $this->extractInverterCapacity($productName, $fallbackCapacity);
        $quantity = max(1, (int) ceil($systemSizeKw / max($capacity, 0.1)));
        $unitPrice = $this->listingUnitPrice($listing, $quantity, $fallbackUnitPrice);

        return [
            'id' => (string) $listing->_id,
            'name' => $productName,
            'brand' => $product?->brand_name ?: $product?->category_name ?: $fallbackBrand,
            'model' => $product?->sku_code ?: $listing->sku_code ?: 'INV-' . $capacity,
            'capacity' => $capacity,
            'unit_price' => round($unitPrice, 2),
            'quantity' => $quantity,
            'tier' => $tier,
        ];
    }

    private function solarListingForTier(string $tier, string $component, string $gridType, string $phaseType): ?ProductListing
    {
        return ProductListing::with('product')
            ->where('is_solar_listing', true)
            ->where('solar_tier', $tier)
            ->where('is_active', true)
            ->orderBy('updated_at', 'desc')
            ->get()
            ->first(fn(ProductListing $listing) => $this->listingMatchesSolarComponent($listing, $component)
                && $this->listingMatchesSolarOption($listing->solar_grid_types ?? [], $gridType)
                && $this->listingMatchesSolarOption($listing->solar_phase_types ?? [], $phaseType));
    }

    private function listingMatchesSolarOption(mixed $configuredValues, string $requestedValue): bool
    {
        $values = is_array($configuredValues) ? $configuredValues : [$configuredValues];
        return in_array($requestedValue, array_filter($values), true);
    }

    private function listingMatchesSolarComponent(ProductListing $listing, string $component): bool
    {
        $product = $listing->product;
        $text = Str::lower(implode(' ', array_filter([
            $product?->product_name,
            $product?->category_name,
            $product?->sub_category_name,
        ])));

        if ($component === 'inverter') {
            return str_contains($text, 'inverter');
        }

        return !str_contains($text, 'inverter')
            && (
                str_contains($text, 'solar panel')
                || str_contains($text, 'solar panels')
                || str_contains($text, 'solar module')
                || str_contains($text, 'pv modules')
            );
    }

    private function listingUnitPrice(ProductListing $listing, int $quantity, float $fallbackPrice): float
    {
        $slots = $listing->slots ?? [];

        foreach ($slots as $slot) {
            $min = (int) ($slot['min_quantity'] ?? 0);
            $max = isset($slot['max_quantity']) && $slot['max_quantity'] !== '' ? (int) $slot['max_quantity'] : null;
            if ($quantity >= $min && ($max === null || $quantity <= $max)) {
                return (float) ($slot['total_price'] ?? $slot['price'] ?? $fallbackPrice);
            }
        }

        $firstSlot = $slots[0] ?? null;
        return (float) ($firstSlot['total_price'] ?? $firstSlot['price'] ?? $fallbackPrice);
    }

    private function extractPanelWatt(string $productName, int $fallbackWatt): int
    {
        if (preg_match('/(\d{3,4})\s*(?:wp|watt|w)\b/i', $productName, $matches)) {
            return (int) $matches[1];
        }

        return $fallbackWatt;
    }

    private function extractInverterCapacity(string $productName, int $fallbackCapacity): float
    {
        if (preg_match('/(\d+(?:\.\d+)?)\s*k(?:w|va)\b/i', $productName, $matches)) {
            return (float) $matches[1];
        }

        return $fallbackCapacity;
    }

    private function tierRecommendation(array $panel, array $inverter, float $systemSizeKw, int $panelCount, string $gridType, int $idOffset): array
    {
        $inverterTotal = $inverter['unit_price'] * $inverter['quantity'];
        $battery = in_array($gridType, ['off-grid', 'hybrid'], true)
            ? [
                'id' => 3000 + $idOffset,
                'name' => 'Lithium Battery Pack',
                'brand' => $panel['brand'],
                'model' => 'BAT-' . ($inverter['capacity'] * 2),
                'capacity' => $inverter['capacity'] * 2,
                'unit_price' => $inverter['capacity'] * 700,
                'quantity' => 1,
                'total_price' => $inverter['capacity'] * 700,
                'tier' => $idOffset,
            ]
            : null;

        $panelTotal = $panelCount * $panel['unit_price'];
        $hardware = $panelTotal + $inverterTotal + ($battery['total_price'] ?? 0);
        $bos = round($hardware * 0.35, 2);

        $products = [
            'panel' => [
                'id' => $panel['id'] ?? 1000 + $idOffset,
                'name' => $panel['name'],
                'brand' => $panel['brand'],
                'model' => $panel['model'],
                'capacity' => $panel['capacity'],
                'unit_price' => $panel['unit_price'],
                'quantity' => $panelCount,
                'total_price' => round($panelTotal, 2),
                'tier' => $panel['tier'],
            ],
            'inverter' => [
                'id' => $inverter['id'],
                'name' => $inverter['name'],
                'brand' => $inverter['brand'],
                'model' => $inverter['model'],
                'capacity' => $inverter['capacity'],
                'unit_price' => $inverter['unit_price'],
                'quantity' => $inverter['quantity'],
                'total_price' => round($inverterTotal, 2),
                'tier' => $inverter['tier'],
            ],
        ];

        if ($battery) {
            $products['battery'] = $battery;
        }

        return [
            'products' => $products,
            'costs' => [
                'hardware_cost' => round($hardware, 2),
                'bos_cost' => $bos,
                'total_cost' => round($hardware + $bos, 2),
            ],
        ];
    }

    private function calculatePolygonArea(array $coordinates): float
    {
        if (count($coordinates) < 3) {
            return 0;
        }

        $lat0 = array_sum(array_column($coordinates, 'lat')) / count($coordinates);
        $points = array_map(function ($point) use ($lat0) {
            return [
                'x' => (float) $point['lng'] * 111320 * cos(deg2rad($lat0)),
                'y' => (float) $point['lat'] * 110540,
            ];
        }, $coordinates);

        $sum = 0;
        $count = count($points);
        for ($i = 0; $i < $count; $i++) {
            $next = ($i + 1) % $count;
            $sum += $points[$i]['x'] * $points[$next]['y'] - $points[$next]['x'] * $points[$i]['y'];
        }

        return abs($sum / 2);
    }

    private function centerPoint(array $coordinates): array
    {
        $count = max(count($coordinates), 1);

        return [
            'lat' => round(array_sum(array_column($coordinates, 'lat')) / $count, 6),
            'lng' => round(array_sum(array_column($coordinates, 'lng')) / $count, 6),
        ];
    }

    private function makeBlueprintDataUri(array $coordinates, int $panelCount, float $systemSizeKw): string
    {
        $label = htmlspecialchars("{$panelCount} panels / {$systemSizeKw} kW", ENT_QUOTES, 'UTF-8');
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="900" height="520" viewBox="0 0 900 520">'
            . '<rect width="900" height="520" fill="#f3f4f6"/>'
            . '<polygon points="140,90 760,110 720,420 180,440" fill="#e5e7eb" stroke="#111827" stroke-width="6"/>'
            . '<g fill="#1f2937" stroke="#60a5fa" stroke-width="2">';

        $columns = 8;
        $rows = max(1, (int) ceil(min($panelCount, 64) / $columns));
        for ($row = 0; $row < $rows; $row++) {
            for ($column = 0; $column < $columns; $column++) {
                $index = $row * $columns + $column;
                if ($index >= min($panelCount, 64)) {
                    break;
                }
                $x = 210 + ($column * 58);
                $y = 150 + ($row * 34);
                $svg .= "<rect x=\"{$x}\" y=\"{$y}\" width=\"46\" height=\"26\" rx=\"2\"/>";
            }
        }

        $svg .= '</g><text x="450" y="475" text-anchor="middle" font-family="Arial" font-size="26" fill="#111827">'
            . $label
            . '</text></svg>';

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    private function projectPayload(Project $project, bool $approvedQuotesOnly = false, ?User $currentUser = null): array
    {
        $allQuotes = array_values($project->quotes ?? []);
        $approvedQuotes = $this->approvedQuotes($allQuotes);
        $quotes = $approvedQuotesOnly
            ? $this->visibleMarketplaceQuotes($allQuotes, $project, $currentUser)
            : $allQuotes;
        $currentUserQuoteStatus = $currentUser
            ? $this->currentUserQuoteStatus($allQuotes, $currentUser)
            : null;

        return [
            'id' => (string) $project->_id,
            '_id' => (string) $project->_id,
            'project_name' => $project->project_name,
            'location_name' => $project->location_name ?: $project->location,
            'latitude' => $project->latitude,
            'longitude' => $project->longitude,
            'polygon_coordinates' => $project->polygon_coordinates ?? [],
            'system_size_kw' => (float) ($project->system_size_kw ?: $project->capacity_kw),
            'panel_count' => (int) $project->panel_count,
            'annual_production_kwh' => (float) $project->annual_production_kwh,
            'system_type' => $project->project_type,
            'grid_type' => $project->grid_type,
            'phase_type' => $project->phase_type,
            'roof_area_sqm' => (float) $project->roof_area_sqm,
            'irradiation' => (float) data_get($project->analysis_results, 'irradiation', data_get($project->analysis_results, 'pvgis_data.irradiation', 5.55)),
            'optimal_tilt' => (float) data_get($project->analysis_results, 'optimal_tilt', data_get($project->analysis_results, 'pvgis_data.optimal_tilt', 24)),
            'optimal_azimuth' => (float) data_get($project->analysis_results, 'optimal_azimuth', data_get($project->analysis_results, 'pvgis_data.optimal_azimuth', 180)),
            'co2_offset_tons' => round(((float) $project->annual_production_kwh) * 0.00045, 2),
            'irec_potential' => round(((float) $project->annual_production_kwh) / 1000, 2),
            'status' => $project->status,
            'created_at' => optional($project->created_at)->toIso8601String(),
            'blueprint_base64' => $project->blueprint_base64,
            'blueprint_image' => $this->stripDataUri($project->blueprint_base64),
            'total_cost' => (float) $project->total_cost,
            'hardware_cost' => (float) $project->hardware_cost,
            'bos_cost' => (float) $project->bos_cost,
            'selected_tier' => $project->selected_tier,
            'selected_products' => $project->selected_products ?? [],
            'quotes' => $quotes,
            'quotes_count' => count($quotes),
            'approved_quotes_count' => count($approvedQuotes),
            'has_current_user_quote' => $currentUserQuoteStatus !== null,
            'current_user_quote_status' => $currentUserQuoteStatus,
            'user' => $project->submitter ? [
                'id' => (string) $project->submitter->_id,
                'name' => $project->submitter->name,
                'email' => $project->submitter->email,
            ] : null,
        ];
    }

    private function approvedQuotes(array $quotes): array
    {
        return array_values(array_filter($quotes, fn ($quote) => data_get($quote, 'admin_status') === 'approved'));
    }

    private function visibleMarketplaceQuotes(array $quotes, Project $project, ?User $currentUser): array
    {
        $isProjectOwner = $currentUser ? $this->isProjectOwner($project, $currentUser) : false;

        return collect($quotes)
            ->filter(function ($quote) use ($currentUser) {
                return data_get($quote, 'admin_status') === 'approved'
                    || ($currentUser && $this->isQuoteOwner($quote, $currentUser));
            })
            ->map(function ($quote) use ($currentUser, $isProjectOwner) {
                $isQuoteOwner = $currentUser ? $this->isQuoteOwner($quote, $currentUser) : false;
                return $this->marketplaceQuotePayload($quote, $isProjectOwner || $isQuoteOwner);
            })
            ->values()
            ->all();
    }

    private function marketplaceQuotePayload(array $quote, bool $canViewPrivateFields): array
    {
        $payload = $quote;
        $payload['can_view_price'] = $canViewPrivateFields;
        $payload['can_view_identity'] = $canViewPrivateFields;

        if (!$canViewPrivateFields) {
            $payload['service_charge'] = null;
            $payload['epc'] = [
                'name' => 'EPC Company',
                'email' => null,
            ];
            unset($payload['scope_of_work'], $payload['terms_and_conditions'], $payload['epc_notes']);
        }

        return $payload;
    }

    private function isProjectOwner(Project $project, User $user): bool
    {
        return (string) $project->submitted_by === (string) $user->_id
            || strtolower((string) $project->submitter?->email) === strtolower((string) $user->email);
    }

    private function isQuoteOwner(array $quote, User $user): bool
    {
        $quoteUserId = (string) data_get($quote, 'epc.id', '');
        $quoteEmail = strtolower((string) data_get($quote, 'epc.email', ''));

        return $quoteUserId === (string) $user->_id || $quoteEmail === strtolower((string) $user->email);
    }

    private function currentUserQuoteStatus(array $quotes, User $user): ?string
    {
        foreach ($quotes as $quote) {
            if ($this->isQuoteOwner($quote, $user)) {
                return (string) data_get($quote, 'admin_status', 'pending');
            }
        }

        return null;
    }

    private function stripDataUri(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        if (str_contains($value, ',')) {
            return substr($value, strpos($value, ',') + 1);
        }

        return $value;
    }

    private function minimalPdf(string $title): string
    {
        $safeTitle = str_replace(['\\', '(', ')'], ['\\\\', '\(', '\)'], $title);

        return "%PDF-1.1\n"
            . "1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj\n"
            . "2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj\n"
            . "3 0 obj << /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R >> endobj\n"
            . "4 0 obj << /Length 74 >> stream\n"
            . "BT /F1 18 Tf 72 720 Td ({$safeTitle}) Tj 0 -30 Td (Solar analysis project report) Tj ET\n"
            . "endstream endobj\n"
            . "xref\n0 5\n0000000000 65535 f \n"
            . "trailer << /Root 1 0 R /Size 5 >>\nstartxref\n0\n%%EOF";
    }

    private function base64UrlDecode(string $value): string
    {
        return base64_decode(strtr($value, '-_', '+/') . str_repeat('=', (4 - strlen($value) % 4) % 4));
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
