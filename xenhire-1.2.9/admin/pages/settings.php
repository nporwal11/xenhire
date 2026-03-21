<?php 
if (!defined('ABSPATH')) exit;

// Fetch settings data from API
// Fetch settings data from API
$xenhire_settings_data = XenHire_API::call('List_Masters', array());

$xenhire_stages_count = 0;
$xenhire_email_templates_count = 0;

if ($xenhire_settings_data['success']) {
    // Decode the JSON string
    $xenhire_data = json_decode($xenhire_settings_data['data'], true);
    
    // Loop through the array structure: [0] => Array of items
    if (isset($xenhire_data[0]) && is_array($xenhire_data[0])) {
        foreach ($xenhire_data[0] as $xenhire_master) {
            if (isset($xenhire_master['Name']) && isset($xenhire_master['Count'])) {
                $xenhire_name = $xenhire_master['Name'];
                $xenhire_count = $xenhire_master['Count'];
                
                // Match Stages (with or without 's')
                if ($xenhire_name === 'Stages' || $xenhire_name === 'Stage') {
                    $xenhire_stages_count = intval($xenhire_count);
                }
                
                // Match EmailTemplates (with or without 's')
                elseif ($xenhire_name === 'EmailTemplates' || $xenhire_name === 'EmailTemplate') {
                    $xenhire_email_templates_count = intval($xenhire_count);
                }
            }
        }
    }
}
?>

<div class="wrap xenhire-settings xenhire-common">
    <div class="xenhire-header">
        <h1>Settings</h1>
        <div class="xenhire-user-info">
            <span>Welcome, <strong><?php echo esc_html(XenHire_Auth::get_logged_email()); ?></strong></span>
            <button id="xenhire-refresh-settings" class="button">
                <span class="dashicons dashicons-update"></span> Refresh
            </button>
            <a href="<?php echo esc_url(site_url('/jobs/')); ?>" target="_blank" class="xh-btn xh-job-preview">Preview Jobs</a>
            <button id="xenhire-logout-btn" class="xh-btn xh-logout">Logout</button>
        </div>
    </div>
    
    <?php if ($xenhire_settings_data['success']): ?>
    
    <div class="xenhire-container">
        <div class="xenhire-settings-grid">
            <!-- Stages Card - Clickable -->
            <a href="<?php echo esc_url(admin_url('admin.php?page=xenhire-stages')); ?>" class="xenhire-setting-card xenhire-card-link xh-card">
                <div class="xenhire-setting-card-content">
                    <div class="setting-icon" style="background: #dfffea;">
                        <i class="ki-outline ki-medal-star ki-fs-1"></i>
                    </div>
                    <p>Stages</p>
                </div>
                <div class="xenhire-setting-card-count">
                    <h3><?php echo esc_html($xenhire_stages_count); ?></h3>
                </div>
            </a>
            
            <!-- Email Templates Card - Clickable (Coming Soon) -->
            <a href="<?php echo esc_url(admin_url('admin.php?page=xenhire-email-templates')); ?>" class="xenhire-setting-card xh-card">
                <div class="xenhire-setting-card-content">
                    <div class="setting-icon" style="background: #e9f3ff;">
                        <i class="ki-duotone ki-directbox-default ki-fs-1">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                            <span class="path4"></span>
                        </i>                        
                    </div>
                    <p>Email Templates</p>
                </div>
                <div class="xenhire-setting-card-count">
                    <h3><?php echo esc_html($xenhire_email_templates_count); ?></h3>                    
                </div>
            </a>        
            
            <!-- Analytics Card - Clickable (Coming Soon) -->
            <!-- <div class="xenhire-setting-card xenhire-card-disabled" data-action="analytics"> -->
            <a href="<?php echo esc_url(admin_url('admin.php?page=xenhire-analytics')); ?>" class="xenhire-setting-card xh-card">
                <div class="xenhire-setting-card-content">
                    <div class="setting-icon" style="background: #f8f5ff;">
                        <i class="ki-duotone ki-code ki-fs-1">
                            <span class="path1"></span>
                            <span class="path2"></span>
                            <span class="path3"></span>
                            <span class="path4"></span>
                        </i>
                    </div>
                    <p>Analytics</p>                    
                </div>                
                <div class="xenhire-setting-card-count">
                    <h3 class="xh-analytics">Facebook Pixel and Google Analytics</h3>
                </div>
            </a>   
            <!-- </div> -->
        </div>
    </div>
    
    <!-- Quick Actions (Optional - can be removed if not needed) -->
    <div class="xenhire-info-box" style="margin-top: 30px;">
        <h2>Quick Links</h2>
        <ul>
            <li><a href="<?php echo esc_url(admin_url('admin.php?page=xenhire-stages')); ?>">Manage Stages</a></li>
            <li><span style="color: #999;">Manage Email Templates (Coming Soon)</span></li>
            <li><span style="color: #999;">Configure Analytics (Coming Soon)</span></li>
        </ul>
    </div>
    
    <?php else: ?>
        <div class="notice notice-error">
            <p><strong>Error:</strong> <?php echo esc_html($xenhire_settings_data['message']); ?></p>
            <p>Unable to fetch settings data. Please try again.</p>
            <button id="xenhire-retry-settings" class="button button-primary">Retry</button>
        </div>
    <?php endif; ?>
</div>


