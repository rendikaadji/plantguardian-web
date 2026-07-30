/**
 * 3D Parallax Card Tilt & Lighting Effect Module
 * High-performance 60fps card tilt effect using requestAnimationFrame
 */

export class ThreeDCardTilt {
  constructor(selector = '.js-tilt-3d', options = {}) {
    this.elements = typeof selector === 'string' ? document.querySelectorAll(selector) : selector;
    this.maxTilt = options.maxTilt || 15; // Max degree rotation
    this.perspective = options.perspective || 1000;
    this.scale = options.scale || 1.03;
    this.init();
  }

  init() {
    this.elements.forEach(card => {
      card.style.transformStyle = 'preserve-3d';

      // Create dynamic 3D lighting reflection overlay
      const glare = document.createElement('div');
      glare.className = 'glare-overlay pointer-events-none absolute inset-0 rounded-3xl opacity-0 transition-opacity duration-300';
      glare.style.background = 'radial-gradient(circle at 50% 50%, rgba(52, 211, 153, 0.25) 0%, transparent 70%)';
      card.appendChild(glare);

      let ticking = false;

      const updateTilt = (e) => {
        const rect = card.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;

        const centerX = rect.width / 2;
        const centerY = rect.height / 2;

        const percentX = (x - centerX) / centerX;
        const percentY = (y - centerY) / centerY;

        const rotateX = (-percentY * this.maxTilt).toFixed(2);
        const rotateY = (percentX * this.maxTilt).toFixed(2);

        card.style.transform = `perspective(${this.perspective}px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale3d(${this.scale}, ${this.scale}, ${this.scale})`;
        
        // Update glare position
        glare.style.opacity = '1';
        glare.style.background = `radial-gradient(circle at ${x}px ${y}px, rgba(52, 211, 153, 0.2) 0%, transparent 60%)`;

        ticking = false;
      };

      card.addEventListener('mousemove', (e) => {
        if (!ticking) {
          window.requestAnimationFrame(() => updateTilt(e));
          ticking = true;
        }
      });

      card.addEventListener('mouseleave', () => {
        card.style.transition = 'transform 0.5s cubic-bezier(0.2, 0.8, 0.2, 1)';
        card.style.transform = `perspective(${this.perspective}px) rotateX(0deg) rotateY(0deg) scale3d(1, 1, 1)`;
        glare.style.opacity = '0';

        setTimeout(() => {
          card.style.transition = '';
        }, 500);
      });

      card.addEventListener('mouseenter', () => {
        card.style.transition = 'none';
      });
    });
  }
}

export default ThreeDCardTilt;
