<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    /**
     * Get admin dashboard statistics
     */
    public function dashboard(): JsonResponse
    {
        try {
            $stats = [
                'total_users' => User::count(),
                'admin_users' => User::where('is_admin', true)->count(),
                'linked_spouses' => User::whereNotNull('spouse_id')->count() / 2, // Divided by 2 since it's bidirectional
                'recent_users' => User::latest()->take(5)->get(['id', 'name', 'email', 'created_at']),
                'database_size' => $this->getDatabaseSize(),
                'last_backup' => $this->getLastBackupTime(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load dashboard: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get all users with pagination
     */
    public function getUsers(Request $request): JsonResponse
    {
        try {
            $perPage = $request->query('per_page', 15);
            $search = $request->query('search');

            $query = User::with('spouse:id,name,email');

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $users = $query->latest()->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $users,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch users: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new user account
     */
    public function createUser(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'is_admin' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'is_admin' => $request->is_admin ?? false,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => $user,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create user: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update user details
     */
    public function updateUser(Request $request, int $id): JsonResponse
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,'.$id,
            'password' => 'sometimes|string|min:8',
            'is_admin' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            if ($request->has('name')) {
                $user->name = $request->name;
            }
            if ($request->has('email')) {
                $user->email = $request->email;
            }
            if ($request->has('password')) {
                $user->password = Hash::make($request->password);
            }
            if ($request->has('is_admin')) {
                $user->is_admin = $request->is_admin;
            }

            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'data' => $user,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update user: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a user account
     */
    public function deleteUser(int $id): JsonResponse
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        // Prevent deleting the last admin
        if ($user->is_admin && User::where('is_admin', true)->count() === 1) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete the last admin user',
            ], 422);
        }

        try {
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete user: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create database backup
     */
    public function createBackup(): JsonResponse
    {
        try {
            $filename = 'backup_'.date('Y-m-d_H-i-s').'.sql';
            $path = storage_path('app/backups');

            // Create backups directory if it doesn't exist
            if (! file_exists($path)) {
                mkdir($path, 0755, true);
            }

            $fullPath = $path.'/'.$filename;

            // Get database credentials
            $database = config('database.connections.'.config('database.default'));
            $host = $database['host'];
            $dbName = $database['database'];
            $username = $database['username'];
            $password = $database['password'];

            // Create mysqldump command
            $command = sprintf(
                'mysqldump -h %s -u %s %s %s > %s',
                escapeshellarg($host),
                escapeshellarg($username),
                $password ? '-p'.escapeshellarg($password) : '',
                escapeshellarg($dbName),
                escapeshellarg($fullPath)
            );

            // Execute backup
            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                throw new \Exception('Backup command failed with code: '.$returnCode);
            }

            // Get file size
            $fileSize = filesize($fullPath);

            return response()->json([
                'success' => true,
                'message' => 'Database backup created successfully',
                'data' => [
                    'filename' => $filename,
                    'path' => $fullPath,
                    'size' => $this->formatBytes($fileSize),
                    'created_at' => date('Y-m-d H:i:s'),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create backup: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * List all backups
     */
    public function listBackups(): JsonResponse
    {
        try {
            $path = storage_path('app/backups');

            if (! file_exists($path)) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                ]);
            }

            $files = array_diff(scandir($path), ['.', '..']);
            $backups = [];

            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
                    $fullPath = $path.'/'.$file;
                    $backups[] = [
                        'filename' => $file,
                        'size' => $this->formatBytes(filesize($fullPath)),
                        'created_at' => date('Y-m-d H:i:s', filemtime($fullPath)),
                        'path' => $fullPath,
                    ];
                }
            }

            // Sort by creation time, newest first
            usort($backups, function ($a, $b) {
                return strtotime($b['created_at']) - strtotime($a['created_at']);
            });

            return response()->json([
                'success' => true,
                'data' => $backups,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to list backups: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Restore database from backup
     */
    public function restoreBackup(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'filename' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $filename = $request->filename;
            $path = storage_path('app/backups/'.$filename);

            if (! file_exists($path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Backup file not found',
                ], 404);
            }

            // Get database credentials
            $database = config('database.connections.'.config('database.default'));
            $host = $database['host'];
            $dbName = $database['database'];
            $username = $database['username'];
            $password = $database['password'];

            // Create mysql restore command
            $command = sprintf(
                'mysql -h %s -u %s %s %s < %s',
                escapeshellarg($host),
                escapeshellarg($username),
                $password ? '-p'.escapeshellarg($password) : '',
                escapeshellarg($dbName),
                escapeshellarg($path)
            );

            // Execute restore
            exec($command, $output, $returnCode);

            if ($returnCode !== 0) {
                throw new \Exception('Restore command failed with code: '.$returnCode);
            }

            // Clear caches after restore
            Artisan::call('cache:clear');
            Artisan::call('config:clear');

            return response()->json([
                'success' => true,
                'message' => 'Database restored successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to restore backup: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a backup file
     */
    public function deleteBackup(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'filename' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $filename = $request->filename;
            $path = storage_path('app/backups/'.$filename);

            if (! file_exists($path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Backup file not found',
                ], 404);
            }

            unlink($path);

            return response()->json([
                'success' => true,
                'message' => 'Backup deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete backup: '.$e->getMessage(),
            ], 500);
        }
    }

    /**
     * Helper: Get database size
     */
    private function getDatabaseSize(): string
    {
        try {
            $dbName = config('database.connections.'.config('database.default').'.database');
            $result = DB::select("SELECT SUM(data_length + index_length) as size FROM information_schema.TABLES WHERE table_schema = ?", [$dbName]);

            return $this->formatBytes($result[0]->size ?? 0);
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    /**
     * Helper: Get last backup time
     */
    private function getLastBackupTime(): ?string
    {
        try {
            $path = storage_path('app/backups');

            if (! file_exists($path)) {
                return null;
            }

            $files = array_diff(scandir($path), ['.', '..']);
            $latestTime = 0;

            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
                    $time = filemtime($path.'/'.$file);
                    if ($time > $latestTime) {
                        $latestTime = $time;
                    }
                }
            }

            return $latestTime > 0 ? date('Y-m-d H:i:s', $latestTime) : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Helper: Format bytes to human readable
     */
    private function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision).' '.$units[$i];
    }
}
