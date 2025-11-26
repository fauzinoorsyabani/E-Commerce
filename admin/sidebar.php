<aside class="main-sidebar">
    <section class="sidebar">

        <!-- Admin Info -->
        <div class="user-panel">
            <div class="pull-left image">
                <img src="img/user.png" class="img-circle" alt="User Image">
            </div>
            <div class="pull-left info">
                <p><?php echo $_SESSION['user']['full_name'] ?? 'Administrator'; ?></p>
                <a><i class="fa fa-circle text-success"></i> Online</a>
            </div>
        </div>

        <ul class="sidebar-menu">

            <li class="header">MAIN NAVIGATION</li>

            <li><a href="index.php"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a></li>

            <li><a href="settings.php"><i class="fa fa-cog"></i> <span>Settings</span></a></li>

            <li><a href="profile.php"><i class="fa fa-user"></i> <span>Profile</span></a></li>

            <li><a href="customer.php"><i class="fa fa-users"></i> <span>Customers</span></a></li>

            <li><a href="product.php"><i class="fa fa-shopping-bag"></i> <span>Products</span></a></li>

            <li><a href="orders.php"><i class="fa fa-shopping-cart"></i> <span>Orders</span></a></li>

            <!-- MENU SECURITY LOGS -->
            <li><a href="logs.php"><i class="fa fa-shield"></i> <span>Security Logs</span></a></li>

            <li><a href="logout.php"><i class="fa fa-sign-out"></i> <span>Logout</span></a></li>

        </ul>

    </section>
</aside>
