<!DOCTYPE html>
<html>
<head>
    <title>処理中...</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .message {
            font-size: 24px;
            color: #343a40;
            text-align: center;
        }
    </style>
    <script>
        setTimeout(function() {
            window.location.href = "/";
        }, 500);
    </script>
</head>
<body>
    <div class="message">
        {{ $message }}
    </div>
</body>
</html>
