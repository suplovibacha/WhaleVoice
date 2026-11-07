<?php

namespace App\Http\Controllers;

use App\Services\FirestoreService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class DictionaryController extends Controller
{
    private FirestoreService $firestore;

    public function __construct(FirestoreService $firestore)
    {
        $this->firestore = $firestore;
    }

    public function index(Request $request)
    {
        // 1. Nhận từ khóa và chữ cái được chọn
        $keyword = trim($request->input('keyword', ''));

        $letter = mb_strtoupper(
            trim($request->input('letter', '')),
            'UTF-8'
        );

        // 2. Lấy dữ liệu từ Firestore
        $words = collect($this->firestore->getAllWords());

        // 3. Lọc theo chữ cái
        if ($letter !== '') {
            $words = $words->filter(function ($item) use ($letter) {
                return mb_strtoupper(
                    trim($item['letter'] ?? ''),
                    'UTF-8'
                ) === $letter;
            });
        }

        // 4. Tìm kiếm theo từ khóa
        if ($keyword !== '') {
            $searchKeyword = mb_strtolower($keyword, 'UTF-8');

            $words = $words->filter(function ($item) use ($searchKeyword) {
                return str_contains(
                    mb_strtolower($item['word'] ?? '', 'UTF-8'),
                    $searchKeyword
                );
            });
        }

        // 5. Sắp xếp từ vựng
        $words = $words->sortBy('word')->values();

        // 6. Tạo URL âm thanh từ file WAV local
        $words = $words->map(function ($item) {
            $item['audio_url'] = !empty($item['storagePath'])
                ? route('audio.local', [
                    'path' => $item['storagePath']
                ])
                : null;

            return $item;
        });

        // 7. Phân trang 20 từ mỗi trang
        $perPage = 20;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();

        $currentItems = $words
            ->slice(($currentPage - 1) * $perPage, $perPage)
            ->values();

        $audios = new LengthAwarePaginator(
            $currentItems,
            $words->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        // 8. Trả dữ liệu về giao diện
        return view('dictionary.index', compact(
            'audios',
            'keyword',
            'letter'
        ));
    }
}
