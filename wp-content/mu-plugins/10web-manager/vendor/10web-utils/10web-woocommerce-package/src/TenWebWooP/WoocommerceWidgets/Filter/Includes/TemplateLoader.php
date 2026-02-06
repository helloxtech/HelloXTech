<?php

namespace TenWebWooP\WoocommerceWidgets\Filter\Includes;

class TemplateLoader {

    public function get_template_locate($template_name, $path = null) {
        return trailingslashit($path) . $template_name;
    }

    public function render_template($template_name, array $context = array(), $path = null) {
        $template = $this->compile_template($template_name, $context, $path);

        if (is_string($template)) {
            echo $template; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        }
    }

    public function compile_template($template_name, array $context = array(), $path = null) {
        $template_loader = $this;
        $template_file = $this->get_template_locate($template_name, $path);

        if (! is_readable($template_file)) {
            return false;
        }

        ob_start();

        // This is because the `get_template_locate` method.
        // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable
        include $template_file;

        return ob_get_clean();
    }
}
