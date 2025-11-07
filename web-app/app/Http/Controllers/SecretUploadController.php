<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\ImageLibrary;

class SecretUploadController extends Controller
{
    private $secretKey = 'bizkit';
    
    public function showUploadForm(Request $request)
    {
        // Check secret access
        if (!$this->hasSecretAccess($request)) {
            abort(404);
        }
        
        return view('admin.secret-upload');
    }
    
    public function upload(Request $request)
    {
        // Check secret access
        if (!$this->hasSecretAccess($request)) {
            abort(404);
        }
        
        $request->validate([
            'files.*' => 'required|file|max:51200', // 50MB max
            'secret_key' => 'required|string',
            'context' => 'nullable|string|max:500'
        ]);
        
        $uploadedFiles = [];
        
        foreach ($request->file('files') as $file) {
            try {
                // Generate hash
                $hash = hash('sha256', file_get_contents($file->getRealPath()));
                
                // Check if already exists
                if (ImageLibrary::where('hash', $hash)->exists()) {
                    $uploadedFiles[] = [
                        'name' => $file->getClientOriginalName(),
                        'status' => 'duplicate',
                        'hash' => $hash
                    ];
                    continue;
                }
                
                // Store file
                $path = $file->storeAs('images', $hash, 'local');
                
                // Add to image library
                ImageLibrary::create([
                    'hash' => $hash,
                    'filename' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                    'upload_ip' => $request->ip(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                $uploadedFiles[] = [
                    'name' => $file->getClientOriginalName(),
                    'status' => 'uploaded',
                    'hash' => $hash,
                    'size' => $this->formatFileSize($file->getSize())
                ];
                
                Log::info('Secret upload successful', [
                    'file' => $file->getClientOriginalName(),
                    'hash' => $hash,
                    'size' => $file->getSize()
                ]);
                
            } catch (\Exception $e) {
                $uploadedFiles[] = [
                    'name' => $file->getClientOriginalName(),
                    'status' => 'error',
                    'error' => $e->getMessage()
                ];
                
                Log::error('Secret upload failed', [
                    'file' => $file->getClientOriginalName(),
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        return response()->json([
            'success' => true,
            'files' => $uploadedFiles
        ]);
    }
    
    private function hasSecretAccess(Request $request)
    {
        // Check for secret key in multiple places
        $key = $request->get('key') ?? 
               $request->header('X-Secret-Key') ?? 
               $request->input('secret_key');
               
        return $key === $this->secretKey;
    }
    
    public function processContext(Request $request)
    {
        // Check secret access
        if (!$this->hasSecretAccess($request)) {
            abort(404);
        }
        
        $request->validate([
            'context' => 'required|string|max:500',
            'hashes' => 'required|array',
            'hashes.*' => 'required|string|size:64|regex:/^[a-f0-9]{64}$/',
            'secret_key' => 'required|string'
        ]);
        
        $context = $request->input('context');
        $hashes = $request->input('hashes');
        
        Log::info('Context processing requested', [
            'context' => $context,
            'hashes' => $hashes,
            'hash_count' => count($hashes)
        ]);
        
        // Process based on context keywords
        $result = $this->handleContextProcessing($context, $hashes);
        
        return response()->json([
            'success' => true,
            'message' => $result['message'] ?? 'Context processed successfully',
            'applied_changes' => $result['changes'] ?? []
        ]);
    }
    
    private function handleContextProcessing($context, $hashes)
    {
        $context = strtolower(trim($context));
        $changes = [];
        
        // Header/Logo processing
        if (str_contains($context, 'logo') || str_contains($context, 'header')) {
            $changes[] = 'Would update site header/logo with uploaded images';
        }
        
        // Jukebox processing
        if (str_contains($context, 'jukebox') || str_contains($context, 'music') || str_contains($context, 'song')) {
            $changes[] = 'Would create music player with uploaded audio files';
        }
        
        // Background processing
        if (str_contains($context, 'background') || str_contains($context, 'theme')) {
            $changes[] = 'Would set uploaded images as board backgrounds';
        }
        
        // Board-specific processing
        if (str_contains($context, 'board')) {
            $changes[] = 'Would apply files to specific board configuration';
        }
        
        if (empty($changes)) {
            $changes[] = 'Context analyzed - files stored and ready for manual processing';
        }
        
        return [
            'message' => "Processed context: '{$context}' with " . count($hashes) . " files",
            'changes' => $changes
        ];
    }
    
    private function formatFileSize($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        }
        return $bytes . ' bytes';
    }
}