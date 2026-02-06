<?php

namespace Tenweb_Builder\Apps\FastEditor\Widgets;

class NestedAccordionFastEditor extends BaseWidgetFastEditor
{
    public $widget = 'Nested_Accordion';

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
                            'analytics' => 'Accordion add',
                            'tooltip' => 'Add item',
                        ),
                    ),
                ),
            )
        );
    }

}
