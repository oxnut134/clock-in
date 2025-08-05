<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <title>返信ボタン付きメール</title>
</head>

<body style="width:100%;display:flex;justify-content:center;">
    <div>
        <h3>登録していただいたメールアドレスに認証メールを送付しました。</h3>
        <div style="display:flex;justify-content:center;">
            <div>
                <h3>メールを確認して認証を完了してください。</h3>
                <div style="display:flex;justify-content:center;flex-direction:column;">
                    <div style="display:flex;justify-content:center;">
                        <a href="http://localhost:8025" style="width:40%;border-radius:5px;padding: 10px 20px; font-size: 16px;background-color:gray;color:#fff;text-decoration:none;display:flex;justify-content:center;">認証はこちらから</a>
                    </div>
                    <div style="display:flex;justify-content:center;">
                        <form action="{{ route('verification.send')}}" method="post">
                            @csrf
                            <input type="hidden" name="email" value="{{ $email }}">
                            <button type="submit" style="margin-top:5vh;;border:none;background-color:#fff;font-size:14px;">認証メールを再送する</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>


</html>
