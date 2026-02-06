class TWBB_DUPLICATE_TOOL extends FE_TOOL_FRONTEND {

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
        window.parent.$e.run('document/elements/duplicate', {
            container
        });
    }
}



let duplicate_tool;
jQuery(document).on('ready', function () {
    duplicate_tool= new TWBB_DUPLICATE_TOOL();
    window['duplicate_tool'] = duplicate_tool;
    duplicate_tool.init();
});