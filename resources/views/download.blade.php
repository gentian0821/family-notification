<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>テキストボックスの例</title>
    <!-- 必要に応じてCSSを追加 -->
    <style>
        body { font-family: sans-serif; padding: 20px; }
        label { display: block; margin-bottom: 5px; }
        input[type="text"] { padding: 8px; width: 300px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; }
        button { padding: 10px 15px; background-color: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { background-color: #0056b3; }
    </style>
</head>
<body>

    <h1>テキスト入力フォーム</h1>

    {{-- フォームの開始 --}}
    {{-- action属性には、フォームの送信先のURLを指定します --}}
    {{-- 例: '/submit-data' ルートにPOSTリクエストを送る --}}
    <form method="POST" action="/download">
        {{-- CSRF保護のためのトークン --}}
        @csrf

        <div>
            {{-- テキストボックスのラベル --}}
            <label for="my_text">何か入力してください:</label>

            {{-- テキストボックス本体 --}}
            {{-- name属性は、コントローラーで値を受け取る際のキーになります --}}
            <input type="text" id="url" name="url" placeholder="ここに入力...">
        </div>

        {{-- 送信ボタン --}}
        <button type="submit">送信</button>
    </form>

</body>
</html>