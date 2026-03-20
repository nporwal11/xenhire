if (!current_user_can('manage_options')) { wp_die('Unauthorized'); }
<?php if (!defined('ABSPATH')) exit; ?>

<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap">
    <div class="xh-cd-container">
        <!-- Header -->
        <div class="xh-cd-header">
            <div class="xh-cd-profile-section">
                <div style="display:flex; flex-direction:column;">
                    <img src="<?php echo esc_url(XENHIRE_PLUGIN_URL . 'public/images/placeholder.png'); ?>" alt="Profile" class="xh-cd-avatar">
                    <div style="display:flex; flex-direction:column;">
                        <a href="#" class="xh-cd-resume-btn">View Resume</a>
                        <a href="javascript:void(0)" class="d-block mt-3 link-primary text-center" id="btnGetMatchScore" style="display:none !important;">Get Match Score</a>
                        <div class="xh-cd-resume-match">
                        </div>
                    </div>
                </div>                
                
            </div>
            <div class="xh-cd-info" style="    padding: 0 30px;">
                    <div style="display:flex; justify-content: space-between; border-bottom: 1px solid #f1f1f4; padding-bottom: 10px;">
                        <div>
                            <h2 class="xh-cd-name"></h2>
                            <div class="xh-cd-role"></div>
                            <div class="xh-cd-company" style="margin-bottom:0;"></div>
                        </div>
                        <div class="xh-applied-for">
                            <span>Applied for</span>
                            <div></div>
                        </div>
                    </div>                    
                    <div class="xh-cd-meta-grid">
                        <div class="xh-cd-meta-item" id="xh-cd-location">
                        </div>
                        <div class="xh-cd-meta-item" id="xh-cd-phone">
                        </div>
                        <div class="xh-cd-meta-item" id="xh-cd-experience">
                        </div>
                        <div class="xh-cd-meta-item" id="xh-cd-email">
                        </div>
                        <div class="xh-cd-meta-item" id="xh-cd-salary">
                        </div>
                        <div class="xh-cd-meta-item" id="xh-cd-linkedin">
                        </div>
                        <div class="xh-cd-meta-item" id="xh-cd-age">
                        </div>
                    </div>                    
                </div>
            <div style="display:flex; flex-direction:column;">                
                <div class="xh-cd-stage-section">
                    <div class="xh-cd-ai-score-section" style="display:none; margin-bottom: 20px;">
                        <div class="xh-cd-stage-label" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            Overall AI Score
                            <div class="xh-rating" style="display: flex; gap: 1px;"></div>
                        </div>
                    </div>
                    <div class="xh-cd-stage-label">Hiring stage</div>
                    <select class="xh-cd-stage-select">
                        <option>Loading...</option>
                    </select>                
                </div>
                <div class="xh-cd-public-link">
                    <div>Public link: <a href="#"></a></div>
                    <span class="ki-outline ki-copy xh-copy-btn" data-text="" title="Copy Link" style="cursor:pointer; font-size:13px;"></span>
                </div>
            </div>
        </div>

        <!-- Main Grid -->
        <div class="xh-cd-grid">
            <!-- Left Column -->
            <div class="xh-cd-main">
                <!-- Resume & Match Score Card (Hidden by default) -->
                <div class="xh-cd-card" id="xh-resume-card" style="display:none; position:relative;">
                    <button id="btnResumeDownload" class="button button-secondary d-none">Download Resume</button>
                    <span class="xh-card-close" style="cursor:pointer;position: absolute; right: 20px; top: 20px; font-size: 24px;" title="Close">&times;</span>
                    <div class="xh-cd-tabs">
                        <div class="xh-cd-tab active" data-tab="resume">Resume</div>
                        <div class="xh-cd-tab" data-tab="match">Match Score</div>
                    </div>
                    <div class="xh-cd-tab-content" id="xh-tab-resume">
                        <div id="divResume" style="width:100%; min-height:500px; overflow-y: auto;"></div>

                    </div>
                    <div class="xh-cd-tab-content" id="xh-tab-match" style="display:none;">
                        <div id="xh-match-score-details">
                            <!-- Match score details will be populated here -->
                            <p>Loading match score...</p>
                        </div>
                    </div>
                </div>
                <!-- PDF.js -->


                <div class="xh-cd-card" id="xh-interview-not-attempted">
                    <h3 class="xh-cd-card-title">Interview Not Attempted</h3>
                    <div class="xh-cd-interview-status">
                        <!-- Content here -->
                    </div>
                </div>

                <!-- Interview Section (Hidden by default) -->
                <div class="xh-cd-card" id="xh-interview-section" style="display:none;">
                    <div class="card-body">
                        <div class="card-header align-items-center p-0 min-h-auto mb-5 pb-1 d-flex justify-content-between">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="fw-bold text-gray-900">Interview</span>
                            </h3>
                            <div class="xh-interview-nav">
                                <a href="javascript:void(0);" id="PrevQues" class="xh-nav-btn">
                                    <span class="las la-angle-left"></span>
                                </a>
                                <span id="xh-question-counter">Question 1 of 2</span>
                                <a href="javascript:void(0);" id="NextQues" class="xh-nav-btn">
                                    <span class="las la-angle-right"></span>
                                </a>
                                <select id="Ques" style="display:none;"></select>
                            </div>
                        </div>
                        <div class="row d-flex mt-5">
                            <div class="col-12">

                                <div class="div-ques">
                                    <div class="d-block mb-4">
                                        <div class="fs-5 d-flex fw-bold text-dark" id="Question"></div>
                                        <pre class="pre-scrollable d-none p-2" id="QuestionDesc"></pre>
                                    </div>
                                    
                                    <div class="video-wrapper rounded w-100 mb-4">
                                        <video id="VideoURL">
                                            <source class="webm-source" src="" type="video/webm">
                                            <source class="mp4-source" src="" type="video/mp4">
                                            Your browser does not support the video tag.
                                        </video>
                                        <canvas id="blurred-background"></canvas>
                                        <div id="controls-overlay"></div>
                                        <div id="custom-progress-bar"><div id="progress"></div></div>
                                        <div id="time-display">0:00 / 0:00</div>
                                        <div id="play-pause-icon" class="control-icon"><i class="las la-play"></i></div>
                                        <div id="rewind-icon" class="control-icon"><i class="las la-undo"></i></div>
                                        <div id="forward-icon" class="control-icon"><i class="las la-redo"></i></div>
                                        <div id="volume-control"><i id="volume-icon" class="las la-volume-up"></i><input type="range" id="volume-slider" min="0" max="1" step="0.01" value="1"></div>
                                    </div>

                                    <div class="mb-4" id="divTranscription" style="display:none;">
                                        <a href="javascript:void(0);" class="xh-transcript-toggle">
                                            <span class="las la-angle-right"></span> Show Video Transcript
                                        </a>
                                        <div class="xh-transcript-content" style="display:none;"></div>
                                    </div>

                                    <div class="other-types" style="display:none;">
                                        <div class="xh-ai-title">Answer</div>
                                        <div class="xh-ai-text" id="divAnswer">
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <div class="col-12">
                                <div id="divAI" class="xh-hidden">
                                    <div class="xh-ai-score">
                                        <div class="xh-ai-title">AI Score</div>
                                        <div class="xh-ai-rating">
                                            <div class="rating rating-elem-ai d-flex gap-1"></div>
                                        </div>
                                    </div>
                                    <div class="xh-ai-strengths">
                                        <div class="xh-ai-title">Strengths</div>
                                        <div class="xh-ai-text" id="AIStrengths"></div>
                                    </div>
                                    <div class="xh-ai-weaknesses">
                                        <div class="xh-ai-title">Improvement Areas</div>
                                        <div class="xh-ai-text" id="AIWeaknesses"></div>
                                    </div>
                                    <div class="xh-ai-suggestions">
                                        <div class="xh-ai-title">Suggestions</div>
                                        <div class="xh-ai-text" id="AISuggestions"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="xh-cd-card" style="display:none;">
                    <h3 class="xh-cd-card-title">Language Proficiency</h3>
                    <div class="xh-cd-lang-row">
                        <span>English</span>
                        <button class="xh-cd-btn-generate">Generate</button>
                    </div>
                </div>

                <div class="xh-cd-card">
                    <h3 class="xh-cd-card-title">Skills</h3>
                    <div class="xh-cd-tags">
                        <span class="xh-cd-tag">java</span>
                        <span class="xh-cd-tag">skill1</span>
                        <span class="xh-cd-tag">skill2</span>
                        <span class="xh-cd-tag">skill3 so on..</span>
                    </div>
                </div>

                <div class="xh-cd-card">
                    <h3 class="xh-cd-card-title">Experience</h3>
                    <div id="xh-experience-list">
                        <div class="xh-cd-empty-state">No experience specified</div>
                    </div>
                </div>

                <div class="xh-cd-card">
                    <h3 class="xh-cd-card-title">Education</h3>
                    <div id="xh-education-list">
                        <div class="xh-cd-empty-state">No education specified</div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="xh-cd-sidebar">
                <div class="xh-cd-card" id="xh-extra-info-card" style="display:none;">
                    <h3 class="xh-cd-card-title">Candidate Extra Information</h3>
                    <div id="xh-extra-info-content"></div>
                </div>

                <div class="xh-cd-card">
                    <h3 class="xh-cd-card-title">Your feedback</h3>
                    
                    <div class="xh-cd-feedback-row">
                        <span>Score</span>
                        <div class="xh-cd-stars">
                            <i class="ki-duotone ki-star"></i>
                            <i class="ki-duotone ki-star"></i>
                            <i class="ki-duotone ki-star"></i>
                            <i class="ki-duotone ki-star"></i>
                            <i class="ki-duotone ki-star"></i>
                        </div>
                    </div>
                    
                    <!-- <div class="xh-cd-feedback-row">
                        <span>Score</span>
                        <div class="xh-cd-stars">
                            <span class="dashicons dashicons-star-filled"></span>
                            <span class="dashicons dashicons-star-filled"></span>
                            <span class="dashicons dashicons-star-filled"></span>
                            <span class="dashicons dashicons-star-filled"></span>
                            <span class="dashicons dashicons-star-filled"></span>
                        </div>
                    </div> -->

                    <div class="xh-cd-subtitle">Feedback</div>
                    <textarea class="xh-cd-textarea" placeholder="Give your feedback"></textarea>
                    <div style="text-align:right">
                        <button class="xh-cd-btn-save">Save Score & Feedback</button>
                    </div>
                </div>

                <div class="xh-cd-card">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; border-bottom: 1px solid #f1f1f4; padding-bottom: 10px;">
                        <h3 class="xh-cd-card-title" style="margin: 0; border-bottom: 0;">Other feedbacks</h3>
                        <button class="xh-cd-team-btn">Add Team Members</button>
                    </div>
                    <div class="xh-cd-empty-state" style="text-align:center; padding: 20px 0;">
                        No other feedbacks
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stage Change Modal -->
    <div id="xh-stage-modal" class="xh-modal" style="display:none;">
        <div class="xh-modal-content">
            <div class="xh-modal-header">
                <h3>Send email to candidate</h3>
                <!-- <span class="xh-close-modal">&times;</span> -->
            </div>
            <div class="xh-modal-body">
                <input type="text" id="xh-email-to" class="xh-modal-input" placeholder="To" readonly style="background-color: #f3f4f6;">
                <input type="text" id="xh-email-cc" class="xh-modal-input" placeholder="Emails (Upto 5)">
                <input type="text" id="xh-email-subject" class="xh-modal-input" placeholder="Subject">
                <div id="xh-email-editor"></div>
            </div>
            <div class="xh-modal-footer">
                <button class="xh-btn-close">Close</button>
                <button class="xh-btn-send">Send Email</button>
            </div>
        </div>
    </div>
</div>
