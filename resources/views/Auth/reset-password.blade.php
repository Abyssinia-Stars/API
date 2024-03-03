<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>Reset your password</h1>
    
    <form action="/api/reset-password" method="POST">
        @csrf 
        <input type="password" name="password" id="" required placeholder="enter New password">
        <input type="password" name="password_confirmation" id="" required placeholder="confirm New password">
        <input type="submit" value="reset my password">
    </form>
</body>
</html>