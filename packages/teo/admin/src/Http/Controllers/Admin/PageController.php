<?php

namespace Teo\Admin\Http\Controllers\Admin;

use Teo\Admin\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PageController extends Controller
{
    /**
     * Определить тип контента из маршрута
     */
    protected function getContentType(Request $request): string
    {
        // Проверяем URI
        $path = $request->path();

        if (str_contains($path, '/api/admin/posts')) {
            return 'post';
        }

        if (str_contains($path, '/api/admin/pages')) {
            return 'page';
        }

        // Проверяем имя маршрута
        $routeName = $request->route()->getName();
        if ($routeName) {
            if (str_contains($routeName, 'posts')) {
                return 'post';
            }
            if (str_contains($routeName, 'pages')) {
                return 'page';
            }
        }

        return 'page';
    }

    /**
     * Получить список контента
     */
    public function index(Request $request)
    {
        try {
            $type = $this->getContentType($request);

            $query = Post::with('author')
                ->where('type', $type);

            // Разная сортировка для разных типов
            if ($type === 'page') {
                $query->orderBy('order');
            } else {
                $query->orderByDesc('published_at');
            }

            // Поиск
            if ($request->search) {
                $query->where(function($q) use ($request) {
                    $q->where('title', 'like', "%{$request->search}%")
                        ->orWhere('slug', 'like', "%{$request->search}%");
                });
            }

            // Фильтр по статусу
            if ($request->status && $request->status !== 'all') {
                $query->where('status', $request->status);
            }

            $content = $query->paginate($request->per_page ?? 15);

            return response()->json($content);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Ошибка загрузки: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Создать новый контент
     */
    public function store(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $type = $this->getContentType($request);

            // Валидация
            $rules = [
                'title' => 'required|string|max:255',
                'slug' => 'nullable|string|unique:posts,slug',
                'content' => 'nullable|string',
                'excerpt' => 'nullable|string|max:500',
                'status' => 'required|in:draft,published,private',
                'parent_id' => 'nullable|exists:posts,id',
                'featured_image' => 'nullable|string',
                'meta' => 'nullable|array',
            ];

            // Для постов добавляем поле published_at
            if ($type === 'post') {
                $rules['published_at'] = 'nullable|date';
            }

            $validated = $request->validate($rules);

            DB::beginTransaction();

            // Базовые данные
            $contentData = [
                'title' => $validated['title'],
                'slug' => $validated['slug'] ?? \Str::slug($validated['title']),
                'content' => $validated['content'] ?? '',
                'excerpt' => $validated['excerpt'] ?? null,
                'type' => $type,
                'status' => $validated['status'],
                'author_id' => $user->id,
                'parent_id' => $validated['parent_id'] ?? null,
                'featured_image' => $validated['featured_image'] ?? null,
                'meta' => $validated['meta'] ?? [],
            ];

            // Для страниц - добавляем order
            if ($type === 'page') {
                $maxOrder = Post::where('type', 'page')->max('order') ?? 0;
                $contentData['order'] = $maxOrder + 1;
                $contentData['published_at'] = $validated['status'] === 'published' ? now() : null;
            }

            // Для постов - добавляем дату публикации
            if ($type === 'post') {
                $contentData['published_at'] = $validated['published_at'] ??
                    ($validated['status'] === 'published' ? now() : null);
            }

            $content = Post::create($contentData);

            DB::commit();

            $content->load('author');

            return response()->json($content, 201);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['errors' => $e->errors()], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Ошибка создания: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Показать один элемент
     */
    public function show(Request $request, $id)
    {
        try {
            $type = $this->getContentType($request);

            $content = Post::with(['author', 'parent', 'children'])
                ->where('type', $type)
                ->findOrFail($id);

            return response()->json($content);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Не найдено'], 404);
        }
    }

    /**
     * Обновить контент
     */
    public function update(Request $request, $id)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $type = $this->getContentType($request);

            $content = Post::where('type', $type)->findOrFail($id);

            // Валидация
            $rules = [
                'title' => 'required|string|max:255',
                'slug' => 'nullable|string|unique:posts,slug,' . $id,
                'content' => 'nullable|string',
                'excerpt' => 'nullable|string|max:500',
                'status' => 'required|in:draft,published,private',
                'parent_id' => 'nullable|exists:posts,id',
                'featured_image' => 'nullable|string',
                'meta' => 'nullable|array',
            ];

            // Для постов добавляем поле published_at
            if ($type === 'post') {
                $rules['published_at'] = 'nullable|date';
            }

            $validated = $request->validate($rules);

            DB::beginTransaction();

            $oldStatus = $content->status;
            $updateData = [
                'title' => $validated['title'],
                'slug' => $validated['slug'] ?? $content->slug,
                'content' => $validated['content'] ?? '',
                'excerpt' => $validated['excerpt'] ?? null,
                'status' => $validated['status'],
                'parent_id' => $validated['parent_id'] ?? null,
                'featured_image' => $validated['featured_image'] ?? null,
                'meta' => array_merge($content->meta ?? [], $validated['meta'] ?? []),
            ];

            // Логика для даты публикации
            if ($type === 'post' && isset($validated['published_at'])) {
                $updateData['published_at'] = $validated['published_at'];
            } else {
                $updateData['published_at'] = $validated['status'] === 'published' && $oldStatus !== 'published'
                    ? now()
                    : $content->published_at;
            }

            $content->update($updateData);

            DB::commit();

            return response()->json($content->load('author'));

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json(['errors' => $e->errors()], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Ошибка обновления: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Удалить контент (soft delete)
     */
    public function destroy(Request $request, $id)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $type = $this->getContentType($request);

            $content = Post::where('type', $type)->findOrFail($id);
            $content->delete();

            $message = $type === 'page' ? 'Страница удалена' : 'Запись удалена';

            return response()->json(['message' => $message]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Ошибка удаления'], 500);
        }
    }

    /**
     * Восстановить удаленный контент
     */
    public function restore(Request $request, $id)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $content = Post::withTrashed()->findOrFail($id);
            $content->restore();

            $message = $content->type === 'page' ? 'Страница восстановлена' : 'Запись восстановлена';

            return response()->json(['message' => $message]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Ошибка восстановления'], 500);
        }
    }

    /**
     * Полностью удалить контент
     */
    public function forceDelete(Request $request, $id)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $content = Post::withTrashed()->findOrFail($id);
            $content->forceDelete();

            $message = $content->type === 'page' ? 'Страница удалена полностью' : 'Запись удалена полностью';

            return response()->json(['message' => $message]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Ошибка удаления'], 500);
        }
    }

    /**
     * Дублировать контент
     */
    public function duplicate(Request $request, $id)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $type = $this->getContentType($request);
            $original = Post::where('type', $type)->findOrFail($id);

            $duplicate = $original->replicate();
            $newTitle = $original->title . ' (Копия)';
            $newSlug = $original->slug . '-copy-' . time();

            $duplicate->title = $newTitle;
            $duplicate->slug = $newSlug;
            $duplicate->status = 'draft';
            $duplicate->created_at = now();

            // Для страниц добавляем order
            if ($type === 'page') {
                $maxOrder = Post::where('type', 'page')->max('order') ?? 0;
                $duplicate->order = $maxOrder + 1;
            }

            $duplicate->save();

            return response()->json($duplicate);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Ошибка дублирования: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Изменить порядок страниц (только для страниц)
     */
    public function reorder(Request $request)
    {
        try {
            $user = $request->user();

            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $validated = $request->validate([
                'orders' => 'required|array',
                'orders.*.id' => 'required|exists:posts,id',
                'orders.*.order' => 'required|integer'
            ]);

            foreach ($validated['orders'] as $item) {
                Post::where('id', $item['id'])
                    ->where('type', 'page')
                    ->update(['order' => $item['order']]);
            }

            return response()->json(['message' => 'Порядок обновлен']);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Ошибка обновления порядка: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Получить версии (заглушка для будущего функционала)
     */
    public function versions($id)
    {
        return response()->json(['message' => 'В разработке']);
    }

    /**
     * Предпросмотр (заглушка для будущего функционала)
     */
    public function preview(Request $request, $id)
    {
        return response()->json(['message' => 'В разработке']);
    }
}
