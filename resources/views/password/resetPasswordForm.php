<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <div>Reset Password</div>

    <form action="{{route('reset.password.post')}}">
        @csrf
        <input name="token" hidden  type="text" value="{{$token}}">
        
        <label for="emailAction">Enter Email</label>
        <input name="email" type="email" id="emailAction">

        <label for="passwordAction">Enter Password</label>
        <input name="password" type="password" id="passwordAction">

        <label for="passwordConfirmation">Enter Password</label>
        <input name="password_confirmation" type="password" id="passwordConfirmation">

        <button type=submit>Submit</button>
    </form>
</body>
</html>