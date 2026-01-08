<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .content-wrapper {
            margin-left: 220px;
            padding-top: 80px;
        }

        .card {
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            background-color: #ffffff;
            color: #343a40;
            transition: transform 0.3s;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 300px; 
        }

        .card:hover {
            transform: scale(1.0);
        }

        .card-body {
            text-align: center;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100%;
        }

        .icon-large {
            font-size: 4rem;
            color: #343a40;
            margin-bottom: 20px;
        }

        
        .dashboard-header h1 {
            color: #343a40;
        }

        .dashboard-header p {
            color: #6c757d;
        }
    </style>
</head>
<body>

    <?php include './includes/navbar.php'; ?>
    <?php include './includes/sidebar.php'; ?>

    <div class="content-wrapper">
        <div class="container-fluid">
            <div class="dashboard-header text-center">
                <h1>Welcome to Your Dashboard!</h1>
                <p class="text-muted">Navigate through different sections using the sidebar.</p>
            </div>

            <div class="row">
                <div class="col-md-6 mb-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-user-circle icon-large"></i> 
                            <h5 class="card-title">Profile Information</h5>
                            <p class="card-text">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce vel eros eget dui mollis vehicula.</p> 
                            <p class="card-text">Nullam scelerisque turpis vel diam volutpat, eu fringilla sapien condimentum.</p> 
                            <a href="profile_card.php" class="btn btn-primary">Go to Profile</a>
                        </div>
                    </div>
                </div>

                <div class="col-md-6 mb-4">
                    <div class="card text-center">
                        <div class="card-body">
                            <i class="fas fa-comments icon-large"></i> 
                            <h5 class="card-title">Feedback</h5>
                            <p class="card-text">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer nec odio. Praesent libero. Sed cursus ante dapibus diam.</p> 
                            <p class="card-text">Curabitur ullamcorper ultricies nisi. Nam eget dui. Etiam rhoncus. Maecenas tempus, tellus eget condimentum rhoncus.</p> 
                            <a href="feedback.php" class="btn btn-primary">Give Feedback</a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

   

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
