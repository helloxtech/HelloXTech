<?php

namespace Tenweb_Builder\Apps\FastEditor\Widgets;

class DefaultWidgetFastEditor extends BaseWidgetFastEditor
{
    //default to show for all widgets
    public $widget = 'All_Widgets';

    protected function setToolsList()
    {
        $this->toolsList = (
            array(
                array(
                    'class' => '\Tenweb_Builder\Apps\FastEditor\Tools\DuplicateTool',
                    'changed-control-data' => [],
                ),
                array(
                    'class' => '\Tenweb_Builder\Apps\FastEditor\Tools\DeleteTool',
                    'changed-control-data' => [],
                ),
                array(
                    'class' => '\Tenweb_Builder\Apps\FastEditor\Tools\MoreTool',
                    'changed-control-data' => [],
                ),
            )
        );
    }

}
