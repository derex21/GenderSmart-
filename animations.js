// Initialize GSAP ScrollTrigger
gsap.registerPlugin(ScrollTrigger);

// Mobile Menu Toggle Functionality
function toggleMenu() {
    const nav = document.querySelector('nav');
    const menuToggle = document.querySelector('.menu-toggle');
    const authButtons = document.querySelector('.auth-buttons');
    
    nav.classList.toggle('active');
    
    // Add mobile auth buttons to nav when menu is open
    if (nav.classList.contains('active')) {
        if (authButtons && !authButtons.classList.contains('mobile')) {
            const mobileAuthButtons = authButtons.cloneNode(true);
            mobileAuthButtons.classList.add('mobile');
            nav.appendChild(mobileAuthButtons);
        }
        menuToggle.innerHTML = '✕';
    } else {
        const mobileAuthButtons = nav.querySelector('.auth-buttons.mobile');
        if (mobileAuthButtons) {
            mobileAuthButtons.remove();
        }
        menuToggle.innerHTML = '☰';
    }
}

// Close mobile menu when clicking outside
document.addEventListener('click', (e) => {
    const nav = document.querySelector('nav');
    const menuToggle = document.querySelector('.menu-toggle');
    
    if (nav.classList.contains('active') && 
        !nav.contains(e.target) && 
        !menuToggle.contains(e.target)) {
        toggleMenu();
    }
});

// Close mobile menu when window is resized to desktop
window.addEventListener('resize', () => {
    const nav = document.querySelector('nav');
    const menuToggle = document.querySelector('.menu-toggle');
    
    if (window.innerWidth > 768 && nav.classList.contains('active')) {
        nav.classList.remove('active');
        menuToggle.innerHTML = '☰';
        const mobileAuthButtons = nav.querySelector('.auth-buttons.mobile');
        if (mobileAuthButtons) {
            mobileAuthButtons.remove();
        }
    }
});

