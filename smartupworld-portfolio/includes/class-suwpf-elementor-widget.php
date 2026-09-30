<?php
/**
 * Elementor widget: Portfolio Showcase.
 *
 * Projects come either from the Portfolio admin section or from a repeater
 * filled in directly in the widget.
 */

defined( 'ABSPATH' ) || exit;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Repeater;
use Elementor\Widget_Base;

class SUWPF_Elementor_Widget extends Widget_Base {

	public function get_name() {
		return 'suw-portfolio';
	}

	public function get_title() {
		return __( 'Portfolio Showcase', 'smartupworld-portfolio' );
	}

	public function get_icon() {
		return 'eicon-gallery-grid';
	}

	public function get_categories() {
		return array( 'smartupworld', 'general' );
	}

	public function get_keywords() {
		return array( 'portfolio', 'projects', 'clients', 'showcase', 'filter', 'gallery' );
	}

	public function get_style_depends() {
		return array( 'suwpf' );
	}

	public function get_script_depends() {
		return array( 'suwpf' );
	}

	protected function register_controls() {

		/* ── CONTENT: Header ─────────────────────────────────────────── */
		$this->start_controls_section( 'section_header', array(
			'label' => __( 'Header', 'smartupworld-portfolio' ),
		) );

		$this->add_control( 'eyebrow', array(
			'label'   => __( 'Eyebrow', 'smartupworld-portfolio' ),
			'type'    => Controls_Manager::TEXT,
			'default' => __( 'Portfolio', 'smartupworld-portfolio' ),
		) );

		$this->add_control( 'heading', array(
			'label'       => __( 'Heading', 'smartupworld-portfolio' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => __( 'Our Clients / Demos', 'smartupworld-portfolio' ),
			'label_block' => true,
		) );

		$this->add_control( 'heading_tag', array(
			'label'   => __( 'Heading HTML Tag', 'smartupworld-portfolio' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'h2',
			'options' => array( 'h1' => 'H1', 'h2' => 'H2', 'h3' => 'H3', 'h4' => 'H4', 'div' => 'div' ),
		) );

		$this->add_control( 'subheading', array(
			'label'   => __( 'Description', 'smartupworld-portfolio' ),
			'type'    => Controls_Manager::TEXTAREA,
			'rows'    => 3,
			'default' => __( 'Explore our portfolio to see the innovative projects and solutions we\'ve crafted for our clients.', 'smartupworld-portfolio' ),
		) );

		$this->end_controls_section();

		/* ── CONTENT: Projects ───────────────────────────────────────── */
		$this->start_controls_section( 'section_projects', array(
			'label' => __( 'Projects', 'smartupworld-portfolio' ),
		) );

		$this->add_control( 'source', array(
			'label'   => __( 'Source', 'smartupworld-portfolio' ),
			'type'    => Controls_Manager::SELECT,
			'default' => 'posts',
			'options' => array(
				'manual' => __( 'Add projects here', 'smartupworld-portfolio' ),
				'posts'  => __( 'Portfolio admin section', 'smartupworld-portfolio' ),
			),
		) );

		$repeater = new Repeater();
		$repeater->add_control( 'title', array(
			'label'       => __( 'Project name', 'smartupworld-portfolio' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => __( 'Project name', 'smartupworld-portfolio' ),
			'label_block' => true,
		) );
		$repeater->add_control( 'type', array(
			'label'       => __( 'Type / category', 'smartupworld-portfolio' ),
			'type'        => Controls_Manager::TEXT,
			'default'     => __( 'Business', 'smartupworld-portfolio' ),
			'description' => __( 'Projects with the same type are grouped under one filter tab.', 'smartupworld-portfolio' ),
		) );
		$repeater->add_control( 'image', array(
			'label' => __( 'Screenshot', 'smartupworld-portfolio' ),
			'type'  => Controls_Manager::MEDIA,
		) );
		$repeater->add_control( 'link', array(
			'label'       => __( 'Website URL', 'smartupworld-portfolio' ),
			'type'        => Controls_Manager::URL,
			'placeholder' => 'https://example.com',
		) );

		$this->add_control( 'items', array(
			'label'       => __( 'Projects', 'smartupworld-portfolio' ),
			'type'        => Controls_Manager::REPEATER,
			'fields'      => $repeater->get_controls(),
			'title_field' => '{{{ title }}} — {{{ type }}}',
			'default'     => array(),
			'condition'   => array( 'source' => 'manual' ),
		) );

		$this->add_control( 'post_types', array(
			'label'       => __( 'Only these types (slugs)', 'smartupworld-portfolio' ),
			'type'        => Controls_Manager::TEXT,
			'placeholder' => 'blog, business',
			'description' => __( 'Leave empty to show every project.', 'smartupworld-portfolio' ),
			'condition'   => array( 'source' => 'posts' ),
		) );

		$this->add_control( 'post_limit', array(
			'label'     => __( 'Number of projects', 'smartupworld-portfolio' ),
			'type'      => Controls_Manager::NUMBER,
			'min'       => 0,
			'default'   => 0,
			'description' => __( '0 = all', 'smartupworld-portfolio' ),
			'condition' => array( 'source' => 'posts' ),
		) );

		$this->end_controls_section();

		/* ── CONTENT: Layout ─────────────────────────────────────────── */
		$this->start_controls_section( 'section_layout', array(
			'label' => __( 'Layout & Features', 'smartupworld-portfolio' ),
		) );

		$this->add_responsive_control( 'columns', array(
			'label'           => __( 'Columns', 'smartupworld-portfolio' ),
			'type'            => Controls_Manager::SELECT,
			'options'         => array( '1' => '1', '2' => '2', '3' => '3', '4' => '4' ),
			'default'         => '3',
			'tablet_default'  => '2',
			'mobile_default'  => '1',
			'selectors'       => array( '{{WRAPPER}} .suwpf' => '--suwpf-cols: {{VALUE}};' ),
		) );

		$this->add_control( 'show_filter', array(
			'label'        => __( 'Filter tabs', 'smartupworld-portfolio' ),
			'type'         => Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'default'      => 'yes',
		) );

		$this->add_control( 'show_counts', array(
			'label'        => __( 'Counts on tabs', 'smartupworld-portfolio' ),
			'type'         => Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'default'      => 'yes',
			'condition'    => array( 'show_filter' => 'yes' ),
		) );

		$this->add_control( 'all_label', array(
			'label'     => __( '"All" tab label', 'smartupworld-portfolio' ),
			'type'      => Controls_Manager::TEXT,
			'default'   => __( 'All', 'smartupworld-portfolio' ),
			'condition' => array( 'show_filter' => 'yes' ),
		) );

		$this->add_control( 'show_browser', array(
			'label'        => __( 'Browser frame on cards', 'smartupworld-portfolio' ),
			'type'         => Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'default'      => 'yes',
		) );

		$this->add_control( 'full_width_bg', array(
			'label'        => __( 'Full-width background', 'smartupworld-portfolio' ),
			'type'         => Controls_Manager::SWITCHER,
			'return_value' => 'yes',
			'default'      => 'yes',
			'description'  => __( 'Background runs edge to edge; content stays in the container.', 'smartupworld-portfolio' ),
		) );

		$this->add_control( 'cta_text', array(
			'label'     => __( 'Button text', 'smartupworld-portfolio' ),
			'type'      => Controls_Manager::TEXT,
			'default'   => __( 'Start your project', 'smartupworld-portfolio' ),
			'separator' => 'before',
		) );

		$this->add_control( 'cta_link', array(
			'label'       => __( 'Button link', 'smartupworld-portfolio' ),
			'type'        => Controls_Manager::URL,
			'placeholder' => '#contact',
			'description' => __( 'Leave empty to hide the button.', 'smartupworld-portfolio' ),
		) );

		$this->end_controls_section();

		/* ── STYLE: Colours ──────────────────────────────────────────── */
		$this->start_controls_section( 'style_colors', array(
			'label' => __( 'Colours', 'smartupworld-portfolio' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$colors = array(
			'accent'    => array( __( 'Accent (tabs, links)', 'smartupworld-portfolio' ), '#1565C0', '--suwpf-accent' ),
			'accent_lt' => array( __( 'Accent light', 'smartupworld-portfolio' ), '#E3F0FB', '--suwpf-accent-lt' ),
			'cta'       => array( __( 'Button & badge', 'smartupworld-portfolio' ), '#FF6D00', '--suwpf-cta' ),
			'dark'      => array( __( 'Headings', 'smartupworld-portfolio' ), '#0D1B2A', '--suwpf-dark' ),
			'muted'     => array( __( 'Body text', 'smartupworld-portfolio' ), '#5A7080', '--suwpf-muted' ),
			'border'    => array( __( 'Borders', 'smartupworld-portfolio' ), '#D8E8F5', '--suwpf-border' ),
			'bg_from'   => array( __( 'Background top', 'smartupworld-portfolio' ), '#F4F8FD', '--suwpf-bg-from' ),
			'bg_to'     => array( __( 'Background bottom', 'smartupworld-portfolio' ), '#FFFFFF', '--suwpf-bg-to' ),
		);
		foreach ( $colors as $key => $c ) {
			$this->add_control( 'color_' . $key, array(
				'label'     => $c[0],
				'type'      => Controls_Manager::COLOR,
				'default'   => $c[1],
				'selectors' => array( '{{WRAPPER}} .suwpf' => $c[2] . ': {{VALUE}};' ),
			) );
		}

		$this->end_controls_section();

		/* ── STYLE: Cards & spacing ──────────────────────────────────── */
		$this->start_controls_section( 'style_cards', array(
			'label' => __( 'Cards & Spacing', 'smartupworld-portfolio' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_responsive_control( 'section_padding', array(
			'label'      => __( 'Section padding (top/bottom)', 'smartupworld-portfolio' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => 0, 'max' => 200 ) ),
			'default'    => array( 'size' => 88, 'unit' => 'px' ),
			'selectors'  => array( '{{WRAPPER}} .suwpf' => '--suwpf-pad: {{SIZE}}{{UNIT}};' ),
		) );

		$this->add_responsive_control( 'container_width', array(
			'label'      => __( 'Content width', 'smartupworld-portfolio' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => 600, 'max' => 1600 ) ),
			'default'    => array( 'size' => 1200, 'unit' => 'px' ),
			'selectors'  => array( '{{WRAPPER}} .suwpf' => '--suwpf-width: {{SIZE}}{{UNIT}};' ),
		) );

		$this->add_responsive_control( 'gap', array(
			'label'      => __( 'Gap between cards', 'smartupworld-portfolio' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => 0, 'max' => 60 ) ),
			'default'    => array( 'size' => 28, 'unit' => 'px' ),
			'selectors'  => array( '{{WRAPPER}} .suwpf' => '--suwpf-gap: {{SIZE}}{{UNIT}};' ),
		) );

		$this->add_control( 'radius', array(
			'label'      => __( 'Card corner radius', 'smartupworld-portfolio' ),
			'type'       => Controls_Manager::SLIDER,
			'size_units' => array( 'px' ),
			'range'      => array( 'px' => array( 'min' => 0, 'max' => 32 ) ),
			'default'    => array( 'size' => 16, 'unit' => 'px' ),
			'selectors'  => array( '{{WRAPPER}} .suwpf' => '--suwpf-radius: {{SIZE}}{{UNIT}};' ),
		) );

		$this->add_control( 'ratio', array(
			'label'     => __( 'Screenshot shape', 'smartupworld-portfolio' ),
			'type'      => Controls_Manager::SELECT,
			'default'   => '16/10',
			'options'   => array( '16/10' => '16:10', '16/9' => '16:9', '4/3' => '4:3', '1/1' => '1:1', '3/4' => '3:4 (tall)' ),
			'selectors' => array( '{{WRAPPER}} .suwpf' => '--suwpf-ratio: {{VALUE}};' ),
		) );

		$this->end_controls_section();

		/* ── STYLE: Typography ───────────────────────────────────────── */
		$this->start_controls_section( 'style_typo', array(
			'label' => __( 'Typography', 'smartupworld-portfolio' ),
			'tab'   => Controls_Manager::TAB_STYLE,
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'heading_typo',
			'label'    => __( 'Heading', 'smartupworld-portfolio' ),
			'selector' => '{{WRAPPER}} .suwpf__heading',
		) );

		$this->add_group_control( Group_Control_Typography::get_type(), array(
			'name'     => 'card_title_typo',
			'label'    => __( 'Card title', 'smartupworld-portfolio' ),
			'selector' => '{{WRAPPER}} .suwpf__title',
		) );

		$this->end_controls_section();
	}

	protected function render() {
		$s = $this->get_settings_for_display();

		if ( isset( $s['source'] ) && 'posts' === $s['source'] ) {
			$items = SUWPF_Post_Type::get_items( array(
				'limit' => isset( $s['post_limit'] ) ? absint( $s['post_limit'] ) : 0,
				'type'  => isset( $s['post_types'] ) ? $s['post_types'] : '',
			) );
		} else {
			$items = array();
			foreach ( (array) ( $s['items'] ?? array() ) as $row ) {
				$items[] = array(
					'title'    => $row['title'] ?? '',
					'type'     => $row['type'] ?? '',
					'url'      => $row['link']['url'] ?? '',
					'image'    => $row['image']['url'] ?? '',
					'image_id' => isset( $row['image']['id'] ) ? (int) $row['image']['id'] : 0,
				);
			}
		}

		echo SUWPF_Renderer::render( $items, array( // phpcs:ignore WordPress.Security.EscapeOutput -- escaped in renderer.
			'eyebrow'       => $s['eyebrow'] ?? '',
			'heading'       => $s['heading'] ?? '',
			'heading_tag'   => $s['heading_tag'] ?? 'h2',
			'subheading'    => $s['subheading'] ?? '',
			'show_filter'   => $s['show_filter'] ?? '',
			'show_counts'   => $s['show_counts'] ?? '',
			'show_browser'  => $s['show_browser'] ?? '',
			'all_label'     => $s['all_label'] ?? __( 'All', 'smartupworld-portfolio' ),
			'cta_text'      => $s['cta_text'] ?? '',
			'cta_url'       => $s['cta_link']['url'] ?? '',
			'full_width_bg' => $s['full_width_bg'] ?? '',
		) );
	}
}
