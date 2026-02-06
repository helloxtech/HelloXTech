class TWBB_DELETE_TOOL extends FE_TOOL_FRONTEND {
    constructor() {
        super();
    }

    init() {
        super.init();
    }

    registerEvents() {

    }

    onToolClick(tool) {
        this.dataPush(jQuery(tool));
        let container = this.container;
        window.parent.$e.run('document/elements/delete', {
            container
        });
    }
}



let delete_tool;
jQuery(document).on('ready', function () {
    delete_tool = new TWBB_DELETE_TOOL();
    window['delete_tool'] = delete_tool;
    delete_tool.init();
});