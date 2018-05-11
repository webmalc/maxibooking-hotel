document.onready = function (ev) {
    $('#mbh_client_document_template_orientation')
        .select2()
        .on('change', function () {
            tinymce.get('mbh_client_document_template_content').plugins.mbh_data.changeOrentation();
        });
};


tinymce.PluginManager.add('mbh_data', function (editor, url) {
    "use strict";

    var self = this;

    String.prototype.ucFirst = function () {
        var str = this;
        if (str.length) {
            str = str.charAt(0).toUpperCase() + str.slice(1);
        }
        return str;
    };

    String.prototype.convertStyle = function () {
        var strArr = this.split('-'),
            str = '';
        for (var i = 0, len = strArr.length; i < len; i++ ){
            if (i>0){
                str += strArr[i].ucFirst();
            } else {
                str += strArr[i];
            }
        }
        return str;
    };

    function menuText(str) {
        var name = '',
            match = str.match(/get([A-Z].+)([A-Z].+)([A-Z].+)|([A-Z].+)([A-Z].+)|([A-Z].+)/);
        for (var i = 1, len = match.length; i <= len; i++) {
            if (match[i] !== undefined) {
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

    self.mbh_property = {};
    self.mbh_property.table = {};
    for (var tableKey in mbh_property.table) {
        self.mbh_property.table[tableKey] = mbh_property.table[tableKey];
    }


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

    for (var entityName in mbh_property.common) {
        if (entityName === 'payer') {
            editor.addMenuItem('mbh_payer', {
                text: 'Payer',
                menu: [
                    {
                        text: 'Organization',
                        menu: self.getMenu('payer', mbh_property.common.payer.organ)
                    },
                    {
                        text: 'Human',
                        menu: self.getMenu('payer', mbh_property.common.payer.mortal)
                    }
                ]
            });
        } else {
            editor.addMenuItem('mbh_' + entityName, {
                text: menuText(entityName.ucFirst()),
                menu: self.getMenu(entityName, mbh_property.common[entityName])
            });
        }

    }

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

    // var firstItem = function (entity) {
    //     return [
    //         {
    //             text   : menuText(entity.ucFirst()),
    //             style  : 'margin-top: -6px; background-color: #888888; border-bottom: 1px solid black;',
    //             disabled: true,
    //             classes: 'first-item-in-table-with-entity'
    //         }
    //     ]
    // };


    function changeMenuItemsTableEntity(entityMenu) {
        if (self.mbh_table !== null) {
            try {
                if (entityMenu.settings.entity === self.mbh_table_match[1]) {
                    entityMenu.show();
                } else {
                    entityMenu.hide();
                }
            } catch (error) {

            }
        }
    }

    var menuEntityTable = [];
    for (var entityNameInTable in self.mbh_property.table) {
        menuEntityTable.push(
            {
                text        : menuText(entityNameInTable.ucFirst()),
                menu        : self.getMenu(entityNameInTable, self.mbh_property.table[entityNameInTable]),
                entity      : entityNameInTable,
                onPostRender: function (e) {
                    var entityMenu = this;
                    changeMenuItemsTableEntity(entityMenu);
                    editor.on('NodeChange', function (e) {
                        changeMenuItemsTableEntity(entityMenu);
                    });
                }
            }
        )
    }

    editor.addMenuItem('mbh_table_entity', {
        context     : 'contextmenu',
        text        : 'MB Table Entity',
        icon        : false,
        onPostRender: function () {
            var menu = this;
            editor.on('NodeChange', function (e) {
                self.mbh_table = editor.dom.getParent(e.element, 'table');
                if (self.mbh_table === null || self.mbh_table.classList.value.search(/mbh_/) == -1) {
                    menu.disabled(true);
                    menu.settings.menu = null;
                } else {
                    self.mbh_table_match = self.mbh_table.classList.value.match(/mbh_(.+)\s|$/);
                    menu.disabled(false);
                    menu.settings.menu = menuEntityTable;
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

    function changeBorderColorInForm(target, func) {
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

    function changeStyleExample(prefix, style, value) {
        var node = document.querySelector('#' + prefix + 'example div');

        if (typeof style === "object") {
            for (var key in style){
                if (key.search(new RegExp(prefix)) !== -1){
                    var nameStyle = key.split('_')[1];
                    if (nameStyle === 'width' || nameStyle === 'height'){
                        continue;
                    }
                    node.style[nameStyle] = addSuffixPX(key, style);
                }
            }
        } else if (typeof style === "string") {
            node.style[style] = addSuffixPX(style, value);
        }
    }

    function addSuffixPX(style, data) {
        var suffix = '';
        if (style.search(/width/i) !== -1 || style.search(/height/i) !== -1 ){
            suffix = 'px';
        }
        if (typeof data === "string"){
            return data + suffix;
        }
        return data[style] + suffix;
    }

    function setTableProperty(table, data) {
        for (var style in data){
            editor.dom.setStyle(table, style, addSuffixPX(style, data));
        }
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

    var PREFIX_TABLE = 'table_',
        PREFIX_THEAD = 'thead_',
        PREFIX_TBODY = 'tbody_';

    var PREFIX = [
        PREFIX_THEAD,
        PREFIX_TBODY,
        PREFIX_TABLE
    ];

    // for (var i = 0, len = PREFIX.length; i<len; i++){
    //     self[PREFIX[i] + 'sideBorder'] = '';
    // }

    function isPrefix(prefixRaw) {
        var prefix = prefixRaw.split('_')[0] + '_';
        return PREFIX.some(function (value) {
            return value === prefix;
        })
    }


    function formStyleChangeBorder(prefix) {
        function add(side) {
            return {
                type: 'container',
                    // label  : 'flex row',
                    layout: 'flex',
                direction: 'row',
                align: 'center',
                spacing: 5,
                disabled: true,
                // minWidth: 20,
                // maxWidth: 160,
                // minHeight: 160,
                items: [
                {
                    type: 'container',
                    layout: 'flex',
                    direction: 'column',
                    // label  : 'flex row1',
                    items: [
                        {type: 'label', text: 'Border style'},
                        {
                            type    : 'listbox',
                            name    : prefix + 'border' + side +'Style',
                            label   : 'Border style',
                            values  : [
                                {text: 'None', value: 'none'},
                                {text: 'Solid', style: 'border: 2px solid black;', value: 'solid'},
                                {text: 'Dotted', style: 'border: 2px dotted black;', value: 'dotted'}
                            ],
                            onSelect: function (e) {
                                changeStyleExample(prefix, 'border' + side +'Style', this.value());
                            }
                        },
                    ]
                },
                {
                    type: 'container',
                    layout: 'flex',
                    direction: 'column',
                    // label  : 'flex row2',
                    items: [
                        {type: 'label', text: 'Border color'},
                        {
                            type    : 'colorbox',  // colorpicker plugin MUST be included for this to work
                            name    : prefix + 'border' + side +'Color',
                            label   : 'Border color',
                            onaction: createColorPickAction(),
                            onChange: function (e) {
                                changeStyleExample(prefix, 'border' + side +'Color', this.value());
                            }
                        },
                    ]
                },
                {
                    type: 'container',
                    layout: 'flex',
                    direction: 'column',
                    // label  : 'flex row1',
                    items: [
                        {type: 'label', text: 'Border width'},
                        {
                            type   : 'textbox',
                            name   : prefix + 'border' + side +'Width',
                            label  : 'Border width',
                            tooltip: 'Only integer',
                            onkeyup: function (e) {
                                changeBorderColorInForm(e.target, isInteger);
                                changeStyleExample(prefix, 'border' + side +'Width', this.value());
                            }
                        },
                    ]
                }
            ]
            }
        }


        return [

            // {
            //     type    : 'listbox',
            //     name    : prefix + 'borderStyle',
            //     label   : 'Border style',
            //     values  : [
            //         {text: 'None', value: 'none'},
            //         {text: 'Solid', style: 'border: 2px solid black;', value: 'solid'},
            //         {text: 'Dotted', style: 'border: 2px dotted black;', value: 'dotted'}
            //     ],
            //     onSelect: function (e) {
            //         changeStyleExample(prefix, 'borderStyle', this.value());
            //     }
            // },
            // {
            //     type    : 'colorbox',  // colorpicker plugin MUST be included for this to work
            //     name    : prefix + 'borderColor',
            //     label   : 'Border color',
            //     onaction: createColorPickAction(),
            //     onChange: function (e) {
            //         changeStyleExample(prefix, 'borderColor', this.value());
            //     }
            // },
            // {
            //     type   : 'textbox',
            //     name   : prefix + 'borderWidth',
            //     label  : 'Border width',
            //     tooltip: 'Only integer',
            //     onkeyup: function (e) {
            //         changeBorderColorInForm(e.target, isInteger);
            //         changeStyleExample(prefix, 'borderWidth', this.value());
            //     }
            // },
            {
                type: 'label',
                style: 'text-align: center',
                text: 'Border'
            },
            add('Top'),
            add('Left'),
            add('Right'),
            add('Bottom')

        ];
    }

    function formStyleTableGeneral(styleData) {
        var items = [
            {
                type   : 'textbox',
                name   : PREFIX_TABLE + 'width',
                label  : 'Width',
                tooltip: 'Only integer',
                onkeyup: function (e) {
                    changeBorderColorInForm(e.target, isInteger);
                }
            },
            {
                type   : 'textbox',
                name   : PREFIX_TABLE + 'height',
                label  : 'Height',
                tooltip: 'Only integer',
                onkeyup: function (e) {
                    changeBorderColorInForm(e.target, isInteger);
                }
            },
            {
                type  : 'listbox',
                name  : PREFIX_TABLE + 'borderCollapse',
                label : 'Border collapse',
                values: [
                    {text: 'Separate', value: 'separate'},
                    {text: 'Collapse', value: 'collapse'}
                ]
            },
            {
                type : 'container',
                label: 'Example border',
                html : '<div id="' + PREFIX_TABLE + 'example"><div style="padding: 2px; text-align: center;">Example</div></div>',
                onPostRender: function () {
                    changeStyleExample(PREFIX_TABLE,styleData);
                }
            }
        ];


        return {
            title: 'General',
            type : 'form',
            items: items.concat(formStyleChangeBorder(PREFIX_TABLE))
        }
    }

    function formStyleTableTHEAD(styleData){
        var items = [
            {
                type : 'container',
                label: 'Example thead',
                html : '<div id="' + PREFIX_THEAD + 'example"><div style="padding: 2px;">Example</div></div>',
                onPostRender: function () {
                    changeStyleExample(PREFIX_THEAD,styleData);
                }
            },
            {
                type    : 'colorbox',  // colorpicker plugin MUST be included for this to work
                name    : PREFIX_THEAD + 'backgroundColor',
                label   : 'Background color',
                onaction: createColorPickAction(),
                onChange: function (e) {
                    changeStyleExample(PREFIX_THEAD, 'backgroundColor', this.value());
                }
            },
            {
                type    : 'colorbox',  // colorpicker plugin MUST be included for this to work
                name    : PREFIX_THEAD + 'color',
                label   : 'Text color',
                onaction: createColorPickAction(),
                onChange: function (e) {
                    changeStyleExample(PREFIX_THEAD, 'color', this.value());
                }
            },
            {
                type   : 'listbox',
                name   : PREFIX_THEAD + 'textAlign',
                label  : 'Text align',
                values : [
                    { text: 'Default', value: '' },
                    { text: 'Center', value: 'center' },
                    { text: 'Left', value: 'left' },
                    { text: 'Right', value: 'right' }
                ],
                onSelect: function (e) {
                    changeStyleExample(PREFIX_THEAD, 'textAlign', this.value());
                }
            },
            {
                type   : 'textbox',
                name   : PREFIX_THEAD + 'height',
                label  : 'Height',
                tooltip: 'Only integer',
                onkeyup: function (e) {
                    changeBorderColorInForm(e.target, isInteger);
                }
            }
        ];

        return {
            title: 'style THEAD',
            type : 'form',
            items: items.concat(formStyleChangeBorder(PREFIX_THEAD))
        }
    }

    function appendStylesToData(dom) {

        dom.thead = dom.table.querySelector('thead tr');
        dom.tbody = dom.table.querySelector('tbody');
        var styleTableStr = dom.table.getAttribute('style'),
            styleTheadStr = dom.thead.getAttribute('style'),
            styleTbodyStr = dom.tbody.getAttribute('style'),
            style = {};

        function getStyle(item) {
            var prefix = this;
            if (item !== '') {
                var singleStyle = item.split(':'),
                    name = singleStyle[0].trim();
                console.log(name);
                if (name === 'border') {
                    var border = singleStyle[1].trim().match(/([\d]*)px\s([\w]*?)\s(.*)/);
                    if (border[1] !== undefined) {
                        style[prefix + 'borderWidth'] = border[1];
                        style[prefix + 'borderStyle'] = border[2];
                        style[prefix + 'borderColor'] = convertColor(border[3]);
                    }
                } else {
                    style[prefix + name.convertStyle()] = convertColor(singleStyle[1].trim().replace(/px$/, ''));
                }
                // style[prefix + name.convertStyle()] = convertColor(singleStyle[1].trim().replace(/px$/, ''));
            }
        }

        if (styleTableStr !== null) {
            styleTableStr.split(';').map(getStyle,PREFIX_TABLE);
        }

        if (styleTheadStr !== null) {
            styleTheadStr.split(';').map(getStyle,PREFIX_THEAD);
        }

        if (styleTbodyStr !== null) {
            styleTbodyStr.split(';').map(getStyle,PREFIX_TBODY);
        }

        return style;
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
            var dom = {
                table: editor.dom.getParent(editor.selection.getStart(), 'table')
            };
            var style = appendStylesToData(dom);
            editor.windowManager.open({
                title       : 'Style table',
                data        : style,
                // onPostRender: function (e) {
                //     // for draw example
                //     changeStyleExample(PREFIX_TABLE,style);
                //     changeStyleExample(PREFIX_THEAD,style);
                // },
                bodyType    : 'tabpanel',
                body        : [
                    formStyleTableGeneral(style),
                    formStyleTableTHEAD(style)
                ],
                onsubmit    : function (e) {
                    var newStyle = {};
                    var data = this.toJSON();
                    for (var rawKey in data) {
                        if (isPrefix(rawKey)) {
                            var key = rawKey.split('_'),
                                prefix = key[0],
                                name = key[1];

                            if (newStyle[prefix] === undefined) {
                                newStyle[prefix] = {};
                            }
                            // if (self[prefix + '_sideBorder'] !== ''){
                            //     name = name.replace(/(border)([A-Z][\w]+)/, '$1' + self[prefix + '_sideBorder'] + '$2');
                            //     console.log(name);
                            // }
                            newStyle[prefix][name] = data[rawKey];
                        }
                    }

                    for (var key in newStyle) {
                        setTableProperty(dom[key], newStyle[key]);
                    }
                }
            });
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
                        values: (function () {
                            var menu = [];
                            for (var key in self.mbh_property.table) {
                                menu.push({text: menuText(key.ucFirst()), value: key});
                            }
                            return menu;
                        })()
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
                            changeBorderColorInForm(e.target, itemColumnIsValid);
                        }
                    }

                ],
                onsubmit: function (e) {
                    if (itemColumnIsValid(e.data['amount'])) {
                        var menu = self.getMenu(e.data['source'], self.mbh_property.table[e.data['source']], false);

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
                            title   : 'Table for ' + menuText(self.mbh_SourceName.ucFirst()),
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