<?php
/**
 * Unit tests for Elegant TOC
 *
 * @package ElegantTOC
 * @since 1.7.0
 */

class Test_Elegant_TOC extends WP_UnitTestCase {

    /**
     * Test singleton pattern
     */
    public function test_singleton() {
        $instance1 = Elegant_TOC::get_instance();
        $instance2 = Elegant_TOC::get_instance();
        $this->assertSame($instance1, $instance2);
    }

    /**
     * Test options default values
     */
    public function test_default_options() {
        delete_option('elegant_toc_options');
        $instance = Elegant_TOC::get_instance();
        $options = $instance->get_options();

        $this->assertTrue($options['enabled']);
        $this->assertEquals(3, $options['min_headings']);
        $this->assertEquals(array('h2', 'h3', 'h4'), $options['heading_levels']);
        $this->assertEquals('light', $options['color_theme']);
    }

    /**
     * Test post disabled check
     */
    public function test_post_disabled() {
        $post_id = $this->factory->post->create();
        $instance = Elegant_TOC::get_instance();

        $this->assertFalse($instance->is_post_disabled($post_id));

        update_post_meta($post_id, 'disable_toc', '1');
        $this->assertTrue($instance->is_post_disabled($post_id));

        delete_post_meta($post_id, 'disable_toc');
        $this->assertFalse($instance->is_post_disabled($post_id));
    }

    /**
     * Test heading extraction
     */
    public function test_heading_extraction() {
        $content = '<h2>Test Heading 1</h2><p>Content</p><h3>Test Heading 2</h3>';
        $instance = Elegant_TOC::get_instance();

        // Use reflection to access private method
        $reflection = new ReflectionClass($instance);
        $method = $reflection->getMethod('extract_and_annotate_headings');
        $method->setAccessible(true);

        $result = $method->invoke($instance, $content, array('h2', 'h3'));
        $headings = $result['headings'];

        $this->assertCount(2, $headings);
        $this->assertEquals('Test Heading 1', $headings[0]['text']);
        $this->assertEquals(2, $headings[0]['level']);
        $this->assertEquals('Test Heading 2', $headings[1]['text']);
        $this->assertEquals(3, $headings[1]['level']);
    }

    /**
     * Test color theme validation
     */
    public function test_color_theme_validation() {
        $instance = Elegant_TOC::get_instance();
        $allowed_themes = $instance->get_allowed_color_themes();

        $this->assertContains('light', $allowed_themes);
        $this->assertContains('blue', $allowed_themes);
        $this->assertContains('dark', $allowed_themes);
        $this->assertNotContains('invalid', $allowed_themes);
    }

    /**
     * Test custom CSS sanitization
     */
    public function test_custom_css_sanitization() {
        $instance = Elegant_TOC::get_instance();

        // Test malicious CSS removal
        $input = array(
            'custom_css' => '#elegant-toc { color: red; javascript:alert(1); }'
        );

        $reflection = new ReflectionClass($instance);
        if (is_admin()) {
            $admin = new Elegant_TOC_Admin($instance);
            $result = $admin->sanitize_options($input);
        } else {
            $method = $reflection->getMethod('sanitize_options');
            $method->setAccessible(true);
            $result = $method->invoke($instance, $input);
        }

        $this->assertStringNotContainsString('javascript:', $result['custom_css']);
    }

    /**
     * Test options caching
     */
    public function test_options_caching() {
        $instance = Elegant_TOC::get_instance();

        // Clear cache
        $instance->clear_options_cache();

        // First call should hit database
        $options1 = $instance->get_options();

        // Second call should use cache
        $options2 = $instance->get_options();

        $this->assertEquals($options1, $options2);
    }

    /**
     * Test shortcode rendering
     */
    public function test_shortcode_rendering() {
        $instance = Elegant_TOC::get_instance();
        $output = $instance->render_shortcode();

        $this->assertStringContainsString('elegant-toc-placeholder', $output);
    }

    /**
     * Test supported post types filter
     */
    public function test_supported_post_types_filter() {
        $instance = Elegant_TOC::get_instance();

        add_filter('elegant_toc_post_types', function($types) {
            $types[] = 'custom_post_type';
            return $types;
        });

        $types = $instance->get_supported_post_types();
        $this->assertContains('custom_post_type', $types);
    }
}
