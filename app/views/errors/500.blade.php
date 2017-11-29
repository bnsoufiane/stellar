<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Stellar - Error 500</title>
    <meta name="viewport" content="initial-scale=1.0,maximum-scale=1.0,user-scalable=no">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <!-- bootstrap framework -->
    <link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet" media="screen">
    <!-- google webfonts -->
    <link href='https://fonts.googleapis.com/css?family=Open+Sans&subset=latin,latin-ext' rel='stylesheet'
          type='text/css'>
    <!-- main stylesheet -->
    <link href="assets/css/main.min.css" rel="stylesheet" media="screen">

</head>
<body class="error_page">

<div id="error_wrapper">
    <div id="error_wrapper_inner">
        <h2 class="error_subheading">Whoops, looks like something went wrong.</h2>
        <p><a href="{{ URL::route('index') }}">Go Back</a></p>
    </div>
</div>

</body>
</html>
