<!DOCTYPE html>
<html>
<head>
    <title>Add Content</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="container mt-4">
    <h2>Add New Content</h2>
    <form action="process_content.php" method="POST">
        <input type="hidden" name="action" value="add">
        <div class="mb-3">
            <label class="form-label">Title:</label>
            <input type="text" name="title" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Body:</label>
            <textarea name="body" class="form-control" rows="5" required></textarea>
        </div>
        <button type="submit" class="btn btn-success">Add Content</button>
    </form>
</body>
</html>