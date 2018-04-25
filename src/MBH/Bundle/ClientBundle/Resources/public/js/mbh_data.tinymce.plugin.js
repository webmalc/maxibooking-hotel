document.onready = function (ev) {
    $('#mbh_client_document_template_orientation')
        .select2()
        .on('change', function () {
            tinymce.get('mbh_client_document_template_content').plugins.mbh_data.changeRotation();
        });
};


tinymce.PluginManager.add('mbh_data', function (editor, url) {
    // editor.addButton('mbh_example', {
    //     text: 'MBH button',
    //     icon: false,
    //     onclick: function() {
    //         // Open window
    //         editor.windowManager.open({
    //             title: 'Example plugin 2',
    //             body: [
    //                 {type: 'textbox', name: 'title', label: 'Title'}
    //             ],
    //             onsubmit: function(e) {
    //                 // Insert content when the window form is submitted
    //                 editor.insertContent('Title: ' + e.data.title);
    //             }
    //         });
    //     }
    // });

    // var editor = editor.activeEditor;

    // console.log(editor);
    // console.log(editor.editorManager.DOM.doc);

    var self = this;

    var hotelProperty = [
        'fullTitle','internationalTitle','zipCode', 'street' , 'mapLink', 'aboutLink', 'internationalStreetName',
        'city', 'region', 'country', 'house', 'flat'
    ];

    var payerProperty = [
        'birthday', 'fullName'
    ];


    var borderShow = 'border: 1px dotted black;';
    var borderHide = 'border: 1px dotted white;';

    editor.addButton('mbh_toggle_border', {
            text: 'Border',
            icon: false,
            onclick: function() {
                var content = editor.getContent();
                if (content.search(borderShow) != -1) {
                    content = content.replace(borderShow, borderHide)
                } else {
                    content = content.replace(borderHide, borderShow)
                }
                editor.setContent(content);
            }
        });

    self.changeRotation = function (select) {
        var change = select === undefined ? false : select;

        var portrait = {
            value:'width: 1240px;height: 1769px;',
            name: 'portrait'
        };
        var landscape = {
            value: 'width: 1769px;height: 1223px;',
            name: 'landscape'
        };

        function changeSelect(change, orientation) {
            if (!change) {
                return;
            }
            $('#mbh_client_document_template_orientation option')
                .attr('selected', false)
                .prop('selected', false);

            $('#mbh_client_document_template_orientation [value="' + orientation + '"]')
                .attr('selected', 'selected')
                .prop('selected', 'selected');

            $('#mbh_client_document_template_orientation')
                .select2()
                .val();
        }

        var content = editor.getContent();

        if (content.search(portrait.value) != -1) {
            content = content.replace(portrait.value, landscape.value);
            changeSelect(change, landscape.name)
        } else {
            content = content.replace(landscape.value, portrait.value);
            changeSelect(change, portrait.name)
        }
        editor.setContent(content);
    };

    editor.addButton('mbh_rotate', {
        text: 'Rotate',
        icon: false,
        onclick: function () {
            self.changeRotation(true);
        }
    });

    function getMenu(property){
        var menuItems = [];
        for (var i = 0; i < property.length ; i++){
            menuItems.push({
                temp_count: i,
                text: 'Hotel ' + property[i].replace(/([a-z].*?)([A-Z][\w])/, '$1 $2'),
                onselect: function (e) {
                    editor.insertContent('{{ if_exist(hotel, "' + property[this.settings.temp_count] + '") }}');
                }
            });
        }
        return menuItems;
    }

    editor.addMenuItem('mbh_hotel', {
        text: 'Hotel',
        menu: getMenu(hotelProperty)
    });

    editor.addMenuItem('mbh_payer', {
        text: 'Payer',
        menu: getMenu(payerProperty)
    });

    var menuItems = [];
    var menuItems2 = '';
    tinymce.each(editor.menuItems, function(value, index) {
        if (index.search('mbh_') != -1){
            menuItems.push(editor.menuItems[index]);
            menuItems2 += index + ' ';
        }
    });

    editor.addMenuItem('data', {
        context: 'mbh_menu',
        text: 'Maxibooking',
        icon: false,
        menu: menuItems
    });
});