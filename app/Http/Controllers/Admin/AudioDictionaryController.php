<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AudioDictionary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AudioDictionaryController extends Controller
{
    /**
     * Danh sách từ điển âm thanh
     */
    public function index(Request $request)
    {
        $keyword = $request->keyword;

        $audios = AudioDictionary::when($keyword, function ($query) use ($keyword) {
                $query->where('word', 'like', "%{$keyword}%");
            })
            ->orderBy('word')
            ->paginate(15);

        return view(
            'admin.audio-dictionary.index',
            compact('audios', 'keyword')
        );
    }

    /**
     * Form thêm
     */
    public function create()
    {
        return view('admin.audio-dictionary.create');
    }

    /**
     * Lưu dữ liệu
     */
    public function store(Request $request)
    {
        $request->validate([
            'word'  => 'required|max:255|unique:audio_dictionaries,word',
            'audio' => 'required|mimes:wav,mp3|max:20480',
        ]);

        // Lấy đúng tên file upload
        $file = $request->file('audio');
        $fileName = $file->getClientOriginalName();

        // Lưu đúng tên file
        $file->storeAs('audio', $fileName, 'public');

        AudioDictionary::create([
            'word'   => $request->word,
            'audio'  => 'audio/' . $fileName,
            'status' => 'Hoạt động',
        ]);

        return redirect()
            ->route('admin.audio-dictionary.index')
            ->with('success', 'Thêm từ thành công.');
    }

    /**
     * Chi tiết
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Form sửa
     */
    public function edit(string $id)
    {
        $audio = AudioDictionary::findOrFail($id);

        return view('admin.audio-dictionary.edit', compact('audio'));
    }

    /**
     * Cập nhật
     */
    public function update(Request $request, string $id)
    {
        $audio = AudioDictionary::findOrFail($id);

        $request->validate([
            'word'  => 'required|max:255|unique:audio_dictionaries,word,' . $audio->id,
            'audio' => 'nullable|mimes:wav,mp3|max:20480',
        ]);

        $audio->word = $request->word;

        if ($request->hasFile('audio')) {

            if (
                $audio->audio &&
                Storage::disk('public')->exists($audio->audio)
            ) {
                Storage::disk('public')->delete($audio->audio);
            }

            $file = $request->file('audio');
            $fileName = $file->getClientOriginalName();

            $file->storeAs('audio', $fileName, 'public');

            $audio->audio = 'audio/' . $fileName;
        }

        $audio->save();

        return redirect()
            ->route('admin.audio-dictionary.index')
            ->with('success', 'Cập nhật thành công.');
    }

    /**
     * Xóa
     */
    public function destroy(string $id)
    {
        $audio = AudioDictionary::findOrFail($id);

        if (
            $audio->audio &&
            Storage::disk('public')->exists($audio->audio)
        ) {
            Storage::disk('public')->delete($audio->audio);
        }

        $audio->delete();

        return redirect()
            ->route('admin.audio-dictionary.index')
            ->with('success', 'Đã xóa từ khỏi từ điển.');
    }

    /**
     * Khóa / Mở khóa
     */
    public function toggleStatus(AudioDictionary $audioDictionary)
    {
        $audioDictionary->status =
            $audioDictionary->status == 'Hoạt động'
                ? 'Khóa'
                : 'Hoạt động';

        $audioDictionary->save();

        return back()->with(
            'success',
            'Đã cập nhật trạng thái.'
        );
    }
}