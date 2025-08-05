<p>メール認証を行ってください。</p>
<form method="POST" action="{{ route('verification.send') }}">
    @csrf
    <button type="submit">認証メールを再送信</button>
</form>

