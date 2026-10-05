document.addEventListener('DOMContentLoaded', () => {
  // Sticky Navbar Scroll Handler
  const header = document.querySelector('header.site-header');
  if (header) {
    window.addEventListener('scroll', () => {
      if (window.scrollY > 50) {
        header.classList.add('scrolled');
      } else {
        header.classList.remove('scrolled');
      }
    });
  }

  // Mobile Navigation Toggle
  const mobileToggle = document.querySelector('.mobile-toggle');
  const navMenu = document.querySelector('.nav-menu');
  if (mobileToggle && navMenu) {
    mobileToggle.addEventListener('click', () => {
      navMenu.classList.toggle('active');
    });
  }

  // Lightbox Functionality
  const galleryImages = document.querySelectorAll('.lightbox-trigger');
  if (galleryImages.length > 0) {
    const lightboxModal = document.createElement('div');
    lightboxModal.className = 'lightbox-modal';
    lightboxModal.style.cssText = `
      position:fixed;top:0;left:0;width:100%;height:100%;
      background:rgba(0,0,0,0.9);display:none;justify-content:center;
      align-items:center;z-index:2000;cursor:pointer;
    `;
    const lightboxImg = document.createElement('img');
    lightboxImg.style.cssText = 'max-width:90%;max-height:80%;border-radius:8px;';
    lightboxModal.appendChild(lightboxImg);
    document.body.appendChild(lightboxModal);

    galleryImages.forEach(img => {
      img.addEventListener('click', () => {
        lightboxImg.src = img.src;
        lightboxModal.style.display = 'flex';
      });
    });

    lightboxModal.addEventListener('click', () => {
      lightboxModal.style.display = 'none';
    });
  }

  // 2-Second Edge-to-Edge Hero Slider Controller
  const heroSlides = document.querySelectorAll('.hero-slide');
  const dotsContainer = document.getElementById('heroDots');

  if (heroSlides.length > 0) {
    let currentSlide = 0;
    const slideIntervalTime = 2000; // 2 Seconds Auto Play
    let slideTimer = null;

    heroSlides.forEach((_, idx) => {
      const dot = document.createElement('div');
      dot.classList.add('hero-dot');
      if (idx === 0) dot.classList.add('active');
      dot.addEventListener('click', () => goToHeroSlide(idx));
      if (dotsContainer) dotsContainer.appendChild(dot);
    });

    const dots = document.querySelectorAll('.hero-dot');

    function goToHeroSlide(index) {
      heroSlides[currentSlide].classList.remove('active');
      if (dots[currentSlide]) dots[currentSlide].classList.remove('active');

      currentSlide = (index + heroSlides.length) % heroSlides.length;

      heroSlides[currentSlide].classList.add('active');
      if (dots[currentSlide]) dots[currentSlide].classList.add('active');
    }

    function nextHeroSlide() {
      goToHeroSlide(currentSlide + 1);
    }

    function startHeroSlider() {
      slideTimer = setInterval(nextHeroSlide, slideIntervalTime);
    }

    const sliderWrapper = document.querySelector('.hero-slider-wrapper');
    if (sliderWrapper) {
      sliderWrapper.addEventListener('mouseenter', () => clearInterval(slideTimer));
      sliderWrapper.addEventListener('mouseleave', startHeroSlider);
    }

    startHeroSlider();
  }

  // 2-Second Animated Edge-to-Edge Cards Slider
  const cardTrack = document.getElementById('cardCarouselTrack');
  const cardPrevBtn = document.getElementById('cardPrevBtn');
  const cardNextBtn = document.getElementById('cardNextBtn');
  const cardDotsContainer = document.getElementById('cardDots');

  if (cardTrack) {
    const cardSlides = Array.from(cardTrack.children);
    let currentCardIndex = 0;
    const cardIntervalTime = 2000; // 2 Seconds Auto Play
    let cardTimer = null;

    function getVisibleCardsCount() {
      const width = window.innerWidth;
      if (width <= 550) return 1;
      if (width <= 800) return 2;
      if (width <= 1200) return 3;
      return 4;
    }

    function getMaxIndex() {
      return Math.max(0, cardSlides.length - getVisibleCardsCount());
    }

    // Dynamic dot setup
    function setupCardDots() {
      if (!cardDotsContainer) return;
      cardDotsContainer.innerHTML = '';
      const maxIdx = getMaxIndex();
      for (let i = 0; i <= maxIdx; i++) {
        const dot = document.createElement('div');
        dot.classList.add('card-dot');
        if (i === currentCardIndex) dot.classList.add('active');
        dot.addEventListener('click', () => {
          currentCardIndex = i;
          updateCardSlider();
          resetCardTimer();
        });
        cardDotsContainer.appendChild(dot);
      }
    }

    function updateCardSlider() {
      const visibleCount = getVisibleCardsCount();
      const slidePercent = 100 / visibleCount;
      cardTrack.style.transform = `translateX(-${currentCardIndex * slidePercent}%)`;

      if (cardDotsContainer) {
        const dots = Array.from(cardDotsContainer.children);
        dots.forEach((d, idx) => {
          d.classList.toggle('active', idx === currentCardIndex);
        });
      }
    }

    function nextCardSlide() {
      const maxIndex = getMaxIndex();
      if (currentCardIndex >= maxIndex) {
        currentCardIndex = 0;
      } else {
        currentCardIndex++;
      }
      updateCardSlider();
    }

    function prevCardSlide() {
      const maxIndex = getMaxIndex();
      if (currentCardIndex <= 0) {
        currentCardIndex = maxIndex;
      } else {
        currentCardIndex--;
      }
      updateCardSlider();
    }

    function startCardTimer() {
      cardTimer = setInterval(nextCardSlide, cardIntervalTime);
    }

    function resetCardTimer() {
      clearInterval(cardTimer);
      startCardTimer();
    }

    if (cardNextBtn) {
      cardNextBtn.addEventListener('click', () => {
        nextCardSlide();
        resetCardTimer();
      });
    }

    if (cardPrevBtn) {
      cardPrevBtn.addEventListener('click', () => {
        prevCardSlide();
        resetCardTimer();
      });
    }

    cardTrack.addEventListener('mouseenter', () => clearInterval(cardTimer));
    cardTrack.addEventListener('mouseleave', startCardTimer);

    window.addEventListener('resize', () => {
      setupCardDots();
      if (currentCardIndex > getMaxIndex()) {
        currentCardIndex = getMaxIndex();
      }
      updateCardSlider();
    });

    setupCardDots();
    updateCardSlider();
    startCardTimer();
  }
});