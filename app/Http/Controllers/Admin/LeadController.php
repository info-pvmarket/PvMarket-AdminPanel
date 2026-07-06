<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeadGeneration;
use App\Models\ProductVisit;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Traits\FiltersAssignedUsers;

class LeadController extends Controller
{
    use FiltersAssignedUsers;

    // ── Leads Management Page ─────────────────────────────────────
    public function index(Request $request)
    {
        $query = LeadGeneration::where('is_active', '!=', '0');

        // Filter by assigned admin
        $this->filterByAssignedAdmin($query);

        // Filter by lead type (All / Book Free / Spot Price / etc.)
        if ($request->filled('lead_type') && $request->lead_type !== 'all') {
            $query->where('lead_type', (int)$request->lead_type);
        }

        // Search by email or name
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', '%' . $search . '%')
                  ->orWhere('name',  'like', '%' . $search . '%');
            });
        }

        $perPage = (int) $request->get('per_page', 10);
        $leads   = $query->orderBy('created_at', 'desc')->paginate($perPage)->withQueryString();

        $admins = $this->getAdminsForAssignment();

        return view('admin.leads.index', compact('leads', 'admins'));
    }

    // ── Update lead status ────────────────────────────────────────
    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|integer|min:0|max:2']);

        $lead = LeadGeneration::findOrFail($id);
        $lead->update([
            'status'     => $request->status,
            'updated_by' => auth()->id(),
        ]);

        return response()->json(['success' => true]);
    }

    public function updateVisitProduct(Request $request, $id, $index)
    {
        $data = $request->validate([
            'status' => 'required|string|in:pending,contacted',
        ]);

        $visit = ProductVisit::findOrFail($id);
        $products = $this->visitProductsForMutation($visit);

        $index = (int)$index;
        if (!array_key_exists($index, $products)) {
            return response()->json([
                'success' => false,
                'message' => 'Visit product row not found.',
            ], 404);
        }

        $products[$index]['status'] = $this->normalizeVisitStatus($data['status']);
        $products[$index]['updated_at'] = now();

        $visit->products = $products;
        $visit->save();

        return response()->json([
            'success' => true,
            'item' => [
                'status' => $products[$index]['status'],
                'status_label' => ucfirst($products[$index]['status']),
            ],
        ]);
    }

    public function addVisitProductNote(Request $request, $id, $index)
    {
        $data = $request->validate([
            'text' => 'required|string|max:2000',
        ]);

        $visit = ProductVisit::findOrFail($id);
        $products = $this->visitProductsForMutation($visit);
        $index = (int)$index;

        if (!array_key_exists($index, $products)) {
            return response()->json([
                'success' => false,
                'message' => 'Visit product row not found.',
            ], 404);
        }

        $notes = $this->storedVisitNotes($products[$index]);
        $notes[] = $this->makeVisitNote($data['text']);

        $products[$index]['admin_notes'] = array_values($notes);
        $products[$index]['updated_at'] = now();
        $visit->products = $products;
        $visit->save();

        return response()->json([
            'success' => true,
            'notes' => $this->formatVisitNotes($products[$index]),
        ]);
    }

    public function updateVisitProductNote(Request $request, $id, $index, $noteIndex)
    {
        $data = $request->validate([
            'text' => 'required|string|max:2000',
        ]);

        $visit = ProductVisit::findOrFail($id);
        $products = $this->visitProductsForMutation($visit);
        $index = (int)$index;
        $noteIndex = (int)$noteIndex;

        if (!array_key_exists($index, $products)) {
            return response()->json([
                'success' => false,
                'message' => 'Visit product row not found.',
            ], 404);
        }

        $notes = $this->storedVisitNotes($products[$index]);
        if (!array_key_exists($noteIndex, $notes)) {
            return response()->json([
                'success' => false,
                'message' => 'Note not found.',
            ], 404);
        }

        $notes[$noteIndex]['text'] = trim($data['text']);
        $notes[$noteIndex]['updated_at'] = now();

        $products[$index]['admin_notes'] = array_values($notes);
        $products[$index]['updated_at'] = now();
        $visit->products = $products;
        $visit->save();

        return response()->json([
            'success' => true,
            'notes' => $this->formatVisitNotes($products[$index]),
        ]);
    }

    public function deleteVisitProductNote($id, $index, $noteIndex)
    {
        $visit = ProductVisit::findOrFail($id);
        $products = $this->visitProductsForMutation($visit);
        $index = (int)$index;
        $noteIndex = (int)$noteIndex;

        if (!array_key_exists($index, $products)) {
            return response()->json([
                'success' => false,
                'message' => 'Visit product row not found.',
            ], 404);
        }

        $notes = $this->storedVisitNotes($products[$index]);
        if (!array_key_exists($noteIndex, $notes)) {
            return response()->json([
                'success' => false,
                'message' => 'Note not found.',
            ], 404);
        }

        unset($notes[$noteIndex]);
        $products[$index]['admin_notes'] = array_values($notes);
        $products[$index]['updated_at'] = now();
        $visit->products = $products;
        $visit->save();

        return response()->json([
            'success' => true,
            'notes' => $this->formatVisitNotes($products[$index]),
        ]);
    }

    // ── Edit lead (show form) ─────────────────────────────────────
    public function edit($id)
    {
        $lead = LeadGeneration::findOrFail($id);
        return view('admin.leads.edit', compact('lead'));
    }

    // ── Update lead ───────────────────────────────────────────────
    public function update(Request $request, $id)
    {
        $request->validate([
            'name'   => 'nullable|string|max:255',
            'email'  => 'nullable|email|max:255',
            'phone'  => 'nullable|string|max:20',
            'status' => 'nullable|integer',
        ]);

        $lead = LeadGeneration::findOrFail($id);
        $lead->update([
            'name'       => $request->name,
            'email'      => $request->email,
            'phone'      => $request->phone,
            'status'     => $request->status,
            'updated_by' => auth()->id(),
        ]);

        return redirect()->route('admin.leads.index')->with('success', 'Lead updated successfully.');
    }

    // ── Delete lead ───────────────────────────────────────────────
    public function destroy($id)
    {
        $lead = LeadGeneration::findOrFail($id);
        $lead->update(['is_active' => '0']);
        return back()->with('success', 'Lead removed.');
    }

    // ── Assign Admin ─────────────────────────────────────────────
    public function assignAdmin(Request $request, $leadId)
    {
        if (!Auth::user()->isSuperAdmin()) {
            return back()->with('error', 'Only Super Admin can assign.');
        }

        $lead = LeadGeneration::findOrFail($leadId);
        $lead->assigned_admin_id = $request->admin_id
            ? new \MongoDB\BSON\ObjectId($request->admin_id)
            : null;
        $lead->save();

        return back()->with('success', 'Assigned successfully.');
    }

    // ══════════════════════════════════════════════════════════════
    // PRODUCT VISITS PAGE
    // ══════════════════════════════════════════════════════════════

    public function productVisits(Request $request)
    {
        $query = ProductVisit::where('is_active', 1);

        // Date range filter
        if ($request->filled('date_from')) {
            $query->where('visit_date', '>=', Carbon::parse($request->date_from)->startOfDay());
        }
        if ($request->filled('date_to')) {
            $query->where('visit_date', '<=', Carbon::parse($request->date_to)->endOfDay());
        }

        $visits = $query
            ->orderBy('last_visited_at', 'desc')
            ->orderBy('visit_date', 'desc')
            ->get();

        // Load user and product info
        $userIds = $this->toObjectIds($visits->pluck('user_id'));
        $productIds = $this->toObjectIds($this->collectVisitProductIds($visits));

        $users = User::whereIn('_id', $userIds)->get()->keyBy(fn($u) => (string)$u->_id);
        $products = Product::whereIn('_id', $productIds)->get()->keyBy(fn($p) => (string)$p->_id);

        // Search filter applied after loading
        $search = $request->search;
        $visits = $visits->map(function ($visit) use ($users, $products) {
            $visit->user_info = $users[(string)$visit->user_id] ?? null;
            $visit->product_info = $products[(string)$visit->product_id] ?? null;
            $visit->product_visit_items = $this->formatVisitProducts($visit, $products);
            $visit->latest_product_visit = $visit->product_visit_items[0] ?? null;
            return $visit;
        });

        if ($search) {
            $visits = $visits->filter(function ($visit) use ($search) {
                $name  = strtolower($visit->user_info?->name ?? '');
                $email = strtolower($visit->user_info?->email ?? '');
                $product = strtolower($visit->latest_product_visit['product_name'] ?? '');
                $needle = strtolower($search);
                return str_contains($name, $needle)
                    || str_contains($email, $needle)
                    || str_contains($product, $needle);
            })->values();
        }

        // Load current visit timer setting
        $perPage     = (int) $request->get('per_page', 10);
$currentPage = (int) $request->get('page', 1);
$total       = $visits->count();
$visits      = new \Illuminate\Pagination\LengthAwarePaginator(
    $visits->forPage($currentPage, $perPage),
    $total,
    $perPage,
    $currentPage,
    ['path' => $request->url(), 'query' => $request->query()]
);

$visitTimerSeconds = (int) \App\Models\Setting::getValue('visit_timer_seconds', 30);

return view('admin.leads.product-visits', compact('visits', 'visitTimerSeconds'));
    }

    private function toObjectIds($ids): array
    {
        return collect($ids)
            ->filter()
            ->map(fn($id) => (string)$id)
            ->filter(fn($id) => preg_match('/^[a-f0-9]{24}$/i', $id))
            ->unique()
            ->values()
            ->map(fn($id) => new \MongoDB\BSON\ObjectId($id))
            ->toArray();
    }

    private function collectVisitProductIds($visits)
    {
        return $visits->flatMap(function ($visit) {
            $ids = collect();
            if ($visit->product_id) {
                $ids->push((string)$visit->product_id);
            }

            foreach (($visit->products ?? []) as $item) {
                $item = $this->visitItemToArray($item);
                if (!empty($item['product_id'])) {
                    $ids->push((string)$item['product_id']);
                }
            }

            return $ids;
        });
    }

    private function formatVisitProducts($visit, $products): array
    {
        $items = collect($visit->products ?? [])
            ->map(function ($item, $index) use ($visit, $products) {
                $item = $this->visitItemToArray($item);
                $productId = !empty($item['product_id']) ? (string)$item['product_id'] : null;
                $product = $productId ? ($products[(string)$productId] ?? null) : null;
                $productName = $item['product_name'] ?? ($product ? lang($product, 'product_name') : null);
                $visitedAt = $item['visited_at'] ?? $visit->visit_date ?? null;
                $status = $this->normalizeVisitStatus($item['status'] ?? null);
                $notes = $this->formatVisitNotes($item);

                return [
                    'visit_id' => (string)$visit->id,
                    'item_index' => $index,
                    'product_id' => $productId,
                    'product_name' => $productName ?: '-',
                    'status' => $status,
                    'status_label' => ucfirst($status),
                    'notes' => $notes,
                    'notes_summary' => $this->visitNotesSummary($notes),
                    'visited_at' => $this->formatVisitDateTime($visitedAt),
                    'visited_at_sort' => $this->visitTimestamp($visitedAt),
                ];
            })
            ->filter(fn($item) => $item['product_name'] !== '-' || $item['product_id'])
            ->sortByDesc('visited_at_sort')
            ->values();

        if ($items->isEmpty() && $visit->product_id) {
            $product = $products[(string)$visit->product_id] ?? null;
            $items->push([
                'visit_id' => (string)$visit->id,
                'item_index' => null,
                'product_id' => (string)$visit->product_id,
                'product_name' => $product ? lang($product, 'product_name') : '-',
                'status' => 'pending',
                'status_label' => 'Pending',
                'notes' => [],
                'notes_summary' => '-',
                'visited_at' => $this->formatVisitDateTime($visit->visit_date ?? null),
                'visited_at_sort' => $this->visitTimestamp($visit->visit_date ?? null),
            ]);
        }

        return $items->map(function ($item) {
            unset($item['visited_at_sort']);
            return $item;
        })->toArray();
    }

    private function normalizeVisitStatus($status): string
    {
        $status = strtolower(trim((string)$status));
        return in_array($status, ['pending', 'contacted'], true) ? $status : 'pending';
    }

    private function visitProductsForMutation(ProductVisit $visit): array
    {
        return collect($visit->products ?? [])
            ->map(fn($item) => $this->visitItemToArray($item))
            ->values()
            ->toArray();
    }

    private function storedVisitNotes(array $item): array
    {
        $hasAdminNotes = array_key_exists('admin_notes', $item);
        $rawNotes = $hasAdminNotes ? $item['admin_notes'] : [];

        if ($rawNotes instanceof \ArrayObject) {
            $rawNotes = $rawNotes->getArrayCopy();
        }

        if ($rawNotes instanceof \Traversable) {
            $rawNotes = iterator_to_array($rawNotes);
        }

        $notes = collect(is_array($rawNotes) ? $rawNotes : [])
            ->map(function ($note) {
                $note = $this->visitNoteToArray($note);
                $text = trim((string)($note['text'] ?? $note['note'] ?? ''));

                if ($text === '') {
                    return null;
                }

                $createdAt = $note['created_at'] ?? $note['createdAt'] ?? now();
                $updatedAt = $note['updated_at'] ?? $note['updatedAt'] ?? $createdAt;

                return [
                    'id' => (string)($note['id'] ?? Str::uuid()),
                    'text' => $text,
                    'created_at' => $createdAt,
                    'updated_at' => $updatedAt,
                    'created_by' => (string)($note['created_by'] ?? ''),
                ];
            })
            ->filter()
            ->values()
            ->toArray();

        if ($hasAdminNotes) {
            return $notes;
        }

        $legacyNote = trim((string)($item['notes'] ?? ''));
        if ($legacyNote === '') {
            return [];
        }

        return [
            $this->makeVisitNote(
                $legacyNote,
                $item['updated_at'] ?? $item['visited_at'] ?? null,
                $item['updated_at'] ?? $item['visited_at'] ?? null
            ),
        ];
    }

    private function makeVisitNote($text, $createdAt = null, $updatedAt = null): array
    {
        $now = now();

        return [
            'id' => (string) Str::uuid(),
            'text' => trim((string)$text),
            'created_at' => $createdAt ?: $now,
            'updated_at' => $updatedAt ?: $now,
            'created_by' => Auth::id() ? (string)Auth::id() : '',
        ];
    }

    private function formatVisitNotes(array $item): array
    {
        return collect($this->storedVisitNotes($item))
            ->map(function ($note, $index) {
                return [
                    'note_index' => $index,
                    'id' => (string)($note['id'] ?? ''),
                    'text' => (string)($note['text'] ?? ''),
                    'created_at' => $this->formatVisitDateTime($note['created_at'] ?? null),
                    'updated_at' => $this->formatVisitDateTime($note['updated_at'] ?? null),
                ];
            })
            ->values()
            ->toArray();
    }

    private function visitNotesSummary(array $notes): string
    {
        $summary = collect($notes)
            ->pluck('text')
            ->filter()
            ->implode(' | ');

        return $summary !== '' ? $summary : '-';
    }

    private function visitNoteToArray($note): array
    {
        if (is_string($note) || is_numeric($note)) {
            return ['text' => (string)$note];
        }

        return $this->visitItemToArray($note);
    }

    private function visitItemToArray($item): array
    {
        if (is_array($item)) {
            return $item;
        }

        if ($item instanceof \ArrayObject) {
            return $item->getArrayCopy();
        }

        return (array)$item;
    }

    private function formatVisitDateTime($value): string
    {
        $date = $this->visitDate($value);
        return $date ? $date->format('d M Y, h:i A') : '-';
    }

    private function visitTimestamp($value): int
    {
        $date = $this->visitDate($value);
        return $date ? $date->timestamp : 0;
    }

    private function visitDate($value): ?Carbon
    {
        if (!$value) {
            return null;
        }

        if ($value instanceof \MongoDB\BSON\UTCDateTime) {
            return Carbon::instance($value->toDateTime())->setTimezone(config('app.timezone'));
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->setTimezone(config('app.timezone'));
        }

        try {
            return Carbon::parse($value)->setTimezone(config('app.timezone'));
        } catch (\Throwable $e) {
            return null;
        }
    }

    // ── Delete product visit ──────────────────────────────────────
    public function destroyVisit($id)
    {
        $visit = ProductVisit::findOrFail($id);
        $visit->update(['is_active' => 0]);
        return response()->json(['success' => true]);
    }

    // ── Export product visits as CSV ──────────────────────────────
    public function exportVisits(Request $request)
    {
        $query = ProductVisit::where('is_active', 1);

        if ($request->filled('date_from')) {
            $query->where('visit_date', '>=', Carbon::parse($request->date_from)->startOfDay());
        }
        if ($request->filled('date_to')) {
            $query->where('visit_date', '<=', Carbon::parse($request->date_to)->endOfDay());
        }

        $visits = $query->orderBy('last_visited_at', 'desc')->get();
        $userIds = $this->toObjectIds($visits->pluck('user_id'));
        $productIds = $this->toObjectIds($this->collectVisitProductIds($visits));
        $users = User::whereIn('_id', $userIds)->get()->keyBy(fn($u) => (string)$u->_id);
        $products = Product::whereIn('_id', $productIds)->get()->keyBy(fn($p) => (string)$p->_id);

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="product_visits.csv"',
        ];

        $callback = function () use ($visits, $users, $products) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['S.No', 'Name', 'Email', 'Mobile', 'Product', 'Status', 'Notes', 'Visit Date']);

            $i = 1;
            foreach ($visits as $visit) {
                $user = $users[(string)$visit->user_id] ?? null;
                $items = $this->formatVisitProducts($visit, $products);

                foreach ($items as $item) {
                    fputcsv($handle, [
                        $i++,
                        $user?->name ?? '-',
                        $user?->email ?? '-',
                        $user?->phone ?? '-',
                        $item['product_name'] ?? '-',
                        $item['status_label'] ?? ucfirst($item['status'] ?? 'pending'),
                        $item['notes_summary'] ?? '-',
                        $item['visited_at'] ?? '-',
                    ]);
                }
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ── Save visit timer setting ──────────────────────────────────
    public function saveVisitTimer(Request $request)
    {
        $request->validate([
            'visit_timer_seconds' => 'required|integer|min:1|max:86400',
        ]);

        \App\Models\Setting::setValue('visit_timer_seconds', $request->visit_timer_seconds);

        return response()->json(['success' => true]);
    }
}
