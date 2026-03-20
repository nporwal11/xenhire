<?php
if (!defined('ABSPATH')) exit;

// phpcs:ignore WordPress.Security.NonceVerification.Recommended
$xenhire_job_id = isset(sanitize_text_field($_GET['id'])) ? intval(sanitize_text_field($_GET['id'])) : 0;
$xenhire_is_new = ($xenhire_job_id <= 0);
?>
<div class="wrap xenhire-job-add-page xenhire-common">
  <div class="xenhire-header xenhire-start">
    <a href="<?php echo esc_url(admin_url('admin.php?page=xenhire-jobs')); ?>" class="xenhire-back">
      <!-- <i class="ki-duotone ki-arrow-left ki-fs-2"><span class="path1"></span><span class="path2"></span></i> -->
       <i class="ki-duotone ki-left ki-fs-1"></i>
    </a>
    <h1 class="xj-title">
        <?php echo $xenhire_is_new ? 'Add New Job' : 'Edit Job'; ?>
        <span id="xj-header-job-title" class="text-muted fs-5 fw-normal ms-2"></span>
    </h1>
  </div>

  <div class="xenhire-container">
    <div class="xh-card xh-card-form">
      <div class="xj-tabs">
        <button class="xj-tab-btn active" data-tab="details">Job Details</button>
        <button class="xj-tab-btn" data-tab="questions">Interview Questions</button>
      </div>

      <div class="xj-content">
        <!-- Job Details -->
        <div id="tab-details" class="xj-pane active">
          <form id="xj_job_form" class="xj-form">
            <input type="hidden" id="RequirementID" value="<?php echo esc_attr($xenhire_job_id); ?>"/>

            <div class="xj-grid">
              <!-- Employer -->
              <div class="xj-field employers">
                <label class="xj-label required">Employer</label>
                <div id="ct-EmployerID" data-class="xj-select">
          <!-- CBO will be loaded here -->
      </div>
    </div>
  </div>

            <!-- Job title -->
            <div class="xj-field">
              <label class="xj-label required">Job Title</label>
              <input type="text" id="JobTitle" class="xj-input" placeholder="Job Title">
            </div>

            <!-- Work experience -->
            <div class="xj-field">
              <label class="xj-label">
                Work Experience
                <small class="xj-muted">in years</small>
              </label>
              <div class="xj-range">
                <select id="WorkExMin" class="xj-select">
                  <option value="0">Fresher</option>
                  <?php for ($xenhire_i = 1; $xenhire_i <= 20; $xenhire_i++): ?>
                    <option value="<?php echo esc_attr($xenhire_i); ?>"><?php echo esc_html($xenhire_i); ?> Year<?php echo $xenhire_i > 1 ? 's' : ''; ?></option>
                  <?php endfor; ?>
                </select>
                <span class="xj-range-sep">to</span>
                <select id="WorkExMax" class="xj-select">
                  <option value="0">Fresher</option>
                  <?php for ($xenhire_i = 1; $xenhire_i <= 20; $xenhire_i++): ?>
                    <option value="<?php echo esc_attr($xenhire_i); ?>"><?php echo esc_html($xenhire_i); ?> Year<?php echo $xenhire_i > 1 ? 's' : ''; ?></option>
                  <?php endfor; ?>
                </select>
              </div>
            </div>

            <!-- Job Description (TinyMCE) -->
            <div class="xj-field" style="flex-direction:column">
              <!-- <div class="xj-label-row"> -->
                <label class="xj-label required" style="margin-bottom:15px;">Job Description</label>
              <!-- </div> -->
              <textarea id="JobDescription" name="JobDescription" class="xj-textarea tinymce-job"></textarea>
            </div>

           <!-- Job Responsibilities (TinyMCE) -->
            <div class="xj-field" style="flex-direction:column">
              <label class="xj-label required" style="margin-bottom:15px;">Job Responsibilities</label>
              <textarea id="JobRole" name="JobRole" class="xj-textarea tinymce-job"></textarea>
            </div>
            <!-- Required Skills -->
            <div class="xj-field ">
              <label class="xj-label required">Required Skills</label>
              <textarea id="Keywords" rows="5" class="xj-textarea" placeholder="Required Skills (comma separated)"></textarea>
            </div>

            <!-- Salary -->
            <div class="xj-field">
              <label class="xj-label">Salary</label>
              <div style="flex-direction: column; display: flex; width: 66.66666667%;">
              <div class="xj-salary">
                <div id="ct-CurrencyID" data-class="xj-select">
                    <!-- CBO will be loaded here -->
                </div>
                <input type="text" id="SalaryFrom" name="SalaryFrom" class="form-control form-control-lg form-control-solid mb-3">
                <span class="xj-range-sep">to</span>
                <input type="text" id="SalaryTo" name="SalaryTo" class="form-control form-control-lg form-control-solid mb-3">
                <select id="SalaryType" class="xj-select">
                  <option value="1">yearly</option>
                  <option value="2">monthly</option>
                  <option value="3">weekly</option>
                  <option value="4">hourly</option>
                </select>
              </div>
              <div style="flex-direction: column;display: flex;">
                <input type="text" id="SalaryText" name="SalaryText" class="form-control form-control-lg form-control-solid mb-3" value="Negotiable" style="width:100%; display:none;">
                <label class="xj-switch" style="margin-top:8px;">
                  <input type="checkbox" id="IsSalaryHidden">
                  <span class="box"></span>
                  <span>Hide Salary</span>
                </label>
              </div>
                  </div>
            </div>

            <!-- Department -->
            <div class="xj-field">
              <label class="xj-label">Department</label>
              <input type="text" id="FunctionalArea" name="FunctionalArea" class="form-control form-control-lg form-control-solid mb-3" placeholder="">
            </div>

            <!-- Employment Type -->
            <div class="xj-field">
              <label class="xj-label required">Employment Type</label>
              <div id="ct-EmploymentTypeID" data-class="xj-select">
        <!-- CBO will be loaded here -->
    </div>
            </div>

            <!-- City -->
            <div class="xj-field">
              <label class="xj-label">City</label>
              <div style="display:flex; flex-direction: column; width: 66.66666667%;">
                <div class="show-city">
                  <input type="text" id="City" name="City" class="form-control form-control-lg form-control-solid mb-3" placeholder="" style="width:100%;">                  
                </div>
                <div class="show-city d-none">
                  <input type="text" id="CityText" name="CityText" class="form-control form-control-lg form-control-solid mb-3" value="Remote" style="width:100%;">
                </div>
                  <label class="xj-switch" style="margin-top:8px;">
                    <input type="checkbox" id="IsCityHidden">
                    <span class="box"></span>
                    <span>Allow remote working</span>
                  </label>
                </div>
            </div>

            <!-- <div class="xj-divider xj-col-2"></div> -->

            <!-- Additional Settings Accordion -->
            <div class="xj-acc xj-col-2">
              <button type="button" class="xj-acc-hd" data-toggle="xj-acc-extra">
                <i class="ar ki-duotone ki-arrow-right ki-fs-2"><span class="path1"></span><span class="path2"></span></i>
                <span class="t">Additional Settings</span>
                <!-- <span class="b">Optional</span> -->
              </button>
              <div id="xj-acc-extra" class="xj-acc-bd">
                <!-- Intro Video -->
                <div class="xj-field column">
                  <label class="xj-label">
                    Introduction Video
                    <small class="xj-muted">You can record or upload the video</small>
                  </label>
                  <div class="xj-video-actions" style="display:flex; gap:10px;">
                    <button type="button" class="xh-btn xh-btn-primary" style="margin-top:0;" id="xj_upload_video">Upload Video</button>
                    <button type="button" class="xh-btn xh-btn-danger" id="xj_play_video" style="display:none;">
                      <span class="dashicons dashicons-controls-play"></span>Play Video
                    </button>
                    <button type="button" class="xh-btn xh-btn-secondary" id="xj_delete_video" style="display:none; color: #f1416c; background: #fff5f8;">
                        <i class="ki-duotone ki-trash ki-fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                    </button>
                  </div>
                  <input type="hidden" id="JobIntroVideoURL" value="">
                </div>

                <!-- Deadline -->
                <div class="xj-field column">
                  <label class="xj-label ">
                    Last date of application
                    <small class="xj-muted">Hide job listing after this date</small>
                  </label>
                  <input type="date" id="DeadlineDatestamp" class="xj-input" min="<?php echo esc_attr(gmdate('Y-m-d')); ?>">
                </div>

                <!-- Contact Email -->
                <div class="xj-field column">
                  <label class="xj-label">
                    Contact Email
                    <small class="xj-muted">Email shown on job details page</small>
                  </label>
                  <input type="text" id="EmailMain" class="xj-input" placeholder="">
                </div>

                <!-- Alert Emails -->
                <div class="xj-field column">
                  <label class="xj-label">
                    Emails to receive job application alerts
                    <small class="xj-muted">Separate multiple emails with comma (max 5)</small>
                  </label>
                  <textarea id="EmailCC" rows="3" class="xj-textarea" placeholder=""></textarea>
                </div>

                <!-- Contact Phone -->
                <div class="xj-field column">
                  <label class="xj-label">
                    Contact Phone
                    <small class="xj-muted">Phone shown on job details page</small>
                  </label>
                  <input type="text" id="PhoneMain" class="xj-input" placeholder="">
                </div>

                <!-- Social media preview image -->
                <div class="xj-field column">
                    <label class="xj-label">
                        Social media preview image
                        <small class="xj-muted">Accepted formats: .png, .jpg. Max size: 4 MB.</small>
                    </label>
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
                    </div>
                </div>

                <!-- Upload Resume -->
                <div class="xj-field column">
                  <label class="xj-label">
                    Upload Resume
                    <small class="xj-muted">Candidate will be asked to upload resume on job application</small>
                  </label>
                  <label class="xj-switch">
                    <input type="checkbox" id="IsUploadResume" name="IsUploadResume" checked>
                    <span class="box"></span>
                  </label>
                </div>

                <!-- Interview after application -->
                <div class="xj-field column">
                  <label class="xj-label">
                    Interview after application
                    <small class="xj-muted">Should have interview questions</small>
                  </label>
                  <label class="xj-switch">
                    <input type="checkbox" id="IsInterview" name="IsInterview" checked>
                    <span class="box"></span>
                  </label>
                </div>

                <!-- Conduct realtime interview -->
                <div class="xj-field column">
                  <label class="xj-label">
                    Conduct realtime interview
                    <small class="xj-muted">Use contextual interview questions (Beta)</small>
                  </label>
                  <label class="xj-switch">
                    <input type="checkbox" id="IsInterviewRealtime" name="IsInterviewRealtime">
                    <span class="box"></span>
                  </label>
                </div>

                <!-- Score interview answers using AI -->
                <div class="xj-field column">
                  <label class="xj-label">
                    Score interview answers using AI
                    <small class="xj-muted">If checked, AI will score interview answers</small>
                  </label>
                  <label class="xj-switch">
                    <input type="checkbox" id="IsEnableAIScoring" name="IsEnableAIScoring" checked>
                    <span class="box"></span>
                  </label>
                </div>

                <!-- Allow multiple attempts for interview questions -->
                <div class="xj-field column">
                  <label class="xj-label">
                    Allow multiple attempts for interview questions
                    <small class="xj-muted">Candidates will be allowed to re-record answers</small>
                  </label>
                  <label class="xj-switch">
                    <input type="checkbox" id="IsInterviewVideoRedoAllowed" name="IsInterviewVideoRedoAllowed">
                    <span class="box"></span>
                  </label>
                </div>
                <!-- Show number of applications -->
                <div class="xj-field column">
                  <label class="xj-label">
                    Show number of applications
                    <small class="xj-muted">Shows number of candidates already applied</small>
                  </label>
                  <label class="xj-switch">
                    <input type="checkbox" id="IsShowApplicationsCount" checked>
                    <span class="box"></span>
                  </label>
                </div>

                <!-- Auto start seconds -->
                <div class="xj-field column">
                  <label class="xj-label">
                    Auto start seconds
                    <small class="xj-muted">Interview will auto start in given seconds</small>
                  </label>
                  <input type="text" id="AutoStartInSeconds" class="xj-input" placeholder="">
                </div>
              </div>
            </div>

            <!-- <div class="xj-divider xj-col-2"></div> -->

            <!-- Extra candidate info accordion -->
            <div class="xj-acc xj-col-2">
              <button type="button" class="xj-acc-hd" data-toggle="xj-acc-extra-data">
                <i class="ar ki-duotone ki-arrow-right ki-fs-2"><span class="path1"></span><span class="path2"></span></i>
                <span class="t">Extra Information to be asked from candidate</span>
              </button>
              <div id="xj-acc-extra-data" class="xj-acc-bd">
                <?php for ($xenhire_i = 1; $xenhire_i <= 4; $xenhire_i++): ?>
                  <div class="xj-field xj-col-2" style="margin-bottom:0">
                    <div class="xj-grid">
                      <div class="xj-field">
                        <input type="text" class="xj-input" placeholder="Label" id="ExtraData<?php echo esc_attr($xenhire_i); ?>Label">
                      </div>
                      <div class="xj-field">
                        <select id="ExtraData<?php echo esc_attr($xenhire_i); ?>Type" class="xj-select extra-type">
                          <option value="0">Select Type</option>
                          <option value="1">Short Text</option>
                          <option value="2">Long Text</option>
                          <option value="3">Single Select</option>
                          <option value="4">Date</option>
                        </select>
                      </div>
                      <div class="xj-field" id="ExtraData<?php echo esc_attr($xenhire_i); ?>OptionsContainer" style="display:none;">
                        <input type="text" class="xj-input" id="ExtraData<?php echo esc_attr($xenhire_i); ?>Options" placeholder="Press enter to add more options">
                      </div>
                      <div class="xj-field xj-mandatory">
                        <label class="xj-switch">
                          <input type="checkbox" id="ExtraData<?php echo esc_attr($xenhire_i); ?>Mandatory">
                          <span class="box"></span>
                          <span>Mandatory</span>
                        </label>
                      </div>
                    </div>
                  </div>
                <?php endfor; ?>
              </div>
            </div>          

            <div class="xj-actions">
              <button type="submit" id="xj_save" class="xj-btn primary">
                <span class="lbl">Save & Add Questions</span>
                <span class="prg">Please wait<span class="xj-spin"></span></span>
              </button>
            </div>
          </div>
        </form>
      

      <!-- Interview Questions -->
        <div id="tab-questions" class="xj-pane">
          <!-- Warning Message -->
          <div id="xj-ques-warning" class="xenhire-alert xenhire-alert-primary" style="display:none;">
            <div class="xenhire-alert-icon">
                <i class="ki-duotone ki-notification-bing ki-fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>
            </div>
            <div class="xenhire-alert-content">
                <h4 class="xenhire-alert-title">Modifications Not Allowed</h4>
                <p class="xenhire-alert-text">Changes or deletions cannot be made as there are active interviews already present for this job.</p>
            </div>
          </div>
          <div class="xj-q-head">
            <!-- <button type="button" id="xj-ai-ques" class="xj-btn ai">
              <span class="lbl">Suggest Questions</span>
              <span class="prg">Please wait<span class="xj-spin"></span></span>
            </button> -->
            <button type="button" id="xj-add-ques" class="xj-btn primary xj-btn-small">Add Question Manually</button>
          </div>

          <div class="xj-q-wrap">
            <table class="xj-table">
              <thead id="tHead">
                <tr>
                  <th style="width: 30px;"></th>
                  <th style="width: 80px;">POSITION</th>
                  <th style="width: 100px;">TYPE</th>
                  <th>QUESTION</th>
                  <th style="width: 160px; text-align:right;">ACTIONS</th>
                </tr>
              </thead>
              <tbody id="tBody"></tbody>
            </table>
          </div>

          <div class="xj-empty" style="display:none">
            <div class="ico">📝</div>
            <h3>No Questions Added Yet</h3>
            <p>Use Add Question Manually to add questions.</p>
            <!-- <p>Use AI to auto-generate starter questions, then refine or add your own.</p> -->
            <!-- <button type="button" id="xj-ai-ques-empty" class="xj-btn ai">
              <span class="lbl">Suggest Questions with AI</span>
              <span class="lbl">Suggest Questions with AI</span>
              <span class="prg">Please wait<span class="xj-spin"></span></span>
            </button> -->
          </div>

          <div class="xj-actions">
            <button type="button" id="xj_publish" class="xj-btn primary">
              <span class="lbl">Save & Publish</span>
              <span class="prg">Publishing<span class="xj-spin"></span></span>
            </button>
          </div>
        </div>
