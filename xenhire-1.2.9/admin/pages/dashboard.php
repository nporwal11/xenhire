<?php 
if (!defined('ABSPATH')) exit;

// Fetch dashboard data - Data fetching moved to localized script in class-xenhire-admin.php, keeping here if needed for PHP-side rendering, but assets are removed.
// $xenhire_dashboard_data = XenHire_API::call('Get_Dashboard', array()); 
// Actually, keep data fetching if it's used in HTML below, but enqueueing is removed.
$xenhire_dashboard_data = XenHire_API::call('Get_Dashboard', array());

// Assets are enqueued in class-xenhire-admin.php

?>

<div class="xenhire-dashboard-v2 xenhire-common">
    <div class="xh-d-header xenhire-header">
        <h1>Dashboard</h1>
        <div class="xenhire-user-info">
            <span>Welcome, <strong><?php echo esc_html(XenHire_Auth::get_logged_email()); ?></strong></span>
            <a href="<?php echo esc_url(site_url('/jobs/')); ?>" target="_blank" class="xh-btn xh-job-preview">Preview Jobs</a>
            <button id="xenhire-logout-btn" class="xh-btn xh-logout">Logout</button>
        </div>
    </div>
    <div class="xenhire-container">
        <!-- Top Grid: Cards & Chart -->
        <div class="xh-d-grid">
            
            <!-- Card 1: Pink (Applications/Candidates) -->
            <div class="xh-d-card pink">
                <div>
                    <div class="xh-d-icon-circle">
                        <!-- <span class="dashicons dashicons-admin-users"></span> -->
                         <i class="ki-outline ki-user"></i>
                    </div>
                    <div class="xh-d-main-stat">
                        <h2 id="xh-stat-applications">-</h2>
                        <p>Applications</p>
                    </div>
                </div>
                <div class="xh-d-sub-stat">
                    <h3 id="xh-stat-candidates">-</h3>
                    <p>Candidates</p>
                </div>
            </div>

            <!-- Card 2: Purple (Jobs/Employers) -->
            <div class="xh-d-card purple">
                <div>
                    <div class="xh-d-icon-circle">
                        <!-- <span class="dashicons dashicons-portfolio"></span> -->
                         <i class="ki-outline ki-briefcase"></i>
                    </div>
                    <div class="xh-d-main-stat">
                        <h2 id="xh-stat-jobs">-</h2>
                        <p>Jobs</p>
                    </div>
                </div>
                <div class="xh-d-sub-stat">
                    <h3 id="xh-stat-employers">-</h3>
                    <p>Employers</p>
                </div>
            </div>

            <!-- Card 3: Chart -->
            <div class="xh-d-card chart-card">
                <div class="xh-d-chart-title">Job Applications</div>
                <div style="flex-grow: 1; position: relative;">
                    <canvas id="xh-applications-chart"></canvas>
                </div>
            </div>

        </div>

        <!-- Bottom Grid: Visits -->
        <div class="xh-d-grid-bottom">
            
            <!-- Visits by Device -->
            <div class="xh-d-card list-card">
                <div class="xh-d-list-title">Visits by Device Type</div>
                <div id="xh-list-devices">
                    <!-- Populated by JS -->
                </div>
            </div>

            <!-- Visits by City -->
            <div class="xh-d-card list-card">
                <div class="xh-d-list-title">Visits by City</div>
                <div id="xh-list-cities">
                    <!-- Populated by JS -->
                </div>
            </div>

            <!-- Visits by OS -->
            <div class="xh-d-card list-card">
                <div class="xh-d-list-title">Visits by Operating System</div>
                <div id="xh-list-os">
                    <!-- Populated by JS -->
                </div>
            </div>

        </div>
    </div>
</div>
