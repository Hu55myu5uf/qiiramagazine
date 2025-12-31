<?php
include 'db.php';
include 'includes/header.php';

// Access Control
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    header("Location: admin_login.php");
    exit();
}

$message = "";

// Handle Form Submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $editor_id = trim($_POST['editor_id']);
    $editor_name = trim($_POST['editor_name']);
    $editor_password = trim($_POST['editor_password']);
    $action = $_POST['action'];

    if ($action == "add") {
        if(!empty($editor_id) && !empty($editor_name) && !empty($editor_password)) {
             // Check if ID exists (using prepared statement to prevent SQL injection)
             $check_stmt = $conn->prepare("SELECT * FROM editors_table WHERE editor_id = ?");
             $check_stmt->bind_param("s", $editor_id);
             $check_stmt->execute();
             $check = $check_stmt->get_result();
             if($check->num_rows > 0) {
                 $message = "<div class='alert alert-danger'>Editor ID already exists.</div>";
             } else {
                 // Hash the password before storing
                 $hashed_password = password_hash($editor_password, PASSWORD_DEFAULT);
                 $stmt = $conn->prepare("INSERT INTO editors_table (editor_id, editor_name, editor_password) VALUES (?, ?, ?)");
                 $stmt->bind_param("sss", $editor_id, $editor_name, $hashed_password);
                 if($stmt->execute()) {
                     $message = "<div class='alert alert-success'>Editor Added Successfully.</div>";
                 } else {
                     $message = "<div class='alert alert-danger'>Error Adding Editor.</div>";
                 }
                 $stmt->close();
             }
             $check_stmt->close();
        } else {
            $message = "<div class='alert alert-warning'>All fields are required to add an editor.</div>";
        }
    } elseif ($action == "update") {
        if(!empty($editor_id)) {
             // Update logic - allow updating name only or both name and password
             if(!empty($editor_name)) {
                 if(!empty($editor_password)) {
                     // Update both name and password
                     $hashed_password = password_hash($editor_password, PASSWORD_DEFAULT);
                     $stmt = $conn->prepare("UPDATE editors_table SET editor_name = ?, editor_password = ? WHERE editor_id = ?");
                     $stmt->bind_param("sss", $editor_name, $hashed_password, $editor_id);
                 } else {
                     // Update name only
                     $stmt = $conn->prepare("UPDATE editors_table SET editor_name = ? WHERE editor_id = ?");
                     $stmt->bind_param("ss", $editor_name, $editor_id);
                 }
                 if($stmt->execute()) {
                     $message = "<div class='alert alert-info'>Editor Updated.</div>";
                 } else {
                     $message = "<div class='alert alert-danger'>Error updating editor.</div>";
                 }
                 $stmt->close();
             } else {
                 $message = "<div class='alert alert-warning'>Editor name is required for update.</div>";
             }
        }
    } elseif ($action == "delete") {
        if(!empty($editor_id)) {
            $stmt = $conn->prepare("DELETE FROM editors_table WHERE editor_id = ?");
            $stmt->bind_param("s", $editor_id);
            if($stmt->execute()) {
                $message = "<div class='alert alert-warning'>Editor Deleted.</div>";
            }
            $stmt->close();
        }
    }
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-5">
            <div class="card">
                <div class="card-body">
                    <center><h4>Editor Details</h4></center>
                    <center><img src="images/imgs/writer.png" width="100px" /></center>
                    <hr>
                    
                    <?php echo $message; ?>

                    <form method="POST">
                        <div class="row">
                            <div class="col-md-4">
                                <label>Editor ID</label>
                                <div class="form-group">
                                    <input type="text" name="editor_id" class="form-control" placeholder="ID" required>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <label>Editor Name</label>
                                <div class="form-group">
                                    <input type="text" name="editor_name" class="form-control" placeholder="Editor Name">
                                </div>
                            </div>
                            <div class="col-md-8">
                                <label>Editor Password</label>
                                <div class="form-group">
                                    <input type="text" name="editor_password" class="form-control" placeholder="Editor Password">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-4">
                                <button type="submit" name="action" value="add" class="btn btn-lg btn-block btn-success">Add</button>
                            </div>
                            <div class="col-4">
                                <button type="submit" name="action" value="update" class="btn btn-lg btn-block btn-warning">Update</button>
                            </div>
                            <div class="col-4">
                                <button type="submit" name="action" value="delete" class="btn btn-lg btn-block btn-danger">Delete</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <br>
            <a href="index.php"><< Back to Home</a><br><br>
        </div>

        <div class="col-md-7">
            <div class="card">
                <div class="card-body">
                    <center><h4>Editor List</h4></center>
                    <hr>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" id="datatable">
                            <thead>
                                <tr>
                                    <th>Editor ID</th>
                                    <th>Editor Name</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $res = $conn->query("SELECT * FROM editors_table");
                                while($row = $res->fetch_assoc()) {
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['editor_id']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['editor_name']) . "</td>";
                                    echo "<td><span class='badge badge-success'>Active</span></td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('table').DataTable();
    });
</script>

<?php include 'includes/footer.php'; ?>
