document.onready = function (ev) {
    $('#mbh_client_document_template_orientation')
        .select2()
        .on('change', function () {
            tinymce.get('mbh_client_document_template_content').plugins.mbh_data.changeOrentation();
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

    // var hotelProperty = mbh_property.hotel;

    var payerProperty = mbh_property.payer;

    self.touristProperty = payerProperty.mortal;

    var borderShow = 'border: 1px dotted black;';
    var borderHide = 'border: 1px dotted white;';

    editor.addButton('mbh_toggle_border', {
        text   : 'Border',
        icon   : false,
        onclick: function () {
            var content = editor.getContent();
            if (content.search(borderShow) != -1) {
                content = content.replace(borderShow, borderHide)
            } else {
                content = content.replace(borderHide, borderShow)
            }
            editor.setContent(content);
        }
    });

    self.changeOrentation = function (select) {
        var change = select === undefined ? false : select;

        var portrait = {
            value: 'width: 1240px;height: 1769px;',
            name : 'portrait'
        };
        var landscape = {
            value: 'width: 1769px;height: 1223px;',
            name : 'landscape'
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
        text   : 'Rotate',
        icon   : false,
        onclick: function () {
            self.changeOrentation(true);
        }
    });

    self.getMenu = function(entity, property, onSelect) {
        var menuItems = [];
        var func = onSelect === undefined ? true : onSelect;
        for (var i = 0; i < property.length; i++) {
            var str = '{{ ' + entity.toLowerCase() + '. ' + property[i] + ' }}';
            var tempObj = {
                tempStr: str,
                text : property[i].replace(/([a-z].*?)([A-Z][\w])/, '$1 $2')
            };
            if (func) {
                tempObj['onselect']  = function (e) {
                    editor.insertContent(this.settings.tempStr);
                }
            } else {
                tempObj.value = str;
            }
            menuItems.push(tempObj);
        }

        return menuItems;
    };

    editor.addMenuItem('mbh_hotel', {
        text: 'Hotel',
        menu: self.getMenu('Hotel', mbh_property.hotel)
    });

    editor.addMenuItem('mbh_order', {
        text: 'Order',
        menu: self.getMenu('Order', mbh_property.order)
    });

    editor.addMenuItem('mbh_payer', {
        text: 'Payer',
        menu: [
            {
                text: 'Organization',
                menu: self.getMenu('Payer',payerProperty.organ)
            },
            {
                text: 'Human',
                menu: self.getMenu('Payer',payerProperty.mortal)
            }
        ]
    });




    // var menuItems = [];
    // var menuItems2 = '';
    // tinymce.each(editor.menuItems, function (value, index) {
    //     if (index.search('mbh_') != -1) {
    //         menuItems.push(editor.menuItems[index]);
    //         menuItems2 += index + ' ';
    //     }
    // });

    // editor.addMenuItem('data', {
    //     context: 'mbh_menu',
    //     text   : 'Maxibooking',
    //     icon   : false,
    //     menu   : menuItems
    // });


    function isValid(value) {
        return value.search(/[^1-9]|^$/) === -1 && value <= 9;
    }

    function renderThead(thead){
        var table = '<table><thead><tr>';
        for (var i = 0; i < thead.length; i++){
            table += '<td>' + thead[i] + '</td>'
        }
        table += '</tr></thead>';
        return table;
    }

    function renderTbody(variables){
        var table = '<tbody>{% for tourist in package.tourists %}<tr>';
        for (var i = 0; i < variables.length; i++){
            table += '<td>' + variables[i] + '</td>'
        }
        table += '</tr>{% endfor %}</tbody></table>';
        return table;
    }

    function renderTable(thead,variables){
        return renderThead(thead) + renderTbody(variables);
    }

    function statementIf(obj){
        var html = '<div>{% if payer is instanceof' + obj + ' %}';
        html += '{# insert data if payer is ' + obj + ' #}';
        html += '{# end insert data #}{% endif %}</div>';

        return html;
    }

    editor.addMenuItem('statement_mbh', {
        text: 'EEE',
        menu: [
            {
                text: 'if Payer is Human',
                onselect: function () {
                    editor.insertContent(statementIf('Mortal'));
                }
            },
            {
                text: 'if Payer is Organization',
                onselect: function () {
                    editor.insertContent(statementIf('Organization'));
                }
            }
        ]
    });

    editor.addMenuItem('setting_table', {
        title  : 'Setting table',
        text   : 'add MB table',
        onclick: function () {
            editor.windowManager.open({
                title   : 'Setting table',
                body    : [
                    {
                        type  : 'listbox',
                        name  : 'source',
                        label : 'Source Data',
                        values: [
                            {text: 'Test1', value: 'test1'},
                            {text: 'Tourist', value: 'Tourist', selected: true}
                        ]
                    },
                    {
                        type   : 'textbox',
                        name   : 'amount',
                        label  : 'Amount columns',
                        tooltip: 'Only numbers',
                        value  : '4',
                        // onchange: function (e) {
                        //     console.log(e);
                        // },
                        onkeyup: function (e) {
                            if (isValid(e.target.value)) {
                                e.target.style.borderColor = 'green';
                            } else {
                                e.target.style.borderColor = 'red';
                            }
                        }
                    }

                ],
                onsubmit: function (e) {
                    if (isValid(e.data['amount'])) {
                        // console.log(e.data['source'].replace(/property(.*?)/,'$1'.toLowerCase()));
                        var menu = self.getMenu(e.data['source'],self[e.data['source'].toLowerCase() + 'Property'], false);

                        // console.dir(menu);

                        // e.data['source'];
                        var items = [];

                        for (var i = 1; i <= e.data['amount']; i++) {
                            items.push({
                                type  : 'container',
                                layout: 'flow',
                                label : i + ' column',
                                items : [
                                    {
                                        type : 'textbox',
                                        name : i + '_name',
                                        label: 'name column',
                                        value: 'Name Column ' + i
                                    },
                                    {
                                        type  : 'combobox',
                                        name  : i + '_value',
                                        label : 'combobox',
                                        values: menu
                                    }
                                ]
                            })
                        }

                        editor.windowManager.open({
                            title   : 'Container',
                            body    : items,
                            onsubmit: function (e) {

                                var headers = [];
                                var variables = [];

                                for (key in e.data){
                                    // console.log(key.search('value'));
                                    if (key.search('value') != -1) {
                                        variables.push(e.data[key]);
                                    } else if (key.search('name') != -1){
                                        headers.push(e.data[key]);
                                    }
                                }

                                // console.dir(headers);
                                // console.dir(variables);

                                // editor.insertHtml(renderTable(headers,variables));
                                tinymce.activeEditor.execCommand('mceInsertContent', false, renderTable(headers,variables));
                                // tinymce.activeEditor.windowManager.close();
                            }
                        });
                    } else {
                        e.preventDefault();
                    }
                }
                // ,
                // onpostrender: function () {
                //     console.dir(this);
                //     var container = this;
                //     // $.post( ajaxurl, {action: 'get_links'}, function( response ){
                //     var items = container.create( {text: 'Test add', type : 'listbox'} );
                //     container.append( items ).reflow().repaint();
                // }
                // onkeyup: function (e) {
                //     console.dir(e)
                // }
            });
        }
    });
});

// {
//     type   : 'textbox',
//         name   : 'headers',
//     label  : 'Name headers table',
//     // tooltip: 'Some nice tooltip to use',
//     value  : 'Name | Address'
//     // onchange: function (e) {
//     //     console.log(e);
//     // },
//     // onkeyup: function (e) {
//     //     console.log(e.target.value.search(/\D|^$/));
//     //     if (e.target.value.search(/[\D]|^$/) === -1 && e.target.value.length < 8 ){
//     //         e.target.style.borderColor = 'green';
//     //     } else {
//     //         // e.target.value =
//     //         e.target.style.borderColor = 'red';
//     //         return;
//     //     }
//     //     console.dir(e.target);
//     // }
// },
// {
//     type   : 'textbox',
//         name   : 'textbox multiline',
//     label  : 'textbox multiline',
//     multiline : true,
//     value  : 'default value\non another line'
// },
// {
//     type   : 'tooltip',
//         name   : 'tooltip',
//     label  : 'tooltip',
//     text   : 'Tooltip'
// },
// {
//     type   : 'button',
//         name   : 'button',
//     label  : 'button',
//     text   : 'My Button',
//     onclick: function(e) {
//     console.log(e);
//     alert('Click')
// }
// },
// {
//     type   : 'buttongroup',
//         name   : 'buttongroup',
//     label  : 'buttongroup',
//     items  : [
//     { text: 'Button 1', value: 'button1', onclick: function(e) {alert('Click')} },
//     { text: 'Button 2', value: 'button2', tooltip: 'A button' }
// ]
// },
// {
//     type   : 'checkbox',
//         name   : 'checkbox',
//     label  : 'checkbox',
//     text   : 'My Checkbox',
//     checked : true
// },
// {
//     type   : 'panelbutton',
//         label  : 'panelbutton',
//     name   : 'panelbutotn',
//     panel: {
//     autohide: true,
//         html: function() { return '<div>HTML can also be in a function.</div>' },
// }
// },
// // {
// //  type   : 'colorbutton',
// // },
// // Please see textcolor plugin for more information on colorbutton
// {
//     type   : 'radio',
//         name   : 'radio',
//     label  : 'radio ( defaults to checkbox with a class of "radio" )',
//     text   : 'My Radio Button'
// }