<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Document</title>

  <link rel="stylesheet" href="styles/index.css">
  <link rel="stylesheet" href="styles/header.css">
</head>
<body>
  <header id="header"></header>

  <!-- Please don't delete this Savin cause i need it for link to the other pages i'm working on -->
  <div>
    <a href="/user_pages/user.php" target="_blank">
      user page
    </a>
  </div>

  <script>
    fetch('/components/header.html')
      .then(res => res.text())
      .then(data => {
        document.getElementById('header').innerHTML = data;
      });
  </script>
</body>
</html>
