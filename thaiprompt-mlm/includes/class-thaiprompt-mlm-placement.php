<?php
/**
 * MLM Placement algorithms class
 */
class Thaiprompt_MLM_Placement {

    /**
     * Place user in network based on settings
     */
    public static function place_user($user_id, $sponsor_id, $placement_type = null) {
        if (!$placement_type) {
            $settings = get_option('thaiprompt_mlm_settings', array());
            $placement_type = $settings['placement_type'] ?? 'auto';
        }

        switch ($placement_type) {
            case 'auto':
                return self::auto_placement($sponsor_id);

            case 'left':
                return self::left_placement($sponsor_id);

            case 'right':
                return self::right_placement($sponsor_id);

            case 'balanced':
                return self::balanced_placement($sponsor_id);

            default:
                return self::auto_placement($sponsor_id);
        }
    }

    /**
     * Auto placement - find first available spot (binary tree BFS)
     */
    public static function auto_placement($sponsor_id) {
        global $wpdb;
        $table = $wpdb->prefix . 'thaiprompt_mlm_network';

        $queue = array($sponsor_id);
        $visited = array();
        $max_iterations = 1000; // Prevent infinite loop
        $iterations = 0;

        while (!empty($queue) && $iterations < $max_iterations) {
            $current_id = array_shift($queue);
            $iterations++;

            if (in_array($current_id, $visited)) {
                continue;
            }
            $visited[] = $current_id;

            // Check if user exists in network
            $current_user = Thaiprompt_MLM_Database::get_user_network($current_id);
            if (!$current_user) {
                continue;
            }

            // Get children
            $children = Thaiprompt_MLM_Database::get_downline($current_id);
            $has_left = false;
            $has_right = false;

            foreach ($children as $child) {
                if ($child->position === 'left') {
                    $has_left = true;
                    $queue[] = $child->user_id;
                }
                if ($child->position === 'right') {
                    $has_right = true;
                    $queue[] = $child->user_id;
                }
            }

            // Found empty spot
            if (!$has_left) {
                return array(
                    'placement_id' => $current_id,
                    'position' => 'left'
                );
            }
            if (!$has_right) {
                return array(
                    'placement_id' => $current_id,
                    'position' => 'right'
                );
            }
        }

        // Fallback
        return array(
            'placement_id' => $sponsor_id,
            'position' => 'left'
        );
    }

    /**
     * Left placement - always try to fill left leg first
     */
    public static function left_placement($sponsor_id) {
        $queue = array($sponsor_id);
        $visited = array();
        $max_iterations = 1000;
        $iterations = 0;

        while (!empty($queue) && $iterations < $max_iterations) {
            $current_id = array_shift($queue);
            $iterations++;

            if (in_array($current_id, $visited)) {
                continue;
            }
            $visited[] = $current_id;

            $children = Thaiprompt_MLM_Database::get_downline($current_id);
            $left_child = null;
            $right_child = null;

            foreach ($children as $child) {
                if ($child->position === 'left') {
                    $left_child = $child;
                }
                if ($child->position === 'right') {
                    $right_child = $child;
                }
            }

            // Try left first
            if (!$left_child) {
                return array(
                    'placement_id' => $current_id,
                    'position' => 'left'
                );
            }

            // Then right
            if (!$right_child) {
                return array(
                    'placement_id' => $current_id,
                    'position' => 'right'
                );
            }

            // Continue down left leg first, then right
            if ($left_child) {
                $queue[] = $left_child->user_id;
            }
            if ($right_child) {
                $queue[] = $right_child->user_id;
            }
        }

        return array(
            'placement_id' => $sponsor_id,
            'position' => 'left'
        );
    }

    /**
     * Right placement - always try to fill right leg first
     */
    public static function right_placement($sponsor_id) {
        $queue = array($sponsor_id);
        $visited = array();
        $max_iterations = 1000;
        $iterations = 0;

        while (!empty($queue) && $iterations < $max_iterations) {
            $current_id = array_shift($queue);
            $iterations++;

            if (in_array($current_id, $visited)) {
                continue;
            }
            $visited[] = $current_id;

            $children = Thaiprompt_MLM_Database::get_downline($current_id);
            $left_child = null;
            $right_child = null;

            foreach ($children as $child) {
                if ($child->position === 'left') {
                    $left_child = $child;
                }
                if ($child->position === 'right') {
                    $right_child = $child;
                }
            }

            // Try right first
            if (!$right_child) {
                return array(
                    'placement_id' => $current_id,
                    'position' => 'right'
                );
            }

            // Then left
            if (!$left_child) {
                return array(
                    'placement_id' => $current_id,
                    'position' => 'left'
                );
            }

            // Continue down right leg first, then left
            if ($right_child) {
                $queue[] = $right_child->user_id;
            }
            if ($left_child) {
                $queue[] = $left_child->user_id;
            }
        }

        return array(
            'placement_id' => $sponsor_id,
            'position' => 'right'
        );
    }

