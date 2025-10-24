<?php
/**
 * Wallet Template
 */

if (!defined('ABSPATH')) {
    exit;
}

$user_id = get_current_user_id();
$wallet = isset($wallet) ? $wallet : Thaiprompt_MLM_Wallet::get_balance($user_id);
$transactions = isset($transactions) ? $transactions : Thaiprompt_MLM_Wallet::get_transactions($user_id, array('limit' => 20));
$withdrawals = isset($withdrawals) ? $withdrawals : Thaiprompt_MLM_Wallet::get_withdrawals($user_id);
$settings = get_option('thaiprompt_mlm_settings', array());
$min_withdrawal = $settings['payout_minimum'] ?? 100;
?>

<div class="mlm-wallet">
    <div class="mlm-wallet-header">
        <div class="mlm-wallet-balance-label"><?php _e('Available Balance', 'thaiprompt-mlm'); ?></div>
        <div class="mlm-wallet-balance"><?php echo wc_price($wallet->balance ?? 0); ?></div>
        <div class="mlm-wallet-pending">
            <?php printf(__('Pending: %s', 'thaiprompt-mlm'), wc_price($wallet->pending_balance ?? 0)); ?>
        </div>
        <div class="mlm-wallet-actions">
            <button class="mlm-wallet-btn primary mlm-withdraw-btn">
                <?php _e('Withdraw', 'thaiprompt-mlm'); ?>
            </button>
            <button class="mlm-wallet-btn secondary" onclick="location.reload()">
                <?php _e('Refresh', 'thaiprompt-mlm'); ?>
            </button>
        </div>
    </div>

    <!-- Wallet Stats -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div style="background: #f8f9fa; padding: 20px; border-radius: 12px; text-align: center;">
            <div style="font-size: 14px; color: #6c757d; margin-bottom: 5px;"><?php _e('Total Earned', 'thaiprompt-mlm'); ?></div>
            <div style="font-size: 24px; font-weight: 700; color: #27ae60;"><?php echo wc_price($wallet->total_earned ?? 0); ?></div>
        </div>
        <div style="background: #f8f9fa; padding: 20px; border-radius: 12px; text-align: center;">
            <div style="font-size: 14px; color: #6c757d; margin-bottom: 5px;"><?php _e('Total Withdrawn', 'thaiprompt-mlm'); ?></div>
            <div style="font-size: 24px; font-weight: 700; color: #e74c3c;"><?php echo wc_price($wallet->total_withdrawn ?? 0); ?></div>
        </div>
    </div>

    <!-- Withdrawals -->
    <?php if ($withdrawals && count($withdrawals) > 0): ?>
    <div class="mlm-withdrawals">
        <h3><?php _e('Withdrawal Requests', 'thaiprompt-mlm'); ?></h3>
        <table style="width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
            <thead>
                <tr style="background: #2c3e50; color: white;">
                    <th style="padding: 15px; text-align: left;"><?php _e('Date', 'thaiprompt-mlm'); ?></th>
                    <th style="padding: 15px; text-align: left;"><?php _e('Amount', 'thaiprompt-mlm'); ?></th>
                    <th style="padding: 15px; text-align: left;"><?php _e('Method', 'thaiprompt-mlm'); ?></th>
                    <th style="padding: 15px; text-align: left;"><?php _e('Status', 'thaiprompt-mlm'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($withdrawals as $withdrawal): ?>
                <tr style="border-bottom: 1px solid #ecf0f1;">
                    <td style="padding: 15px;"><?php echo date_i18n(get_option('date_format'), strtotime($withdrawal->requested_at)); ?></td>
                    <td style="padding: 15px; font-weight: 600;"><?php echo wc_price($withdrawal->amount); ?></td>
                    <td style="padding: 15px;"><?php echo esc_html($withdrawal->method); ?></td>
                    <td style="padding: 15px;">
                        <span class="mlm-status-badge <?php echo esc_attr($withdrawal->status); ?>">
                            <?php echo esc_html(ucfirst($withdrawal->status)); ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- Transactions -->
    <div class="mlm-transactions">
        <h3 class="mlm-transactions-header"><?php _e('Recent Transactions', 'thaiprompt-mlm'); ?></h3>
        <?php if ($transactions && count($transactions) > 0): ?>
            <?php foreach ($transactions as $transaction): ?>
            <div class="mlm-transaction-item">
                <div class="mlm-transaction-info">
                    <div class="mlm-transaction-type">
                        <?php
                        $type_labels = array(
                            'commission' => __('Commission Earned', 'thaiprompt-mlm'),
                            'commission_approved' => __('Commission Approved', 'thaiprompt-mlm'),
                            'withdrawal' => __('Withdrawal', 'thaiprompt-mlm'),
                            'withdrawal_refund' => __('Withdrawal Refund', 'thaiprompt-mlm'),
                            'admin_credit' => __('Admin Credit', 'thaiprompt-mlm'),
                            'admin_debit' => __('Admin Debit', 'thaiprompt-mlm'),
                            'rank_bonus' => __('Rank Bonus', 'thaiprompt-mlm')
                        );
                        echo $type_labels[$transaction->transaction_type] ?? esc_html($transaction->transaction_type);
                        ?>
                    </div>
                    <div class="mlm-transaction-date">
                        <?php echo date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($transaction->created_at)); ?>
                    </div>
                    <?php if ($transaction->description): ?>
                    <div class="mlm-transaction-description" style="font-size: 12px; color: #95a5a6; margin-top: 5px;">
                        <?php echo esc_html($transaction->description); ?>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="mlm-transaction-amount <?php echo $transaction->amount >= 0 ? 'positive' : 'negative'; ?>">
                    <?php echo ($transaction->amount >= 0 ? '+' : '') . wc_price(abs($transaction->amount)); ?>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
        <p style="text-align: center; color: #7f8c8d; padding: 40px;">
            <?php _e('No transactions yet', 'thaiprompt-mlm'); ?>
        </p>
        <?php endif; ?>
    </div>
