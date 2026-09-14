<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($title ?? ('پنل مدیریت — ' . $clinic['name'])) ?></title>
  <link rel="stylesheet" href="/static/css/tailwind.css">
  <script defer src="/static/js/alpine.min.js"></script>
  <script defer src="/static/js/htmx.min.js"></script>
</head>
<body class="bg-sand-50 min-h-screen" hx-headers='{"X-CSRFToken": "<?= e($csrf_token) ?>"}'>
  <?= $content ?>
</body>
</html>
