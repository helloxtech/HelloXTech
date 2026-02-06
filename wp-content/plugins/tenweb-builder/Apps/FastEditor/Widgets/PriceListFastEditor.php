<?php

namespace Tenweb_Builder\Apps\FastEditor\Widgets;

class PriceListFastEditor extends BaseWidgetFastEditor
{
    public $widget = 'Price_List';

    protected function setToolsList()
    {
        $this->toolsList = (
            array(
              array(
                'class' => '\Tenweb_Builder\Apps\FastEditor\Tools\ClickTool',
                'changed-control-data' => array(
                  array(
                      'control_name' => 'elementor-repeater-add',
                      'title' => 'Repeater Add',
                      'analytics' => 'Price List add',
                      'tooltip' => 'Add item',
                  ),
                ),
              ),
            )
        );
    }

}
