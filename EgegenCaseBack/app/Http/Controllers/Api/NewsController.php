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
        // Arama filtresi varsa sorguya ekle
        $newsQuery = News::query();

        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $newsQuery->where(function ($query) use ($searchTerm) {
                $query->where('title', 'like', "%{$searchTerm}%")
                    ->orWhere('content', 'like', "%{$searchTerm}%");
            });
        }

        // Sayfalama: Sayfa başına 20 kayıt
        $news = $newsQuery->paginate(20);

        // JSON olarak paginasyon bilgileri ve haberleri döndür
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
        // ID ile haberi bul
        $news = News::find($id);

        // Haber bulunamadıysa 404 döndür
        if (!$news) {
            return response()->json([
                'message' => 'Haber bulunamadı.'
            ], 404);
        }

        // Haber bulunduysa veri ve mesaj ile 200 döndür
        return response()->json([
            'data' => $news,
            'message' => 'Haber başarıyla bulundu.'
        ], 200);
    }

    public function store(StoreNewsRequest $request)
    {
        // Yeni haber modeli oluştur
        $news = new News();
        $news->title = $request->title;
        $news->content = $request->content;

        // Eğer istek içinde 'image' dosyası varsa işle
        if ($request->hasFile('image')) {
            $imageFile = $request->file('image');

            // Benzersiz dosya adı oluştur, .webp formatı kullan
            $filename = Str::uuid() . '.webp';

            // Görseli 800x800 boyutuna orantılı olarak yeniden boyutlandır ve webp olarak encode et
            $img = Image::read($imageFile->path());
            $imagePath = storage_path('app/public/news');

            // Eğer klasör yoksa oluştur
            if (!file_exists($imagePath)) {
                mkdir($imagePath, 0755, true);
            }

            // Resmi kaydet
            $img->resize(800, 800, function ($constraint) {
                $constraint->aspectRatio();
            })->save($imagePath . '/' . $filename);

            $news->image_path = "storage/news/{$filename}";
        }

        // Haber kaydını veritabanına kaydet
        $news->save();

        // Başarılı yanıt dön (201 Created)
        return response()->json([
            'message' => 'Haber başarıyla eklendi.'
        ], 201);
    }


    public function update(StoreNewsRequest $request, int $id)
    {
        // Güncellenecek haberi bul
        $news = News::find($id);

        // Haber yoksa 404 döndür
        if (!$news) {
            return response()->json([
                'message' => 'Haber bulunamadı.'
            ], 404);
        }

        // Gelen verilerle haber başlığı ve içeriğini güncelle
        $news->title = $request->title;
        $news->content = $request->content;

        // Eğer yeni bir resim yüklenmişse eski resmi sil ve yenisini kaydet
        if ($request->hasFile('image')) {
            // Var olan resmi storage'dan sil (varsa)
            if ($news->image_path) {
                // 'storage/' ile başlayan yolu 'public/' ile değiştiriyoruz çünkü Storage bu şekilde çalışır
                Storage::delete(str_replace('storage/', 'public/', $news->image_path));
            }

            $imageFile = $request->file('image');
            $filename = Str::uuid() . '.webp';


            // Görseli 800x800 boyutuna orantılı olarak yeniden boyutlandır ve webp olarak encode et
            $img = Image::read($imageFile->path());
            $imagePath = storage_path('app/public/news');

            // Eğer klasör yoksa oluştur
            if (!file_exists($imagePath)) {
                mkdir($imagePath, 0755, true);
            }
            $img->resize(800, 800, function ($constraint) {
                $constraint->aspectRatio();
            })->save($imagePath . '/' . $filename);

            // Yeni resim yolunu modelde güncelle
            $news->image_path = "storage/news/{$filename}";
        }

        // Veritabanında güncelle
        $news->save();

        return response()->json([
            'message' => 'Haber başarıyla güncellendi.'
        ], 200);
    }

    public function destroy(int $id)
    {
        // Silinecek haberi bul
        $news = News::find($id);

        // Haber yoksa 404 döndür
        if (!$news) {
            return response()->json([
                'message' => 'Haber bulunamadı.'
            ], 404);
        }

        // Haber ile ilişkili resim dosyası varsa sil
        if ($news->image_path) {
            // 'storage/' ile başlayan yolu 'public/' ile değiştiriyoruz çünkü Storage bu şekilde çalışır
            Storage::delete(str_replace('storage/', 'public/', $news->image_path));
        }

        // Haber kaydını veritabanından sil
        $news->delete();

        // Başarı mesajı ile 200 OK döndür
        return response()->json([
            'message' => 'Haber başarıyla silindi.'
        ], 200);
    }
}
