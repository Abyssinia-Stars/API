<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
</head>
<style>
    form{
        display: flex;
        flex-direction: column;
        width: 200px;

    }
</style>
<body>
    <h1>register</h1>

    <form action="/api/register" method="post" >
    @csrf
        <input type="text" name="name" placeholder="name"  required>
        <input type="email" name="email" placeholder="email" required>
        <input type="password" name="password" placeholder="password" required>
        <input type="password" name="password_confirmation" placeholder="confirm password" required>
        <input type="submit" value="Register">
        </form>
    
</body>
</html>