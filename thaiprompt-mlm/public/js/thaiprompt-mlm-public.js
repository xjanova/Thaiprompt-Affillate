/**
 * Thaiprompt MLM Public JavaScript
 */

(function($) {
    'use strict';

    // Copy to clipboard functionality
    $(document).on('click', '.mlm-copy-button', function() {
        const text = $(this).data('clipboard-text') || $(this).siblings('input').val();
        const $button = $(this);

        // Create temporary input
        const $temp = $('<input>');
        $('body').append($temp);
        $temp.val(text).select();
        document.execCommand('copy');
        $temp.remove();

        // Show feedback
        const originalText = $button.text();
        $button.text('Copied!');
        setTimeout(() => {
            $button.text(originalText);
        }, 2000);
    });

    // Withdrawal form
    $(document).on('submit', '.mlm-withdrawal-form', function(e) {
        e.preventDefault();

        const $form = $(this);
        const $button = $form.find('button[type="submit"]');
        const originalText = $button.text();

        // Disable button
        $button.prop('disabled', true).text('Processing...');

        $.ajax({
            url: thaipromptMLM.ajax_url,
            type: 'POST',
            data: $form.serialize() + '&action=thaiprompt_mlm_withdraw_request&nonce=' + thaipromptMLM.nonce,
            success: function(response) {
                if (response.success) {
                    showNotice('success', response.data.message);
                    $form[0].reset();
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                } else {
                    showNotice('error', response.data.message);
                }
            },
            error: function() {
                showNotice('error', 'An error occurred. Please try again.');
            },
            complete: function() {
                $button.prop('disabled', false).text(originalText);
            }
        });
    });

    // Show notice
    function showNotice(type, message) {
        const $notice = $('<div class="mlm-notice ' + type + '"></div>').text(message);
        $('.mlm-dashboard, .mlm-wallet, .mlm-genealogy-container').prepend($notice);

        // Fade in
        $notice.hide().fadeIn();

        // Remove after 5 seconds
        setTimeout(() => {
            $notice.fadeOut(() => {
                $notice.remove();
            });
        }, 5000);
    }

    // Tab switcher
    $(document).on('click', '.mlm-tabs-nav a', function(e) {
        e.preventDefault();

        const $link = $(this);
        const target = $link.attr('href');

        // Update active state
        $link.closest('.mlm-tabs-nav').find('a').removeClass('active');
        $link.addClass('active');

        // Show target tab
        $('.mlm-tab-content').hide();
        $(target).fadeIn();
    });

    // Animate stats on scroll
    function animateStats() {
        const $stats = $('.mlm-stat-value');

        if ($stats.length && typeof gsap !== 'undefined') {
            $stats.each(function() {
                const $stat = $(this);
                const value = parseFloat($stat.text().replace(/[^0-9.-]+/g, ''));

                if (!isNaN(value)) {
                    gsap.from($stat[0], {
                        innerText: 0,
                        duration: 2,
                        ease: 'power1.out',
                        snap: { innerText: 1 },
                        scrollTrigger: {
                            trigger: $stat[0],
                            start: 'top 80%'
                        },
                        onUpdate: function() {
                            $stat.text(Math.round(this.targets()[0].innerText));
                        }
                    });
                }
            });
        }
    }

    // Progress bar animation
    function animateProgressBars() {
        const $progressBars = $('.mlm-progress-bar');

        if ($progressBars.length && typeof gsap !== 'undefined') {
            $progressBars.each(function() {
                const $bar = $(this);
                const width = $bar.data('progress') || $bar.css('width');

                gsap.from($bar[0], {
                    width: 0,
                    duration: 1.5,
                    ease: 'power2.out',
                    scrollTrigger: {
                        trigger: $bar[0],
                        start: 'top 80%'
                    }
                });
            });
        }
    }

    // Card hover effect
    function initCardHoverEffects() {
        const $cards = $('.mlm-stat-card, .mlm-card');

        if ($cards.length && typeof gsap !== 'undefined') {
            $cards.each(function() {
                const $card = $(this);

                $card.on('mouseenter', function() {
                    gsap.to(this, {
                        scale: 1.05,
                        duration: 0.3,
                        ease: 'power2.out'
                    });
                });

                $card.on('mouseleave', function() {
                    gsap.to(this, {
                        scale: 1,
                        duration: 0.3,
                        ease: 'power2.out'
                    });
                });
            });
        }
    }

    // Withdrawal modal
    $(document).on('click', '.mlm-withdraw-btn', function() {
        $('#mlm-withdrawal-modal').fadeIn();
    });

    $(document).on('click', '.mlm-modal-close, .mlm-modal-overlay', function() {
        $('.mlm-modal').fadeOut();
    });

    // Filter commissions
    $(document).on('change', '#mlm-commission-filter', function() {
        const type = $(this).val();
        const $rows = $('.mlm-commission-table tbody tr');

        if (type === 'all') {
            $rows.show();
        } else {
            $rows.hide();
            $rows.filter('[data-type="' + type + '"]').show();
        }
    });

    // Real-time balance update
    function updateBalance() {
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
                        $('.mlm-wallet-balance').text(formatCurrency(response.data.balance));
                        $('.mlm-wallet-pending').text(formatCurrency(response.data.pending));
                    }
                }
            });
        }
    }

    // Format currency
    function formatCurrency(amount) {
        return new Intl.NumberFormat('th-TH', {
            style: 'currency',
            currency: 'THB'
        }).format(amount);
    }

    // Countdown timer for bonuses
    function initCountdownTimers() {
        $('.mlm-countdown').each(function() {
            const $timer = $(this);
            const endDate = new Date($timer.data('end-date')).getTime();

            const interval = setInterval(() => {
                const now = new Date().getTime();
                const distance = endDate - now;

                if (distance < 0) {
                    clearInterval(interval);
                    $timer.text('Expired');
                    return;
                }

                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);

                $timer.text(days + 'd ' + hours + 'h ' + minutes + 'm ' + seconds + 's');
            }, 1000);
        });
    }

    // Rank badge animation
    function animateRankBadge() {
        const $badge = $('.mlm-rank-badge-large');

        if ($badge.length && typeof gsap !== 'undefined') {
            gsap.from($badge[0], {
                scale: 0,
                rotation: -180,
                duration: 1,
                ease: 'elastic.out(1, 0.5)',
                scrollTrigger: {
                    trigger: $badge[0],
                    start: 'top 80%'
                }
            });
        }
    }

    // Referral sharing
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

    // Initialize on document ready
    $(document).ready(function() {
        animateStats();
        animateProgressBars();
        initCardHoverEffects();
        initCountdownTimers();
        animateRankBadge();

        // Update balance every 30 seconds
        setInterval(updateBalance, 30000);

        // Add fade-in class to elements
        $('.mlm-stat-card, .mlm-card, .mlm-wallet, .mlm-rank-progress').addClass('mlm-fade-in');
    });

    // Initialize on window scroll (for lazy loading)
    $(window).on('scroll', function() {
        // Trigger animations for elements coming into view
    });

})(jQuery);
