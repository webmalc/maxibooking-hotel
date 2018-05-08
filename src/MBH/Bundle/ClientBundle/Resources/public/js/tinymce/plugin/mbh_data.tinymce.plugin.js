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

    // self.mbh_border_style = 'none';
    // self.mbh_border_color = 'white';

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

    function menuText(str){
        var name = '',
            match = str.match(/get([A-Z].+)([A-Z].+)([A-Z].+)|([A-Z].+)([A-Z].+)|([A-Z].+)/);
        for (var i = 1, len = match.length; i <= len; i++){
            if (match[i] !== undefined){
                name += match[i] + ' ';
            }
        }
        return name.trim();
    }

    self.getMenu = function (entity, property, onSelect) {
        var menuItems = [];
        var func = onSelect === undefined ? true : onSelect;
        for (var i = 0, len = property.length; i < len; i++) {
            var str = '{{ ' + entity + '.' + property[i] + ' }}';
            var tempObj = {
                tempStr: str,
                text   : menuText(property[i])
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

    var firstItem = function (entity) {
        return [
            {
                text   : menuText(entity.ucFirst()),
                style  : 'margin-top: -6px; background-color: #888888; border-bottom: 1px solid black;',
                disabled: true,
                classes: 'first-item-in-table-with-entity'
            }
        ]
    };

    editor.addMenuItem('mbh_table_entity', {
        context     : 'contextmenu',
        text        : 'MB Table Entity',
        icon        : false,
        onPostRender: function () {
            var menu = this;
            editor.on('NodeChange', function (e) {
                var table = editor.dom.getParent(e.element, 'table');
                if (table === null || table.classList.value.search(/mbh_/) == -1) {
                    menu.disabled(true);
                    menu.settings.menu = null;
                } else {
                    var match = table.classList.value.match(/mbh_(.+)\s|$/);
                    if (match[1] !== undefined) {
                        menu.disabled(false);
                        menu.settings.menu = firstItem(match[1]).concat(self.getMenu(match[1], self[match[1] + 'Property']));
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
        text: 'MB Conditions',
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

    function createColorPickAction() {
        var colorPickerCallback = editor.settings.color_picker_callback;

        if (colorPickerCallback) {
            return function () {
                var self = this;

                colorPickerCallback.call(
                    editor,
                    function (value) {
                        self.value(value).fire('change');
                    },
                    self.value()
                );
            };
        }
    }

    function getBorderStyle() {
        if (self.mbh_border_style === 'none') {
            return '';
        }
        return self.mbh_border_width + 'px ' + self.mbh_border_style + ' ' + self.mbh_border_color;
    }

    function changeStyleTableProperty(where, value) {
        if (where !== undefined) {
            switch (where) {
                case 'style':
                    self.mbh_border_style = value;
                    break;
                case 'color':
                    self.mbh_border_color = value;
                    break;
                case 'width':
                    self.mbh_border_width = value;
                    break;
            }
        }

        document.querySelector('#example_border_style div').style.border = getBorderStyle();
    }

    function setTableProperty(table, data) {
        editor.dom.setStyle(table, 'width', data.width + 'px');
        editor.dom.setStyle(table, 'height', data.height + 'px');
        editor.dom.setStyle(table, 'borderCollapse', data['border-collapse']);
        editor.dom.setStyle(table, 'border', getBorderStyle());
    }

    function convertColor(str) {
        function convertDexToHex(number) {
            var n = parseInt(number, 10).toString(16);
            return n.length > 1 ? n : '0' + n;
        }

        if (str.search(/rgb/) != -1) {
            var num = str.match(/rgb\(\s?([\d]*?),\s?([\d]*?),\s?([\d]*?)\)/);
            return '#' + convertDexToHex(num[1]) + convertDexToHex(num[2]) + convertDexToHex(num[3]);
        }
        return str;
    }

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
                        var name = singleStyle[0].trim();
                        if (name === 'border') {
                            var border = singleStyle[1].trim().match(/([\d]*)px\s([\w]*?)\s(.*)/);
                            if (border[1] !== undefined) {
                                style['border-width'] = self.mbh_border_width = border[1];
                                style['border-style'] = self.mbh_border_style = border[2];
                                style['border-color'] = self.mbh_border_color = convertColor(border[3]);
                            }
                        } else {
                            style[name] = singleStyle[1].trim().replace(/px$/, '');
                        }
                    }
                });
            }

            console.log(style);

            editor.windowManager.open({
                title       : 'Style table',
                data        : style,
                heigth      : 'auto',
                onPostRender: function (e) {
                    changeStyleTableProperty();
                },
                body        : [
                    {
                        type   : 'textbox',
                        name   : 'width',
                        label  : 'Width',
                        tooltip: 'Only integer',
                        onkeyup: function (e) {
                            changeBorderColor(e.target, isInteger);
                        }
                    },
                    {
                        type   : 'textbox',
                        name   : 'height',
                        label  : 'Height',
                        tooltip: 'Only integer',
                        onkeyup: function (e) {
                            changeBorderColor(e.target, isInteger);
                        }
                    },
                    {
                        type  : 'listbox',
                        name  : 'border-collapse',
                        label : 'Border collapse',
                        values: [
                            {text: 'Separate', value: 'separate'},
                            {text: 'Collapse', value: 'collapse'}
                        ]
                    },
                    {
                        type : 'container',
                        label: 'Example border',
                        html : '<div id="example_border_style"><div style="padding: 2px; text-align: center;">Example</div></div>'
                    },
                    {
                        type    : 'listbox',
                        name    : 'border-style',
                        label   : 'Border style',
                        values  : [
                            {text: 'None', value: 'none'},
                            {text: 'Solid', style: 'border: 2px solid black;', value: 'solid'},
                            {text: 'Dotted', style: 'border: 2px dotted black;', value: 'dotted'}
                        ],
                        onSelect: function (e) {
                            changeStyleTableProperty('style', this.value());
                        }
                    },

                    {
                        type    : 'colorbox',  // colorpicker plugin MUST be included for this to work
                        name    : 'border-color',
                        label   : 'Border color',
                        onaction: createColorPickAction(),
                        onChange: function (e) {
                            changeStyleTableProperty('color', this.value());
                        }
                    },
                    {
                        type   : 'textbox',
                        name   : 'border-width',
                        label  : 'Border width',
                        tooltip: 'Only integer',
                        onkeyup: function (e) {
                            changeBorderColor(e.target, isInteger);
                            changeStyleTableProperty('width', this.value());
                        }
                    }
                ],
                onsubmit    : function (e) {
                    setTableProperty(table, e.data);
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
                            changeBorderColor(e.target, itemColumnIsValid);
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