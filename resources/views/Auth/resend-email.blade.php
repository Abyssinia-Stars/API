<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>resend email</h1>
    <form action="/api/">
        @csrf
        <input type="email" name="email" placeholder="email" required>
        <input type="submit" value="resend email">
    </form>
    
</body>
</html>