<?php
if (!defined('ABSPATH')) exit;

// Get jobs from API
$xenhire_jobs_result = XenHire_API::public_call('List_Jobs', array());
?>

<div class="xenhire-jobs-container">
    <h1>Job Openings</h1>

    <?php if ($xenhire_jobs_result['success']): 
        $xenhire_jobs = json_decode($xenhire_jobs_result['data'], true);

        if (!empty($xenhire_jobs)):
            foreach ($xenhire_jobs as $xenhire_job):
    ?>
        <div class="xenhire-job-card">
            <h2 class="xenhire-job-title"><?php echo esc_html($xenhire_job['Title'] ?? 'Job Position'); ?></h2>
            <div class="xenhire-job-meta">
                <span><?php echo esc_html($xenhire_job['Location'] ?? 'Location'); ?></span> • 
                <span><?php echo esc_html($xenhire_job['Type'] ?? 'Full-time'); ?></span>
            </div>
            <div class="xenhire-job-description">
                <?php echo wp_kses_post(substr($xenhire_job['Description'] ?? '', 0, 200)); ?>...
            </div>
            <a href="#" class="xenhire-job-btn">Apply Now</a>
        </div>
    <?php 
            endforeach;
        else:
    ?>
        <p>No jobs available at the moment.</p>
    <?php 
        endif;
    else: 
    ?>
        <div class="notice notice-error">
            <p><?php echo esc_html($xenhire_jobs_result['message']); ?></p>
        </div>
    <?php endif; ?>
</div>
