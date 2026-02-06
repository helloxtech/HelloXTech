<?php

namespace TenWebWooP\WoocommerceWidgets\Filter\Includes\Structure;

class HookManager {

    protected $component;

    public function __construct(Component $component) {
        $this->component = $component;
    }

    public function getComponent() {
        return $this->component;
    }

    public function applyFilters($filter, $value = null) {
        return call_user_func_array('apply_filters', func_get_args());
    }

    public function triggerAction($action) {
        call_user_func_array('do_action', func_get_args());
    }

    public function addAction($action, $handler, $priority = 10, $accepted_args = 1) {
        add_action($action, $this->prepareHandler($handler), $priority, $accepted_args);
    }

    public function removeAction($action, $handler, $priority = 10, $accepted_args = 1) {
        remove_action($action, $this->prepareHandler($handler), $priority, $accepted_args);
    }

    public function addFilter($filter, $handler, $priority = 10, $accepted_args = 1) {
        add_filter($filter, $this->prepareHandler($handler), $priority, $accepted_args);
    }

    public function removeFilter($action, $handler, $priority = 10, $accepted_args = 1) {
        remove_filter($action, $this->prepareHandler($handler), $priority, $accepted_args);
    }

    protected function prepareHandler($handler) {
        if (is_string($handler) && method_exists($this->component, $handler)) {
            $handler = array( $this->component, $handler );
        }

        return $handler;
    }
}
