<?php
session_start();
require 'init.php';

$student_id = $_SESSION['user_id'] ?? 1;

$stmtUser = $conn->prepare("SELECT * FROM users WHERE id = :student_id");
$stmtUser->execute([':student_id' => $student_id]);
$student = $stmtUser->fetch(PDO::FETCH_ASSOC);

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_profile'])){
    $username = trim($_POST['username']);
    $grade = trim($_POST['grade']);

    if(isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === 0){
        $ext = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
        $filename = 'uploads/profile_'.$student_id.'.'.$ext;
        move_uploaded_file($_FILES['profile_picture']['tmp_name'], $filename);
    } else {
        $filename = $student['profile_picture'] ?? 'picture_library/default_avatar.png';
    }

    $stmtUpdate = $conn->prepare("UPDATE users SET username=:username, grade=:grade, profile_picture=:profile_picture WHERE id=:id");
    $stmtUpdate->execute([
        ':username'=>$username,
        ':grade'=>$grade,
        ':profile_picture'=>$filename,
        ':id'=>$student_id
    ]);

    $student['username'] = $username;
    $student['grade'] = $grade;
    $student['profile_picture'] = $filename;
}

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_name'], $_POST['score'])){
    $test_name = trim($_POST['test_name']);
    $score = (int)$_POST['score'];

    if($test_name !== '' && $score >=0 && $score <=100){
        $stmtInsert = $conn->prepare("INSERT INTO results (user_id, test_name, score) VALUES (:user_id, :test_name, :score)");
        $stmtInsert->execute([
            ':user_id' => $student_id,
            ':test_name' => $test_name,
            ':score' => $score
        ]);

        if(isset($_POST['ajax'])){
            echo json_encode([
                'success' => true,
                'test_name' => $test_name,
                'score' => $score,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            exit;
        }
    }
}

$stmtResults = $conn->prepare("SELECT * FROM results WHERE user_id = :user_id ORDER BY created_at DESC");
$stmtResults->execute([':user_id' => $student_id]);
$results = $stmtResults->fetchAll(PDO::FETCH_ASSOC);

$totalTests = count($results);
$averageScore = $totalTests > 0 ? round(array_sum(array_column($results, 'score')) / $totalTests) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Личный кабинет</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<style>
body { color:#0b2f26; font-family:"Inter",sans-serif; background:#f8f9fa; }
.card-nf { background:white; border-radius:22px; padding:32px; border:1px solid #daebe3; transition:0.2s; margin-bottom:30px; }
.card-nf:hover { transform: translateY(-4px); box-shadow:0 14px 40px rgba(0,0,0,0.05); }
.btn-green { background-color:#0f5d4a; color:white; border-radius:16px; padding:10px 20px; transition:0.2s; }
.btn-green:hover { background-color:#187964; }
.btn-outline-green { border:2px solid #0f5d4a; color:#0f5d4a; border-radius:16px; padding:8px 16px; }
.profile-box img { width:150px; height:150px; border-radius:50%; border:4px solid #0f5d4a; object-fit:cover; }
.table thead th { background:#0f5d4a; color:white; }
</style>
</head>
<body class="text-green-900 font-sans">

<?php include "header.php"; ?>

<section class="container my-5">
    <div class="card-nf text-center profile-box">
        <img src="<?= htmlspecialchars($student['profile_picture'] ?? 'picture_library/default_avatar.png') ?>" 
             alt="Profile Photo" id="profilePhoto" class="mb-3">
        <h3 id="profileName"><?= htmlspecialchars($student['username']) ?></h3>
        <button class="btn btn-outline-green mt-3" data-bs-toggle="modal" data-bs-target="#editProfileModal">
            <i class="fas fa-edit"></i>Edit Profile
        </button>
    </div>
</section>
  
<div class="modal fade" id="editProfileModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" enctype="multipart/form-data">
        <input type="hidden" name="edit_profile" value="1">
        <div class="modal-header">
          <h5 class="modal-title">Edit Profile</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
            <div class="mb-3">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" class="form-control" value="<?= htmlspecialchars($student['username']) ?>" required>
            </div>
            <div class="mb-3">
                <label for="photo">Profile Photo</label>
                <input type="file" name="profile_picture" id="photo" class="form-control" accept="image/*">
            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-green">Save Changes</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include "footer.php"; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.getElementById('addResultForm').addEventListener('submit', function(e){
    e.preventDefault();
    const formData = new FormData(this);
    formData.append('ajax', 1);

    fetch('', { method:'POST', body:formData })
    .then(res => res.json())
.then(json => {
    if(json.success){
        var modalEl = document.getElementById('addResultModal');
        var modal = bootstrap.Modal.getInstance(modalEl);
        modal.hide();

        let table = document.querySelector('#resultsTable tbody');

        let noResultsRow = table.querySelector('tr td[colspan="3"]');
        if(noResultsRow){
            noResultsRow.parentElement.remove();
        }

        let newRow = table.insertRow(0);
        newRow.insertCell(0).innerText = json.test_name;
        newRow.insertCell(1).innerText = json.score + '%';
        newRow.insertCell(2).innerText = json.created_at;

        let totalTestsEl = document.getElementById('totalTests');
        let averageScoreEl = document.getElementById('averageScore');

        let currentTotal = parseInt(totalTestsEl.innerText);
        let currentAvg = averageScoreEl.innerText === '—' ? 0 : parseInt(averageScoreEl.innerText);
        let newTotal = currentTotal + 1;
        let newAvg = Math.round((currentAvg * currentTotal + json.score)/newTotal);

        totalTestsEl.innerText = newTotal;
        averageScoreEl.innerText = newAvg + '%';

        this.reset();
    } else {
        alert('Ошибка сохранения результата');
    }
})

    .catch(err => console.error(err));
});
</script>
</body>
</html>
