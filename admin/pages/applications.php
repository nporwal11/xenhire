if (!current_user_can('manage_options')) { wp_die('Unauthorized'); }
<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap xh-app-wrap1 xenhire-common">
    <!-- Header -->
    <div class="xenhire-header">
        <h1>Applications</h1>
        <div class="xenhire-user-info">
            <span>Welcome, <strong><?php echo esc_html(XenHire_Auth::get_logged_email()); ?></strong></span>
            <a href="<?php echo esc_url(site_url('/jobs/')); ?>" target="_blank" class="xh-btn xh-job-preview">Preview Jobs</a>
            <button id="xenhire-logout-btn" class="xh-btn xh-logout">Logout</button>
        </div>
    </div>
    <div class="xenhire-container xh-card">
        <div class="xenhire-jobs-header">
            <div class="xh-header-left">
                <div class="xh-custom-select" id="custom-req-select">
                    <div class="xh-select-trigger">
                        <span class="ki-outline ki-briefcase ki-fs-2"></span>
                        <div class="xh-select-tigginn">
                            <span class="xh-selected-text">All Jobs</span>
                            <i class="ki-duotone ki-down ki-fs-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                        </div>
                    </div>
                    <div class="xh-select-dropdown">
                        <input type="text" class="xh-select-search" placeholder="Search jobs...">
                        <div class="xh-options-list">
                            <div class="xh-option" data-value="-1">All Jobs</div>
                        </div>
                    </div>
                    <select id="filter-requirement" style="display:none">
                        <option value="-1">All Jobs</option>
                    </select>
                </div>
                <button class="xh-btn-icon" title="Refresh">
                    <i class="ki-duotone ki-arrows-circle ki-fs-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </button>
            </div>
            <div class="xh-header-right" style="position: relative;">
                <button class="xh-btn-filter" id="xh-toggle-filter">
                    <span class="ki-outline ki-filter ki-fs-6" style="padding-right: .35rem; margin-right: .25rem"></span> Filter
                </button>
                <button class="xh-btn-primary" id="xh-btn-download">
                    <svg style="vertical-align: middle; margin-right:5px;" width="17.5" height="17.5" viewBox="0 0 24 24" fill="none" xmlns="">
                        <path opacity="0.3" d="M19 15C20.7 15 22 13.7 22 12C22 10.3 20.7 9 19 9C18.9 9 18.9 9 18.8 9C18.9 8.7 19 8.3 19 8C19 6.3 17.7 5 16 5C15.4 5 14.8 5.2 14.3 5.5C13.4 4 11.8 3 10 3C7.2 3 5 5.2 5 8C5 8.3 5 8.7 5.1 9H5C3.3 9 2 10.3 2 12C2 13.7 3.3 15 5 15H19Z" fill="currentColor"></path>
                        <path d="M13 17.4V12C13 11.4 12.6 11 12 11C11.4 11 11 11.4 11 12V17.4H13Z" fill="currentColor"></path>
                        <path opacity="0.3" d="M8 17.4H16L12.7 20.7C12.3 21.1 11.7 21.1 11.3 20.7L8 17.4Z" fill="currentColor"></path>
                    </svg>    
                    Download CSV
                </button>

                <!-- Filter Panel -->
                <div id="xh-filter-panel" class="xh-filter-panel" style="display:none;">
                    <div class="xh-filter-header">Filter Options</div>
                    <div class="xh-filter-body">
                        <!-- Main Filters -->
                        <div class="xh-filter-row">
                            <label>Interview Status</label>
                            <select id="filter-interview-status">
                                <option value="-1">All</option>
                                <option value="1">Completed</option>
                                <option value="0">Pending</option>
                            </select>
                        </div>
                        <div class="xh-filter-row">
                            <label>Your Rating</label>
                            <select id="filter-rating">
                                <option value="-1">Select Rating...</option>
                            </select>
                        </div>
                        <div class="xh-filter-row">
                            <label>AI Score</label>
                            <select id="filter-ai-score">
                                <option value="-1">Select AI Score...</option>
                            </select>
                        </div>
                        <div class="xh-filter-row">
                            <label>Stage</label>
                            <select id="filter-stage">
                                <option value="-1">Select Stage...</option>
                            </select>
                        </div>

                        <!-- Additional Filters Toggle -->
                        <div class="xh-filter-toggle" id="xh-toggle-additional">
                            <i class="ki-duotone ki-down ki-fs-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i> Additional filters
                        </div>

                        <!-- Additional Filters Content -->
                        <div class="xh-additional-filters" id="xh-additional-filters" style="display:none;">
                            <div class="xh-filter-row">
                                <label>Name</label>
                                <input type="text" id="filter-name" placeholder="">
                            </div>
                            <div class="xh-filter-row">
                                <label>Email</label>
                                <input type="text" id="filter-email" placeholder="">
                            </div>
                            <div class="xh-filter-row">
                                <label>Mobile</label>
                                <input type="text" id="filter-mobile" placeholder="">
                            </div>
                            <div class="xh-filter-row">
                                <label>Experience</label>
                                <div class="xh-range-inputs">
                                    <input type="number" id="filter-exp-from" placeholder="">
                                    <span>-</span>
                                    <input type="number" id="filter-exp-to" placeholder="">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="xh-filter-footer">
                        <button class="xh-btn xh-btn-default" id="xh-clear-filter">Reset</button>
                        <button class="xh-btn xh-btn-primary" id="xh-apply-filter">Apply</button>
                    </div>
                </div>
            </div>
        </div>
            <div class="xenhire-jobs-content">
            <!-- List Header -->
            <div class="xh-list-header">
                <div class="xh-col xh-col-name">CANDIDATE NAME</div>
                <div class="xh-col xh-col-stage">STAGE</div>
                <div class="xh-col xh-col-date">APPLIED ON</div>
                <div class="xh-col xh-col-emp">EMPLOYMENT</div>
                <div class="xh-col xh-col-exp">EXPERIENCE</div>
                <div class="xh-col xh-col-int">INTERVIEW</div>
                <div class="xh-col xh-col-action">ACTION</div>
            </div>

            <!-- List Body -->
            <div class="xh-list-body" id="xh-app-list">
                <div class="xh-loading" >Loading applications...</div>
            </div>

            <!-- Pagination -->
            <div class="xh-pagination-bar">
                <div class="xh-page-size">
                    <div class="xh-custom-select xh-select-small" id="custom-page-size">
                        <div class="xh-select-trigger">
                            <span class="xh-selected-text">10</span>
                            <i class="ki-duotone ki-down ki-fs-2">
                                <span class="path1"></span>
                                <span class="path2"></span>
                            </i>
                        </div>
                        <div class="xh-select-dropdown xh-dropdown-up">
                            <div class="xh-options-list">
                                <div class="xh-option selected" data-value="10">10</div>
                                <div class="xh-option" data-value="20">20</div>
                                <div class="xh-option" data-value="50">50</div>
                            </div>
                        </div>
                        <select id="xh-page-size" style="display:none">
                            <option value="10">10</option>
                            <option value="20">20</option>
                            <option value="50">50</option>
                        </select>
                    </div>
                </div>
                <div class="xh-page-info" id="xh-page-info">showing 0 to 0 of 0 records</div>
                <div class="xh-page-nav" id="xh-page-nav">
                    <!-- Pagination buttons injected by JS -->
                </div>
            </div>


        </div>
    </div>
</div>




