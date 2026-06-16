<?php

namespace App\Http\Controllers\Admin;

use App\Models\Incoterm;
use Illuminate\Http\Request;

class IncotermController extends ResourceController
{
    protected string $model   = Incoterm::class;
    protected string $view    = 'admin.setup.incoterms.incoterms';
    protected string $route   = 'admin.setup.incoterms';
    protected string $orderBy = 'name';
    protected string $orderDir = 'asc';

    protected array $rules = [
        'code' => 'required|string|max:20',
        'name' => 'required|string|max:255',
    ];

    protected array $fields = ['code', 'name'];

protected function extraStoreData(Request $request): array
{
    return [
        'is_active'  => true,
        'created_by' => auth()->id(),
        'updated_by' => auth()->id(),
    ];
}

protected function extraUpdateData(Request $request): array
{
    return [
        'updated_by' => auth()->id(),
    ];
}
}