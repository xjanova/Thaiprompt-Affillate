/**
 * Thaiprompt MLM Portal JavaScript
 * Modern SPA-like experience
 */

(function($) {
    'use strict';

    class MLMPortal {
        constructor() {
            this.currentTab = 'dashboard';
            this.init();
        }

        init() {
            this.setupTabNavigation();
            this.setupAnimations();
            this.loadDashboardData();
            this.setupEventHandlers();
            this.startBackgroundAnimations();
        }

        setupTabNavigation() {
            const self = this;

            console.log('MLM Portal: Setting up tab navigation, found', $('.mlm-portal-nav-link').length, 'nav links');

            $('.mlm-portal-nav-link').on('click', function(e) {
                e.preventDefault();

                const tab = $(this).data('tab');
                console.log('MLM Portal: Tab clicked:', tab);

                // Update active state
                $('.mlm-portal-nav-link').removeClass('active');
                $(this).addClass('active');

                // Switch content
                self.switchTab(tab);
            });
        }

        switchTab(tab) {
            const self = this;

            // Fade out all content first
            $('.mlm-portal-tab-content').fadeOut(200, function() {
                $(this).removeClass('active');
            });

            // After a brief delay, fade in the new content
            setTimeout(function() {
                const $newTab = $('[data-tab-content="' + tab + '"]');
                $newTab.addClass('active').fadeIn(300);

                // Load data for the tab
                self.loadTabData(tab);

                // Scroll to top of content
                $('.mlm-portal-main').animate({ scrollTop: 0 }, 300);
            }, 250);

            this.currentTab = tab;
        }

        loadTabData(tab) {
            switch(tab) {
                case 'dashboard':
                    this.loadDashboardData();
                    break;
                case 'genealogy':
                    this.loadGenealogyTree();
                    break;
                case 'network':
                    this.loadNetworkData();
                    break;
                case 'wallet':
                    this.loadWalletData();
                    break;
                case 'commissions':
                    this.loadCommissionsData();
                    break;
                case 'rank':
                    this.loadRankData();
                    break;
            }
        }

        loadDashboardData() {
            // Animate stats cards
            $('.mlm-stat-card').each(function(index) {
                $(this).css({
                    'animation-delay': (index * 0.1) + 's'
                }).addClass('mlm-fade-in-up');
            });

            // Animate stat values
            $('.mlm-stat-value').each(function() {
                const $this = $(this);
                const countTo = parseInt($this.text().replace(/[^0-9]/g, ''));

                if (!isNaN(countTo)) {
                    $({ countNum: 0 }).animate({
                        countNum: countTo
                    }, {
                        duration: 2000,
                        easing: 'swing',
                        step: function() {
                            $this.text(Math.floor(this.countNum).toLocaleString());
                        },
                        complete: function() {
                            $this.text(countTo.toLocaleString());
                        }
                    });
                }
            });

            // Animate progress bars
            this.animateProgressBars();
        }

        animateProgressBars() {
            $('.mlm-progress-fill').each(function() {
                const $this = $(this);
                const width = $this.data('progress') || 0;

                $this.css('width', '0%');

                setTimeout(() => {
                    $this.css('width', width + '%');
                }, 300);
            });
        }

        loadGenealogyTree() {
            const $container = $('.mlm-genealogy-container');

            if ($container.length && !$container.data('loaded')) {
                // Initialize genealogy tree if not already done
                if (typeof $container.mlmGenealogyTree === 'function') {
                    $container.mlmGenealogyTree({
                        userId: thaipromptMLM.user_id,
                        maxDepth: 5
                    });
                    $container.data('loaded', true);
                }
            }
        }

        loadNetworkData() {
            // Refresh network stats
            console.log('Loading network data...');
        }

        loadWalletData() {
            // Refresh wallet balance
            this.updateWalletBalance();
        }

        loadCommissionsData() {
            // Refresh commissions
            console.log('Loading commissions data...');
        }

        loadRankData() {
            // Refresh rank progress
            this.animateProgressBars();
        }

        updateWalletBalance() {
            if (typeof thaipromptMLM !== 'undefined' && thaipromptMLM.user_id) {
                $.ajax({
                    url: thaipromptMLM.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'thaiprompt_mlm_get_balance',
                        nonce: thaipromptMLM.nonce
                    },
                    success: function(response) {
                        if (response.success && response.data.balance !== undefined) {
                            $('.mlm-wallet-balance').each(function() {
                                $(this).text(formatCurrency(response.data.balance));
                            });
                        }
                    }
                });
            }
        }

        setupAnimations() {
            // Parallax effect on scroll
            $(window).on('scroll', function() {
                const scrolled = $(window).scrollTop();
                $('.mlm-portal-wrapper::before').css('transform', 'translateY(' + (scrolled * 0.5) + 'px)');
            });

            // Hover effects
            $('.mlm-glass-card').hover(
                function() {
                    $(this).addClass('mlm-pulse');
                },
                function() {
                    $(this).removeClass('mlm-pulse');
                }
            );

            // Add entrance animations to elements
            this.observeElements();
        }

        observeElements() {
            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('mlm-fade-in-up');
                            observer.unobserve(entry.target);
                        }
                    });
                }, {
                    threshold: 0.1
                });

                document.querySelectorAll('.mlm-glass-card, .mlm-portal-table').forEach(el => {
                    observer.observe(el);
                });
            }
        }

        setupEventHandlers() {
            // Copy referral link
            $(document).on('click', '.mlm-copy-referral', function() {
                const link = $(this).data('link') || $(this).closest('.mlm-referral-box').find('input').val();

                const $temp = $('<input>');
                $('body').append($temp);
                $temp.val(link).select();
                document.execCommand('copy');
                $temp.remove();

                // Show feedback
                const $button = $(this);
                const originalText = $button.text();
                $button.text('✓ Copied!').css('background', 'linear-gradient(135deg, #10b981, #059669)');

                setTimeout(() => {
                    $button.text(originalText).css('background', '');
                }, 2000);
            });

            // Withdrawal modal
            $(document).on('click', '.mlm-withdraw-btn', function() {
                $('#mlm-withdrawal-modal').fadeIn(300);
            });

            $(document).on('click', '.mlm-modal-close, .mlm-modal-overlay', function() {
                $('.mlm-modal').fadeOut(300);
            });

            // Share buttons
            $(document).on('click', '.mlm-share-btn', function() {
                const platform = $(this).data('platform');
                const url = $('.mlm-referral-input').val();
                const text = 'Join my MLM network!';

                let shareUrl = '';

                switch (platform) {
                    case 'facebook':
                        shareUrl = 'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(url);
                        break;
                    case 'twitter':
                        shareUrl = 'https://twitter.com/intent/tweet?text=' + encodeURIComponent(text) + '&url=' + encodeURIComponent(url);
                        break;
                    case 'line':
                        shareUrl = 'https://social-plugins.line.me/lineit/share?url=' + encodeURIComponent(url);
                        break;
                    case 'whatsapp':
                        shareUrl = 'https://wa.me/?text=' + encodeURIComponent(text + ' ' + url);
                        break;
                }

                if (shareUrl) {
                    window.open(shareUrl, '_blank', 'width=600,height=400');
                }
            });
        }

        startBackgroundAnimations() {
            // Floating animation for icons
            setInterval(() => {
                $('.mlm-stat-icon').each(function() {
                    $(this).css({
                        'transform': 'translateY(' + (Math.sin(Date.now() / 1000) * 5) + 'px)'
                    });
                });
            }, 50);

            // Particle effect
            this.createParticles();
        }

        createParticles() {
            const $wrapper = $('.mlm-portal-wrapper');

            // Create floating particles
            for (let i = 0; i < 20; i++) {
                const $particle = $('<div class="mlm-particle"></div>');
                const size = Math.random() * 4 + 2;
                const duration = Math.random() * 10 + 10;
                const delay = Math.random() * 5;

                $particle.css({
                    'position': 'absolute',
                    'width': size + 'px',
                    'height': size + 'px',
                    'background': 'rgba(255, 255, 255, 0.3)',
                    'border-radius': '50%',
                    'left': Math.random() * 100 + '%',
                    'top': Math.random() * 100 + '%',
                    'animation': 'mlm-float ' + duration + 's ease-in-out ' + delay + 's infinite',
                    'pointer-events': 'none',
                    'z-index': 0
                });

                $wrapper.append($particle);
            }
        }
    }

    // Helper functions
    function formatCurrency(amount) {
        return new Intl.NumberFormat('th-TH', {
            style: 'currency',
            currency: 'THB'
        }).format(amount);
    }

    // Initialize portal when document is ready
    $(document).ready(function() {
        console.log('MLM Portal: Initializing...');

        if ($('.mlm-portal-wrapper').length) {
            console.log('MLM Portal: Wrapper found, creating portal instance');
            window.mlmPortal = new MLMPortal();

            // Add floating animation keyframe
            const style = $('<style>@keyframes mlm-float { 0%, 100% { transform: translateY(0px) translateX(0px); } 25% { transform: translateY(-20px) translateX(10px); } 50% { transform: translateY(0px) translateX(20px); } 75% { transform: translateY(20px) translateX(10px); } }</style>');
            $('head').append(style);

            // Smooth scroll for # links (but not for tab links)
            $('a[href^="#"]:not(.mlm-portal-nav-link)').on('click', function(e) {
                e.preventDefault();
                const target = $(this.getAttribute('href'));
                if (target.length) {
                    $('html, body').stop().animate({
                        scrollTop: target.offset().top - 100
                    }, 1000);
                }
            });
        } else {
            console.log('MLM Portal: Wrapper not found');
        }
    });

    // Auto-refresh every 30 seconds
    setInterval(function() {
        if (window.mlmPortal && window.mlmPortal.currentTab === 'dashboard') {
            window.mlmPortal.updateWalletBalance();
        }
    }, 30000);

})(jQuery);
