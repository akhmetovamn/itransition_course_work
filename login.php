<?php
session_start();

$servername = "localhost";
$usernameDB = "oqueasyk_user";
$passwordDB = "Poiujkl9!";
$dbname = "oqueasyk_db";

$conn = new mysqli($servername, $usernameDB, $passwordDB, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = '';
$msg_type = 'info';
$old = ['email' => ''];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $old['email'] = htmlspecialchars($email, ENT_QUOTES);

    if ($email === '' || $password === '') {
        $message = 'Пожалуйста, заполните все поля.';
        $msg_type = 'danger';
    } else {
        $stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE email = ?");
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows === 1) {
                $stmt->bind_result($id, $username, $hashedPassword, $role);
                $stmt->fetch();

                if (password_verify($password, $hashedPassword)) {
					session_regenerate_id(true);
                    $_SESSION['user_id'] = $id;
                    $_SESSION['username'] = $username;
                    $_SESSION['role'] = $role;

                    header('Location: index.php');
                    exit;
                } else {
                    $message = 'Неверный пароль.';
                    $msg_type = 'warning';
                }
            } else {
                $message = 'Пользователь с таким email не найден.';
                $msg_type = 'danger';
            }

            $stmt->close();
        } else {
            $message = 'Ошибка запроса: ' . htmlspecialchars($conn->error);
            $msg_type = 'danger';
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css?v=3">

<style>
    body {
        color: #0b2f26;
        font-family: "Inter", sans-serif;
    }

    .login-section {
        padding-top: 110px !important;
        padding-bottom: 110px !important;
        min-height: calc(100vh - var(--header-height));
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .login-card {
        background: white;
        border-radius: 22px;
        padding: 48px;
        border: 1px solid #daebe3;
        transition: 0.2s;
        max-width: 880px;
        width: 100%;
        box-shadow: 0 14px 40px rgba(0,0,0,0.05);
    }

    .login-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 18px 50px rgba(0,0,0,0.07);
    }

    h2.login-title {
        font-size: 40px;
        font-weight: 800;
        letter-spacing: -0.5px;
        line-height: 1.2;
        color: #0b2f26;
        margin-bottom: 10px;
    }

    .login-title span {
        color: #0f5d4a;
    }

    .login-sub {
        font-size: 18px;
        color: #4e6f62;
        margin-bottom: 18px;
    }

    p, li, label {
        font-size: 18px;
        line-height: 1.6;
    }

    .form-control {
        padding: 14px 18px;
        font-size: 18px;
        border-radius: 16px;
        border: 2px solid #cce3d9;
        background: white;
        transition: 0.2s;
    }

    .form-control:focus {
        border-color: #0f5d4a !important;
        box-shadow: 0 0 0 3px rgba(15,93,74,0.15) !important;
    }

    .btn-cta.primary {
        background-color: #0f5d4a;
        padding: 14px 32px;
        border-radius: 16px;
        color: white;
        font-weight: 600;
        transition: 0.2s;
        font-size: 18px;
    }

    .btn-cta.primary:hover {
        background-color: #187964;
    }

    .btn-cta.outline {
        border: 2px solid #0f5d4a;
        color: #0f5d4a;
        padding: 14px 32px;
        border-radius: 16px;
        font-weight: 600;
        font-size: 18px;
        transition: 0.2s;
    }

    .btn-cta.outline:hover {
        background-color: #0f5d4a;
        color: white;
    }

    @media (max-width: 700px) {
        .login-card {
            padding: 24px;
        }
        h2.login-title {
            font-size: 32px !important;
        }
    }
</style>


</head>
<body>

  <?php include 'header.php'; ?>

  <section class="login-section">
    <div class="container">
      <div class="login-card mx-auto">

        <?php if ($message): ?>
          <div class="alert alert-<?php echo htmlspecialchars($msg_type); ?> alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        <?php endif; ?>

        <div class="row gx-4 align-items-center">
          <div class="col-lg-6">
			<h2 class="login-title">Welcome back to</h2>
			<h2 class="login-title"><span class="title-green">Inventory Management</span></h2>
            <p class="login-sub">Enter your credentials to access your account.</p>

            <ul class="list-unstyled">
              <li class="mb-2"><i class="fa fa-lock text-success me-2"></i> Secure password storage</li>
              <li class="mb-2"><i class="fa fa-user-check text-success me-2"></i> Fast access to courses</li>
              <li class="mb-2"><i class="fa fa-award text-success me-2"></i> Track progress & badges</li>
            </ul>
          </div>

          <div class="col-lg-6">
            <form method="POST" action="" novalidate>
              <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required value="<?php echo $old['email']; ?>">
              </div>

              <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
              </div>

              <div class="mb-3 d-flex justify-content-between align-items-center">
                <div>
                  <input type="checkbox" id="remember" name="remember">
                  <label for="remember" class="form-label mb-0"> Remember me</label>
                </div>
                <a href="forgot.php" class="small">Forgot the password?</a>
              </div>

              <div class="d-grid gap-2">
                <button type="submit" class="btn btn-cta primary">Log in</button>
                <a href="register.php" class="btn btn-cta outline text-center">No account? Register</a>
              </div>
            </form>
          </div>
        </div>

      </div>
    </div>
  </section>

  <?php include 'footer.php'; ?>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <script>
    (function syncHeaderHeight() {
      const header = document.querySelector('header');
      if (!header) return;
      const h = header.getBoundingClientRect().height;
      document.documentElement.style.setProperty('--header-height', h + 'px');
    })();
  </script>
</body>
</html>
