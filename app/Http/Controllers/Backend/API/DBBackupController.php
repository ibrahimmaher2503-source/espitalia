<?php

namespace App\Http\Controllers\Backend\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class DBBackupController extends Controller
{
    // NOTE: This endpoint exposes storage file listing to all authenticated users.
    // The route middleware should be restricted to admin-only access.
    public function index(Request $request)
    {

        $data = Storage::allFiles(env('APP_NAME', 'Espitalia Laravel'));
        return response()->json(['data' => $data, 'status' => true]);
    }

    public function downloadBkFiles(Request $request)
    {
        if(isset($request->file_name) && !empty($request->file_name))
        {
            // Sanitize file name to prevent path traversal attacks
            $fileName = basename($request->file_name);

            // Reject any file name that contains directory traversal characters
            if ($fileName !== $request->file_name || preg_match('/\.\./', $request->file_name)) {
                return response()->json(['status' => false, 'message' => 'Invalid file name.']);
            }

            $file = storage_path('app/').$fileName;

            if (!file_exists($file)) {
                return response()->json(['status' => false, 'message' => 'File not found.']);
            }

            return Response::download($file);
        }else{
            return response()->json(['status' => false, 'message' => 'Something went wrong..!']);
        }
    }
}
