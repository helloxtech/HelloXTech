<?php

namespace Tenweb_Builder\Apps\FastEditor\Widgets;

class ButtonFastEditor extends BaseWidgetFastEditor
{
    public $widget = 'Widget_Button';

    protected function setToolsList()
    {
        $this->toolsList = (
            array(
                array(
                    'class' => '\Tenweb_Builder\Apps\FastEditor\Tools\URLTool',
                    'changed-control-data' => array(
                        array('control_name' => 'link', 'title' => 'Link', 'analytics' => 'Button Link'),
                    ),
                ),
                array(
                    'class' => '\Tenweb_Builder\Apps\FastEditor\Tools\ColorPickerTool',
                    'changed-control-data' => array(
                        array('control_name' => 'button_text_color', 'title' => 'Text color'),
                        array('control_name' => 'background_color', 'title' => 'Background color'),
                    ),
                ),
            )
        );
    }

}
