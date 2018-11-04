<?php
?>

<!doctype html>

<html lang="en">

<head>
    <meta charset="utf-8">

    <title><?php echo $html->getTitle(); ?></title>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u"
        crossorigin="anonymous">
    <link rel="stylesheet" href="pakiti.css">
    <script src="https://code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
        crossorigin="anonymous"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa"
        crossorigin="anonymous"></script>
    <script>
        $(function () { $('[data-toggle="popover"]').popover({html:true}); })
    </script>
</head>
<body>
    <div class="container-fluid">
        <nav class="navbar navbar-inverse">
            <div class="container-fluid">
                <div class="navbar-header">
                    <a class="navbar-brand" href="hosts.php">
                    <span style="font-weight: bold; letter-spacing: 1px; color: #ffffff;"><span style="background-color: #337ab7; color: #ffffff; border-radius: 0.15em;">P</span>akiti &nbsp;</span>
                    </a>
                </div>
                <div class="navbar-text navbar-right">
                    <?php if ($html->getUsername() != null) { echo 'Signed in as <b>'. $html->getUsername() .'</b> &nbsp; '; } ?>
                </div>
                <ul class="nav navbar-nav">
                    <?php foreach ($html->getMenuItems() as $key => $value) { ?>
                        <li<?php if ($key == $html->getMenuActiveItem()) { echo ' class="active"'; } ?>>
                            <a href="<?php echo $key; ?>"><?php echo $value; ?></a>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </nav>
        <?php foreach($html->getErrors() as $msg) { ?>
            <div class="alert alert-danger" role="alert"><b>ERROR: </b><?php echo $msg; ?></div>
        <?php } ?>
