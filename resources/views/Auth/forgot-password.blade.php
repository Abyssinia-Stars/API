<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>forgot password</h1>
    
    <form action="/api/forgot-password" method="POST">
        @csrf 
        <input type="email" name="email" id="" required placeholder="enter email to reset password">
        <input type="submit" value="send link">
    </form>
</body>
</html>