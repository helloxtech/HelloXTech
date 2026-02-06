<?php

namespace TenWebWooP\WoocommerceWidgets\Filter\Includes\Structure;

use TenWebWooP\WoocommerceWidgets\Filter\Includes\Widget;

class ComponentBuilder {

    protected $widget;

    public function getWidget() {
        return $this->widget;
    }

    public function setWidget(Widget $widget) {
        $this->widget = $widget;
    }

    public function build($component, $implementation = true) {
        if (is_string($component)) {
            $component = new $component();
        }

        if (! $component instanceof Component) {
            return false;
        }

        if ($implementation) {
            $this->implementation($component);
        }

        return $component;
    }

    public function implementation(Component $component) {
        $component->setWidget($this->getWidget());

        $component->initialProperties();

        $component->attachHooks($component->getHookManager());
    }
}
