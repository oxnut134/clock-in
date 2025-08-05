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
                <h3>メール認証を完了してください。</h3>
                <div style="display:flex;flex-direction:column;justify-content:center;">
                    <a style="border-radius:5px;padding: 10px 20px; font-size: 16px;background-color:gray;color:#fff;text-decoration:none;display:flex;justify-content:center;" href="{{ $verificationUrl }}">認証はこちらから</a>
                    <form style="display:flex;justify-content:center;" action="{{ route('verification.send') }}" method="POST">
                        @csrf
                        <button style="margin-top:5vh;;border:none;background-color:#fff;font-size:14px;">認証メールを再送する</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>


</html>
