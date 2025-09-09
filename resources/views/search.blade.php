<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SEO Rank Checker</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 30px; }
        input, select { padding: 5px; margin: 5px 0; width: 300px; }
        button { padding: 5px 15px; }
        .result { margin-top: 20px; font-weight: bold; }
        .error { color: red; margin-top: 10px; }
    </style>
</head>
<body>
    <h1>SEO Rank Checker</h1>

    @if(session('error'))
        <div class="error">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('search.run') }}">
        @csrf
        <div>
            <label>Пошукове слово:</label><br>
            <input type="text" name="keyword" value="{{ old('keyword') }}" required>
        </div>
        <div>
            <label>Назва сайту (URL):</label><br>
            <input type="url" name="site" value="{{ old('site') }}" required>
        </div>
        <div>
            <label>Локація:</label><br>
            <input type="text" name="location" value="{{ old('location') }}" required>
        </div>
        <div>
            <label>Мова:</label><br>
            <input type="text" name="language" value="{{ old('language') }}" required>
        </div>
        <button type="submit">Пошук</button>
    </form>

    @isset($rank)
        <div class="result">
            @if($rank)
                Ранг сайту {{ $site }} по ключовому слову '{{ $keyword }}' — {{ $rank }}
            @else
                Сайт {{ $site }} не знайдено в органічних результатах
            @endif
        </div>
    @endisset
</body>
</html>
