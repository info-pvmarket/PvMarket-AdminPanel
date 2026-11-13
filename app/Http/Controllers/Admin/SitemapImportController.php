<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SitemapUpload;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SitemapImportController extends Controller
{
    public function index()
    {
        $published = SitemapUpload::query()->where('key', 'published')
            ->first(['updated_at', 'history', 'entries']);
        $history = $published?->history ?? [];
        if (!$history && $published?->updated_at) {
            $history = [[
                'published_at' => (string) $published->updated_at,
                'url_count' => count($published->entries ?? []),
            ]];
        }

        return view('admin.seo-meta.sitemap-import', [
            'publishedAt' => $published?->updated_at,
            'history' => array_reverse((array) $history),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate(['sitemap_xml' => ['required', 'file', 'max:5120']]);
        $upload = $request->file('sitemap_xml');
        if ($upload->getClientOriginalName() !== 'sitemap.xml') {
            $this->invalid('The filename must be exactly sitemap.xml.');
        }

        $xml = file_get_contents($upload->getRealPath());
        if ($xml === false || !mb_check_encoding($xml, 'UTF-8') || str_contains($xml, "\0") || preg_match('/<!DOCTYPE/i', $xml)) {
            $this->invalid('Upload a UTF-8 sitemap without a document type declaration.');
        }

        $document = new \DOMDocument();
        $root = @$document->loadXML($xml, LIBXML_NONET) ? $document->documentElement : null;
        $namespace = 'http://www.sitemaps.org/schemas/sitemap/0.9';
        if (!$root || $root->localName !== 'urlset' || $root->namespaceURI !== $namespace) {
            $this->invalid('Upload a valid sitemap with a urlset root.');
        }

        $entries = [];
        foreach ($root->childNodes as $node) {
            if (!$node instanceof \DOMElement) {
                continue;
            }
            if ($node->localName !== 'url' || $node->namespaceURI !== $namespace || count($entries) >= 50000) {
                $this->invalid('The sitemap must contain only URL entries and at most 50,000 URLs.');
            }
            $locations = $node->getElementsByTagNameNS($namespace, 'loc');
            $dates = $node->getElementsByTagNameNS($namespace, 'lastmod');
            $url = trim($locations->item(0)?->textContent ?? '');
            $parts = parse_url($url);
            if ($locations->length !== 1 || !filter_var($url, FILTER_VALIDATE_URL) || strlen($url) > 2048
                || ($parts['scheme'] ?? '') !== 'https' || ($parts['host'] ?? '') !== 'pv.market'
                || isset($parts['user']) || isset($parts['port']) || isset($parts['fragment']) || isset($entries[$url])) {
                $this->invalid('Each entry must have one unique HTTPS URL on pv.market without a fragment.');
            }

            $entry = ['url' => $url];
            if ($dates->length > 0) {
                $date = trim($dates->item(0)->textContent);
                if ($dates->length !== 1 || !preg_match('/^\d{4}-\d{2}-\d{2}(?:T\d{2}:\d{2}:\d{2}(?:\.\d+)?(?:Z|[+-]\d{2}:\d{2}))?$/D', $date)
                    || !checkdate((int) substr($date, 5, 2), (int) substr($date, 8, 2), (int) substr($date, 0, 4))
                    || strtotime($date) === false) {
                    $this->invalid('Use a valid sitemap lastmod date for '.$url.'.');
                }
                $entry['lastModified'] = $date;
            }
            $entries[$url] = $entry;
        }

        if (!$entries) {
            $this->invalid('The sitemap must contain at least one URL.');
        }

        $timestamp = new \MongoDB\BSON\UTCDateTime(now());
        $record = [
            'published_at' => now()->toIso8601String(),
            'admin' => (string) $request->user()->name,
            'source_name' => $upload->getClientOriginalName(),
            'url_count' => count($entries),
        ];

        // Replace the active entries and append the audit record in one atomic write.
        SitemapUpload::raw(fn ($collection) => $collection->updateOne(
            ['_id' => 'published'],
            [['$set' => [
                'key' => 'published',
                'entries' => ['$literal' => array_values($entries)],
                'updated_at' => $timestamp,
                'history' => ['$concatArrays' => [
                    ['$ifNull' => ['$history', ['$cond' => [
                        ['$ne' => [['$ifNull' => ['$updated_at', null]], null]],
                        [[
                            'published_at' => ['$toString' => '$updated_at'],
                            'url_count' => ['$size' => ['$ifNull' => ['$entries', []]]],
                        ]],
                        [],
                    ]]]],
                    ['$literal' => [$record]],
                ]],
            ]]],
            ['upsert' => true],
        ));

        return back()->with('success', count($entries).' URLs published to sitemap.xml.');
    }

    public function published()
    {
        $entries = SitemapUpload::query()->where('key', 'published')->value('entries');
        abort_if(!$entries, 404);

        return response()->json($entries)->header('Cache-Control', 'no-store');
    }

    private function invalid(string $message): never
    {
        throw ValidationException::withMessages(['sitemap_xml' => $message]);
    }
}