    /**
     * Balanced placement - try to keep left and right legs balanced
     */
    public static function balanced_placement($sponsor_id) {
        $sponsor_data = Thaiprompt_MLM_Database::get_user_network($sponsor_id);

        if (!$sponsor_data) {
            return array(
                'placement_id' => $sponsor_id,
                'position' => 'left'
            );
        }

        // Check which leg has fewer members
        if ($sponsor_data->left_count <= $sponsor_data->right_count) {
            // Place in left leg
            return self::find_position_in_leg($sponsor_id, 'left');
        } else {
            // Place in right leg
            return self::find_position_in_leg($sponsor_id, 'right');
        }
    }

    /**
     * Find position in specific leg
     */
    private static function find_position_in_leg($user_id, $leg) {
        $children = Thaiprompt_MLM_Database::get_downline($user_id);
        $target_child = null;

        foreach ($children as $child) {
            if ($child->position === $leg) {
                $target_child = $child;
                break;
            }
        }

        if (!$target_child) {
            // Empty spot found
            return array(
                'placement_id' => $user_id,
                'position' => $leg
            );
        }

        // Continue down this leg
        return self::auto_placement($target_child->user_id);
    }

    /**
     * Get available positions for a sponsor
     */
    public static function get_available_positions($sponsor_id) {
        $children = Thaiprompt_MLM_Database::get_downline($sponsor_id);
        $positions = array('left' => true, 'right' => true);

        foreach ($children as $child) {
            if ($child->position === 'left') {
                $positions['left'] = false;
            }
            if ($child->position === 'right') {
                $positions['right'] = false;
            }
        }

        return $positions;
    }

    /**
     * Validate placement
     */
    public static function validate_placement($placement_id, $position) {
        // Check if placement user exists
        $placement_user = Thaiprompt_MLM_Database::get_user_network($placement_id);
        if (!$placement_user) {
            return new WP_Error('invalid_placement', __('Invalid placement user', 'thaiprompt-mlm'));
        }

        // Check if position is valid
        if (!in_array($position, array('left', 'right'))) {
            return new WP_Error('invalid_position', __('Invalid position', 'thaiprompt-mlm'));
        }

        // Check if position is available
        $children = Thaiprompt_MLM_Database::get_downline($placement_id);
        foreach ($children as $child) {
            if ($child->position === $position) {
                return new WP_Error('position_taken', __('Position already taken', 'thaiprompt-mlm'));
            }
        }

        return true;
    }

    /**
     * Move user to new position (admin function)
     */
    public static function move_user($user_id, $new_placement_id, $new_position) {
        global $wpdb;
        $table = $wpdb->prefix . 'thaiprompt_mlm_network';

        // Validate new placement
        $validation = self::validate_placement($new_placement_id, $new_position);
        if (is_wp_error($validation)) {
            return $validation;
        }

        // Get user's current data
        $user_data = Thaiprompt_MLM_Database::get_user_network($user_id);
        if (!$user_data) {
            return new WP_Error('user_not_found', __('User not found in network', 'thaiprompt-mlm'));
        }

        $old_placement_id = $user_data->placement_id;

        // Update user's placement
        $wpdb->update(
            $table,
            array(
                'placement_id' => $new_placement_id,
                'position' => $new_position
            ),
            array('user_id' => $user_id)
        );

        // Update counts for old and new parents
        if ($old_placement_id) {
            Thaiprompt_MLM_Database::update_downline_counts($old_placement_id);
        }
        Thaiprompt_MLM_Database::update_downline_counts($new_placement_id);

        do_action('thaiprompt_mlm_user_moved', $user_id, $old_placement_id, $new_placement_id, $new_position);

        return true;
    }
}
