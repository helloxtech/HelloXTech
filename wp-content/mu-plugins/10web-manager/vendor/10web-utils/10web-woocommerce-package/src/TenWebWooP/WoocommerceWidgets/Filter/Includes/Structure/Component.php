<?php

namespace TenWebWooP\WoocommerceWidgets\Filter\Includes\Structure;

use TenWebWooP\WoocommerceWidgets\Filter\Includes\Widget;

class Component {

    protected $widget;

    protected $hook_manager;

    public function __construct(HookManager $hook_manager = null) {
        if (null === $hook_manager) {
            $hook_manager = new HookManager($this);
        }

        $this->setHookManager($hook_manager);
    }

    public function getHookManager() {
        return $this->hook_manager;
    }

    public function getWidget() {
        return $this->widget;
    }

    public function setWidget(Widget $widget) {
        $this->widget = $widget;
    }

    public function setHookManager(HookManager $hook_manager) {
        $this->hook_manager = $hook_manager;
    }

    public function attachHooks(HookManager $hook_manager) {
    }

    public function initialProperties() {
    }
}
