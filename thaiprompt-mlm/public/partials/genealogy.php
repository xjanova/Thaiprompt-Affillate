<?php
/**
 * Genealogy Tree Template
 */

if (!defined('ABSPATH')) {
    exit;
}

$user_id = isset($atts['user_id']) ? $atts['user_id'] : get_current_user_id();
$max_depth = isset($atts['max_depth']) ? $atts['max_depth'] : 5;
?>

<div class="mlm-genealogy-container">
    <div class="mlm-genealogy-header">
        <h3><?php _e('Genealogy Tree', 'thaiprompt-mlm'); ?></h3>
        <p><?php _e('View your team structure and downline organization', 'thaiprompt-mlm'); ?></p>
    </div>

    <div class="mlm-genealogy-controls">
        <button class="mlm-genealogy-btn mlm-genealogy-zoom-in">
            <span>🔍+</span> <?php _e('Zoom In', 'thaiprompt-mlm'); ?>
        </button>
        <button class="mlm-genealogy-btn mlm-genealogy-zoom-out">
            <span>🔍-</span> <?php _e('Zoom Out', 'thaiprompt-mlm'); ?>
        </button>
        <button class="mlm-genealogy-btn mlm-genealogy-reset">
            <span>🔄</span> <?php _e('Reset View', 'thaiprompt-mlm'); ?>
        </button>
        <button class="mlm-genealogy-btn mlm-genealogy-reload">
            <span>↻</span> <?php _e('Reload', 'thaiprompt-mlm'); ?>
        </button>
    </div>

    <div class="mlm-genealogy-tree"
         data-user-id="<?php echo esc_attr($user_id); ?>"
         data-max-depth="<?php echo esc_attr($max_depth); ?>">
        <!-- Tree will be rendered here by JavaScript -->
    </div>
</div>

<style>
.mlm-genealogy-container {
    margin: 30px 0;
}

.mlm-genealogy-header {
    text-align: center;
    margin-bottom: 30px;
}

.mlm-genealogy-header h3 {
    font-size: 28px;
    color: #2c3e50;
    margin-bottom: 10px;
}

.mlm-genealogy-header p {
    color: #7f8c8d;
}

.mlm-genealogy-controls {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-bottom: 30px;
    flex-wrap: wrap;
}

.mlm-tree-container {
    display: flex;
    justify-content: center;
    transform-origin: center top;
}

.mlm-tree-node {
    display: flex;
    flex-direction: column;
    align-items: center;
    margin: 0 20px;
}

.mlm-tree-node-content {
    position: relative;
    z-index: 1;
}

.mlm-tree-children {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin-top: 60px;
}

.mlm-tree-child-wrapper {
    position: relative;
}

/* Connection lines */
.mlm-tree-node::before {
    content: '';
    position: absolute;
    top: 100%;
    left: 50%;
    width: 2px;
    height: 60px;
    background: #3498db;
    transform: translateX(-50%);
}

.mlm-tree-children::before {
    content: '';
    position: absolute;
    top: -60px;
    left: 0;
    right: 0;
    height: 2px;
    background: #3498db;
}

.mlm-tree-node:only-child::before,
.mlm-tree-node > .mlm-tree-node-content:only-child::before {
    display: none;
}

/* Loading state */
.mlm-genealogy-loading {
    text-align: center;
    padding: 60px;
}

/* Responsive */
@media (max-width: 768px) {
    .mlm-tree-node {
        margin: 0 10px;
    }

    .mlm-tree-children {
        gap: 10px;
    }

    .mlm-tree-node-content {
        min-width: 120px;
        padding: 12px;
    }

    .mlm-tree-node-avatar {
        width: 50px;
        height: 50px;
        font-size: 20px;
    }
}
</style>