</div>

<!-- Withdrawal Modal -->
<div id="mlm-withdrawal-modal" class="mlm-modal" style="display: none;">
    <div class="mlm-modal-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9998;"></div>
    <div class="mlm-modal-content" style="position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 9999; background: white; padding: 30px; border-radius: 12px; max-width: 500px; width: 90%;">
        <div class="mlm-modal-header">
            <h3 class="mlm-modal-title"><?php _e('Request Withdrawal', 'thaiprompt-mlm'); ?></h3>
            <button class="mlm-modal-close" style="background: none; border: none; font-size: 24px; cursor: pointer;">&times;</button>
        </div>
        <form class="mlm-withdrawal-form">
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">
                    <?php _e('Amount', 'thaiprompt-mlm'); ?>
                    <span style="color: red;">*</span>
                </label>
                <input type="number" name="amount" step="0.01" min="<?php echo esc_attr($min_withdrawal); ?>" max="<?php echo esc_attr($wallet->balance ?? 0); ?>" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;" />
                <small style="color: #6c757d;">
                    <?php printf(__('Minimum: %s, Available: %s', 'thaiprompt-mlm'), wc_price($min_withdrawal), wc_price($wallet->balance ?? 0)); ?>
                </small>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">
                    <?php _e('Withdrawal Method', 'thaiprompt-mlm'); ?>
                    <span style="color: red;">*</span>
                </label>
                <select name="method" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;">
                    <option value=""><?php _e('Select method', 'thaiprompt-mlm'); ?></option>
                    <option value="bank_transfer"><?php _e('Bank Transfer', 'thaiprompt-mlm'); ?></option>
                    <option value="paypal"><?php _e('PayPal', 'thaiprompt-mlm'); ?></option>
                    <option value="promptpay"><?php _e('PromptPay', 'thaiprompt-mlm'); ?></option>
                </select>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">
                    <?php _e('Bank Name', 'thaiprompt-mlm'); ?>
                </label>
                <input type="text" name="bank_name" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;" />
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">
                    <?php _e('Account Number', 'thaiprompt-mlm'); ?>
                </label>
                <input type="text" name="account_number" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;" />
            </div>

            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 600;">
                    <?php _e('Account Name', 'thaiprompt-mlm'); ?>
                </label>
                <input type="text" name="account_name" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px;" />
            </div>

            <button type="submit" style="width: 100%; padding: 12px; background: linear-gradient(135deg, #3498db 0%, #2c3e50 100%); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                <?php _e('Submit Request', 'thaiprompt-mlm'); ?>
            </button>
        </form>
    </div>
</div>
