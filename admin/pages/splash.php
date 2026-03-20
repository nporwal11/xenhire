<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="xh-splash-container">
  <!-- Logo -->
  <div class="logo">
    <img src="<?php echo esc_url(XENHIRE_PLUGIN_URL . 'public/images/xenhire-logo.png'); ?>" height="60" alt="">
</div>
  <!-- Hero Section -->
  <section class="hero">
    <!-- Left -->
     <div class="hero-image">
      <img src="<?php echo esc_url(XENHIRE_PLUGIN_URL . 'public/images/xenhire-dashboard.png'); ?>" alt="xenhire-dashboard" />
      <div class="video-play-icon">
        <svg xmlns="" viewBox="0 0 24 24" fill="currentColor">
          <path d="M8 5v14l11-7z" />
        </svg>
      </div>
    </div>   

    <!-- Right -->
    <div>
      <h1><span>AI-Powered Video Interview</span><br> Platform for Smart Hiring</h1> 
      <p class="desc" style="margin-bottom:0;">Xenhire is an AI-powered video interview platform designed to help businesses streamline their hiring process. It enables recruiters to conduct video interviews, automate candidate screening, and gain AI-driven insights for better hiring decisions.</p>
      <p class="desc">With Xenhire, you can easily create job listings and career pages directly on your website, attract qualified candidates, and reduce time-to-hire. The platform helps save time, effort, and resources while improving overall recruitment efficiency.</p>
      <a href="<?php echo esc_url(admin_url('admin.php?page=xenhire&action=login')); ?>" class="btn">GET STARTED</a>
    </div>
  </section>
  <div class="features">
        <div class="feature">
          <img src="<?php echo esc_url(XENHIRE_PLUGIN_URL . 'public/images/video-interview.png'); ?>" height="50" alt="">
          <span>Video Interviews</span>
        </div>
        <div class="feature">
          <img src="<?php echo esc_url(XENHIRE_PLUGIN_URL . 'public/images/ai-interview-question.png'); ?>" height="50" alt="">
          <span>Al Interview Questions</span>
        </div>
        <div class="feature">
          <img src="<?php echo esc_url(XENHIRE_PLUGIN_URL . 'public/images/ai-scoring-and-feedback.png'); ?>" height="50" alt="">
          <span>AI Scoring & Feedback</span>
        </div>
        <div class="feature">
          <img src="<?php echo esc_url(XENHIRE_PLUGIN_URL . 'public/images/job-sharing.png'); ?>" height="50" alt="">
          <span>Job Sharing</span>
        </div>
        <div class="feature">
          <img src="<?php echo esc_url(XENHIRE_PLUGIN_URL . 'public/images/resumeparsing.png'); ?>" height="50" alt="">
          <span>Resume Parsing</span>
        </div>
      </div>
</div>






