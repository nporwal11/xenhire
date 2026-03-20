if (!current_user_can('manage_options')) { wp_die('Unauthorized'); }
<?php 
if (!defined('ABSPATH')) exit;
?>

<div class="wrap xenhire-stages-page xenhire-common">
    <div class="xenhire-header">
        <h1>Jobs</h1>
        <div class="xenhire-user-info">
            <span>Welcome, <strong><?php echo esc_html(XenHire_Auth::get_logged_email()); ?></strong></span>
            <a href="<?php echo esc_url(site_url('/jobs/')); ?>" target="_blank" class="xh-btn xh-job-preview">Preview Jobs</a>
            <button id="xenhire-logout-btn" class="xh-btn xh-logout">Logout</button>
        </div>
    </div>
    <div class="xenhire-container">
        <div class="xenhire-jobs-card xh-card">
            <div class="xenhire-jobs-header">
                <div class="xenhire-search-wrapper">
                    <button type="button" id="kt_search_btn" class="xenhire-search-icon-btn">
                        <i class="ki-outline ki-magnifier ki-fs-2"></i>
                    </button>
                    <input type="text" id="kt_filter_search" class="xh-search" placeholder="Search" />
                    <button type="button" id="kt_clear_search" class="xenhire-search-clear-btn" style="display: none;">
                        <i class="ki-outline ki-cross ki-fs-2"></i>
                    </button>                    
                </div>                
                <div class="xenhire-jobs-toolbar">
                    <button type="button" id="kt_filter_btn" class="xh-btn-filter">
                        <i class="ki-outline ki-filter ki-fs-6"></i>
                        Filter
                    </button>
                    <a id="btnAddJob" href="<?php echo esc_url(admin_url('admin.php?page=xenhire-job-add')); ?>" class="xh-btn-primary">Add New Job</a>
                </div>
                <div id="kt_filter_dropdown" class="xenhire-filter-dropdown" style="display: none;">
                    <div class="xenhire-filter-header">
                        <h3>Filter Options</h3>
                    </div>
                    <div class="xenhire-filter-body">
                        <div class="xenhire-filter-field">
                            <label>Employer:</label>
                            <select id="EmployerID" class="xenhire-filter-select">
                                <option value="-1">Select Employer...</option>
                            </select>
                        </div>
                        
                        <div class="xenhire-filter-field">
                            <label>Job Status:</label>
                            <select id="kt_filter_status" class="xenhire-filter-select">
                                <option value="-1">All</option>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                        
                        <div class="xenhire-filter-actions">
                            <button type="button" id="kt_reset_status" class="xh-btn secondary">Reset</button>
                            <button type="button" id="kt_button_status" class="xh-btn primary">Apply</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="xenhire-jobs-content">
                <div class="xenhire-jobs-table-wrapper">
                    <table class="xenhire-jobs-table">
                        <thead id="tHead">
                            <tr>
                                <th class="col-employer">Employer</th>
                                <th class="col-designation">Designation</th>
                                <th class="col-details">Details</th>
                                <th class="col-published">Published</th>
                                <th class="col-applications">Applications</th>
                                <th class="col-action">Action</th>
                            </tr>
                        </thead>
                        <tbody id="tBody">
                            <tr>
                                <td colspan="6" class="loading-row">
                                    <div class="loading-spinner">Loading jobs...</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="xenhire-pagination-wrapper" id="pagerDiv" style="display: none;">
                    <div class="xenhire-pagination-controls">
                        <select id="selectPager" class="xenhire-page-size">
                            <option value="10">10</option>
                            <option value="20">20</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                    <div class="xenhire-pagination-info" id="pagination-info">
                        <span id="record-info">showing 0 to 0 of 0 records</span>
                    </div>
                    <div class="xenhire-pagination-pages" id="Pager"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Invite Candidates Modal -->
<div id="kt_modal_Invite" class="invite-modal-overlay" style="display: none;">
    <div class="invite-modal-container">
        <div class="invite-modal-content">
            <!-- Modal Header -->
            <div class="invite-modal-header">
                <h2>Invite Candidates</h2>
                <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
            </div>            
           <div class="invite-modal-body">
                <div class="invite-tabs-wrapper">
                    <button class="invite-tab-btn active" data-tab="share-link">Share link</button>
                    <button class="invite-tab-btn" data-tab="invite-email">Invite by email</button>
                </div>
                <div id="share-link-tab" class="invite-tab-pane active">
                    <p class="invite-info-text">
                        Invite candidates by sharing a public link in your job post. This link will direct them to the job listing where they can apply and appear for the interview.
                    </p>
                    
                    <div class="invite-url-section">
                        <input type="text" id="ShareURL" class="invite-url-input" readonly />
                        <button type="button" id="btnCopyLink" class="invite-btn invite-btn-primary">
                            <i class="ki-outline ki-copy fs-2"></i>
                            Copy Link
                        </button>
                        <div class="invite-share-dropdown">
                            <button type="button" id="btnShareDropdown" class="invite-btn invite-btn-secondary">
                                <i class="ki-outline ki-share fs-2"></i>
                                Share
                            </button>
                            <div id="shareDropdownMenu" class="share-dropdown-menu" style="display: none;">
                                <a href="#" class="share-item linkedin" target="_blank">
                                    <span class="dashicons dashicons-linkedin me-3"></span> LinkedIn
                                </a>
                                <a href="#" class="share-item facebook" target="_blank">
                                    <span class="dashicons dashicons-facebook"></span> Facebook
                                </a>
                                <a href="#" class="share-item whatsapp" target="_blank">
                                    <span class="dashicons dashicons-whatsapp"></span> WhatsApp
                                </a>
                                <a href="#" class="share-item twitter" target="_blank">
                                    <span class="dashicons dashicons-twitter"></span> Twitter
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <p class="invite-info-text" style="margin-top: 30px;">
                        Copy and add this text to your job post:
                    </p>
                    
                    <div class="invite-editor-wrapper">
                        <textarea id="InviteText" class="invite-editor-container"></textarea>
                    </div>
                    
                    <div class="invite-modal-actions">
                        <button type="button" class="invite-btn invite-btn-light" data-dismiss="modal">Close</button>
                    </div>
                </div>
                
                <div id="invite-email-tab" class="invite-tab-pane">
                    <form id="kt_modal_SendEmail_form">
                        <div class="invite-form-group">
                            <!-- <label class="invite-form-label">To</label> -->
                            <input type="text" id="InviteCCEmails" class="invite-form-control" placeholder="Enter email addresses" />
                            <small class="invite-form-help">Press enter or comma after each email</small>
                        </div>
                        
                        <div class="invite-form-group">
                            <!-- <label class="invite-form-label">Subject</label> -->
                            <input type="text" id="InviteSubject" class="invite-form-control" placeholder="Email subject" />
                        </div>
                        
                        <div class="invite-form-group">
                            <!-- <label class="invite-form-label">Message</label> -->
                            <div class="invite-editor-wrapper">
                                <textarea id="Body" class="invite-editor-container"></textarea>
                            </div>
                        </div>
                        
                        <div class="invite-modal-actions">
                            <button type="button" class="invite-btn invite-btn-light" data-dismiss="modal">Close</button>
                            <button type="submit" id="kt_modal_SendEmail_submit" class="invite-btn invite-btn-primary">
                                <span class="indicator-label">Send Email</span>
                                <span class="indicator-progress" style="display: none;">
                                    Sending...<span class="spinner is-active"></span>
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>




