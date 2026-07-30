<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WebsiteTranslationStore;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WebsiteTranslationController extends Controller
{
    public function show(
        Request $request,
        string $locale,
        WebsiteTranslationStore $store,
    ): JsonResponse|Response {
        $language = strtolower(trim($locale));

        if (preg_match('/^[a-z]{2,3}$/', $language) !== 1) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid language code.',
            ], 422);
        }

        $bundle = $store->find($language);
        if (! $bundle) {
            return response()->json([
                'status' => 'error',
                'message' => 'Website translation bundle not found.',
            ], 404);
        }

        $etag = '"'.($bundle['content_hash'] ?: hash(
            'sha256',
            json_encode($bundle['sections'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
        )).'"';

        if ($request->headers->get('If-None-Match') === $etag) {
            return response('', 304)
                ->header('ETag', $etag)
                ->header('Cache-Control', 'public, max-age=300, stale-while-revalidate=3600');
        }

        return response()->json($bundle)
            ->header('ETag', $etag)
            ->header('Cache-Control', 'public, max-age=300, stale-while-revalidate=3600');
    }
}
