<?php

namespace App\Http\Controllers;

use App\Models\AudioDictionary;
use Illuminate\Http\Request;

class DictionaryController extends Controller
{
    public function index(Request $request)
    {
        $keyword = $request->keyword;
        $letter = $request->letter;

        $audios = AudioDictionary::query()

            ->when($keyword, function ($query) use ($keyword) {
                $query->where('word', 'like', "%{$keyword}%");
            })

            ->when($letter, function ($query) use ($letter) {
                $query->where('word', 'like', $letter.'%');
            })

            ->where('status', 'Hoạt động')

            ->orderBy('word')

            ->paginate(20)

            ->withQueryString();

        return view('dictionary.index', compact(
            'audios',
            'keyword',
            'letter'
        ));
    }
}