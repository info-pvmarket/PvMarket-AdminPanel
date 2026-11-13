@extends('layouts.admin')
@section('title', 'Import XML')

@section('styles')
<style>
    .sitemap-import { max-width: 760px; }
    .sitemap-import-header { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 20px; }
    .sitemap-import h1 { font-size: 24px; margin: 0; }
    .sitemap-import-card { background: #fff; border: 1px solid var(--border); border-radius: 16px; padding: clamp(16px, 4vw, 28px); }
    .sitemap-import-card h2 { font-size: 17px; margin: 0 0 8px; }
    .sitemap-import-card p { margin: 0 0 16px; color: var(--muted); }
    .sitemap-import-history { margin-top: 20px; overflow-x: auto; }
    .sitemap-import-history table { width: 100%; border-collapse: collapse; text-align: left; }
    .sitemap-import-history th, .sitemap-import-history td { padding: 10px; border-bottom: 1px solid var(--border); overflow-wrap: anywhere; }
    .sitemap-import-field { display: grid; gap: 10px; padding: 16px; margin-bottom: 16px; border: 1px dashed var(--border); border-radius: 10px; }
    .sitemap-import-field label { font-weight: 650; }
    .sitemap-import-field input { width: 100%; font: inherit; }
    .sitemap-import-action { display: inline-block; padding: 10px 16px; border-radius: 8px; font: inherit; font-weight: 650; text-decoration: none; cursor: pointer; }
    .sitemap-import-back { color: var(--text); background: #fff; border: 1px solid var(--border); }
    .sitemap-import-publish { color: #fff; background: #0369a1; border: 1px solid #0369a1; }
    .sitemap-import-card .sitemap-import-success { color: #166534; background: #f0fdf4; padding: 12px; border-radius: 8px; }
    .sitemap-import-card .sitemap-import-error { color: #991b1b; margin: 0; }
</style>
@endsection

@section('content')
<section class="sitemap-import" aria-labelledby="sitemapImportTitle">
    <header class="sitemap-import-header">
        <h1 id="sitemapImportTitle">Import XML</h1>
        <a href="{{ route('admin.seo-meta.index') }}" class="sitemap-import-action sitemap-import-back">Back to SEO Meta</a>
    </header>

    <div class="sitemap-import-card">
        <h2>Publish a sitemap</h2>
        <p>Publishing replaces the entire storefront sitemap at <code>/sitemap.xml</code>.</p>
        @if($publishedAt)
            <p>Last published {{ $publishedAt }}</p>
        @endif
        @if(session('success'))
            <p class="sitemap-import-success" role="status">{{ session('success') }}</p>
        @endif

        <form method="POST" action="{{ route('admin.seo-meta.sitemap.store') }}" enctype="multipart/form-data"
              onsubmit="return confirm('Publish this XML as the complete storefront sitemap?')">
            @csrf
            <div class="sitemap-import-field">
                <label for="sitemapXml">Sitemap XML file</label>
                <input type="file" id="sitemapXml" name="sitemap_xml" accept=".xml,application/xml,text/xml"
                       aria-describedby="sitemapFileHelp{{ $errors->has('sitemap_xml') ? ' sitemapFileError' : '' }}"
                       @error('sitemap_xml') aria-invalid="true" @enderror required>
                <small id="sitemapFileHelp">Name the file <code>sitemap.xml</code>. Use a URL set with HTTPS links on pv.market. Maximum file size is 5 MB.</small>
                @error('sitemap_xml')
                    <p id="sitemapFileError" class="sitemap-import-error" role="alert">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="sitemap-import-action sitemap-import-publish">Publish Sitemap</button>
        </form>
    </div>
    <div class="sitemap-import-card sitemap-import-history">
        <h2>Upload history</h2>
        @if($history)
            <table aria-label="Sitemap upload history">
                <thead><tr><th scope="col">Published at</th><th scope="col">Uploaded file</th><th scope="col">Published by</th><th scope="col">URLs</th></tr></thead>
                <tbody>
                    @foreach($history as $upload)
                        <tr>
                            <td>{{ $upload['published_at'] }}</td>
                            <td>{{ $upload['source_name'] ?? 'Previous upload' }}</td>
                            <td>{{ $upload['admin'] ?? 'Unknown' }}</td>
                            <td>{{ number_format($upload['url_count']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>New uploads will appear here after publishing.</p>
        @endif
    </div>
</section>
@endsection
