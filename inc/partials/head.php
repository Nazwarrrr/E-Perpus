<?php
$pageTitle = $pageTitle ?? 'E-Perpustakaan';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link rel="stylesheet" href="assets/css/app.css">
    <script>
    (function(){
        var t=localStorage.getItem('eperpustakaan-theme');
        document.documentElement.classList.add(t==='dark'?'dark':'light');
    })();
    </script>
</head>
