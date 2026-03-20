<?php 
if (!defined('ABSPATH')) exit;

// Initialize default analytics data
// Initialize default analytics data
$xenhire_analytics_data = array(
    'FacebookAnalyticsCode' => '',
    'GoogleAnalyticsCode' => '',
    'GoogleTagManager' => ''
);

// Fetch existing data from API
$xenhire_result = XenHire_API::call('Get_Vendor_Analytics', array());

if ($xenhire_result['success'] && !empty($xenhire_result['data'])) {
    $xenhire_data = json_decode($xenhire_result['data'], true);
    
    if (is_array($xenhire_data) && count($xenhire_data) > 0) {
        // Get first row of data
        $xenhire_row = $xenhire_data[0];
        
        // Safely extract values with fallback to empty string
        $xenhire_analytics_data['FacebookAnalyticsCode'] = isset($xenhire_row['FacebookAnalyticsCode']) ? $xenhire_row['FacebookAnalyticsCode'] : '';
        $xenhire_analytics_data['GoogleAnalyticsCode'] = isset($xenhire_row['GoogleAnalyticsCode']) ? $xenhire_row['GoogleAnalyticsCode'] : '';
        $xenhire_analytics_data['GoogleTagManager'] = isset($xenhire_row['GoogleTagManager']) ? $xenhire_row['GoogleTagManager'] : '';
    }
}
?>

<div class="wrap xenhire-stages-page xenhire-common">
    <!-- Header with Back Button -->
    <div class="xenhire-header">
        <div class="xenhire-header-left">
            <a href="<?php echo esc_url(admin_url('admin.php?page=xenhire-settings')); ?>" class="xenhire-back">
                <span class="dashicons dashicons-arrow-left-alt2"></span>
            </a>
            <h1>Analytics</h1>
        </div>
    </div>

    <!-- Analytics Form Card -->
    <div class="xenhire-container">
        <div class="xh-card xh-card-form">
            <form id="kt_analytics_form" class="analytics-form">
                
                <!-- Facebook Analytics Code -->
                <div class="analytics-field">
                    <label class="analytics-label">Facebook Analytics Code</label>
                    <input 
                        type="text" 
                        name="FacebookAnalyticsCode" 
                        id="FacebookAnalyticsCode" 
                        class="analytics-input" 
                        placeholder="Facebook Analytics Code" 
                        value="<?php echo esc_attr($xenhire_analytics_data['FacebookAnalyticsCode']); ?>"
                    />
                </div>

                <!-- Google Analytics Code -->
                <div class="analytics-field">
                    <label class="analytics-label">Google Analytics Code</label>
                    <input 
                        type="text" 
                        name="GoogleAnalyticsCode" 
                        id="GoogleAnalyticsCode" 
                        class="analytics-input" 
                        placeholder="Google Analytics Code" 
                        value="<?php echo esc_attr($xenhire_analytics_data['GoogleAnalyticsCode']); ?>"
                    />
                </div>

                <!-- Google Tag Manager -->
                <div class="analytics-field">
                    <label class="analytics-label">Google Tag Manager</label>
                    <input 
                        type="text" 
                        name="GoogleTagManager" 
                        id="GoogleTagManager" 
                        class="analytics-input" 
                        placeholder="Google Tag Manager" 
                        value="<?php echo esc_attr($xenhire_analytics_data['GoogleTagManager']); ?>"
                    />
                </div>

                <!-- Save Button -->
                <div class="analytics-actions">
                    <button type="submit" id="kt_analytics_submit" class="analytics-save-btn" data-kt-indicator="off">
                        <span class="indicator-label">Save</span>
                        <span class="indicator-progress" style="display: none;">
                            Please wait...<span class="spinner is-active"></span>
                        </span>
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>


