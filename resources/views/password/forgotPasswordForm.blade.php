<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>

    <div>
        @if (session()->has('success')) {
            <p>{{ session('success') }} </p>
        }

        @elseif(session()->has('error')) {
           <p> {{ session('error') }}</p> 
        }
        
        @endif
    </div>

    <form action={{route('forgot.password.post')}} method="POST">
        @csrf

        <label for="emailAnchor">Enter Email</label>
        <input name="email" type="email" id="emailAnchor">
        <button type="submit">Submit</button>
    </form>
</body>
</html>