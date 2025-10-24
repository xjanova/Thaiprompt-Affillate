/**
 * Thaiprompt MLM Genealogy Tree with GSAP Animation
 */

(function($) {
    'use strict';

    class MLMGenealogyTree {
        constructor(container, options = {}) {
            this.container = $(container);
            this.options = $.extend({
                userId: null,
                maxDepth: 5,
                nodeWidth: 160,
                nodeHeight: 180,
                horizontalSpacing: 40,
                verticalSpacing: 80,
                animationDuration: 0.8,
                staggerDelay: 0.1
            }, options);

            this.data = null;
            this.svg = null;
            this.init();
        }

        init() {
            this.createSVG();
            this.loadData();
        }

        createSVG() {
            this.container.html('<div class="mlm-genealogy-loading"><div class="mlm-loading-spinner"></div></div>');
        }

        loadData() {
            const self = this;

            $.ajax({
                url: thaipromptMLM.ajax_url,
                type: 'POST',
                data: {
                    action: 'thaiprompt_mlm_get_user_genealogy',
                    nonce: thaipromptMLM.nonce,
                    user_id: this.options.userId || thaipromptMLM.user_id,
                    max_depth: this.options.maxDepth
                },
                success: function(response) {
                    if (response.success) {
                        self.data = response.data;
                        self.render();
                    } else {
                        self.showError(response.data.message);
                    }
                },
                error: function() {
                    self.showError('Failed to load genealogy tree');
                }
            });
        }

        render() {
            this.container.html('');
            const treeContainer = $('<div class="mlm-tree-container"></div>');
            this.container.append(treeContainer);

            this.renderNode(this.data, treeContainer, 0);
            this.animateTree();
        }

        renderNode(node, container, level) {
            if (!node || level >= this.options.maxDepth) return;

            const nodeElement = this.createNodeElement(node, level);
            const nodeWrapper = $('<div class="mlm-tree-node" data-level="' + level + '"></div>');
            nodeWrapper.append(nodeElement);

            // Add children
            if (node.children && node.children.length > 0) {
                const childrenContainer = $('<div class="mlm-tree-children"></div>');

                node.children.forEach(child => {
                    const childWrapper = $('<div class="mlm-tree-child-wrapper"></div>');
                    this.renderNode(child, childWrapper, level + 1);
                    childrenContainer.append(childWrapper);
                });

                nodeWrapper.append(childrenContainer);
            }

            container.append(nodeWrapper);
        }

        createNodeElement(node, level) {
            const nodeDiv = $('<div class="mlm-tree-node-content"></div>');

            // Avatar
            const avatar = $('<div class="mlm-tree-node-avatar"></div>');
            const initial = node.name.charAt(0).toUpperCase();
            avatar.text(initial);

            // Name
            const name = $('<div class="mlm-tree-node-name"></div>').text(node.name);

            // Rank badge (if available)
            const rank = $('<div class="mlm-tree-node-rank"></div>');
            rank.text('Level ' + node.level);
            rank.css('background-color', this.getRankColor(node.level));

            // Stats
            const stats = $('<div class="mlm-tree-node-stats"></div>');
            stats.html(`
                <div>Sales: ${this.formatCurrency(node.personal_sales)}</div>
                <div>Team: ${node.left_count + node.right_count}</div>
            `);

            nodeDiv.append(avatar, name, rank, stats);

            // Add click handler
            nodeDiv.on('click', () => this.onNodeClick(node));

            return nodeDiv;
        }

        animateTree() {
            const nodes = this.container.find('.mlm-tree-node-content');

            // Set initial state
            gsap.set(nodes, {
                opacity: 0,
                scale: 0.5,
                y: 50
            });

            // Animate nodes level by level
            let delay = 0;
            for (let level = 0; level < this.options.maxDepth; level++) {
                const levelNodes = this.container.find(`.mlm-tree-node[data-level="${level}"] > .mlm-tree-node-content`);

                gsap.to(levelNodes, {
                    opacity: 1,
                    scale: 1,
                    y: 0,
                    duration: this.options.animationDuration,
                    delay: delay,
                    stagger: this.options.staggerDelay,
                    ease: 'back.out(1.7)'
                });

                delay += (levelNodes.length * this.options.staggerDelay) + 0.2;
            }

            // Animate connections (if any lines are drawn)
            this.animateConnections();
        }

        animateConnections() {
            // Draw SVG lines between nodes
            const connections = [];
            const nodes = this.container.find('.mlm-tree-node-content');

            nodes.each(function() {
                const $node = $(this);
                const $parent = $node.closest('.mlm-tree-node').parent().closest('.mlm-tree-node').find('> .mlm-tree-node-content').first();

                if ($parent.length) {
                    connections.push({
                        from: $parent,
                        to: $node
                    });
                }
            });

            // Create SVG overlay for connections
            if (connections.length > 0) {
                const svgNS = "http://www.w3.org/2000/svg";
                const svg = document.createElementNS(svgNS, "svg");
                svg.style.position = "absolute";
                svg.style.top = "0";
                svg.style.left = "0";
                svg.style.width = "100%";
                svg.style.height = "100%";
                svg.style.pointerEvents = "none";
                svg.style.zIndex = "0";

                this.container.css('position', 'relative');
                this.container.prepend(svg);

                connections.forEach(conn => {
                    const line = document.createElementNS(svgNS, "line");

                    const fromPos = conn.from.offset();
                    const toPos = conn.to.offset();
                    const containerPos = this.container.offset();

                    line.setAttribute("x1", fromPos.left - containerPos.left + conn.from.outerWidth() / 2);
                    line.setAttribute("y1", fromPos.top - containerPos.top + conn.from.outerHeight());
                    line.setAttribute("x2", toPos.left - containerPos.left + conn.to.outerWidth() / 2);
                    line.setAttribute("y2", toPos.top - containerPos.top);
                    line.setAttribute("stroke", "#3498db");
                    line.setAttribute("stroke-width", "2");
                    line.setAttribute("opacity", "0.5");

                    svg.appendChild(line);

                    // Animate line drawing
                    const length = Math.sqrt(
                        Math.pow(parseFloat(line.getAttribute("x2")) - parseFloat(line.getAttribute("x1")), 2) +
                        Math.pow(parseFloat(line.getAttribute("y2")) - parseFloat(line.getAttribute("y1")), 2)
                    );

                    gsap.from(line, {
                        strokeDasharray: length,
                        strokeDashoffset: length,
                        duration: 1,
                        ease: "power2.out"
                    });
                });
            }
        }

        onNodeClick(node) {
            // Show node details in modal or expand/collapse
            console.log('Node clicked:', node);

            // Highlight animation
            const nodeElement = this.container.find('.mlm-tree-node-content').filter(function() {
                return $(this).find('.mlm-tree-node-name').text() === node.name;
            });

            gsap.to(nodeElement, {
                scale: 1.1,
                duration: 0.2,
                yoyo: true,
                repeat: 1
            });

            // Trigger custom event
            $(document).trigger('mlm:nodeClicked', [node]);
        }

        getRankColor(level) {
            const colors = [
                '#95a5a6', // Gray
                '#cd7f32', // Bronze
                '#c0c0c0', // Silver
                '#ffd700', // Gold
                '#e5e4e2', // Platinum
                '#b9f2ff'  // Diamond
            ];

            return colors[Math.min(level, colors.length - 1)];
        }

        formatCurrency(amount) {
            return new Intl.NumberFormat('th-TH', {
                style: 'currency',
                currency: 'THB'
            }).format(amount);
        }

        showError(message) {
            this.container.html(`
                <div class="mlm-notice error">
                    <p>${message}</p>
                </div>
            `);
        }

        reload(userId) {
            this.options.userId = userId;
            this.loadData();
        }

        zoomIn() {
            gsap.to(this.container.find('.mlm-tree-container'), {
                scale: '+=0.1',
                duration: 0.3
            });
        }

        zoomOut() {
            gsap.to(this.container.find('.mlm-tree-container'), {
                scale: '-=0.1',
                duration: 0.3
            });
        }

        reset() {
            gsap.to(this.container.find('.mlm-tree-container'), {
                scale: 1,
                x: 0,
                y: 0,
                duration: 0.3
            });
        }
    }

    // jQuery plugin
    $.fn.mlmGenealogyTree = function(options) {
        return this.each(function() {
            const $this = $(this);
            let instance = $this.data('mlmGenealogyTree');

            if (!instance) {
                instance = new MLMGenealogyTree(this, options);
                $this.data('mlmGenealogyTree', instance);
            }

            return instance;
        });
    };

    // Auto-initialize on page load
    $(document).ready(function() {
        $('.mlm-genealogy-tree').each(function() {
            const $tree = $(this);
            const options = {
                userId: $tree.data('user-id'),
                maxDepth: $tree.data('max-depth') || 5
            };

            $tree.mlmGenealogyTree(options);
        });

        // Control buttons
        $(document).on('click', '.mlm-genealogy-zoom-in', function() {
            const tree = $(this).closest('.mlm-genealogy-container').find('.mlm-genealogy-tree').data('mlmGenealogyTree');
            if (tree) tree.zoomIn();
        });

        $(document).on('click', '.mlm-genealogy-zoom-out', function() {
            const tree = $(this).closest('.mlm-genealogy-container').find('.mlm-genealogy-tree').data('mlmGenealogyTree');
            if (tree) tree.zoomOut();
        });

        $(document).on('click', '.mlm-genealogy-reset', function() {
            const tree = $(this).closest('.mlm-genealogy-container').find('.mlm-genealogy-tree').data('mlmGenealogyTree');
            if (tree) tree.reset();
        });

        $(document).on('click', '.mlm-genealogy-reload', function() {
            const tree = $(this).closest('.mlm-genealogy-container').find('.mlm-genealogy-tree').data('mlmGenealogyTree');
            if (tree) tree.reload();
        });
    });

    // Export to global scope
    window.MLMGenealogyTree = MLMGenealogyTree;

})(jQuery);
