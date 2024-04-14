<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>

    <p>You can reset your forgotten password on the <strong> Hyla </strong> by click the link below </p>
    <div>
        <p>we may include our company's image to spice things up </p>
        {{-- <img src=
            <?= 
        // $message->embed("$pathToFile");}} 
        //?> 
        alt=""> --}}
    </div>
    <p> Forgot password for  <strong>{{ $name }}</strong>,</p>
    <p>{{$body}}</p>
    <a href={{route('reset.passport.get', ["token" => $token])}}>Click here to reset password {{$token}}</a>

    <form action={{route('reset.passport.get', ["token" => $token])}} method="GET">
        <button type="submit">click here </button>
    </form>

</body>
</html>