</div>
    </div>
  </div>
</div>









<!-- Modal: Add/Edit Question -->
<div class="modal fade" id="kt_modal_ques" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-600px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold modal-title" id="headTitle">Add Interview Question</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-lg-10 px-lg-10">
                <form class="form" id="kt_modal_ques_form">
                    <input type="hidden" id="ID" name="ID" value="-1" />
                    <div class="fv-row mb-7">
                        <label class="required fs-6 fw-semibold mb-2">Question Type</label>
                        <div id="ct-QuestionTypeID"></div>
                    </div>
                    <div class="fv-row mb-7">
                        <label class="required fs-6 fw-semibold mb-2">Question</label>
                        <textarea class="form-control form-control-solid" rows="3" name="Name" id="Name" placeholder="Question"></textarea>
                    </div>
                    <div class="fv-row mb-7 d-none" id="divDescription">
                        <label class="fs-6 fw-semibold mb-2">Description</label>
                        <textarea class="form-control form-control-solid" rows="3" name="Description" id="Description" placeholder="Description"></textarea>
                    </div>
                    <div class="fv-row mb-7" id="divAnswerTime">
                        <label class="required fs-6 fw-semibold mb-2">Answer Time</label>
                        <select class="form-select form-select-solid" name="MaxSeconds" id="MaxSeconds">
                            <option value="60">1 minute</option>
                            <option value="120" selected>2 minutes</option>
                            <option value="180">3 minutes</option>
                            <option value="300">5 minutes</option>
                        </select>
                    </div>
                    <div class="fv-row mb-7">
                        <div class="form-check form-switch form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" value="1" id="IsNotAIScore" />
                            <label class="form-check-label fw-semibold text-gray-400 ms-3" for="IsNotAIScore">Do not calculate AI score</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="xj-btn secondary" data-bs-dismiss="modal">Discard</button>
                <button type="button" class="xj-btn primary" id="kt_modal_ques_submit">Submit</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Job Published / Instruction -->

