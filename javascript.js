document.addEventListener('DOMContentLoaded', () => {
    // --- Header Scroll ---
    let lastScrollPosition = 0;
    const header = document.getElementById('header');
    if (header) {
        window.addEventListener('scroll', () => {
            const currentScrollPosition = window.pageYOffset;
            if (currentScrollPosition > lastScrollPosition && currentScrollPosition > 80) {
                header.style.transform = 'translateY(-100%)';
            } else {
                header.style.transform = 'translateY(0)';
            }
            lastScrollPosition = currentScrollPosition;
        });
    }

    // --- Dashboard Navigation ---
    function setupDashboardNavigation() {
        const navLinks = document.querySelectorAll('.dash-nav-link');
        const sections = document.querySelectorAll('.dashboard-section');
        const urlParams = new URLSearchParams(window.location.search);
        let currentView = urlParams.get('view') || 'dashboard';

        function showSection(targetId) {
            sections.forEach(section => section.classList.remove('active'));
            navLinks.forEach(link => link.classList.remove('active'));
            
            const targetSection = document.getElementById(targetId);
            const targetLink = document.querySelector(`.dash-nav-link[data-target="${targetId}"]`);

            if (targetSection) targetSection.classList.add('active');
            if (targetLink) targetLink.classList.add('active');
        }

        navLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                if (link.getAttribute('href').startsWith('logout')) return;
                e.preventDefault();
                const targetId = link.getAttribute('data-target');
                showSection(targetId);
                const url = new URL(window.location);
                url.searchParams.set('view', targetId);
                window.history.pushState({}, '', url);
            });
        });
        showSection(currentView);
    }
    if (document.body.classList.contains('dashboard-body')) {
        setupDashboardNavigation();
    }

    // --- Animated Stats Counter ---
    const statsSection = document.querySelector('.stats-section');
    if (statsSection) {
        const counters = document.querySelectorAll('.stat-number');
        const speed = 200;

        const startCounters = (entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    counters.forEach(counter => {
                        const updateCount = () => {
                            const target = +counter.getAttribute('data-target');
                            const count = +counter.innerText;
                            const inc = Math.ceil(target / speed);

                            if (count < target) {
                                counter.innerText = count + inc;
                                setTimeout(updateCount, 1);
                            } else {
                                counter.innerText = target.toLocaleString();
                            }
                        };
                        updateCount();
                    });
                    observer.unobserve(statsSection);
                }
            });
        };

        const observer = new IntersectionObserver(startCounters, {
            root: null,
            threshold: 0.2
        });
        observer.observe(statsSection);
    }
});