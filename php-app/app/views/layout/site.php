<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($title ?? $clinic['name']) ?></title>
  <meta name="description" content="<?= e($description ?? ('رزرو آنلاین نوبت در ' . $clinic['name'] . ' — خدمات تخصصی پوست، مو و زیبایی')) ?>">
  <link rel="stylesheet" href="/static/css/tailwind.css">
  <script defer src="/static/js/alpine.min.js"></script>
  <script defer src="/static/js/htmx.min.js"></script>
</head>
<body class="min-h-screen flex flex-col" hx-headers='{"X-CSRFToken": "<?= e($csrf_token) ?>"}'>
  <?php require __DIR__ . '/../website/partials/navbar.php'; ?>
  <main class="flex-1">
    <?= $content ?>
  </main>
  <?php require __DIR__ . '/../website/partials/footer.php'; ?>
  <?= $extra_body ?? '' ?>
</body>
</html>
