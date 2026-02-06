class TWBCUpdateElementorTree {
    constructor(args, main_container, action_type) {
        this.tree = args['tree'];
        this.deleted_repeated_widgets = args['deleted_repeated_widgets'];
        this.deleted_widgets_ids = args['deleted_widgets_ids'];
        this.main_container = main_container;
        this.action_type = action_type;
        this.elementorContainersChache = {};
    }

    run() {
        this.walk(this.tree, null);
        try {
            this.deleteRepeatedWidgets();
            this.deleteWidgets();
        } catch (e) {
            console.log("TWBCUpdateElementorTree.run.error", e);
        }

        if(this.main_container){
            this.main_container.view.render()
        }
    }

    walk(nodes, parent) {
        for (let index = 0; index < nodes.length; index++) {
            let node = nodes[index];

            if (node['is_new_widget'] === true) {
                if (node['repeated_data']) {
                    this.addNewRepeatedWidget(node, parent);
                } else {
                    this.addNewWidget(node, parent, index);
                }
            } else {
                // don't change order of this.updateWidgetSettings and this.moveWidgetIfNeeded
                // If you first move the widget and then update the settings, the widget styles will be reset to defaults
                if (Object.values(node['settings']).length || (node['globals_to_unset'] && Object.values(node['globals_to_unset']).length)) {
                    this.updateWidgetSettings(node, parent);
                }

                if(node['repeated_data']){
                    this.moveRepeatedWidgetIfNeeded(node, parent, index);
                }else{
                    this.moveWidgetIfNeeded(node, parent, index);
                }
            }

            this.walk(node['children'], node);
        }
    }

    addNewWidget(node, parent_node, index) {
        let at = 0;
        if (parent_node !== null) {
            at = index;
        } else if (Object.keys(this.main_container).length === 0) {
            // if there is no element selected, create a new container and add the widget to it
            this.createEmptyContainer();
            parent_node = this.main_container;
            at = parent_node["children"].length;
        } else if (this.main_container["type"] === "container") {
            // if no parent node is specified, but there is a container selected, add the widget to the end of container
            parent_node = this.main_container;
            at = parent_node["children"].length;
        } else {
            // if no parent node and no container selected, add the widget to the parent container of the selected widget
            parent_node = this.main_container.parent;
            for (let n of parent_node["children"]) {
                if (n["id"] === this.main_container["id"]) {
                    break;
                }
                at++;
            }
            at++;
        }

        let model = {
            "id": node["id"],
            "elType": node["type"] === "container" ? 'container' : "widget",
            "widgetType": node["type"],
            "settings": node["settings"],
            "children": []
        }

        if (typeof coPilot !== 'undefined' && coPilot.newAddedWidgetModelId !== 'undefined') {
            coPilot.newAddedWidgetModelId = node["id"];
        }
        let widget_container = window.parent.$e.commands.run('document/elements/create', {
            "container": this.findContainerById(parent_node["id"]),
            "model": model,
            "options": {
                "at": at
            }
        });


        console.log("Adding new widget: ", node, parent_node["id"], at);
        if (node["children"].length === 0 || !widget_container["repeaters"] || !node["children"][0]["repeated_data"]) {
            return;
        }

        let child_control_name = node["children"][0]["repeated_data"]["control_name"];
        if (!widget_container["repeaters"][child_control_name] || !widget_container["repeaters"][child_control_name]["children"]) {
            return;
        }

        let child_count = widget_container["repeaters"][child_control_name]["children"].length;
        for (let i = 0; i <= child_count; i++) {
            window.parent.$e.commands.run('document/repeater/remove', {
                "container": widget_container,
                "index": 0,
                "name": child_control_name
            });
        }

    }

    createEmptyContainer() {
        let newContainerData = {
            elType: 'container', // Specify container type
            settings: {
                flex_direction: "column",
            },
        };

        this.main_container = window.parent.$e.commands.run('document/elements/create', {
            container: elementor.settings.page.getEditedView().getContainer(),
            model: newContainerData,
            options: {
                at: -1, // Position at the end
            }
        });
    }

    addNewRepeatedWidget(node, parent_node) {
        let model = node['settings'];
        model["_id"] = node["id"];

        window.parent.$e.commands.run('document/repeater/insert', {
            "container": this.findContainerById(parent_node["id"]),
            "model": model,
            "name": node["repeated_data"]["control_name"]
        });

        console.log("Adding new repeated widget: ", node, parent_node["id"]);
    }

    updateWidgetSettings(node, parent_node) {

        let container
        if (node["repeated_data"]) {
            let parent_container = this.findContainerById(parent_node["id"]);

            for(let child of TWBCUtils.getChildrenContainers(parent_container)){
                if(child["id"] === node["id"]){
                    container = child;
                    break;
                }
            }

        } else {
            container = this.findContainerById(node["id"]);
        }


        if( this.action_type !== 'image_generation' ) {
            TWBCUtils.disableGlobals(container, node["globals_to_unset"]).then(function () {
                TWBCUtils.setSetting(container, node["settings"], {
                    external: true,
                    render: true,
                });
            });
        } else {
            TWBCUtils.setSetting(container, node["settings"], {
                external: true,
                render: true,
            });
        }

    }

    deleteRepeatedWidgets() {
        for (let deleted_widget of this.deleted_repeated_widgets) {
            let parent_container = this.findContainerById(deleted_widget["parent_widget_id"]);

            let index = this.getChildIndex(parent_container, deleted_widget["repeated_widget_id"]);

            window.parent.$e.commands.run('document/repeater/remove', {
                "container": parent_container,
                "index": index,
                "name": deleted_widget["repeater_name"]
            });
        }
    }

    deleteWidgets(){
        let containers = [];
        for (let widget_id of this.deleted_widgets_ids) {
            containers.push(this.findContainerById(widget_id));
        }

        if(!containers.length){
            return;
        }

        window.parent.$e.commands.run('document/elements/delete', {
            "containers": containers,
        });
    }

    moveWidgetIfNeeded(node, parentNode, newPosition) {
        if (!parentNode) {
            return;
        }

        let widgetContainer = this.findContainerById(node["id"]);
        let parentContainer = this.findContainerById(parentNode["id"]);

        let currentParentContainer = widgetContainer.parent;
        let currentPosition = this.getChildIndex(currentParentContainer, node["id"]);

        if (parentContainer["id"] === currentParentContainer["id"] && currentPosition === newPosition) {
            return;
        }

        window.parent.$e.run('document/elements/move', {
            container: widgetContainer,
            target: parentContainer,
            options: {
                at: newPosition
            }
        });
    }

    moveRepeatedWidgetIfNeeded(node, parentNode, targetIndex) {
        let parentContainer = this.findContainerById(parentNode["id"]);
        let childrenContainers = TWBCUtils.getChildrenContainers(parentContainer);
        let sourceIndex = null;

        for (let i = 0; i < childrenContainers.length; i++) {
            if (childrenContainers[i]["id"] === node["id"]) {
                sourceIndex = i;
                break;
            }
        }

        if(sourceIndex === null){
            console.log("Source index not found");
            return;
        }

        $e.run('document/repeater/move', {
            container: parentContainer,
            name: node["repeated_data"]["control_name"],
            sourceIndex: sourceIndex,
            targetIndex: targetIndex
        });
    }

    getChildIndex(parent_container, child_id) {
        let index = 0;

        for (let child of TWBCUtils.getChildrenContainers(parent_container)) {
            if (child["id"] === child_id) {
                break;
            }
            index++;
        }

        return index;
    }

    findContainerById(id){
        // if widget is not in the editor html (e.g. deleted), delete it from cache
        if(this.elementorContainersChache[id] && this.elementorContainersChache[id].view.el.isConnected === false){
            delete this.elementorContainersChache[id];
        }


        if(!this.elementorContainersChache[id]){
            this.elementorContainersChache[id] = TWBCUtils.findContainerById(id);
        }

        return this.elementorContainersChache[id];
    }

}