<div class="modal fade" id="kt_modal_instruction" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-600px">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="fw-bold modal-title">See your job interview in practice!</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                  <p>Congratulations on creating a new job! You can now test the interview internally with your team or share it with candidates.!</p>
                  <div class="xh-box-wrap">
                    <div class="xh-practice-box">
                      <p class="xh-modal-title"><i class="ki-outline ki-eye ki-fs-3">
                            </i>Try job interview</p>
                      <p class="xh-modal-text">Review how the job listing looks and try out the job interview.</p>
                      <button type="button" class="xh-btn secondary" id="btnPreview">Try yourself</button>
                    </div>
                    <div class="xh-practice-box">
                      <p class="xh-modal-title"><i class="ki-outline ki-message-text-2 ki-fs-3">
                            </i>Invite candidates</p>
                      <p class="xh-modal-text">Invite candidates to take this apply and appear for job interview.</p>
                      <button type="button" class="xh-btn primary" id="btnInvite">Invite</button>
                    </div>
                  </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="xh-btn light" data-bs-dismiss="modal">I'll do it later</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Invite Candidates -->
<div class="modal fade" id="kt_modal_invite" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-800px">
        <div class="modal-content">
            <!-- <div class="modal-header pb-0 border-0 justify-content-end">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div> -->
            <div class="modal-header border-0 pt-0">
                <h2 class="fw-bold modal-title">Invite Candidates</h2>
            </div>
            <div class="modal-body pt-0">
                <ul class="nav nav-tabs nav-line-tabs mb-5 fs-6">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="tab" href="#kt_tab_share_link">Share link</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="tab" href="#kt_tab_invite_email">Invite by email</a>
                    </li>
                </ul>

                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane fade show active" id="kt_tab_share_link" role="tabpanel">
                        <p class="xh-tab-text">Invite candidates by sharing a public link in your job post. This link will direct them to the job listing where they can apply and appear for the interview.</p>
                        
                        <div class="d-flex invite-copy position-relative">
                            <input type="text" id="InviteJobLink" class="form-control form-control-solid me-3" readonly value="" />
                            <button type="button" id="btnCopyLink" class="xh-btn primary">
                                <i class="ki-outline ki-copy ki-fs-2"><span class="path1"></span><span class="path2"></span></i> Copy Link
                            </button>
                            
                            <div class="dropdown">
                                <button type="button" id="btnShareLink" class="xh-btn info dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false" style="background-color: #7239ea;">
                                    <i class="ki-outline ki-share ki-fs-2"></i> Share
                                </button>
                                <ul class="dropdown-menu share-dropdown-menu" aria-labelledby="btnShareLink">
                                    <li>
                                        <a class="dropdown-item share-item linkedin" href="#" target="_blank">
                                            <span class="dashicons dashicons-linkedin me-3"></span>
                                            <span class="fw-semibold">LinkedIn</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item share-item facebook" href="#" target="_blank">
                                            <span class="dashicons dashicons-facebook me-3"></span>
                                            <span class="fw-semibold">Facebook</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item share-item whatsapp" href="#" target="_blank">
                                            <span class="dashicons dashicons-whatsapp me-3"></span>
                                            <span class="fw-semibold">WhatsApp</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item share-item twitter" href="#" target="_blank">
                                            <span class="dashicons dashicons-twitter me-3"></span>
                                            <span class="fw-semibold">Twitter</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <div class="">
                            <p class="xh-tab-text">Copy and add this text to your job post:</p>
                            <textarea id="InviteText" name="InviteText" class="xj-textarea"></textarea>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="kt_tab_invite_email" role="tabpanel">
                        <div class="xh-inv-gap">
                            <input type="text" id="InviteCCEmails" class="form-control form-control-solid" placeholder="CC Emails (Upto 5)" />
                            <p class="xh-cc-text">Press enter(↵) after adding email</p>
                        </div>

                        <div class="xh-inv-gap">
                            <input type="text" id="InviteSubject" class="form-control form-control-solid" readonly />
                        </div>

                        <div class="xh-inv-gap">
                            <textarea id="InviteEmailBody" name="InviteEmailBody" class="xj-textarea"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="xh-btn light" id="btnInviteClose" data-bs-dismiss="modal">Close</button>
                <button type="button" class="xh-btn primary d-none" id="btnInviteSubmit">Submit</button>
            </div>
        </div>
    </div>
