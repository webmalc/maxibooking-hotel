document.onready = function (ev) {
    $('#mbh_client_document_template_orientation')
        .select2()
        .on('change', function () {
            tinymce.get('mbh_client_document_template_content').plugins.mbh_data.changeOrentation();
        });
};


tinymce.PluginManager.add('mbh_data', function (editor, url) {

    "use strict";

    String.prototype.ucFirst = function () {
        var str = this;
        if (str.length) {
            str = str.charAt(0).toUpperCase() + str.slice(1);
        }
        return str;
    };

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
    self.cashDocumentProperty = mbh_property.cashDocument;

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

    // function ucFirst(str) {
    //     return str.map(function (value, index) {
    //         if (index === 0) {
    //             return value.toUpperCase();
    //         }
    //     })
    // }


    self.getMenu = function (entity, property, onSelect) {
        var menuItems = [];
        var func = onSelect === undefined ? true : onSelect;
        for (var i = 0; i < property.length; i++) {
            var str = '{{ ' + entity + '.' + property[i] + ' }}';
            var tempObj = {
                tempStr: str,
                text   : property[i].replace(/([a-z].*?)([A-Z][\w])/, '$1 $2')
            };
            if (func) {
                tempObj['onselect'] = function (e) {
                    editor.insertContent(this.settings.tempStr);
                }
            } else {
                tempObj.value = str;
            }
            menuItems.push(tempObj);
        }

        return menuItems;
    };

    editor.addMenuItem('mbh_user', {
        text: 'User',
        menu: self.getMenu('user', mbh_property.user)
    });

    editor.addMenuItem('mbh_package', {
        text: 'Package',
        menu: self.getMenu('package', mbh_property.package)
    });

    editor.addMenuItem('mbh_hotel', {
        text: 'Hotel',
        menu: self.getMenu('hotel', mbh_property.hotel)
    });

    editor.addMenuItem('mbh_order', {
        text: 'Order',
        menu: self.getMenu('order', mbh_property.order)
    });

    editor.addMenuItem('mbh_payer', {
        text: 'Payer',
        menu: [
            {
                text: 'Organization',
                menu: self.getMenu('payer', payerProperty.organ)
            },
            {
                text: 'Human',
                menu: self.getMenu('payer', payerProperty.mortal)
            }
        ]
    });

    var menuItems = [];
    // var menuItems2 = '';
    tinymce.each(editor.menuItems, function (value, index) {
        if (index.search('mbh_') != -1) {
            menuItems.push(editor.menuItems[index]);
            // menuItems2 += index + ' ';
        }
    });

    menuItems.push({
        text   : 'Current date',
        onclick: function () {
            editor.insertContent('{{ currentDate }}')
        }
    });

    editor.addMenuItem('mbh_all_data', {
        context: 'contextmenu',
        text   : 'MB Entity',
        icon   : false,
        menu   : menuItems
    });

    editor.addMenuItem('mbh_table_entity', {
        context     : 'contextmenu',
        text        : 'MB Table Entity',
        icon        : false,
        onPostRender: function () {
            var menu = this;
            // var table = editor.dom.getParent(editor.selection.getStart(),'table');
            editor.on('NodeChange', function (e) {
                var table = editor.dom.getParent(e.element, 'table');
                if (table === null || table.classList.value.search(/mbh_/) == -1) {
                    menu.disabled(true);
                    menu.settings.menu = null;
                } else {
                    var match = table.classList.value.match(/mbh_(.*)\s|$/);
                    if (match[1] !== undefined) {
                        menu.disabled(false);
                        menu.settings.menu = self.getMenu(match[1], self[match[1] + 'Property']);
                    }
                }
            })

        }
    });

    function itemColumnIsValid(value) {
        return value.search(/[^1-9]|^$/) === -1 && value <= 9;
    }

    function isInteger(value) {
        return value.search(/[^0-9]/) === -1;
    }

    function changeBorderColor(target, func) {
        if (func(target.value)) {
            target.style.borderColor = 'green';
        } else {
            target.style.borderColor = 'red';
        }
    }

    function insertThead(elClass, thead) {
        var table = '<table class="mbh_' + elClass + '"><thead><tr>';
        if (self.mbh_Counter) {
            table += '<td>#</td>';
        }
        for (var i = 0; i < thead.length; i++) {
            table += '<td>' + thead[i] + '</td>'
        }
        table += '</tr></thead>';
        return table;
    }

    function insertTbody(entityTarget, entitySource, variables) {
        var table = '';
        if (self.mbh_Counter) {
            table += '{% set count = 0 %}'
        }
        table += '<tbody>{% for ' + entityTarget + ' in ' + entitySource + ' %}<tr>';
        if (self.mbh_Counter) {
            table += '{% set count = count +1 %}<td>{{ count }}</td>';
        }
        for (var i = 0; i < variables.length; i++) {
            table += '<td>' + variables[i] + '</td>'
        }
        table += '</tr>{% endfor %}</tbody></table>';
        return table;
    }

    self.dataForTable = [
        {text: 'Cash Document', value: 'cashDocument'},
        {text: 'Tourist', value: 'tourist', selected: true}
    ];

    self.touristInsertTable = function (thead, variables) {
        return insertThead('tourist', thead) + insertTbody('tourist', 'package.allTourists', variables);
    };

    self.cashDocumentInsertTable = function (thead, variables) {
        return insertThead('cashDocument', thead) + insertTbody('cashDocument', 'order.allCashDocuments', variables);
    };

    function statementIf(obj) {
        var html = '<div>{% if payer is instanceof' + obj + ' %}';
        html += '{# insert data if payer is ' + obj + ' #}';
        html += '{# end insert data #}{% endif %}</div>';

        return html;
    }

    editor.addMenuItem('statement_mbh', {
        text: 'EEE',
        menu: [
            {
                text    : 'if Payer is Human',
                onselect: function () {
                    editor.insertContent(statementIf('Mortal'));
                }
            },
            {
                text    : 'if Payer is Organization',
                onselect: function () {
                    editor.insertContent(statementIf('Organization'));
                }
            }
        ]
    });

    var tableForm = {
        title: 'pizdec',
        // type: 'form',
        // layout: 'flex',
        items: [
            {
                type : 'form',
                items: [
                    {
                        label: 'Width', name: 'width'
                    }
                ]
            }
        ]
    };

    editor.addMenuItem('mbh_table_property', {
        title       : "MB Table properties",
        text        : 'MB Table properties',
        onPostRender: function () {
            var menu = this;
            editor.on('NodeChange', function (e) {
                var table = editor.dom.getParent(e.element, 'table');
                if (table === null) {
                    menu.disabled(true);
                } else {
                    menu.disabled(false);
                }
            })
        },
        onclick     : function (e) {
            var table = editor.dom.getParent(editor.selection.getStart(), 'table'),
                styleStr = table.getAttribute('style'),
                style = {};
            if (styleStr !== null) {
                styleStr.split(';').map(function (item) {
                    if (item !== '') {
                        var singleStyle = item.split(':');
                        style[singleStyle[0]] = singleStyle[1].trim().replace(/px$/, '');
                    }
                });

            }

            editor.windowManager.open({
                title: 'Parent xz',
                data : style,
                body : [
                    {
                        type   : 'textbox',
                        name   : 'width',
                        label  : 'Width',
                        tooltip: 'Only integer',
                        onkeyup: function (e) {
                            changeBorderColor(e.target,isInteger);
                        }
                    },
                    {
                        type   : 'textbox',
                        name   : 'height',
                        label  : 'Height',
                        tooltip: 'Only integer',
                        onkeyup: function (e) {
                            changeBorderColor(e.target,isInteger);
                        }
                    },
                    {
                        type   : 'colorbutton'
                    }
                ],
                onsubmit: function (e) {
                    console.log(e.data)
                }
            });
            // console.dir(table);
            // console.log(style);
        }
    });

    editor.addMenuItem('mbh_table_add', {
        title  : 'table add',
        text   : 'MB Table add',
        onclick: function () {
            editor.windowManager.open({
                title   : 'Setting table',
                body    : [
                    {
                        type  : 'listbox',
                        name  : 'source',
                        label : 'Source Data',
                        values: self.dataForTable
                    },
                    {
                        type : 'checkbox',
                        name : 'counter',
                        label: 'Counter'
                    },
                    {
                        type   : 'textbox',
                        name   : 'amount',
                        label  : 'Amount columns',
                        tooltip: 'Only numbers',
                        value  : '4',
                        onkeyup: function (e) {
                            changeBorderColor(e.target,itemColumnIsValid);
                        }
                    }

                ],
                onsubmit: function (e) {
                    if (itemColumnIsValid(e.data['amount'])) {
                        var menu = self.getMenu(e.data['source'], self[e.data['source'] + 'Property'], false);

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
                        self.mbh_SourceName = e.data['source'];
                        self.mbh_Counter = e.data['counter'];
                        editor.windowManager.open({
                            title   : 'Container',
                            body    : items,
                            onsubmit: function (e) {
                                var headers = [];
                                var variables = [];
                                for (var key in e.data) {
                                    if (key.search('value') != -1) {
                                        variables.push(e.data[key]);
                                    } else if (key.search('name') != -1) {
                                        headers.push(e.data[key]);
                                    }
                                }
                                tinymce.activeEditor.execCommand('mceInsertContent', false, self[self.mbh_SourceName + 'InsertTable'](headers, variables));
                            }
                        });
                    } else {
                        e.preventDefault();
                    }
                }
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