<?php
    include_once "../utils/session.php";
    include_once "../utils/database.php";
    
    $selectedFile = null;
    $fileId = null;
    $tags = [];
    $uploadedByUsername = null;
    
    // Get file ID from URL parameter
    if (isset($_GET['fileid'])) {
        $fileId = intval($_GET['fileid']);
        $allFiles = loadAllFiles();
        
        // Find the file with matching ID
        foreach ($allFiles as $f) {
            if ($f->id === $fileId) {
                $selectedFile = $f;
                $tags = loadTagsByFileWithId($fileId);
                
                // Get the username of the uploader
                $allUsers = loadAllUsers();
                foreach ($allUsers as $user) {
                    if ($user->id === $selectedFile->fk_uid) {
                        $uploadedByUsername = $user->username;
                        break;
                    }
                }
                
                break;
            }
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/downloadFile.css">
    <title>File Details</title>
</head>
<body>
<?php 
    include_once "../components/navbar.php";
?>    
<div class="container-fluid">
    <h1>File Details</h1>
    
    <?php if ($selectedFile): ?>
        <div class="file-details-container">
            <div class="details-header">
                <div class="header-info">
                    <h2><?php echo htmlspecialchars($selectedFile->filename); ?></h2>
                    <p class="file-id">File ID: <?php echo $selectedFile->id; ?></p>
                </div>
            </div>

            <!-- PDF Viewer -->
            <div class="pdf-viewer-container">
                <iframe src="<?php echo '../uploads/' . htmlspecialchars($selectedFile->filename); ?>" 
                        width="100%" 
                        height="600px" 
                        style='border: 1px solid #ddd; border-radius: 8px;' 
                        title='PDF Viewer'></iframe>
            </div>

            <!-- File Information -->
            <div class="file-info-section">
                <div class="info-card">
                    <h3>File Information</h3>
                    <div class="info-item">
                        <label>Filename:</label>
                        <p><?php echo htmlspecialchars($selectedFile->filename); ?></p>
                    </div>
                    <div class="info-item">
                        <label>Upload ID:</label>
                        <p><?php echo $selectedFile->id; ?></p>
                    </div>
                    <div class="info-item">
                        <label>Uploaded by:</label>
                        <p><?php echo htmlspecialchars($uploadedByUsername ?? 'Unknown'); ?></p>
                    </div>
                </div>

                <!-- Tags Section -->
                <div class="info-card tags-section">
                    <h3>Tags</h3>
                    <div class="tags-list">
                        <?php 
                            if (empty($tags)) {
                                echo "<p class='no-tags'>No tags for this file</p>";
                            } else {
                                $tagValues = array_map(function($tag) { return htmlspecialchars($tag['value']); }, $tags);
                                echo implode(", ", $tagValues);
                            }
                        ?>
                    </div>
                </div>
            </div>

            <!-- Back Button -->
            <div class="back-button-section">
                <a href="explore.php" class="btn btn-secondary">Back to Explore</a>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-warning">
            <h4>File Not Found</h4>
            <p>The file you're looking for could not be found.</p>
            <a href="explore.php" class="btn btn-secondary">Back to Explore</a>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcVqwpEV7tZrpomMgA" crossorigin="anonymous"></script>
</body>
</html>
