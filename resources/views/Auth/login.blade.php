<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <h1>login</h1>
    
    <form action="/api/login" method="post" >
    @csrf
      
        <input type="email" name="email" placeholder="email" required>
        <input type="password" name="password" placeholder="password" required>

        <input type="submit" value="Login">
        </form>
    
</body>
</html>