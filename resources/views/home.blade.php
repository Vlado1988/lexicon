@extends('layouts.app')

@section('content')
    <form action="{{ route('home.translate') }}" class="flex flex-wrap items-center gap-2">
        <input type="search" name="search_text" id="search-text" class="flex-shrink sm:flex-grow sm:w-auto w-full min-w-[200px]" placeholder="search ..." value="{{ request()->search_text }}" autofocus>
        <div class="lang-select-container flex gap-2 items-end flex-shrink flex-wrap sm:flex-grow sm:w-fit">
            <select name="source_language" id="source_word_language" class="flex-1 min-w-[200px]">
                <option value="">-- Select --</option>
                @foreach ($languages as $language)
                    <option value="{{ $language->id }}" {{ request()->source_language == $language->id ? 'selected' : '' }}>{{ $language->name }}</option>
                @endforeach
            </select>

            <div class="translate-y-[-7px] cursor-pointer mt-2 min-w-[200px]">
                <span class="switch_language_order text-2xl">
                    &#11020;
                </span>
            </div>

            <select name="target_language" id="target_word_language" class="flex-1 min-w-[200px]">
                <option value="">-- Select --</option>
                @foreach ($languages as $language)
                    <option value="{{ $language->id }}" {{ request()->target_language == $language->id ? 'selected' : '' }}>{{ $language->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="translate_btn btn btn-translate flex-shrink w-full sm:w-auto">Translate</button>
    </form>

    @if(isset($translations))
        <table class="translation-table">
        @if($translations->count() > 0)
            @foreach ($translations as $translation)
                @php
                    $targetWords = explode(', ', $translation->target_word);
                    $targetWords = format_translations($targetWords, $source_language, $target_language, '<ul>', '</ul>', '<li class="translation-item">', '</li>');
                @endphp
                <tr>
                    <td>{{ $translation->source_word }}</td>
                    <td>{!! $targetWords !!}</td>
                </tr>
            @endforeach
        @else
            <tr>
                <td colspan="2" class="italic">'<strong>{{ request()->search_text }}</strong>' Nothing found</td>
            </tr>
        @endif
        </table>
    @endif
@endsection
