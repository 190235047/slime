<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?=$h1?></title>
</head>
<body>
    <h1><?=$h1?></h1>
    <ul>
        <?php foreach ($G_User as $Item_User):?>
        <li><?=$Item_User?></li>
        <?php endforeach;?>
    </ul>
</body>
</html>