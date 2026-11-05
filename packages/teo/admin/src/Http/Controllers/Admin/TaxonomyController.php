<?php

namespace Teo\Admin\Http\Controllers\Admin;

use Teo\Admin\Http\Controllers\Controller;
use App\Models\Taxonomy;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TaxonomyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        Log::channel('chat')->info('TaxonomyController@index START', [
            'type' => $request->type,
            'ip' => $request->ip(),
            'user_id' => auth()->id()
        ]);

        try {
            $query = Taxonomy::with('parent');

            if ($request->type) {
                $query->where('type', $request->type);
            }

            $taxonomies = $query->orderBy('name')->get();

            Log::channel('chat')->info('TaxonomyController@index SUCCESS', [
                'count' => $taxonomies->count(),
                'types' => $taxonomies->groupBy('type')->map->count()
            ]);

            return response()->json($taxonomies);

        } catch (\Exception $e) {
            Log::channel('chat')->error('TaxonomyController@index ERROR', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Ошибка загрузки таксономий'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        Log::channel('chat')->info('TaxonomyController@store START', [
            'request_data' => $request->all(),
            'user_id' => auth()->id()
        ]);

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'slug' => 'nullable|string|unique:taxonomies',
                'type' => 'required|in:category,tag',
                'parent_id' => 'nullable|exists:taxonomies,id'
            ]);

            Log::channel('chat')->info('TaxonomyController@store validation passed', [
                'validated' => $validated
            ]);

            $taxonomy = Taxonomy::create($validated);

            Log::channel('chat')->info('TaxonomyController@store SUCCESS', [
                'taxonomy_id' => $taxonomy->id,
                'name' => $taxonomy->name,
                'type' => $taxonomy->type,
                'user_id' => auth()->id()
            ]);

            return response()->json($taxonomy, 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::channel('chat')->error('TaxonomyController@store VALIDATION ERROR', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            return response()->json(['errors' => $e->errors()], 422);

        } catch (\Exception $e) {
            Log::channel('chat')->error('TaxonomyController@store CRITICAL ERROR', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            return response()->json(['error' => 'Ошибка создания таксономии'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        Log::channel('chat')->info('TaxonomyController@update START', [
            'taxonomy_id' => $id,
            'request_data' => $request->all(),
            'user_id' => auth()->id()
        ]);

        try {
            $taxonomy = Taxonomy::findOrFail($id);

            Log::channel('chat')->info('TaxonomyController@update taxonomy found', [
                'current_name' => $taxonomy->name,
                'current_type' => $taxonomy->type
            ]);

            $validated = $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'slug' => 'sometimes|nullable|string|unique:taxonomies,slug,' . $id,
                'parent_id' => 'nullable|exists:taxonomies,id'
            ]);

            Log::channel('chat')->info('TaxonomyController@update validation passed', [
                'validated' => $validated
            ]);

            $taxonomy->update($validated);

            Log::channel('chat')->info('TaxonomyController@update SUCCESS', [
                'taxonomy_id' => $taxonomy->id,
                'new_name' => $taxonomy->name,
                'user_id' => auth()->id()
            ]);

            return response()->json($taxonomy);

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::channel('chat')->error('TaxonomyController@update VALIDATION ERROR', [
                'taxonomy_id' => $id,
                'errors' => $e->errors()
            ]);
            return response()->json(['errors' => $e->errors()], 422);

        } catch (\Exception $e) {
            Log::channel('chat')->error('TaxonomyController@update CRITICAL ERROR', [
                'taxonomy_id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Ошибка обновления таксономии'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Log::channel('chat')->info('TaxonomyController@destroy START', [
            'taxonomy_id' => $id,
            'user_id' => auth()->id()
        ]);

        try {
            $taxonomy = Taxonomy::findOrFail($id);
            $name = $taxonomy->name;
            $type = $taxonomy->type;

            $taxonomy->delete();

            Log::channel('chat')->info('TaxonomyController@destroy SUCCESS', [
                'taxonomy_id' => $id,
                'name' => $name,
                'type' => $type,
                'user_id' => auth()->id()
            ]);

            return response()->json(['message' => 'Deleted']);

        } catch (\Exception $e) {
            Log::channel('chat')->error('TaxonomyController@destroy ERROR', [
                'taxonomy_id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Ошибка удаления таксономии'], 500);
        }
    }
}
