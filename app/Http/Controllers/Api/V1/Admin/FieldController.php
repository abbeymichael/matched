<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Admin\UpdateFieldDefinition;
use App\Actions\Admin\UpdateFieldOption;
use App\Http\Controllers\Controller;
use App\Models\FieldDefinition;
use App\Models\ProfileFieldOption;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FieldController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(FieldDefinition::orderBy('category')->orderBy('sort_order')->get());
    }

    public function update(Request $request, string $field, UpdateFieldDefinition $action): JsonResponse
    {
        $data = $request->validate([
            'is_active' => ['sometimes', 'boolean'],
            'is_hard_filter' => ['sometimes', 'boolean'],
            'is_required' => ['sometimes', 'boolean'],
            'weight' => ['sometimes', 'numeric', 'min:0'],
            'sort_order' => ['sometimes', 'integer'],
        ]);

        $updated = $action->handle(FieldDefinition::findOrFail($field), $data);

        return response()->json($updated);
    }

    public function options(string $field): JsonResponse
    {
        return response()->json(FieldDefinition::findOrFail($field)->options()->get());
    }

    public function updateOption(Request $request, string $field, string $option, UpdateFieldOption $action): JsonResponse
    {
        $data = $request->validate([
            'label' => ['sometimes', 'string', 'max:255'],
            'sort_order' => ['sometimes', 'integer'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $updated = $action->handle(ProfileFieldOption::findOrFail($option), $data);

        return response()->json($updated);
    }
}
