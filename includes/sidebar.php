<div class="sidebar">
    <a href="dashboard.php">Home</a>
    <a href="profile_card.php">Profile</a>
    <a href="feedback.php">Feedback</a>
    <a href="logout.php">Logout</a>
</div>

<style>
    .sidebar {
        height: 100vh;
        width: 220px;
        position: fixed;
        top: 0;
        left: 0;
        background-color: #2c3e50; 
        padding-top: 60px;
        box-shadow: 2px 0 5px rgba(0, 0, 0, 0.2); 
        transition: all 0.3s ease; 
    }

    .sidebar a {
        display: block;
        color: #ecf0f1; 
        padding: 15px;
        font-size: 16px;
        text-decoration: none;
        transition: all 0.3s ease; 
    }

    .sidebar a:hover {
        background-color: #3498db; 
        color: #fff;
        padding-left: 25px; 
    }

    
    .sidebar a:first-child {
        padding-top: 30px;
    }
</style>
