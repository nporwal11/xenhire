document.addEventListener('DOMContentLoaded', function() {
  const heroImageContainer = document.querySelector('.hero-image');
  const heroImg = heroImageContainer.querySelector('img');

  if (heroImageContainer && heroImg) {
    heroImageContainer.addEventListener('click', function() {
      // Prevent multiple clicks
      if (this.classList.contains('playing')) return;
      this.classList.add('playing');

      // Create iframe element
      const iframe = document.createElement('iframe');
      iframe.src = 'https://www.youtube.com/embed/wcNVIZrqUIs?si=15nrUPXOShi2wGBI&autoplay=1&controls=0&rel=0';        
      iframe.title = "YouTube video player";
      iframe.frameBorder = "0";
      iframe.allow = "accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share";
      iframe.referrerPolicy = "strict-origin-when-cross-origin";
      iframe.allowFullscreen = true;
      
      iframe.style.width = '513px';
      iframe.style.borderRadius = '16px';
      iframe.style.position = 'absolute';
      iframe.style.top = '50%';
      iframe.style.left = '0';
      iframe.style.right = '0';
      iframe.style.transform = 'translateY(-50%)';
      iframe.style.margin = '0 auto';
      iframe.style.height = '322px';
      iframe.style.zIndex = '1'; 
      // Start hidden for fade-in
      iframe.style.opacity = '0';
      iframe.style.transition = 'opacity 0.8s ease-in-out';
      
      this.appendChild(iframe);

      // Trigger fade in/out
      requestAnimationFrame(() => {
          // Fade poster out
          heroImageContainer.classList.add('video-active');
          // Fade video in
          iframe.style.opacity = '1';
      });
    });
  }
});
