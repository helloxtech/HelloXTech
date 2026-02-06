<?php

namespace Tenweb_Builder\Apps\FastEditor\Widgets;

class VideoFastEditor extends BaseWidgetFastEditor
{
    public $widget = 'Widget_Video';

    protected function setToolsList()
    {
        $this->toolsList = (
            array(
                array(
                    'class' => '\Tenweb_Builder\Apps\FastEditor\Tools\VideoTool',
                    'changed-control-data' => array(
                        array('control_name' => 'url', 'title' => 'Url'),
                    ),
                ),
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
