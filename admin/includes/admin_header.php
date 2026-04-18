<?php

// Shared header for all the admin pages


require_once dirname(__DIR__, 2) . '/config/config.php';
require_once dirname(__DIR__, 2) . '/includes/db.php';
require_once dirname(__DIR__, 2) . '/includes/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Protect all admin pages
requireAdmin();

$admin_name = $_SESSION['admin_name'];

// Sidebar badge counts
$pending_bookings = $conn->query("SELECT COUNT(*) as total FROM bookings WHERE status = 'pending'")->fetch_assoc()['total'];
$unread_messages = $conn->query("SELECT COUNT(*) as total FROM contact_messages WHERE is_read = 0")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? $page_title . ' | Admin' : 'Admin Panel' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary:     #8B6914;
            --primary-dark:#6B5010;
            --dark:        #1a1a2e;
            --sidebar-w:   260px;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: #f4f6f9;
        }
        /*Sidebar*/
        .sidebar {
            width: var(--sidebar-w);
            min-height: 100vh;
            background: var(--dark);
            position: fixed;
            top: 0; left: 0;
            z-index: 100;
            transition: transform 0.3s;
        }
        .sidebar-brand {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .sidebar-brand h5 {
            font-family: 'Playfair Display', serif;
            color: var(--primary);
            margin: 0;
            font-size: 1.1rem;
        }
        .sidebar-brand small { color: #aaa; }
        .sidebar-nav { padding: 1rem 0; }
        .nav-section-title {
            color: #666;
            font-size: 0.7rem;
            font-weight: 600;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            padding: 0.75rem 1.5rem 0.25rem;
        }
        .sidebar .nav-link {
            color: #ccc !important;
            padding: 0.6rem 1.5rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
            border-radius: 0;
            transition: all 0.2s;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: #fff !important;
            background: rgba(139,105,20,0.2);
            border-left: 3px solid var(--primary);
        }
        .sidebar .nav-link i { font-size: 1rem; width: 20px; }
        .badge-count {
            margin-left: auto;
            background: var(--primary);
            color: #fff;
            font-size: 0.7rem;
            padding: 2px 7px;
            border-radius: 10px;
        }
        /*Top Navbar*/
        .admin-topbar {
            margin-left: var(--sidebar-w);
            background: #fff;
            padding: 0.75rem 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 99;
        }
        /* Main Content */
        .admin-content {
            margin-left: var(--sidebar-w);
            padding: 2rem;
            min-height: calc(100vh - 60px);
        }
        /*Cards */
        .stat-card {
            background: #fff;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 15px rgba(0,0,0,0.06);
            border-left: 4px solid var(--primary);
            transition: transform 0.2s;
        }
        .stat-card:hover { transform: translateY(-3px); }
        .stat-card .stat-icon {
            width: 50px; height: 50px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }
        .admin-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.06);
            border: none;
        }
        .admin-card .card-header {
            background: #fff;
            border-bottom: 1px solid #f0f0f0;
            padding: 1rem 1.5rem;
            font-weight: 600;
            border-radius: 10px 10px 0 0 !important;
        }
        /*Buttons*/
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        .btn-primary:hover {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }
        .btn-outline-primary {
            color: var(--primary);
            border-color: var(--primary);
        }
        .btn-outline-primary:hover {
            background-color: var(--primary);
            border-color: var(--primary);
        }
        /* Table*/
        .admin-table th {
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #888;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 600;
            white-space: nowrap;
            display: inline-block;
        }
        .status-pending   { background: #fff3cd; color: #856404; }
        .status-confirmed { background: #d1e7dd; color: #0f5132; }
        .status-cancelled { background: #f8d7da; color: #842029; }
        /* --- Responsive ------------------------------------ */
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .admin-topbar, .admin-content { margin-left: 0; }
        }

        <?php
        $dark_mode        = getSetting('dark_mode')        ?: '0';
        $theme_color      = getSetting('theme_color')      ?: '#8B6914';
        $theme_color_dark = getSetting('theme_color_dark') ?: '#6B5010';
        ?>

        /* Dynamic theme colours from database */
        :root {
            --primary:     <?= $theme_color ?>;
            --primary-dark:<?= $theme_color_dark ?>;
        }

        <?php if ($dark_mode === '1'): ?>
        body                         { background: #0f0f1a !important; color: #e0e0e0; }
        .admin-content               { background: #0f0f1a; }
        .admin-card, .stat-card      { background: #1e1e30 !important; color: #e0e0e0; }
        .admin-topbar                { background: #1a1a2e !important; color: #e0e0e0; }
        .card-header                 { background: #1e1e30 !important; color: #ffffff !important; border-color: #333 !important; }
        .table                       { color: #e0e0e0; }
        .form-control, .form-select  { background: #2a2a40; color: #e0e0e0; border-color: #444; }
        .dropdown-menu               { background: #1e1e30; border-color: #444; }
        .dropdown-item               { color: #ccc; }
        .dropdown-item:hover         { background: #2a2a40; }
        .list-group-item             { background: #1e1e30; color: #e0e0e0; border-color: #333; }
        .modal-content               { background: #1e1e30; color: #e0e0e0; }
        .modal-header, .modal-footer { border-color: #333; }
        h1,h2,h3,h4,h5,h6           { color: #ffffff !important; }
        .text-muted                  { color: #aaaaaa !important; }
        .fw-semibold                 { color: #dddddd; }
        .nav-section-title           { color: #888 !important; }
        .admin-table th              { color: #aaa !important; }
        <?php endif; ?>

    </style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">
    <div class="sidebar-brand d-flex justify-content-between align-items-center">
    <div>
        <h5><i class="bi bi-house-heart me-2"></i><?= SITE_NAME ?></h5>
        <small>Management Panel</small>
    </div>
    <!-- X button only visible on mobile -->
    <button class="btn btn-sm d-md-none" id="sidebarClose"
            style="color:#aaa; background:none; border:none; font-size:1.3rem;">
        <i class="bi bi-x-lg"></i>
    </button>
</div>
    <nav class="sidebar-nav">
        <div class="nav-section-title">Main</div>
        <a href="<?= SITE_URL ?>/admin/index.php"
           class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        <div class="nav-section-title">Bookings</div>
        <a href="<?= SITE_URL ?>/admin/bookings.php"
           class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'bookings.php' ? 'active' : '' ?>">
            <i class="bi bi-calendar-check"></i> Bookings
            <?php if ($pending_bookings > 0): ?>
            <span class="badge-count"><?= $pending_bookings ?></span>
            <?php endif; ?>
        </a>

        <div class="nav-section-title">Content</div>
        <a href="<?= SITE_URL ?>/admin/rooms.php"
           class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'rooms.php' ? 'active' : '' ?>">
            <i class="bi bi-door-open"></i> Rooms
        </a>
        <a href="<?= SITE_URL ?>/admin/gallery.php"
           class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'gallery.php' ? 'active' : '' ?>">
            <i class="bi bi-images"></i> Gallery
        </a>
        <a href="<?= SITE_URL ?>/admin/theme.php"
            class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'theme.php' ? 'active': ''?>">
            <i class="bi bi-palette"></i>Theme
        </a>

        <div class="nav-section-title">Communications</div>
        <a href="<?= SITE_URL ?>/admin/messages.php"
           class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'messages.php' ? 'active' : '' ?>">
            <i class="bi bi-envelope"></i> Messages
            <?php if ($unread_messages > 0): ?>
            <span class="badge-count"><?= $unread_messages ?></span>
            <?php endif; ?>
        </a>

        <div class="nav-section-title">System</div>
        <a href="<?= SITE_URL ?>/admin/settings.php"
           class="nav-link <?= basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : '' ?>">
            <i class="bi bi-gear"></i> Settings
        </a>
        <a href="<?= SITE_URL ?>/admin/logout.php" class="nav-link">
            <i class="bi bi-box-arrow-left"></i> Logout
        </a>
    </nav>
</div>

<div class="admin-topbar">
    <div class="d-flex align-items-center gap-3">
        <button class="btn btn-sm btn-outline-secondary d-md-none" id="sidebarToggle">
            <i class="bi bi-list"></i>
        </button>
        <h6 class="mb-0 fw-semibold"><?= isset($page_title) ? $page_title: 'Dashboard' ?></h6>
    </div>

    <div class="d-flex align-items-center gap-3">
        <a href="<?= SITE_URL ?>/public/index.php" target="_blank"
        class="btn btn-sm btn-outline-primary">
        <i class="bi bi-eye me-1"></i>
        View Site
        </a>
        <div class="dropdown">
            <button class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle me-1"></i><?= $admin_name ?>
            </button>

            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item" href="<?= SITE_URL ?>/admin/settings.php">
                        <i class="bi bi-gear me-2"></i>
                        Settings
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item text-danger" href="<?= SITE_URL ?>/admin/logout.php">
                        <i class="bi bi-box-arrow-left me-2"></i>Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>

<!-- Main content starts here -->
<div class="admin-content">