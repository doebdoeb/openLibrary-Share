<?php 
include_once '../utils/session.php'; 
$page = 'home.php'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Open Library Share - Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include_once '../components/navbar.php'; ?>
    
    <div class="container mt-5">
        <div class="jumbotron text-center bg-light p-5 rounded">
            <h1 class="display-4">Welcome to Open Library Share</h1>
            <p class="lead">A platform to upload, share, and explore PDF documents with tags for easy discovery.[file:13]</p>
            <?php if (isset($_SESSION['name'])): ?>
                <p>Hello, <?php echo htmlspecialchars($_SESSION['name']); ?>! Ready to explore files?</p>
                <a href="explore.php" class="btn btn-primary btn-lg">Explore Files</a>
            <?php else: ?>
                <a href="login.php" class="btn btn-primary btn-lg me-3">Login</a>
                <a href="registration.php" class="btn btn-secondary btn-lg">Sign Up</a>
            <?php endif; ?>
        </div>
        
        <div class="row mt-5">
            <div class="col-md-4">
                <h3>Upload PDFs</h3>
                <p>Share your documents securely with others.</p>
                <?php if (isset($_SESSION['email'])): ?>
                    <a href="upload.php" class="btn btn-success">Upload Now</a>
                <?php endif; ?>
            </div>
            <div class="col-md-4">
                <h3>Explore Library</h3>
                <p>Browse and discover shared files by tags and users.[file:13]</p>
                <a href="explore.php" class="btn btn-info">Browse</a>
            </div>
            <div class="col-md-4">
                <h3>Manage Your Files</h3>
                <p>View, tag, and organize your uploads.[file:9]</p>
                <?php if (isset($_SESSION['email'])): ?>
                    <a href="fileView.php" class="btn btn-warning">Manage Files</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