</div>

<!-- Video Upload/Record Modal -->
<div id="xj_video_modal" class="xb-modal" style="display:none;">
    <div class="xb-modal-content">
        <div class="xb-modal-header">
            <h2>Introduction Video</h2>
            <span class="xb-close">&times;</span>
        </div>
        <div class="xb-modal-body">
            
            <!-- File Upload Section -->
            <div class="xb-video-section">
                <div class="xb-file-input-wrapper">
                    <label for="xj_video_file" class="button">Choose file</label>
                    <span id="xj_file_name">No file chosen</span>
                    <input type="file" id="xj_video_file" accept="video/*" style="display:none;">
                </div>
                <div class="upload-inst">
                    <p class="description">max file size: 40 MB</p>
                    <button type="button" class="xh-btn-primary" id="xj_save_uploaded_video" disabled>Save Video</button>
                </div>
            </div>

            <div class="xb-divider-text">Or</div>

            <!-- Recording Section -->
            <div class="xb-video-section">
                <div class="xb-video-recorder-wrapper">
                    <video id="xj_recorder_preview" autoplay muted playsinline></video>
                    <div id="xj_recorder_placeholder">
                        <span class="dashicons dashicons-video-alt3" style="font-size: 64px; width: 64px; height: 64px; color: #ccc;"></span>
                    </div>
                </div>
                
                <div class="xb-recorder-controls">
                    <button type="button" class="xh-btn-success" id="xj_start_recording">
                        <span class="dashicons dashicons-controls-play"></span> Start Recording
                    </button>
                    <button type="button" class="xh-btn-danger" id="xj_stop_recording">
                        <span class="dashicons dashicons-controls-pause"></span> Stop Recording
                    </button>
                    <span id="xj_timer" style="display:none; margin-left: 10px; color: #99a1b7;font-weight: 500;">00:00 / 03:00</span>
                    
                    <button type="button" class="xh-btn-primary" id="xj_save_recorded_video" style="display:none; float: right;">Save Video</button>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Video Playback Modal -->
<div id="xj_playback_modal" class="xb-modal" style="display:none;">
    <div class="xb-modal-content" style="max-width: 800px;">
        <div class="xb-modal-header">
            <h2>Video Preview</h2>
            <span class="xb-close-playback" style="cursor:pointer; font-size:28px;">&times;</span>
        </div>
        <div class="xb-modal-body" style="padding:1.35rem;">
            <video id="xj_playback_video" controls style="width:100%; display:block; border-radius: .475rem;"></video>
        </div>
    </div>
</div>