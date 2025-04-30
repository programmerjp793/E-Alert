<?php
include 'db_connection.php';

// Fetch About Us content
$aboutUsQuery = "SELECT * FROM content WHERE content_id = 'about_us'";
$aboutUsResult = $conn->query($aboutUsQuery);
$aboutUs = $aboutUsResult->fetch_assoc();

// Fetch Carousel content
$carouselQuery = "SELECT * FROM content WHERE content_id = 'carousel'";
$carouselResult = $conn->query($carouselQuery);
$carousel = $carouselResult->fetch_assoc();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Content</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .tab-content {
            padding: 20px;
            border: 1px solid #ddd;
            border-top: none;
            border-radius: 0 0 5px 5px;
        }
        .preview-image {
            max-width: 100px;
            max-height: 100px;
            margin: 5px;
        }
    </style>
</head>
<body class="container mt-4">
    <h2>Edit Website Content</h2>
    
    <ul class="nav nav-tabs" id="contentTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="about-tab" data-bs-toggle="tab" data-bs-target="#about" type="button" role="tab">About Us</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="carousel-tab" data-bs-toggle="tab" data-bs-target="#carousel" type="button" role="tab">Carousel Images</button>
        </li>
    </ul>
    
    <div class="tab-content" id="contentTabsContent">
        <!-- About Us Tab -->
        <div class="tab-pane fade show active" id="about" role="tabpanel">
            <form action="process_content.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update_about">
                <input type="hidden" name="content_id" value="about_us">
                
                <div class="mb-3">
                    <label class="form-label">Title:</label>
                    <input type="text" name="title" class="form-control" value="<?php echo htmlspecialchars($aboutUs['title'] ?? ''); ?>" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Content:</label>
                    <textarea name="body" class="form-control" rows="10" required><?php echo htmlspecialchars($aboutUs['body'] ?? ''); ?></textarea>
                </div>
                
                <button type="submit" class="btn btn-primary">Update About Us</button>
            </form>
        </div>
        
        <!-- Carousel Tab -->
        <div class="tab-pane fade" id="carousel" role="tabpanel">
            <form action="process_content.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update_carousel">
                <input type="hidden" name="content_id" value="carousel">
                
                <div class="row">
                    <?php for ($i = 1; $i <= 7; $i++): ?>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Image <?php echo $i; ?>:</label>
                            <?php if (!empty($carousel["image_$i"])): ?>
                                <div>
                                    <img src="<?php echo htmlspecialchars($carousel["image_$i"]); ?>" class="preview-image">
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" name="delete_image_<?php echo $i; ?>" id="delete_image_<?php echo $i; ?>">
                                        <label class="form-check-label" for="delete_image_<?php echo $i; ?>">
                                            Delete this image
                                        </label>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <input type="file" name="image_<?php echo $i; ?>" class="form-control mt-2">
                        </div>
                    <?php endfor; ?>
                </div>
                
                <button type="submit" class="btn btn-primary">Update Carousel</button>
            </form>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>