<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Stores an image chosen with a rich-text editor's image button, so the
 * content holds a URL instead of a base64 copy of the file (TASK-6.4).
 */
class EditorImageController extends Controller
{
    /** Anyone who can edit one of the rich-text sections may upload. */
    private const PERMISSIONS = [
        'knowledge_hub.blogs',
        'knowledge_hub.news',
        'knowledge_hub.events',
        'static_pages',
    ];

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless(
            $user && $user->canAccessAdmin()
                && collect(self::PERMISSIONS)->contains(fn ($permission) => $user->hasAdminPermission($permission)),
            403
        );

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
        ]);

        $disk = $request->query('context') === 'blog' ? 'r2' : 'public';
        if ($disk === 'r2') abort_unless($user->hasAdminPermission('knowledge_hub.blogs'), 403);

        $path = $request->file('image')->store('inline-images/editor/' . now()->format('Y/m'), $disk);
        abort_unless(is_string($path) && $path !== '', 503, 'Image upload failed. Please try again.');

        return response()->json(['url' => Storage::disk($disk)->url($path)]);
    }
}