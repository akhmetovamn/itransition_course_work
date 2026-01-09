<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$user_id   = $_SESSION['user_id'] ?? null;
$username  = $_SESSION['username'] ?? null;
$role      = $_SESSION['role'] ?? null;

function esc($s) {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
?>

<header class="fixed top-0 w-full z-50 bg-green-50/90 backdrop-blur-md shadow-sm border-b border-green-100">
    <div class="max-w-6xl mx-auto flex items-center justify-between px-6 py-3">
        <a href="index.php" class="text-2xl font-extrabold text-green-700">
            Oqu <span class="text-green-800">Easy</span>
        </a>
        <button id="menuToggle" class="text-green-700 lg:hidden">
            <i class="fa-solid fa-bars text-2xl"></i>
        </button>
        <nav id="navbar"
             class="hidden lg:flex gap-6 items-center text-green-900 font-medium">
<a href="user_guide.php" 
   сlass="hover:texct-green-700">
    User Guide
</a>
            <a href="index.php" class="hover:text-green-700">Home</a>
<a href="<?php echo $user_id ? '/study_materials.php' : 'login.php'; ?>" сlass="hover:texct-green-700">
    Study Materials
</a>
            <a href="glossary.php" class="hover:text-green-700">Glossary</a>
        </nav>
        <div id="authArea"
             class="hidden lg:flex items-center gap-4">

            <?php if ($user_id && $role === 'student'): ?>
                <a href="/dashboard.php"
                   class="px-4 py-2 border border-green-600 text-green-700 rounded-lg font-semibold hover:bg-green-600 hover:text-white transition">
                    Student Dashboard
                </a>
                <a href="logout.php"
                   class="px-4 py-2 bg-green-700 text-white rounded-lg font-semibold hover:bg-green-600 transition">
                    Logout
                </a>

            <?php elseif ($user_id && $role === 'teacher'): ?>
                <a href="teacher_dashboard.php"
                   class="px-4 py-2 border border-green-600 text-green-700 rounded-lg font-semibold hover:bg-green-600 hover:text-white transition">
                    Teacher Dashboard
                </a>
                <a href="logout.php"
                   class="px-4 py-2 bg-green-700 text-white rounded-lg font-semibold hover:bg-green-600 transition">
                    Logout
                </a>

            <?php else: ?>
                <a href="login.php"
                   class="px-4 py-2 border border-green-600 text-green-700 rounded-lg font-semibold hover:bg-green-600 hover:text-white transition">
                    Log in
                </a>
                <a href="register.php"
                   class="px-4 py-2 bg-green-700 text-white rounded-lg font-semibold hover:bg-green-600 transition">
                    Register
                </a>
            <?php endif; ?>
        </div>
    </div>

    <div id="mobileMenu"
         class="hidden flex-col bg-green-50 border-t border-green-100 px-6 py-4 space-y-3 lg:hidden">

        <a href="index.php" class="block font-medium text-green-900">Home</a>
        <hr class="border-green-200">
        <?php else: ?>
            <a href="login.php" class="block font-semibold text-green-700">Log in</a>
            <a href="register.php" class="block font-semibold text-green-700">Register</a>
        <?php endif; ?>
    </div>
</header>

<script>
    const menuToggle = document.getElementById("menuToggle");
    const mobileMenu = document.getElementById("mobileMenu");

    menuToggle?.addEventListener("click", () => {
        mobileMenu.classList.toggle("hidden");
        mobileMenu.classList.toggle("flex");
    });
</script>
