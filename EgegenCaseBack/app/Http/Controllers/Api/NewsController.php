<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNewsRequest;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image;
use Illuminate\Support\Str;


class NewsController extends Controller
{
    public function index(Request $request)
    {
        // if 'search' parameter is present, filter news by title or content
        $newsQuery = News::query();

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $newsQuery->where(function ($query) use ($searchTerm) {
                $query->where('title', 'like', "%{$searchTerm}%")
                    ->orWhere('content', 'like', "%{$searchTerm}%");
            });
        }

        $news = $newsQuery->paginate(20);

        // Pagination response with links and meta information
        return response()->json([
            'data' => $news->items(),
            'links' => [
                'first' => $news->url(1),
                'last' => $news->url($news->lastPage()),
                'prev' => $news->previousPageUrl(),
                'next' => $news->nextPageUrl(),
            ],
            'meta' => [
                'current_page' => $news->currentPage(),
                'from' => $news->firstItem(),
                'last_page' => $news->lastPage(),
                'path' => $news->path(),
                'per_page' => $news->perPage(),
                'to' => $news->lastItem(),
                'total' => $news->total(),
            ],
        ])->setStatusCode(200, 'Haberler başarıyla listelendi.');
    }


    public function show(int $id)
    {
        // Find the news by ID
        $news = News::find($id);

        // If news not found, return 404
        if (!$news) {
            return response()->json([
                'message' => 'Haber bulunamadı.'
            ], 404);
        }

        // Return the news data with a success message
        return response()->json([
            'data' => $news,
            'message' => 'Haber başarıyla bulundu.'
        ], 200);
    }

    public function store(StoreNewsRequest $request)
    {
        // Create a new News instance
        $news = new News();
        $news->title = $request->title;
        $news->content = $request->content;

        // If an image is uploaded, process it
        if ($request->hasFile('image')) {
            $imageFile = $request->file('image');

            // create a unique filename using UUID
            $filename = Str::uuid() . '.webp';

            // resize the image to 800x800 and encode it as webp
            $img = Image::read($imageFile->path());
            $imagePath = storage_path('app/public/news');

            // if the directory does not exist, create it
            if (!file_exists($imagePath)) {
                mkdir($imagePath, 0755, true);
            }

            // save the resized image
            $img->resize(800, 800, function ($constraint) {
                $constraint->aspectRatio();
            })->save($imagePath . '/' . $filename);

            $news->image_path = "storage/news/{$filename}";
        }

        // save the news to the database
        $news->save();

        // return a success response
        return response()->json([
            'message' => 'Haber başarıyla eklendi.'
        ], 201);
    }


    public function update(StoreNewsRequest $request, int $id)
    {
        // find the news by ID
        $news = News::find($id);

        // if news not found, return 404
        if (!$news) {
            return response()->json([
                'message' => 'Haber bulunamadı.'
            ], 404);
        }

        // Update the news title and content
        $news->title = $request->title;
        $news->content = $request->content;

        // If an image is uploaded, process it
        if ($request->hasFile('image')) {
            // Var olan resmi storage'dan sil (varsa)
            if ($news->image_path) {
                Storage::delete(str_replace('storage/', 'public/', $news->image_path));
            }

            $imageFile = $request->file('image');
            $filename = Str::uuid() . '.webp';


            // resize the image to 800x800 and encode it as webp
            $img = Image::read($imageFile->path());
            $imagePath = storage_path('app/public/news');

            // Eğer klasör yoksa oluştur
            if (!file_exists($imagePath)) {
                mkdir($imagePath, 0755, true);
            }
            $img->resize(800, 800, function ($constraint) {
                $constraint->aspectRatio();
            })->save($imagePath . '/' . $filename);

            // update the image path in the news model
            $news->image_path = "storage/news/{$filename}";
        }

        // Save the updated news to the database
        $news->save();

        return response()->json([
            'message' => 'Haber başarıyla güncellendi.'
        ], 200);
    }

    public function destroy(int $id)
    {
        // find the news by ID
        $news = News::find($id);

        // if news not found, return 404
        if (!$news) {
            return response()->json([
                'message' => 'Haber bulunamadı.'
            ], 404);
        }

        // delete the image from storage if it exists
        if ($news->image_path) {
            Storage::delete(str_replace('storage/', 'public/', $news->image_path));
        }

        // Delete the news from the database
        $news->delete();

        // return a success response
        return response()->json([
            'message' => 'Haber başarıyla silindi.'
        ], 200);
    }
}
