<?php

namespace Teo\Admin\Http\Controllers\Admin;

use Teo\Admin\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaController extends Controller
{
    /**
     * Get list of uploaded media files.
     */
    public function index(Request $request)
    {
        Log::channel('chat')->info('MediaController@index START', [
            'user_id' => auth()->id(),
            'ip' => $request->ip()
        ]);

        try {
            $files = collect(Storage::disk('public')->files('uploads'))
                ->map(function ($file) {
                    return [
                        'id' => Str::random(10),
                        'name' => basename($file),
                        'url' => Storage::url($file),
                        'size' => Storage::disk('public')->size($file),
                        'last_modified' => Storage::disk('public')->lastModified($file),
                    ];
                })
                ->values();

            Log::channel('chat')->info('MediaController@index SUCCESS', [
                'files_count' => $files->count(),
                'user_id' => auth()->id()
            ]);

            return response()->json($files);
        } catch (\Exception $e) {
            Log::channel('chat')->error('MediaController@index ERROR', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);
            return response()->json(['error' => 'Failed to load media'], 500);
        }
    }

    /**
     * Upload a media file.
     */
    public function upload(Request $request)
    {
        Log::channel('chat')->info('MediaController@upload START', [
            'user_id' => auth()->id(),
            'has_file' => $request->hasFile('file'),
            'ip' => $request->ip()
        ]);

        try {
            $request->validate([
                'file' => 'required|image|max:5120' // 5MB max
            ]);

            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $filename = time() . '_' . Str::slug(pathinfo($originalName, PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('uploads', $filename, 'public');

            Log::channel('chat')->info('MediaController@upload SUCCESS', [
                'original_name' => $originalName,
                'saved_name' => $filename,
                'path' => $path,
                'size' => $file->getSize(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'url' => Storage::url($path),
                'filename' => $filename,
                'path' => $path
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::channel('chat')->error('MediaController@upload VALIDATION ERROR', [
                'errors' => $e->errors(),
                'user_id' => auth()->id()
            ]);
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::channel('chat')->error('MediaController@upload CRITICAL ERROR', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'user_id' => auth()->id()
            ]);
            return response()->json(['error' => 'Upload failed'], 500);
        }
    }

    /**
     * Delete a media file (soft – only removes from storage).
     */
    public function destroy($filename, Request $request)
    {
        Log::channel('chat')->info('MediaController@destroy START', [
            'filename' => $filename,
            'user_id' => auth()->id(),
            'ip' => $request->ip()
        ]);

        try {
            // Ищем файл в папке uploads
            $path = 'uploads/' . $filename;

            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);

                Log::channel('chat')->info('MediaController@destroy SUCCESS', [
                    'filename' => $filename,
                    'user_id' => auth()->id()
                ]);

                return response()->json(['success' => true, 'message' => 'File deleted']);
            } else {
                Log::channel('chat')->warning('MediaController@destroy FILE NOT FOUND', [
                    'filename' => $filename,
                    'path' => $path
                ]);

                return response()->json(['error' => 'File not found'], 404);
            }
        } catch (\Exception $e) {
            Log::channel('chat')->error('MediaController@destroy ERROR', [
                'filename' => $filename,
                'message' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            return response()->json(['error' => 'Deletion failed'], 500);
        }
    }

    /**
     * CKEditor upload handler.
     */
    public function ckeditorUpload(Request $request)
    {
        Log::channel('chat')->info('MediaController@ckeditorUpload START', [
            'user_id' => auth()->id(),
            'has_file' => $request->hasFile('upload'),
            'ip' => $request->ip()
        ]);

        try {
            // CKEditor sends file as 'upload' field, not 'file'
            $request->merge(['file' => $request->file('upload')]);

            $uploadResponse = $this->upload($request);

            if ($uploadResponse->getStatusCode() === 200) {
                $data = $uploadResponse->getData();
                $url = $data->url;

                Log::channel('chat')->info('MediaController@ckeditorUpload SUCCESS', [
                    'url' => $url,
                    'user_id' => auth()->id()
                ]);

                // CKEditor expects JSON with "url" field
                return response()->json(['url' => $url]);
            } else {
                throw new \Exception('Upload failed');
            }
        } catch (\Exception $e) {
            Log::channel('chat')->error('MediaController@ckeditorUpload ERROR', [
                'message' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);
            return response()->json(['error' => 'CKEditor upload failed'], 500);
        }
    }
}
