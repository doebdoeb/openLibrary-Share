<?php 
include_once '../utils/session.php'; 
include_once '../utils/database.php'; 
$page = 'explore.php'; 
$allFiles = [];
$searchMessage = '';
if (isset($_GET['tag']) && !empty($_GET['tag'])) {
    $allFiles = searchFilesByTag($_GET['tag']);
    if (empty($allFiles)) {
        $searchMessage = 'No files found for tag "' . htmlspecialchars($_GET['tag']) . '".';
    }
} else {
    $allFiles = loadAllFiles();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explore - Open Library Share</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .file:hover { transform: scale(1.05); transition: 0.2s; }
        .pdf-preview { height: 200px; border: none; }
    </style>
</head>
<body>
    <?php include_once '../components/navbar.php'; ?>

    <div class="container mt-4">
        <div class="header">
            <h1>Explore Files</h1>
            <p class="lead">Search by tags or browse all.</p>
        </div>
        
        <!-- Tag Search -->
        <form method="GET" class="mb-4">
            <div class="input-group">
                <input type="text" name="tag" class="form-control" placeholder="e.g., math, tutorial, pdf" 
                       value="<?php echo htmlspecialchars($_GET['tag'] ?? ''); ?>">
                <button type="submit" class="btn btn-primary">Search</button>
            </div>
        </form>
        
        <?php if ($searchMessage): ?>
            <div class="alert alert-info"><?php echo $searchMessage; ?></div>
        <?php endif; ?>
        
        <div class="files row">
            <?php if (empty($allFiles)): ?>
                <div class="col-12">
                    <p>No files yet. <a href="upload.php">Upload first!</a></p>
                </div>
            <?php else: ?>
                <?php foreach ($allFiles as $file): ?>
                    <div class="col-md-4 mb-4">
                        <div class="file card shadow-sm">
                            <iframe src="../uploads/<?php echo htmlspecialchars($file->filename); ?>" class="pdf-preview w-100 rounded-top"></iframe>
                            <div class="card-body">
                                <h5><?php echo htmlspecialchars($file->filename); ?></h5>
                                <p class="tags-display">
                                    <?php 
                                        $tags = loadTagsByFileWithId($file->id);
                                        if(!empty($tags)) {
                                            $tagValues = array_map(function($tag) { return htmlspecialchars($tag['value']); }, $tags);
                                            echo implode(", ", $tagValues);
                                        } else {
                                            echo "<span class='text-muted'>No tags</span>";
                                        }
                                    ?>
                                </p>
                                <a href="downloadFile.php?fileid=<?php echo $file->id; ?>" class="btn btn-primary">Details</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <?php if (isset($_GET['tag']) && !empty($_GET['tag'])): ?>
            <div class="text-center mt-4">
                <a href="explore.php" class="btn btn-secondary">Clear Search & Browse All</a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
