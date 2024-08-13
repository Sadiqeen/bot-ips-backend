<?php

namespace App\Http\Controllers;

use App\Models\Hijri;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class FileController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['read']]);
    }

    public function upload(Request $request)
    {
        $this->validate($request, [
            'file' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'id' => 'required|exists:hijris,id',
        ]);

        if ($request->hasFile('file') && $request->file('file')->isValid()) {
            // Get the original file extension
            $extension = $request->file->extension();
            // Generate a unique, hashed file name
            $fileName = Str::random(40) . $request->id . '.' . $extension;
            // Store the file in the storage/app/files directory (not in public)
            $request->file('file')->move(storage_path('app/files'), $fileName);

            $this->updateImage($request->id, $fileName);

            return response()->json([
                "status" => "success",
                "message" => ""
            ], 201);
        }

        return response()->json(['error' => 'Invalid file upload'], 400);
    }

    private function updateImage($id, $imageName) {
        $hijri = Hijri::findOrFail($id);

        $oldImage = $hijri->image_name;

        $hijri->image_name = $imageName;
        $hijri->save();

        if ($oldImage) {
            unlink(storage_path('app/files/') . $oldImage);
        }
    }

    public function read(Request $request)
    {
        $name = Str::replace('_', '.', $request->name);
        $name = basename($name);

        $filePath = storage_path('app/files/') . $name;

        if (file_exists($filePath)) {
            return response()->file($filePath);
        }

        return response()->json(['error' => 'File not found'], 404);
    }
}
