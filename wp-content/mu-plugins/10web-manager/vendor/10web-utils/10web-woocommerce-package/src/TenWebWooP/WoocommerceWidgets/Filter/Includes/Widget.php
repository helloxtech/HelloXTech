<?php

namespace TenWebWooP\WoocommerceWidgets\Filter\Includes;

use TenWebWooP\WoocommerceWidgets\Filter\Includes\Admin\Admin;
use TenWebWooP\WoocommerceWidgets\Filter\Includes\Admin\Ajax;
use TenWebWooP\WoocommerceWidgets\Filter\Includes\Admin\Pages\PageLoader;
use TenWebWooP\WoocommerceWidgets\Filter\Includes\Structure\ComponentBuilder;

class Widget {

    protected $component_register;

    protected $entity_register;

    /**
     * Widget constructor.
     */
    public function __construct() {
    }

    /**
     * Initialize the Widget.
     *
     * @return void
     */
    public function init() {
        $this->attachHooks();
        $this->loadComponents();
    }

    /**
     * Attach relevant hooks.
     */
    protected function attachHooks() {
    }

    protected function loadComponents() {
        $component_builder = new ComponentBuilder();
        $component_builder->setWidget($this);

        foreach ($this->getComponents() as $component) {
            $component_builder->build($component);
        }
    }

    public function GetComponentRegister() {
        return $this->component_register;
    }

    public function GetObjectRegister() {
        return $this->object_register;
    }

    public function GetEntityRegister() {
        return $this->entity_register;
    }

    public function GetWidgetUrl() {
        return plugin_dir_url(TWW_PRODUCT_FILTER_FILE);
    }

    public function GetWidgetPath() {
        return plugin_dir_url(TWW_PRODUCT_FILTER_FILE);
    }

    public function GetResourceUrl() {
        return $this->GetWidgetUrl() . 'assets/';
    }

    public function GetAssetsUrl() {
        return $this->GetWidgetUrl() . 'assets/';
    }

    public function GetAssetsPath() {
        return $this->GetWidgetPath() . 'assets/';
    }

    /**
     * Get the components.
     *
     * @return array
     */
    protected function getComponents() {
        $components = array(
            PostType::class,
            ComponentBuilder::class,
            Assets::class,
        );

        if (is_admin()) {
            $components = array_merge(
                array(
                    Admin::class,
                    Ajax::class,
                    PageLoader::class,
                ),
                $components
            );
        }

        return $components;
    }
}
