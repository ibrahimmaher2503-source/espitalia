/**
 * Dashboard Animations
 * Modern interactive enhancements for Espitalia admin dashboard
 */

(function() {
    'use strict';

    // ========================================
    // COUNTER ANIMATION
    // ========================================

    /**
     * Animate counter from 0 to target value
     * @param {HTMLElement} element - The element to animate
     * @param {number} target - Target number
     * @param {number} duration - Animation duration in ms
     */
    function animateCounter(element, target, duration = 1500) {
        // Skip if target is not a number
        if (isNaN(target)) return;

        const start = 0;
        const startTime = performance.now();
        const isCurrency = element.textContent.includes('$') || element.textContent.includes('€') || element.textContent.includes('£');
        const originalText = element.textContent;

        function update(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);

            // Easing function (ease-out-cubic)
            const easeOutCubic = 1 - Math.pow(1 - progress, 3);
            const current = Math.floor(start + (target - start) * easeOutCubic);

            if (isCurrency) {
                // Preserve currency formatting
                const currencySymbol = originalText.match(/[$€£]/)?.[0] || '$';
                element.textContent = currencySymbol + current.toLocaleString();
            } else {
                element.textContent = current.toLocaleString();
            }

            if (progress < 1) {
                requestAnimationFrame(update);
            } else {
                // Restore original formatting
                element.textContent = originalText;
            }
        }

        requestAnimationFrame(update);
    }

    /**
     * Initialize all counters on the page
     */
    function initCounters() {
        const counterElements = [
            { id: 'total_booking_count', selector: '#total_booking_count' },
            { id: 'total_active_service_count', selector: '#total_active_service_count' },
            { id: 'total_active_vendor_count', selector: '#total_active_vendor_count' },
            { id: 'total_user', selector: '#total_user' },
            { id: 'child_patient_count', selector: '#child_patient_count' },
            { id: 'adult_patient_count', selector: '#adult_patient_count' },
            { id: 'old_patient_count', selector: '#old_patient_count' }
        ];

        counterElements.forEach(({ selector }) => {
            const element = document.querySelector(selector);
            if (element) {
                const text = element.textContent.trim();
                const number = parseInt(text.replace(/[^\d]/g, ''), 10);
                if (!isNaN(number)) {
                    element.classList.add('counter-value');
                    // Delay counter animation slightly for staggered effect
                    const delay = Math.random() * 200;
                    setTimeout(() => animateCounter(element, number), delay);
                }
            }
        });
    }

    // ========================================
    // INTERSECTION OBSERVER FOR SCROLL ANIMATIONS
    // ========================================

    /**
     * Initialize Intersection Observer for scroll-triggered animations
     */
    function initScrollAnimations() {
        const observerOptions = {
            root: null,
            rootMargin: '0px',
            threshold: 0.1
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    // Optionally unobserve after animation
                    // observer.unobserve(entry.target);
                }
            });
        }, observerOptions);

        // Observe elements with .observe-animation class
        document.querySelectorAll('.observe-animation').forEach(element => {
            observer.observe(element);
        });
    }

    // ========================================
    // CARD HOVER INTERACTIONS
    // ========================================

    // initCardTiltEffect removed: inline transform conflicted with CSS hover transitions

    /**
     * Add ripple effect on card click
     */
    function initRippleEffect() {
        const cards = document.querySelectorAll('.dash-stat-card, .dash-appt-item');

        cards.forEach(card => {
            card.addEventListener('click', function(e) {
                const ripple = document.createElement('span');
                const rect = card.getBoundingClientRect();
                const size = Math.max(rect.width, rect.height);
                const x = e.clientX - rect.left - size / 2;
                const y = e.clientY - rect.top - size / 2;

                ripple.style.width = ripple.style.height = size + 'px';
                ripple.style.left = x + 'px';
                ripple.style.top = y + 'px';
                ripple.classList.add('ripple-effect');

                card.style.position = 'relative';
                card.style.overflow = 'hidden';
                card.appendChild(ripple);

                setTimeout(() => ripple.remove(), 600);
            });
        });

        // Add CSS for ripple effect
        if (!document.getElementById('ripple-style')) {
            const style = document.createElement('style');
            style.id = 'ripple-style';
            style.textContent = `
                .ripple-effect {
                    position: absolute;
                    border-radius: 50%;
                    background: rgba(255, 255, 255, 0.6);
                    transform: scale(0);
                    animation: ripple-animation 0.6s ease-out;
                    pointer-events: none;
                }
                @keyframes ripple-animation {
                    to {
                        transform: scale(4);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);
        }
    }

    // ========================================
    // CHART ANIMATIONS
    // ========================================

    /**
     * Add entrance animation to chart when it appears
     */
    function initChartAnimations() {
        const chartContainer = document.querySelector('#total-revenue');
        if (!chartContainer) return;

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    chartContainer.style.opacity = '0';
                    chartContainer.style.transform = 'translateY(20px)';

                    setTimeout(() => {
                        chartContainer.style.transition = 'opacity 0.8s ease, transform 0.8s ease';
                        chartContainer.style.opacity = '1';
                        chartContainer.style.transform = 'translateY(0)';
                    }, 100);

                    observer.unobserve(chartContainer);
                }
            });
        }, { threshold: 0.2 });

        observer.observe(chartContainer);
    }

    // ========================================
    // SMOOTH SCROLL EFFECTS
    // ========================================

    /**
     * Add smooth reveal animations for list items
     */
    function initListItemAnimations() {
        const lists = [
            document.querySelector('#upcoming-appointments'),
            document.querySelector('#register_vendors_list')
        ];

        lists.forEach(list => {
            if (!list) return;

            const items = list.querySelectorAll('li');
            items.forEach((item, index) => {
                item.style.opacity = '0';
                item.style.transform = 'translateX(-20px)';

                setTimeout(() => {
                    item.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
                    item.style.opacity = '1';
                    item.style.transform = 'translateX(0)';
                }, index * 50);
            });
        });
    }

    // ========================================
    // TABLE ROW ANIMATIONS
    // ========================================

    /**
     * Add entrance animations to table rows
     */
    function initTableAnimations() {
        const tableRows = document.querySelectorAll('.dash-table tbody tr');

        tableRows.forEach((row, index) => {
            row.style.opacity = '0';
            row.style.transform = 'translateX(-10px)';

            setTimeout(() => {
                row.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                row.style.opacity = '1';
                row.style.transform = 'translateX(0)';
            }, index * 30);
        });
    }

    // ========================================
    // PARALLAX EFFECT
    // ========================================

    // initParallaxEffect removed: pseudo-elements (::before) cannot be queried
    // with document.querySelectorAll, so the parallax logic was non-functional

    // ========================================
    // NUMBER FORMAT UPDATES
    // ========================================

    /**
     * Update numbers with smooth transitions when data changes
     */
    function initNumberUpdates() {
        // This function can be called externally when AJAX updates occur
        window.updateDashboardNumber = function(elementId, newValue) {
            const element = document.getElementById(elementId);
            if (element) {
                animateCounter(element, newValue, 1000);
            }
        };
    }

    // ========================================
    // LOADING STATES
    // ========================================

    /**
     * Add skeleton loading states
     */
    function showSkeletonLoading(container) {
        if (!container) return;

        const skeleton = document.createElement('div');
        skeleton.className = 'skeleton';
        skeleton.style.width = '100%';
        skeleton.style.height = '100%';
        skeleton.style.minHeight = '200px';

        container.appendChild(skeleton);

        return skeleton;
    }

    /**
     * Remove skeleton loading states
     */
    function hideSkeletonLoading(skeleton) {
        if (skeleton && skeleton.parentNode) {
            skeleton.parentNode.removeChild(skeleton);
        }
    }

    // ========================================
    // ENHANCED HOVER STATES
    // ========================================

    /**
     * Add enhanced hover effects to interactive elements
     */
    function initEnhancedHoverStates() {
        // Add glow effect to buttons on hover
        const buttons = document.querySelectorAll('.dash-btn-icon, .btn-primary, .btn-outline-primary');

        buttons.forEach(button => {
            button.addEventListener('mouseenter', function() {
                this.style.transition = 'all 0.3s ease';
            });
        });

        // Add scale effect to badges
        const badges = document.querySelectorAll('.badge');
        badges.forEach(badge => {
            badge.style.cursor = 'default';
        });
    }

    // ========================================
    // ACCESSIBILITY ENHANCEMENTS
    // ========================================

    /**
     * Respect user's motion preferences
     */
    function respectMotionPreferences() {
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        if (prefersReducedMotion) {
            // Disable animations
            document.documentElement.style.setProperty('--animate-duration', '0.01ms');

            // Remove animation classes
            document.querySelectorAll('.fade-in-up, .slide-in-left, .scale-in').forEach(element => {
                element.style.animation = 'none';
                element.style.opacity = '1';
                element.style.transform = 'none';
            });
        }
    }

    // ========================================
    // PERFORMANCE OPTIMIZATIONS
    // ========================================

    /**
     * Use Intersection Observer for performance
     */
    function optimizePerformance() {
        // Disable animations on low-end devices
        const isLowEndDevice = navigator.hardwareConcurrency && navigator.hardwareConcurrency <= 2;

        if (isLowEndDevice) {
            document.documentElement.style.setProperty('--animate-duration', '0.2s');
        }

        // Lazy load heavy animations
        const heavyAnimations = document.querySelectorAll('.dash-card, .dash-stat-card');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animation-ready');
                }
            });
        }, { rootMargin: '50px' });

        heavyAnimations.forEach(element => observer.observe(element));
    }

    // ========================================
    // INITIALIZATION
    // ========================================

    /**
     * Initialize all dashboard animations
     */
    function init() {
        // Check if DOM is ready
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
            return;
        }

        // Check user preferences first
        respectMotionPreferences();

        // Initialize core features
        setTimeout(() => {
            initCounters();
            initScrollAnimations();
            initChartAnimations();
        }, 100);

        // Initialize interactive features
        setTimeout(() => {
            initRippleEffect();
            initEnhancedHoverStates();
        }, 300);

        // Initialize content animations
        setTimeout(() => {
            initListItemAnimations();
            initTableAnimations();
        }, 500);

        // Initialize effects
        initNumberUpdates();
        optimizePerformance();

        // Add loaded class to body
        document.body.classList.add('dashboard-animations-loaded');
    }

    // ========================================
    // PUBLIC API
    // ========================================

    // Expose public methods
    window.DashboardAnimations = {
        init: init,
        animateCounter: animateCounter,
        updateNumber: function(elementId, newValue) {
            if (window.updateDashboardNumber) {
                window.updateDashboardNumber(elementId, newValue);
            }
        },
        showLoading: showSkeletonLoading,
        hideLoading: hideSkeletonLoading
    };

    // Auto-initialize
    init();

})();
