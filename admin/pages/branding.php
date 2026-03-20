<?php if (!defined('ABSPATH')) exit; ?>

<div class="wrap xenhire-branding-wrap xenhire-common">
    <div class="xenhire-header">
        <h1>Branding</h1>
        <div class="xenhire-user-info">
            <span>Welcome, <strong><?php echo esc_html(XenHire_Auth::get_logged_email()); ?></strong></span>
            <a href="<?php echo esc_url(site_url('/jobs/')); ?>" target="_blank" class="xh-btn xh-job-preview">Preview Jobs</a>
            <button id="xenhire-logout-btn" class="xh-btn xh-logout">Logout</button>            
        </div>
    </div>
    <div class="xenhire-container">
        <form id="xb_form" class="xb-form xh-card">
            
            <!-- Career Page URL -->
            <!-- <div class="xb-field-group" style="display:none;">
                <label for="CareerPageURL">Career Page URL <span class="required">*</span></label>
                <div class="xb-input-wrapper">
                    <input type="text" id="CareerPageURL" name="CareerPageURL" class="regular-text" placeholder="e.g. 4960fd">
                    <div class="xb-url-actions">
                        <a href="#" id="xb_preview_link" target="_blank"> id="xb_url_slug">...</span></a>
                        <button type="button" class="button button-small" id="xb_copy_url">Copy URL</button>
                        <button type="button" class="button button-small" id="xb_add_cname">Add CNAME</button>
                    </div>
                </div>
            </div> -->

            <!-- Brand Name -->
            <div class="xb-field-group">
                <label for="BrandName">Brand Name <span class="required">*</span></label>
                <input type="text" id="BrandName" name="BrandName" class="regular-text" placeholder="Brand Name">
            </div>

            <!-- Company Name -->
            <div class="xb-field-group">
                <label for="CompanyName">Company Name <span class="required">*</span></label>
                <input type="text" id="CompanyName" name="CompanyName" class="regular-text" placeholder="Company Name">
            </div>

            <!-- Website -->
            <div class="xb-field-group">
                <label for="Website">Website</label>
                <input type="url" id="Website" name="Website" class="regular-text" placeholder="Website">
            </div>

            <!-- Industry -->
            <!-- Industry -->
            <div class="xb-field-group">
                <label for="Industry">Industry</label>
                <input type="text" id="Industry_Search" class="regular-text" placeholder="Type to search industry...">
                <input type="hidden" id="Industry" name="Industry">
            </div>

            <!-- About the brand -->
            <div class="xb-field-group">
                <label for="AboutBrand">About the brand <span class="required">*</span></label>
                <textarea id="AboutBrand" name="AboutBrand" rows="5" class="large-text" placeholder="About the brand"></textarea>
                <!-- <div class="xb-ai-actions">
                    <button type="button" class="button" id="xb_optimize">Optimize</button>
                    <button type="button" class="button button-primary" id="xb_write_ai">Write with AI</button>
                </div> -->
            </div>

            <!-- <hr class="xb-divider"> -->

            <!-- Colors -->
            <div class="xb-field-group">
                <label for="PrimaryColor">Primary Color</label>
                <input type="color" id="PrimaryColor" name="PrimaryColor" value="#8b5cf6">
            </div>

            <div class="xb-field-group">
                <label for="SecondaryColor">Secondary Color</label>
                <input type="color" id="SecondaryColor" name="SecondaryColor" value="#7c3aed">
            </div>

            <div class="xb-field-group">
                <label for="TagLineColor">Tag Line Color</label>
                <input type="color" id="TagLineColor" name="TagLineColor" value="#000000">
            </div>

            <!-- Favicon -->
            <div class="xb-field-group">
                <label>Favicon</label>
                <div class="xh-flex-column">
                    <div class="xb-upload-area" id="xb_favicon_area">
                        <div class="xb-preview" id="xb_favicon_preview"></div>
                        <input type="hidden" id="FaviconURL" name="FaviconURL">
                        <button type="button" class="xb-upload-btn" data-target="FaviconURL" data-preview="xb_favicon_preview">
                            <span class="dashicons dashicons-edit"></span>
                        </button>
                        <button type="button" class="xb-remove-btn" data-target="FaviconURL" data-preview="xb_favicon_preview">
                            <span class="dashicons dashicons-no-alt"></span>
                        </button>
                    </div>
                    <p class="description">Accepted formats: .png, .jpg. Max size: 1 MB.</p>
                </div>
            </div>

            <!-- Logo -->
            <div class="xb-field-group">
                <label>Logo</label>
                <div class="xh-flex-column">
                    <div class="xb-upload-area wide" id="xb_logo_area">
                        <div class="xb-preview" id="xb_logo_preview"></div>
                        <input type="hidden" id="LogoURL" name="LogoURL">
                        <button type="button" class="xb-upload-btn" data-target="LogoURL" data-preview="xb_logo_preview">
                            <span class="dashicons dashicons-edit"></span>
                        </button>
                        <button type="button" class="xb-remove-btn" data-target="LogoURL" data-preview="xb_logo_preview">
                            <span class="dashicons dashicons-no-alt"></span>
                        </button>
                    </div>
                    <p class="description">Accepted formats: .png, .jpg. Max size: 4 MB.</p>
                </div>
            </div>

            <!-- Social Media Preview Image -->
            <div class="xb-field-group">
                <label>Social media preview image</label>
                <div class="xh-flex-column">
                    <div class="xb-upload-area wide" id="xb_social_area">
                        <div class="xb-preview" id="xb_social_preview"></div>
                        <input type="hidden" id="SocialPreviewURL" name="SocialPreviewURL">
                        <button type="button" class="xb-upload-btn" data-target="SocialPreviewURL" data-preview="xb_social_preview">
                            <span class="dashicons dashicons-edit"></span>
                        </button>
                        <button type="button" class="xb-remove-btn" data-target="SocialPreviewURL" data-preview="xb_social_preview">
                            <span class="dashicons dashicons-no-alt"></span>
                        </button>
                    </div>
                    <p class="description">Accepted formats: .png, .jpg. Max size: 4 MB.</p>
                </div>
            </div>

            <!-- Introduction Video -->
            <div class="xb-field-group">
                <label>Introduction Video <br><small class="description">You can record or upload the video</small></label>
                <div class="xb-video-actions">
                    <button type="button" class="xh-btn xh-btn-primary button-primary" id="xb_upload_video">Upload Video</button>
                    <button type="button" class="xh-btn xh-btn-danger" id="xb_play_video" style="display:none;">
                        <span class="dashicons dashicons-controls-play"></span> Play Video
                    </button>
                    <button type="button" class="xh-btn xh-btn-danger-outline" id="xb_delete_video" style="display:none;">
                        <span class="dashicons dashicons-trash"></span>
                    </button>
                </div>
                <input type="hidden" id="IntroVideoURL" name="IntroVideoURL">
            </div>

            <!-- Banner Image -->
            <div class="xb-field-group" style="display: flex; flex-direction: column;">
                <label style="margin-bottom: 15px;">Banner Image</label>
                <div class="xb-upload-area full-width" id="xb_banner_area">
                    <div class="xb-preview" id="xb_banner_preview"></div>
                    <input type="hidden" id="BannerURL" name="BannerURL">
                    <button type="button" class="xb-upload-btn" data-target="BannerURL" data-preview="xb_banner_preview">
                        <span class="dashicons dashicons-edit"></span>
                    </button>
                    <button type="button" class="xb-remove-btn" data-target="BannerURL" data-preview="xb_banner_preview">
                        <span class="dashicons dashicons-no-alt"></span>
                    </button>
                </div>
                <p class="description">Accepted formats: .png, .jpg. Max size: 4 MB.</p>
                <!-- <div class="xb-banner-actions">
                    <button type="button" class="button button-primary" id="xb_search_image">Search Image</button>
                </div> -->
            </div>

            <!-- <hr class="xb-divider"> -->

            <!-- Toggles -->
            <div class="xb-field-group row-layout">
                <label for="IsHiringMultipleBrands">Hiring for multiple brands?</label>
                <div style="flex: 0 0 auto; width: 66.66666667%;">
                    <label class="xb-switch">
                        <input type="checkbox" id="IsHiringMultipleBrands" name="IsHiringMultipleBrands">
                        <span class="slider round"></span>
                    </label>
                </div>                
            </div>

            <div class="xb-field-group row-layout">
                <label for="IsHideCityFilter">Hide city filter from career page?</label>
                <div style="flex: 0 0 auto; width: 66.66666667%;">
                    <label class="xb-switch">
                        <input type="checkbox" id="IsHideCityFilter" name="IsHideCityFilter">
                        <span class="slider round"></span>
                    </label>
                </div>
            </div>

            <!-- Other Languages -->
            <div class="xb-field-group">
                <label for="OtherLanguages">Other Languages <br><small class="description">(Default is English)</small></label>
                <input type="text" id="OtherLanguages" name="OtherLanguages" class="regular-text" placeholder="Select multiple languages">
            </div>

            <div class="xb-footer">
                <button type="submit" class="button button-primary button-large" id="xb_save_bottom">Save</button>
            </div>

        </form>
    </div>
