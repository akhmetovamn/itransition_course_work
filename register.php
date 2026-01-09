<?php

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
$old = [
    'username' => '',
    'email' => '',
    'role' => 'student'
];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $role = in_array($_POST['role'] ?? '', ['student','teacher']) ? $_POST['role'] : 'student';

    $old['username'] = htmlspecialchars($username, ENT_QUOTES);
    $old['email'] = htmlspecialchars($email, ENT_QUOTES);
    $old['role'] = $role;

    if ($username === '' || $email === '' || $password === '') {
        $message = 'Пожалуйста, заполните все обязательные поля.';
        $msg_type = 'danger';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Неверный формат email.';
        $msg_type = 'danger';
    } elseif (!preg_match('/^[a-zA-Z]{4,}$/', $username)) {  
        $message = 'Имя пользователя должно содержать только латинские буквы и быть не короче 4 символов.';
        $msg_type = 'danger';
    } elseif (strlen($password) < 8) {  
        $message = 'Пароль должен содержать минимум 8 символов.';
        $msg_type = 'danger';
    } else {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        if ($check) {
            $check->bind_param("s", $email);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $message = "Этот email уже зарегистрирован!";
                $msg_type = 'warning';
            } else {
                $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
                if ($stmt) {
                    $stmt->bind_param("ssss", $username, $email, $hashedPassword, $role);
                    if ($stmt->execute()) {
                        $message = "Регистрация прошла успешно! Вы можете войти.";
                        $msg_type = 'success';
                        $old = ['username'=>'','email'=>'','role'=>'student'];
                    } else {
                        $message = "Ошибка при регистрации: " . htmlspecialchars($stmt->error);
                        $msg_type = 'danger';
                    }
                    $stmt->close();
                } else {
                    $message = "Ошибка подготовки запроса: " . htmlspecialchars($conn->error);
                    $msg_type = 'danger';
                }
            }
            $check->close();
        } else {
            $message = "Ошибка проверки email: " . htmlspecialchars($conn->error);
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
  <title>Oqu Easy - Register</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
	<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link rel="stylesheet" href="style.css?v=3">
  <link rel="icon" href="picture_library/favicon.png" type="image/png">

<style>
    body {
        color: #0b2f26;
        font-family: "Inter", sans-serif;
    }

    .register-section {
        padding-top: 110px !important;
        padding-bottom: 110px !important;
        min-height: calc(100vh - var(--header-height));
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .register-card {
        background: white;
        border-radius: 22px;
        padding: 48px;
        border: 1px solid #daebe3;
        transition: 0.2s;
        max-width: 880px;
        width: 100%;
        box-shadow: 0 14px 40px rgba(0,0,0,0.05);
    }

    .register-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 18px 50px rgba(0,0,0,0.07);
    }

    h2.register-title {
        font-size: 40px;
        font-weight: 800;
        letter-spacing: -0.5px;
        line-height: 1.2;
        color: #0b2f26;
        margin-bottom: 10px;
    }

    .register-title .title-green {
        color: #0f5d4a;
    }

    .register-sub {
        font-size: 18px;
        color: #4e6f62;
        margin-bottom: 18px;
    }

    p, li, label {
        font-size: 18px;
        line-height: 1.6;
    }

    .form-control, .form-select {
        padding: 14px 18px;
        font-size: 18px;
        border-radius: 16px;
        border: 2px solid #cce3d9;
        background: white;
        transition: 0.2s;
    }

    .form-control:focus, .form-select:focus {
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
        .register-card {
            padding: 24px;
        }
        h2.register-title {
            font-size: 32px !important;
        }
    }
</style>

</head>
<body>

  <?php include 'header.php'; ?>

<section class="register-section">
    <div class="container">
        <div class="register-card mx-auto">

            <?php if ($message): ?>
              <div class="alert alert-<?php echo htmlspecialchars($msg_type); ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              </div>
            <?php endif; ?>

            <div class="row gx-4 align-items-center">
                <div class="col-lg-6">

                    <h2 class="register-title">Create your</h2>
                    <h2 class="register-title"><span class="title-green">Inventory Management</span> account</h2>

                    <p class="register-sub">Join us today.</p>
                </div>

                <div class="col-lg-6">
                    <form method="POST" action="" novalidate>

                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" required value="<?php echo $old['username']; ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required value="<?php echo $old['email']; ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                            <div class="form-text">Your password will be stored securely in encrypted form.</div>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-cta primary">Register</button>
                            <a href="login.php" class="btn btn-cta outline text-center">Already have an account? Log in</a>
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
