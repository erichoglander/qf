<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>500 - Internal error</title>
</head>
<body>
  <h1>Internal error</h1>
  <p>Something went wrong while trying to load your page.</p>
  <?php if (isset($console)) { ?>
  <script>console.log("<?=$console?>");</script>
  <?php } ?>
</body>
</html>