if (!current_user_can('manage_options')) { wp_die('Unauthorized'); }
<?php 
if (!defined('ABSPATH')) exit;
?>

<div class="wrap xenhire-stages-page xenhire-common">
    <!-- Header with Back Button -->
    <div class="xenhire-page-header">
        <div class="xenhire-header-left">
            <a href="<?php echo esc_url(admin_url('admin.php?page=xenhire-settings')); ?>" class="xenhire-back-btn">
                <span class="ki-duotone ki-left ki-fs-1"></span>
            </a>
            <h1>Email Templates</h1>
        </div>
    </div>

    <!-- Card with Search Bar and Actions -->
    
        <!-- Card Header -->
         <div class="xenhire-container">
            <div class="xh-card">
                <div class="xenhire-toolbar">
                    <!-- Search -->
                    <div class="xenhire-search-box">
                        <span class="ki-outline ki-magnifier ki-fs-2"></span>
                        <input type="text" id="kt_filter_search" placeholder="Search templates..." />
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="xenhire-card-toolbar">
                        <button type="button" class="xh-btn-success" id="kt-btn-smtp">
                            Configure SMTP
                        </button>
                        <button type="button" class="xh-btn-primary" id="kt-btn-create">
                            Add New Template
                        </button>
                    </div>
                </div>
                <div class="xenhire-table-container">
                    <!-- Table -->
                    <table class="wp-list-table widefat fixed xenhire-stages-table">
                        <thead id="tHead">
                            <tr>
                                <th class="column-name" width="120">Name</th>
                                <th class="column-template">Subject</th>
                                <th class="column-actions" style="text-align: right;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="tBody">
                            <tr>
                                <td colspan="3" style="text-align: center; padding: 40px;">
                                    <p style="color: #999; font-size: 16px;">Loading templates...</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
</div>

<!-- SMTP Configuration Modal - Upgrade Notice -->
<div class="xenhire-modal fade" id="kt_modal_smtp" tabindex="-1" aria-hidden="true" style="display: none;">
    <div class="xenhire-modal-overlay"></div>
    <div class="xenhire-modal-dialog modal-dialog-centered" style="max-width: 450px;">
        <div class="xenhire-modal-content">
            <div class="xenhire-modal-header" style="border-bottom: none; padding-bottom: 0;">
                <button type="button" class="xenhire-modal-close" data-dismiss="modal">
                    <span class="dashicons dashicons-no-alt"></span>
                </button>
            </div>
            
            <div class="xenhire-modal-body" style="text-align: center; padding: 40px 30px;">
                <!-- Info Icon -->
                <div style="margin-bottom: 25px;">
                    <svg width="80" height="80" viewBox="0 0 80 80" fill="none" xmlns="">
                        <circle cx="40" cy="40" r="38" stroke="#6366F1" stroke-width="4"/>
                        <path d="M40 20C39.0717 20 38.3334 20.7383 38.3334 21.6667V23.3333C38.3334 24.2617 39.0717 25 40 25C40.9284 25 41.6667 24.2617 41.6667 23.3333V21.6667C41.6667 20.7383 40.9284 20 40 20Z" fill="#6366F1"/>
                        <path d="M40 30C39.0717 30 38.3334 30.7383 38.3334 31.6667V56.6667C38.3334 57.595 39.0717 58.3333 40 58.3333C40.9284 58.3333 41.6667 57.595 41.6667 56.6667V31.6667C41.6667 30.7383 40.9284 30 40 30Z" fill="#6366F1"/>
                    </svg>
                </div>
                
                <!-- Message -->
                <p style="font-size: 16px; color: #1f2937; line-height: 1.5; margin: 0 0 30px 0; font-weight: 400;">
                    Please upgrade your plan to send emails from<br>your brand email.
                </p>
                
                <!-- Upgrade Button -->
                <a href="javascript:void(0);" id="upgrade-plan-link" class="button button-primary" style="
                    background: #6366F1; 
                    border-color: #6366F1; 
                    color: white; 
                    padding: 5px 20px; 
                    font-size: 15px; 
                    font-weight: 600;
                    text-decoration: none;
                    display: inline-block;
                    border-radius: 6px;
                    transition: all 0.3s ease;
                " onmouseover="this.style.background='#4F46E5'" onmouseout="this.style.background='#6366F1'">
                    Upgrade Now
                </a>
            </div>
        </div>
    </div>
</div>


<!-- Add/Edit Email Template Modal -->
<div class="xenhire-modal fade" id="kt_modal_add_template" tabindex="-1" aria-hidden="true" style="display: none;">
    <div class="xenhire-modal-overlay"></div>
    <div class="xenhire-modal-dialog modal-dialog-centered">
        <div class="xenhire-modal-content">
            <div class="xenhire-modal-header">
                <h2 id="headTitle">Add New Template</h2>
                <button type="button" class="xenhire-modal-close" data-dismiss="modal">
                    <span class="dashicons dashicons-no-alt"></span>
                </button>
            </div>
            
            <div class="xenhire-modal-body scroll-y">
                <form id="kt_modal_add_template_form" class="xenhire-stage-form">
                    <input type="hidden" id="ID" name="ID" value="-1" />
                    
                    <div class="xenhire-form-row fv-row">
                        <label class="xenhire-form-label required">Name</label>
                        <div class="position-relative">
                            <input type="text" class="xenhire-form-control" placeholder="Template Name" name="Name" id="Name" value="" />
                            <div class="fv-feedback"></div>
                        </div>
                    </div>
                    
                    <div class="xenhire-form-row fv-row">
                        <label class="xenhire-form-label required">Subject</label>
                        <div class="position-relative">
                            <input type="text" class="xenhire-form-control" placeholder="Email Subject" name="Subject" id="Subject" value="" />
                            <div class="fv-feedback"></div>
                        </div>
                    </div>
                    
                    <div class="xenhire-form-row fv-row">
                        <label class="xenhire-form-label">Body</label>
                        <div class="position-relative">
                            <textarea id="Body" name="Body"></textarea>
                            <div class="fv-feedback"></div>
                        </div>
                    </div>
                    
                    <div class="xenhire-modal-actions">
                        <button type="button" class="xh-btn xh-btn-large xh-secondary xenhire-btn-cancel" data-dismiss="modal">Close</button>
                        <button type="submit" id="kt_modal_add_template_submit" class="xh-btn xh-btn-large xh-primary">
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
</div>

<!-- Load CKEditor 5 -->

