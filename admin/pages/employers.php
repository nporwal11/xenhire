if (!current_user_can('manage_options')) { wp_die('Unauthorized'); }
<?php if (!defined('ABSPATH')) exit; 
// Check if any employer exists to limit to single employer
$xenhire_employer_check = XenHire_API::call('List_Employer', array(
    array('Key' => 'IsActive', 'Value' => -1),
    // array('Key' => 'Search', 'Value' => ''),
    array('Key' => 'PageNo', 'Value' => 1),
    array('Key' => 'PageSize', 'Value' => 1)
));
$xenhire_has_employers = false;
if ($xenhire_employer_check['success'] && !empty($xenhire_employer_check['data'])) {
    $xenhire_emp_data = json_decode($xenhire_employer_check['data'], true);
    // Check TotalRecordCount in metadata (usually second element of response array)
    if (isset($xenhire_emp_data[1][0]['TotalRecordCount']) && $xenhire_emp_data[1][0]['TotalRecordCount'] > 0) {
        $xenhire_has_employers = true;
    }
}
?>

<div class="wrap xenhire-common">
    <div class="xenhire-header">
        <h1>Employers</h1>
        <div class="xenhire-user-info">
            <span>Welcome, <strong><?php echo esc_html(XenHire_Auth::get_logged_email()); ?></strong></span>
            <a href="<?php echo esc_url(site_url('/jobs/')); ?>" target="_blank" class="xh-btn xh-job-preview">Preview Jobs</a>
            <button id="xenhire-logout-btn" class="xh-btn xh-logout">Logout</button>
        </div>
    </div>
    <div class="xenhire-container">
        <div class="xh-card">
            <!-- Header Controls -->
            <div class="xenhire-jobs-header">
                <div class="xenhire-search-wrapper">
                    <button type="button" id="" class="xenhire-search-icon-btn">
                        <span class="ki-outline ki-magnifier ki-fs-2"></span>
                    </button>
                    <input type="text" id="xh-search-input" placeholder="Search" class="xh-search">
                    <button type="button" id="xh-clear-search-btn" class="xenhire-search-clear-btn" style="display: none;">
                        <span class="ki-outline ki-cross ki-fs-2"></span>
                    </button>
                </div>
                <div class="xenhire-jobs-toolbar" style="position:relative;">
                    <button class="xh-btn-filter" id="xh-filter-btn">
                        <span class="ki-outline ki-filter ki-fs-6"></span> Filter
                    </button>
                    <!-- Filter Dropdown -->
                    <div id="xh-filter-dropdown" class="xh-filter-dropdown" style="display:none;">
                        <div class="xh-filter-header">Filter Options</div>
                        <div class="xh-filter-body">
                            <label>Status:</label>
                            <select id="xh-filter-status" class="xh-filter-select">
                                <option value="-1">All</option>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                            <div style="text-align: right; margin-top: 16px;">
                                <button id="xh-apply-filter" class="xh-btn-primary">Apply</button>
                            </div>
                        </div>
                    </div>

                    <?php if (!$xenhire_has_employers): ?>
                        <button class="xh-btn-primary" id="xh-add-employer-btn">Add New Employer</button>
                    <?php endif; ?>
                </div>
            </div>
            <div class="xenhire-jobs-content">
                <!-- List Header -->
                <div class="xh-list-header">
                    <div>LOGO</div>
                    <div>BRAND NAME</div>
                    <div>COMPANY NAME</div>
                    <div>CREATED ON</div>
                    <div style="text-align: center;">RECRUITING</div>
                    <div style="text-align: center;">JOBS</div>
                    <div style="text-align: center;">ACTION</div>
                </div>

                <!-- List Body -->
                <div class="xh-list-body" id="xh-emp-list">
                    <div class="xh-list-row">
                        <div style="grid-column: 1/-1; text-align:center; padding: 20px;">Loading...</div>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="xenhire-pagination-wrapper">
                    <select class="xh-page-size-select xenhire-page-size" id="xh-page-size">
                        <option value="10">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                    </select>
                    
                    <div class="xenhire-pagination-info" id="xh-page-info">showing 0 to 0 of 0 records</div>
                    
                    <div class="xenhire-pagination-pages" id="xh-page-nav">
                        <!-- JS Injected -->
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Employer Modal -->
    <div id="xh-add-employer-modal" class="xh-modal" style="display:none;">
        <div class="xh-modal-content">
            <form id="xh-add-employer-form">
                <input type="hidden" id="xh-employer-id" name="EmployerID" value="-1">
                <div class="xh-modal-header">
                    <h2 id="xh-modal-title">Add New Employer</h2>
                    <span class="xh-close-modal">&times;</span>
                </div>
            <div class="xh-modal-body">
                    <!-- Logo -->
                    <div class="xh-form-group">
                        <label>Logo</label>
                        <div class="xh-form-column">
                            <div class="xh-upload-area" id="xh-logo-upload-area">
                                <div class="xh-preview" id="xh-logo-preview"></div>
                                <input type="hidden" id="xh-logo-url" name="LogoURL">
                                <button type="button" class="xh-upload-btn" id="xh-upload-logo-btn">
                                    <span class="dashicons dashicons-edit"></span>
                                </button>
                            </div>
                            <p class="description">Accepted formats: .png, .jpg. Max size: 1 MB.</p>
                        </div>
                    </div>

                    <!-- Brand Name -->
                    <div class="xh-form-group">
                        <label for="xh-brand-name">Brand Name <span class="required">*</span></label>
                        <input type="text" id="xh-brand-name" name="BrandName" class="regular-text" placeholder="Brand Name" required>
                    </div>

                    <!-- Company Name -->
                    <div class="xh-form-group">
                        <label for="xh-company-name">Company Name <span class="required">*</span></label>
                        <input type="text" id="xh-company-name" name="CompanyName" class="regular-text" placeholder="Company Name" required>
                    </div>

                    <!-- Website -->
                    <div class="xh-form-group">
                        <label for="xh-website">Website <span class="required">*</span></label>
                        <input type="url" id="xh-website" name="Website" class="regular-text" placeholder="Website URL" required>
                    </div>

                    <!-- Industry -->
                    <div class="xh-form-group">
                        <label for="xh-industry">Industry <span class="required">*</span></label>
                        <input type="text" id="xh-industry-search" class="regular-text" placeholder="Type to search industry..." required>
                        <input type="hidden" id="xh-industry" name="Industry">
                    </div>

                    <!-- Company Description -->
                    <div class="xh-form-group">
                        <label for="xh-description">Company Description</label>
                        <textarea id="xh-description-editor" name="Description" placeholder="About the company"></textarea>
                    </div>

                    <!-- Currently Recruiting -->
                    <div class="xh-form-group row-layout">
                        <label>Currently Recruiting</label>
                        <label class="xh-toggle-switch">
                            <input type="checkbox" id="xh-is-recruiting" name="IsRecruiting" checked>
                            <span class="xh-slider"></span>
                        </label>
                    </div>

                    <div class="xh-modal-footer" style="text-align:center;">
                        <button type="button" class="xh-btn-secondary" id="xh-cancel-btn">Discard</button>
                        <button type="submit" class="xh-btn-primary" id="xh-submit-btn">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