</div>

<!-- Video Upload/Record Modal -->
<div id="xb_video_modal" class="xb-modal" style="display:none;">
    <div class="xb-modal-content">
        <div class="xb-modal-header">
            <h2>Introduction Video</h2>
            <span class="xb-close">&times;</span>
        </div>
        <div class="xb-modal-body">
            
            <!-- File Upload Section -->
            <div class="xb-video-section">
                <div class="xb-file-input-wrapper">
                    <label for="xb_video_file" class="button">Choose file</label>
                    <span id="xb_file_name">No file chosen</span>
                    <input type="file" id="xb_video_file" accept="video/*" style="display:none;">
                </div>
                <div class="upload-inst">
                    <p class="description">max file size: 40 MB</p>
                    <button type="button" class="xh-btn xh-btn-primary" id="xb_save_uploaded_video" disabled>Save Video</button>
                </div>
            </div>

            <div class="xb-divider-text">Or</div>

            <!-- Recording Section -->
            <div class="xb-video-section">
                <div class="xb-video-recorder-wrapper">
                    <video id="xb_recorder_preview" autoplay muted playsinline></video>
                    <div id="xb_recorder_placeholder">
                        <span class="dashicons dashicons-video-alt3" style="font-size: 64px; width: 64px; height: 64px; color: #ccc;"></span>
                    </div>
                </div>
                
                <div class="xb-recorder-controls">
                    <button type="button" class="xh-btn xh-btn-success" id="xb_start_recording">
                        <span class="dashicons dashicons-controls-play"></span> Start Recording
                    </button>
                    <button type="button" class="xh-btn xh-btn-danger" id="xb_stop_recording">
                        <span class="dashicons dashicons-controls-pause"></span> Stop Recording
                    </button>
                    <span id="xb_timer" style="display:none; margin-left: 10px; color: #99a1b7;font-weight: 500;">00:00 / 03:00</span>
                    
                    <button type="button" class="xh-btn xh-btn-primary" id="xb_save_recorded_video" style="display:none; float: right;">Save Video</button>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Video Playback Modal -->
<div id="xb_playback_modal" class="xb-modal" style="display:none;">
    <div class="xb-modal-content" style="max-width: 800px;">
        <div class="xb-modal-header">
            <h2>Video Preview</h2>
            <span class="xb-close-playback" style="cursor:pointer; font-size:28px;">&times;</span>
        </div>
        <div class="xb-modal-body" style="padding:0;">
            <video id="xb_playback_video" controls style="width:100%; display:block;"></video>
        </div>
    </div>
</div>
