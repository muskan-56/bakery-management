<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'DBConnection.php';

$page = $_GET['page'] ?? 'home';

// Restrict certain pages for non-admin users
$restricted_pages = ['maintenance', 'products', 'stocks'];
if ($_SESSION['type'] != 1 && in_array($page, $restricted_pages)) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= ucwords(str_replace('_', ' ', $page)) ?> | Bakery Shop Management System</title>
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="Font-Awesome-master/css/all.min.css">
    <link rel="stylesheet" href="select2/css/select2.min.css">
    <link rel="stylesheet" href="DataTables/datatables.min.css">

    <!-- JavaScript Libraries -->
    <script src="js/jquery-3.6.0.min.js"></script>
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="DataTables/datatables.min.js"></script>
    <script src="select2/js/select2.full.min.js"></script>
    <script src="Font-Awesome-master/js/all.min.js"></script>
    <script src="js/script.js"></script>

    <style>
        :root {
            --bs-success-rgb: 71, 222, 152 !important;
        }
        body {
            background: url('images/wallpaper.jfif') center/cover no-repeat;
            backdrop-filter: brightness(0.7);
            height: 100%;
            width: 100%;
        }
        main {
            display: flex;
            flex-direction: column;
            height: 100%;
        }
        #page-container {
            flex-grow: 1;
            overflow: auto;
        }
        .thumbnail-img, .display-select-image {
            width: 50px;
            height: 50px;
            margin: 2px;
        }
        .truncate-1 {
            display: -webkit-box;
            -webkit-line-clamp: 1;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .truncate-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .modal-dialog.large {
            width: 80% !important;
        }
        .modal-dialog.mid-large {
            width: 50% !important;
        }
        @media (max-width: 720px) {
            .modal-dialog.large,
            .modal-dialog.mid-large {
                width: 100% !important;
            }
        }
        ::-webkit-scrollbar {
            width: 5px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        ::-webkit-scrollbar-thumb {
            background: #888;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
    </style>
</head>
<body>
    <main>
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark bg-gradient">
            <div class="container">
                <a class="navbar-brand" href="index.php">Bakery Shop Management System</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav">
                        <li class="nav-item"><a class="nav-link <?= ($page == 'home') ? 'active' : '' ?>" href="index.php">Home</a></li>
                        <?php if ($_SESSION['type'] == 1): ?>
                            <li class="nav-item"><a class="nav-link <?= ($page == 'products') ? 'active' : '' ?>" href="?page=products">Products</a></li>
                            <li class="nav-item"><a class="nav-link <?= ($page == 'stocks') ? 'active' : '' ?>" href="?page=stocks">Stocks</a></li>
                        <?php endif; ?>
                        <li class="nav-item"><a class="nav-link <?= ($page == 'sales') ? 'active' : '' ?>" href="?page=sales">POS</a></li>
                        <li class="nav-item"><a class="nav-link <?= ($page == 'sales_report') ? 'active' : '' ?>" href="?page=sales_report">Sales</a></li>
                        <?php if ($_SESSION['type'] == 1): ?>
                            <li class="nav-item"><a class="nav-link <?= ($page == 'users') ? 'active' : '' ?>" href="?page=users">Users</a></li>
                            <li class="nav-item"><a class="nav-link" href="?page=maintenance">Maintenance</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
                <div class="dropdown">
                    <button class="btn btn-secondary dropdown-toggle bg-transparent text-light border-0" data-bs-toggle="dropdown">
                        Hello, <?= htmlspecialchars($_SESSION['fullname']) ?>
                    </button>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="?page=manage_account">Manage Account</a></li>
                        <li><a class="dropdown-item" href="Actions.php?a=logout">Logout</a></li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <div class="container py-3" id="page-container">
            <?php if (!empty($_SESSION['flashdata'])): ?>
                <div class="alert alert-<?= $_SESSION['flashdata']['type'] ?> rounded-0 shadow">
                    <div class="float-end">
                        <a href="javascript:void(0)" class="text-dark text-decoration-none" onclick="$(this).closest('.alert').hide('slow').remove()">x</a>
                    </div>
                    <?= $_SESSION['flashdata']['msg'] ?>
                </div>
                <?php unset($_SESSION['flashdata']); ?>
            <?php endif; ?>
            <?php include "$page.php"; ?>
        </div>
    </main>

    <!-- Modals -->
    <div class="modal fade" id="uni_modal" data-bs-backdrop="static">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"></h5>
                </div>
                <div class="modal-body"></div>
                <div class="modal-footer">
                    <button class="btn btn-primary btn-sm" id="submit" onclick="$('#uni_modal form').submit()">Save</button>
                    <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="confirm_modal">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Confirmation</h5>
                </div>
                <div class="modal-body">
                    <div id="delete_content"></div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary btn-sm" id="confirm">Continue</button>
                    <button class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
