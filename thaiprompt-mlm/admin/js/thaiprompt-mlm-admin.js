/**
 * Thaiprompt MLM Admin JavaScript
 */

(function($) {
    'use strict';

    // Approve commission
    $(document).on('click', '.mlm-approve-commission', function(e) {
        e.preventDefault();

        const $button = $(this);
        const commissionId = $button.data('commission-id');

        if (!confirm('Are you sure you want to approve this commission?')) {
            return;
        }

        $button.prop('disabled', true).text('Processing...');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'thaiprompt_mlm_approve_commission',
                nonce: thaipromptMLM.nonce,
                commission_id: commissionId
            },
            success: function(response) {
                if (response.success) {
                    showNotice('success', response.data.message);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotice('error', response.data.message);
                    $button.prop('disabled', false).text('Approve');
                }
            },
            error: function() {
                showNotice('error', 'An error occurred');
                $button.prop('disabled', false).text('Approve');
            }
        });
    });

    // Approve withdrawal
    $(document).on('click', '.mlm-approve-withdrawal', function(e) {
        e.preventDefault();

        const $button = $(this);
        const withdrawalId = $button.data('withdrawal-id');

        if (!confirm('Are you sure you want to approve this withdrawal?')) {
            return;
        }

        $button.prop('disabled', true).text('Processing...');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'thaiprompt_mlm_approve_withdrawal',
                nonce: thaipromptMLM.nonce,
                withdrawal_id: withdrawalId
            },
            success: function(response) {
                if (response.success) {
                    showNotice('success', response.data.message);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotice('error', response.data.message);
                    $button.prop('disabled', false).text('Approve');
                }
            },
            error: function() {
                showNotice('error', 'An error occurred');
                $button.prop('disabled', false).text('Approve');
            }
        });
    });

    // Reject withdrawal
    $(document).on('click', '.mlm-reject-withdrawal', function(e) {
        e.preventDefault();

        const $button = $(this);
        const withdrawalId = $button.data('withdrawal-id');
        const reason = prompt('Enter rejection reason:');

        if (!reason) {
            return;
        }

        $button.prop('disabled', true).text('Processing...');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'thaiprompt_mlm_reject_withdrawal',
                nonce: thaipromptMLM.nonce,
                withdrawal_id: withdrawalId,
                reason: reason
            },
            success: function(response) {
                if (response.success) {
                    showNotice('success', response.data.message);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotice('error', response.data.message);
                    $button.prop('disabled', false).text('Reject');
                }
            },
            error: function() {
                showNotice('error', 'An error occurred');
                $button.prop('disabled', false).text('Reject');
            }
        });
    });

    // Add user to network
    $(document).on('click', '.mlm-add-user-network', function(e) {
        e.preventDefault();
        $('#mlm-add-user-modal').fadeIn();
    });

    $(document).on('submit', '#mlm-add-user-form', function(e) {
        e.preventDefault();

        const $form = $(this);
        const $button = $form.find('button[type="submit"]');

        $button.prop('disabled', true).text('Adding...');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: $form.serialize() + '&action=thaiprompt_mlm_add_user_to_network&nonce=' + thaipromptMLM.nonce,
            success: function(response) {
                if (response.success) {
                    showNotice('success', response.data.message);
                    $('#mlm-add-user-modal').fadeOut();
                    $form[0].reset();
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotice('error', response.data.message);
                }
                $button.prop('disabled', false).text('Add User');
            },
            error: function() {
                showNotice('error', 'An error occurred');
                $button.prop('disabled', false).text('Add User');
            }
        });
    });

    // Update user rank
    $(document).on('click', '.mlm-update-rank', function(e) {
        e.preventDefault();

        const $button = $(this);
        const userId = $button.data('user-id');

        $button.prop('disabled', true).text('Updating...');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'thaiprompt_mlm_update_rank',
                nonce: thaipromptMLM.nonce,
                user_id: userId
            },
            success: function(response) {
                if (response.success) {
                    showNotice('success', response.data.message + ': ' + response.data.rank);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotice('error', response.data.message);
                    $button.prop('disabled', false).text('Update Rank');
                }
            },
            error: function() {
                showNotice('error', 'An error occurred');
                $button.prop('disabled', false).text('Update Rank');
            }
        });
    });

    // View genealogy
    $(document).on('click', '.mlm-view-genealogy', function(e) {
        e.preventDefault();

        const userId = $(this).data('user-id');
        $('#mlm-genealogy-modal').fadeIn();

        // Load genealogy tree
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'thaiprompt_mlm_get_genealogy',
                nonce: thaipromptMLM.nonce,
                user_id: userId,
                max_depth: 5
            },
            success: function(response) {
                if (response.success) {
                    renderGenealogyTree(response.data);
                } else {
                    $('#mlm-genealogy-content').html('<p>Failed to load genealogy tree</p>');
                }
            },
            error: function() {
                $('#mlm-genealogy-content').html('<p>An error occurred</p>');
            }
        });
    });

    // Render genealogy tree (simple version for admin)
    function renderGenealogyTree(data) {
        const html = renderNode(data);
        $('#mlm-genealogy-content').html('<div class="mlm-admin-tree">' + html + '</div>');
    }

    function renderNode(node) {
        if (!node) return '';

        let html = '<div class="mlm-admin-tree-node">';
        html += '<div class="mlm-admin-tree-node-content">';
        html += '<strong>' + node.name + '</strong><br>';
        html += 'Level: ' + node.level + '<br>';
        html += 'Sales: ' + formatCurrency(node.personal_sales) + '<br>';
        html += 'Team: ' + (node.left_count + node.right_count);
        html += '</div>';

        if (node.children && node.children.length > 0) {
            html += '<div class="mlm-admin-tree-children">';
            node.children.forEach(child => {
                html += renderNode(child);
            });
            html += '</div>';
        }

        html += '</div>';
        return html;
    }

    // Modal close
    $(document).on('click', '.mlm-modal-close, .mlm-modal-overlay', function() {
        $('.mlm-modal').fadeOut();
    });

    // Show notice
    function showNotice(type, message) {
        const $notice = $('<div class="mlm-notice ' + type + '"></div>').text(message);
        $('.wrap').prepend($notice);
        $notice.hide().fadeIn();

        setTimeout(() => {
            $notice.fadeOut(() => $notice.remove());
        }, 5000);
    }

    // Format currency
    function formatCurrency(amount) {
        return new Intl.NumberFormat('th-TH', {
            style: 'currency',
            currency: 'THB'
        }).format(amount);
    }

    // Charts
    function initCharts() {
        // Sales chart
        if ($('#mlm-sales-chart').length && typeof Chart !== 'undefined') {
            const ctx = $('#mlm-sales-chart')[0].getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Sales',
                        data: [12000, 19000, 15000, 25000, 22000, 30000],
                        borderColor: '#2271b1',
                        backgroundColor: 'rgba(34, 113, 177, 0.1)',
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    }
                }
            });
        }

        // Commission distribution chart
        if ($('#mlm-commission-chart').length && typeof Chart !== 'undefined') {
            const ctx = $('#mlm-commission-chart')[0].getContext('2d');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Level Commissions', 'Fast Start', 'Binary', 'Rank Bonus'],
                    datasets: [{
                        data: [45, 25, 20, 10],
                        backgroundColor: [
                            '#2271b1',
                            '#00a32a',
                            '#dba617',
                            '#d63638'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom'
                        }
                    }
                }
            });
        }
    }

    // Export data
    $(document).on('click', '.mlm-export-csv', function(e) {
        e.preventDefault();

        const type = $(this).data('export-type');
        window.location.href = ajaxurl + '?action=thaiprompt_mlm_export&type=' + type + '&nonce=' + thaipromptMLM.nonce;
    });

    // Bulk actions
    $(document).on('click', '.mlm-bulk-action', function(e) {
        e.preventDefault();

        const action = $('#mlm-bulk-action-select').val();
        const selected = [];

        $('.mlm-checkbox-item:checked').each(function() {
            selected.push($(this).val());
        });

        if (selected.length === 0) {
            alert('Please select at least one item');
            return;
        }

        if (!confirm('Are you sure you want to perform this action on ' + selected.length + ' items?')) {
            return;
        }

        // Process bulk action
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'thaiprompt_mlm_bulk_action',
                nonce: thaipromptMLM.nonce,
                bulk_action: action,
                items: selected
            },
            success: function(response) {
                if (response.success) {
                    showNotice('success', response.data.message);
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotice('error', response.data.message);
                }
            },
            error: function() {
                showNotice('error', 'An error occurred');
            }
        });
    });

    // Select all checkboxes
    $(document).on('change', '.mlm-checkbox-all', function() {
        $('.mlm-checkbox-item').prop('checked', $(this).prop('checked'));
    });

    // Initialize on document ready
    $(document).ready(function() {
        initCharts();

        // Add smooth scrolling
        $('a[href^="#"]').on('click', function(e) {
            const target = $(this.getAttribute('href'));
            if (target.length) {
                e.preventDefault();
                $('html, body').stop().animate({
                    scrollTop: target.offset().top - 100
                }, 500);
            }
        });
    });

})(jQuery);