// Animate sections on scroll
document.addEventListener('DOMContentLoaded', () => {
    // Hero section animations
    gsap.from('.hero-text', {
        scrollTrigger: {
            trigger: '.hero',
            start: 'top center',
            toggleActions: 'play none none reverse'
        },
        opacity: 0,
        y: 100,
        duration: 1.2,
        ease: "power3.out"
    });

    gsap.from('.floating-cards .hero-card', {
        scrollTrigger: {
            trigger: '.hero-visual',
            start: 'top center',
            toggleActions: 'play none none reverse'
        },
        opacity: 0,
        y: 50,
        scale: 0.8,
        duration: 1,
        stagger: 0.2,
        ease: "back.out(1.7)"
    });

    // Features section animations
    gsap.from('.features h2', {
        scrollTrigger: {
            trigger: '.features',
            start: 'top center+=100',
            toggleActions: 'play none none reverse'
        },
        opacity: 0,
        y: 50,
        duration: 1,
        ease: "power3.out"
    });

    gsap.from('.feature-card', {
        scrollTrigger: {
            trigger: '.features-grid',
            start: 'top center+=100',
            toggleActions: 'play none none reverse'
        },
        opacity: 0,
        y: 80,
        rotation: 5,
        duration: 1,
        stagger: 0.2,
        ease: "power3.out"
    });

    // Programs section animations
    gsap.from('.programs h2', {
        scrollTrigger: {
            trigger: '.programs',
            start: 'top center+=100',
            toggleActions: 'play none none reverse'
        },
        opacity: 0,
        y: 50,
        duration: 1,
        ease: "power3.out"
    });

    gsap.from('.program-card', {
        scrollTrigger: {
            trigger: '.program-cards',
            start: 'top center+=100',
            toggleActions: 'play none none reverse'
        },
        opacity: 0,
        y: 100,
        scale: 0.9,
        duration: 1.2,
        stagger: 0.3,
        ease: "power3.out"
    });

    // ICE Materials section animations
    gsap.from('.ice-materials h2', {
        scrollTrigger: {
            trigger: '.ice-materials',
            start: 'top center+=100',
            toggleActions: 'play none none reverse'
        },
        opacity: 0,
        y: 50,
        duration: 1,
        ease: "power3.out"
    });

    gsap.from('.flyer-card', {
        scrollTrigger: {
            trigger: '.flyer-gallery',
            start: 'top center+=100',
            toggleActions: 'play none none reverse'
        },
        opacity: 0,
        y: 80,
        rotation: -5,
        duration: 1,
        stagger: 0.2,
        ease: "power3.out"
    });

    // VAWC Education section animations
    gsap.from('.vawc-education h2', {
        scrollTrigger: {
            trigger: '.vawc-education',
            start: 'top center+=100',
            toggleActions: 'play none none reverse'
        },
        opacity: 0,
        y: 50,
        duration: 1,
        ease: "power3.out"
    });

    gsap.from('.vawc-feature', {
        scrollTrigger: {
            trigger: '.vawc-features',
            start: 'top center+=100',
            toggleActions: 'play none none reverse'
        },
        opacity: 0,
        x: -100,
        duration: 1,
        stagger: 0.3,
        ease: "power3.out"
    });

    // Learning Features section animations
    gsap.from('.learning-features h2', {
        scrollTrigger: {
            trigger: '.learning-features',
            start: 'top center+=100',
            toggleActions: 'play none none reverse'
        },
        opacity: 0,
        y: 50,
        duration: 1,
        ease: "power3.out"
    });

    gsap.from('.feature-block', {
        scrollTrigger: {
            trigger: '.features-container',
            start: 'top center+=100',
            toggleActions: 'play none none reverse'
        },
        opacity: 0,
        y: 100,
        duration: 1.2,
        stagger: 0.4,
        ease: "power3.out"
    });

    // Footer animations
    gsap.from('.footer-section', {
        scrollTrigger: {
            trigger: 'footer',
            start: 'top center+=100',
            toggleActions: 'play none none reverse'
        },
        opacity: 0,
        y: 50,
        duration: 1,
        stagger: 0.2,
        ease: "power3.out"
    });

    // Vision section animation (for other pages)
    gsap.from('#visionCard', {
        scrollTrigger: {
            trigger: '#visionCard',
            start: 'top center+=100',
            toggleActions: 'play none none reverse'
        },
        opacity: 0,
        y: 50,
        duration: 1
    });

    // Mission section animation (for other pages)
    gsap.from('#missionCard', {
        scrollTrigger: {
            trigger: '#missionCard',
            start: 'top center+=100',
            toggleActions: 'play none none reverse'
        },
        opacity: 0,
        y: 50,
        duration: 1,
        delay: 0.3
    });

    // Animate mandate items one by one (for other pages)
    gsap.utils.toArray('.mandate-item').forEach((item, i) => {
        gsap.from(item, {
            scrollTrigger: {
                trigger: item,
                start: 'top center+=100',
                toggleActions: 'play none none reverse'
            },
            opacity: 0,
            x: -50,
            duration: 0.5,
            delay: i * 0.2
        });
    });

    // Animate function items with hover effect (for other pages)
    const functionItems = document.querySelectorAll('.function-item');
    functionItems.forEach(item => {
        item.addEventListener('mouseenter', () => {
            gsap.to(item, {
                scale: 1.05,
                backgroundColor: '#f0e6ff',
                duration: 0.3
            });
        });

        item.addEventListener('mouseleave', () => {
            gsap.to(item, {
                scale: 1,
                backgroundColor: '#ffffff',
                duration: 0.3
            });
        });
    });

    // Enhanced hover animations for feature cards
    document.querySelectorAll('.feature-card').forEach(card => {
        card.addEventListener('mouseenter', () => {
            gsap.to(card, {
                y: -10,
                scale: 1.02,
                duration: 0.3,
                ease: "power2.out"
            });
        });

        card.addEventListener('mouseleave', () => {
            gsap.to(card, {
                y: 0,
                scale: 1,
                duration: 0.3,
                ease: "power2.out"
            });
        });
    });

    // Enhanced hover animations for program cards
    document.querySelectorAll('.program-card').forEach(card => {
        card.addEventListener('mouseenter', () => {
            gsap.to(card, {
                y: -15,
                scale: 1.03,
                duration: 0.4,
                ease: "power2.out"
            });
        });

        card.addEventListener('mouseleave', () => {
            gsap.to(card, {
                y: 0,
                scale: 1,
                duration: 0.4,
                ease: "power2.out"
            });
        });
    });

    // Enhanced hover animations for flyer cards
    document.querySelectorAll('.flyer-card').forEach(card => {
        card.addEventListener('mouseenter', () => {
            gsap.to(card, {
                y: -20,
                scale: 1.05,
                rotation: 2,
                duration: 0.4,
                ease: "power2.out"
            });
        });

        card.addEventListener('mouseleave', () => {
            gsap.to(card, {
                y: 0,
                scale: 1,
                rotation: 0,
                duration: 0.4,
                ease: "power2.out"
            });
        });
    });

    // Parallax effect for hero background
    gsap.to('.hero', {
        scrollTrigger: {
            trigger: '.hero',
            start: 'top top',
            end: 'bottom top',
            scrub: 1
        },
        y: -100,
        ease: "none"
    });

    // Floating animation for hero cards
    gsap.to('.hero-card', {
        y: -20,
        duration: 2,
        repeat: -1,
        yoyo: true,
        ease: "power2.inOut",
        stagger: 0.5
    });
});

// Progress bar animation
const progressBar = document.querySelector('.progress-bar');
window.addEventListener('scroll', () => {
    const windowHeight = window.innerHeight;
    const documentHeight = document.documentElement.scrollHeight - windowHeight;
    const scrolled = window.scrollY;
    const progress = (scrolled / documentHeight) * 100;
    progressBar.style.width = `${progress}%`;
});

// Interactive cards flip animation
document.querySelectorAll('.flip-card').forEach(card => {
    card.addEventListener('click', () => {
        card.classList.toggle('is-flipped');
    });
});

// Initialize counters animation
const counters = document.querySelectorAll('.counter');
counters.forEach(counter => {
    const target = +counter.getAttribute('data-target');
    const duration = 2000; // 2 seconds
    const increment = target / (duration / 16); // 60fps

    let current = 0;
    const updateCounter = () => {
        if (current < target) {
            current += increment;
            counter.textContent = Math.ceil(current);
            requestAnimationFrame(updateCounter);
        } else {
            counter.textContent = target;
        }
    };

    ScrollTrigger.create({
        trigger: counter,
        start: 'top center+=100',
        onEnter: () => updateCounter()
    });
}); 