document.onready = function(ev) {
  $('#mbh_client_document_template_orientation').
      select2().
      on('change', function() {
        tinymce.get('mbh_client_document_template_content').
            plugins.
            mbh_data.
            changeOrentation();
      });
};

tinymce.PluginManager.add('mbh_data', function(editor, url) {
  'use strict';

  var self = this;

  String.prototype.ucFirst = function() {
    var str = this;
    if (str.length) {
      str = str.charAt(0).toUpperCase() + str.slice(1);
    }
    return str;
  };

  String.prototype.convertStyle = function() {
    var strArr = this.split('-'),
        str = '';
    for (var i = 0, len = strArr.length; i < len; i++) {
      if (i > 0) {
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

  self.getMenu = function(entity, property, onSelect) {
    var menuItems = [];
    var func = onSelect === undefined ? true : onSelect;
    for (var i = 0, len = property.length; i < len; i++) {
      var str = '{{ ' + entity + '.' + property[i] + ' }}';
      if (entity === 'hotel' && property[i] === 'getLogo') {
        str = '{{ ' + entity + '.' + property[i] + '|raw }}';
      }
      var tempObj = {
        tempStr: str,
        text   : menuText(property[i])
      };
      if (func) {
        tempObj['onselect'] = function(e) {
          editor.insertContent(this.settings.tempStr);
        };
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
    self.mbh_property.table[tableKey] = mbh_property.table[tableKey].methods;
  }

  var mbhTable = {
    getThead: function() {
      var table = '<table class="mbh_' + this.propertyName + '"><thead><tr>';
      if (this.counter) {
        table += '<td>#</td>';
      }
      for (var i = 0, len = this.nameThead.length; i < len; i++) {
        table += '<td>' + this.nameThead[i] + '</td>';
      }
      table += '</tr></thead>';
      return table;
    },
    getTbody: function() {
      var table = '';
      if (this.counter) {
        table += '{% set count = 0 %}';
      }
      table += '<tbody>{% for ' + this.propertyName + ' in ' + mbh_property.table[this.propertyName].source + ' %}<tr>';
      if (this.counter) {
        table += '{% set count = count +1 %}<td>{{ count }}</td>';
      }
      for (var i = 0, len = this.variablesTbody.length; i < len; i++) {
        table += '<td>' + this.variablesTbody[i] + '</td>';
      }
      table += '</tr>{% endfor %}</tbody></table>';
      return table;
    },
    insert  : function(counter, name, thead, variables) {
      this.counter = counter;
      this.propertyName = name;
      this.nameThead = thead;
      this.variablesTbody = variables;
      return this.getThead() + this.getTbody();
    }
  };

  var borderShow = 'border: 1px dotted black;';
  var borderHide = 'border: 1px dotted white;';

  editor.addButton('mbh_toggle_border', {
    text   : 'Border',
    icon   : false,
    onclick: function() {
      var content = editor.getContent();
      if (content.search(borderShow) != -1) {
        content = content.replace(borderShow, borderHide);
      } else {
        content = content.replace(borderHide, borderShow);
      }
      editor.setContent(content);
    }
  });

  self.changeOrentation = function(select) {
    var change = select === undefined ? false : select;

    var portrait = {
      value : function() {
        return this.width + ';' + this.height + ';';
      },
      name  : 'portrait',
      width : 'width: 1240px',
      height: 'height: 1769px'
    };
    var landscape = {
      value : function() {
        return this.width + ';' + this.height + ';';
      },
      name  : 'landscape',
      width : 'width: 1769px',
      height: 'height: 1206px'
    };

    function changeSelect(change, orientation) {
      if (!change) {
        return;
      }
      $('#mbh_client_document_template_orientation option').
          attr('selected', false).
          prop('selected', false);

      $('#mbh_client_document_template_orientation [value="' + orientation + '"]').
          attr('selected', 'selected').
          prop('selected', 'selected');

      $('#mbh_client_document_template_orientation').
          select2().
          val();
    }

    function newOrientation(str, newOrientation) {
      var stAsArr = str.trim().split(';').map(function(value) {
        // console.log(value);
        var n = value.trim().split(':')[0];
        if (n == 'width') {
          value = this.width;
        }
        if (n == 'height') {
          value = this.height;
        }
        return value;
      }, newOrientation);

      return stAsArr.join(';');
    }

    var content = editor.getContent();
    // т.к. при вставки стилей при создании нового шаблона используется не совсем корректный способ
    // образуется лишний, пустой тег
    content = content.replace('<meta="" />', '');

    var el = document.createElement('html');
    el.innerHTML = content;

    var st = el.querySelector('head style');

    if (st !== null) {
      st.innerHTML = st.innerHTML.replace(/\n/g, '');
      st.innerHTML = st.innerHTML.replace(/\s{2,}/g, '');
      var regExp = /html\s?{(.*?)}/;
      var match = st.innerHTML.match(regExp);
      if (match[1] !== undefined) {
        if (match[1].search(portrait.value()) !== -1) {
          st.innerHTML = st.innerHTML.replace(regExp, 'html {' + newOrientation(match[1], landscape) + '}');
          changeSelect(change, landscape.name);
        } else if (match[1].search(landscape.value()) !== -1) {
          st.innerHTML = st.innerHTML.replace(regExp, 'html {' + newOrientation(match[1], portrait) + '}');
          changeSelect(change, portrait.name);
        }
        editor.setContent(el.innerHTML);
      }
    }
  };

  editor.addButton('mbh_rotate', {
    text   : 'Rotate',
    icon   : false,
    onclick: function() {
      self.changeOrentation(true);
    }
  });

  for (var entityName in mbh_property.common) {
    if (entityName === 'payer') {
      editor.addMenuItem('mbh_payer', {
        text: 'Payer',
        menu: [
          {
            text: 'Payer Organization',
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
  tinymce.each(editor.menuItems, function(value, index) {
    if (index.search('mbh_') != -1) {
      menuItems.push(editor.menuItems[index]);
    }
  });

  menuItems.push({
    text   : 'Current date',
    onclick: function() {
      editor.insertContent('{{ currentDate }}');
    }
  });

  editor.addMenuItem('mbh_all_data', {
    context: 'contextmenu',
    text   : 'MB Entity',
    icon   : false,
    menu   : menuItems
  });

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
          onPostRender: function(e) {
            var entityMenu = this;
            changeMenuItemsTableEntity(entityMenu);
            editor.on('NodeChange', function(e) {
              changeMenuItemsTableEntity(entityMenu);
            });
          }
        }
    );
  }

  editor.addMenuItem('mbh_table_entity', {
    context     : 'contextmenu',
    text        : 'MB Table Entity',
    icon        : false,
    onPostRender: function() {
      var menu = this;
      editor.on('NodeChange', function(e) {
        self.mbh_table = editor.dom.getParent(e.element, 'table');
        if (self.mbh_table === null || self.mbh_table.classList.value.search(/mbh_/) === -1) {
          menu.disabled(true);
          menu.settings.menu = null;
        } else {
          self.mbh_table_match = self.mbh_table.classList.value.match(/mbh_(.+)\s|$/);
          menu.disabled(false);
          menu.settings.menu = menuEntityTable;
        }
      });
    }
  });

  function typeOfPayer() {
    var html = '<div>{% if payer is instanceofMortal %}';
    html += '{# data for payer is a Mortal #}<div></div>';
    html += '{# end data Mortal#}<br>';
    html += '{% elseif payer is instanceofOrganization %}';
    html += '{# data for payer is a Organization #}<div></div>';
    html += '{# end data Organization#}';
    html += '{% endif %}</div>';

    return html;
  }

  editor.addMenuItem('statement_mbh', {
    text   : 'MB Type Of Payer',
    onClick: function() {
      editor.insertContent(typeOfPayer());
    }
  });

  function createColorPickAction() {
    var colorPickerCallback = editor.settings.color_picker_callback;

    if (colorPickerCallback) {
      return function() {
        var self = this;

        colorPickerCallback.call(
            editor,
            function(value) {
              self.value(value).fire('change');
            },
            self.value()
        );
      };
    }
  }

  var PREFIX_TABLE = 'table_',
      PREFIX_THEAD = 'thead_',
      PREFIX_TBODY = 'tbody_';

  var PREFIX = [
    PREFIX_THEAD,
    PREFIX_TBODY,
    PREFIX_TABLE
  ];

  var colorBorder = {
    change       : function(target, func) {
      if (func(target.value)) {
        target.style.borderColor = 'green';
      } else {
        target.style.borderColor = 'red';
      }
    },
    isInteger    : function(value) {
      return value.search(/[^0-9]/) === -1;
    },
    isValidColumn: function(value) {
      return value.search(/[^1-9]|^$/) === -1 && value <= 9;
    }
  };

  var styleTable = {
    changeExample: function(prefix, style, value) {
      var node = document.querySelector('#' + prefix + 'example div');

      if (typeof style === 'object') {
        for (var key in style) {
          if (key.search(new RegExp(prefix)) !== -1) {
            var nameStyle = key.split('_')[1];
            if (nameStyle === 'width' || nameStyle === 'height') {
              continue;
            }
            node.style[nameStyle] = this.addSuffixPX(key, style);
          }
        }
      } else if (typeof style === 'string') {
        node.style[style] = this.addSuffixPX(style, value);
      }
    },
    addSuffixPX  : function(style, data) {
      var suffix = '';
      if (style.search(/width/i) !== -1 || style.search(/height/i) !== -1) {
        suffix = 'px';
      }
      if (typeof data === 'string') {
        return data + suffix;
      }
      return data[style] + suffix;
    },
    setProperty  : function(table, data) {
      for (var style in data) {
        editor.dom.setStyle(table, style, this.addSuffixPX(style, data));
      }
    },
    convertColor : function(str) {
      function convertDexToHex(number) {
        var n = parseInt(number, 10).toString(16);
        return n.length > 1 ? n : '0' + n;
      }

      if (str.search(/rgb/) != -1) {
        var num = str.match(/rgb\(\s?([\d]*?),\s?([\d]*?),\s?([\d]*?)\)/);
        return '#' + convertDexToHex(num[1]) + convertDexToHex(num[2]) + convertDexToHex(num[3]);
      }
      return str;
    },
    setValue     : function(rawVal) {
      return this.convertColor(rawVal.trim().replace(/px$/, ''));
    }
  };

  function isPrefix(prefixRaw) {
    var prefix = prefixRaw.split('_')[0] + '_';
    return PREFIX.some(function(value) {
      return value === prefix;
    });
  }

  var form = {
    changeBorder: function(prefix) {
      function add(side) {
        return {
          type     : 'container',
          layout   : 'flex',
          direction: 'row',
          align    : 'center',
          spacing  : 5,
          disabled : true,
          items    : [
            {
              type     : 'container',
              layout   : 'flex',
              direction: 'column',
              items    : [
                {type: 'label', text: side + ' style'},
                {
                  type    : 'listbox',
                  // id      : prefix + side + '_style',
                  name    : prefix + 'border' + side + 'Style',
                  label   : side + ' style',
                  values  : [
                    {text: 'None', value: 'none'},
                    {
                      text : 'Solid',
                      style: 'border: 2px solid black;',
                      value: 'solid'
                    },
                    {
                      text : 'Dotted',
                      style: 'border: 2px dotted black;',
                      value: 'dotted'
                    }
                  ],
                  minWidth: 100,
                  onSelect: function(e) {
                    styleTable.changeExample(prefix, 'border' + side + 'Style', this.value());
                  }
                }
              ]
            },
            {
              type     : 'container',
              layout   : 'flex',
              direction: 'column',
              items    : [
                {type: 'label', text: side + ' color'},
                {
                  type    : 'colorbox',
                  // id      : prefix + side + '_color',
                  name    : prefix + 'border' + side + 'Color',
                  label   : side + ' color',
                  onaction: createColorPickAction(),
                  onChange: function(e) {
                    styleTable.changeExample(prefix, 'border' + side + 'Color', this.value());
                  }
                }
              ]
            },
            {
              type     : 'container',
              layout   : 'flex',
              direction: 'column',
              items    : [
                {type: 'label', text: side + ' width'},
                {
                  type   : 'textbox',
                  // id     : prefix + side + '_width',
                  name   : prefix + 'border' + side + 'Width',
                  label  : side + ' width',
                  tooltip: 'Only integer',
                  onkeyup: function(e) {
                    colorBorder.change(e.target, colorBorder.isInteger);
                    styleTable.changeExample(prefix, 'border' + side + 'Width', this.value());
                  }
                }
              ]
            }
          ]
        };
      }

      return [
        {
          type : 'label',
          style: 'text-align: center',
          text : 'Border'
        },
        add('Top'),
        add('Right'),
        add('Bottom'),
        add('Left')
      ];
    },
    general     : function(styleData) {
      var items = [
        {
          type   : 'textbox',
          name   : PREFIX_TABLE + 'width',
          label  : 'Width',
          tooltip: 'Only integer',
          onkeyup: function(e) {
            colorBorder.change(e.target, colorBorder.isInteger);
          }
        },
        {
          type   : 'textbox',
          name   : PREFIX_TABLE + 'height',
          label  : 'Height',
          tooltip: 'Only integer',
          onkeyup: function(e) {
            colorBorder.change(e.target, colorBorder.isInteger);
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
          type        : 'container',
          label       : 'Example border',
          html        : '<div id="' + PREFIX_TABLE +
          'example"><div style="padding: 2px; text-align: center;">Example</div></div>',
          onPostRender: function() {
            styleTable.changeExample(PREFIX_TABLE, styleData);
          }
        }
      ];

      return {
        title: 'General',
        type : 'form',
        items: items.concat(this.changeBorder(PREFIX_TABLE))
      };
    },
    thead       : function(styleData) {
      var items = [
        {
          type        : 'container',
          label       : 'Example thead',
          html        : '<div id="' + PREFIX_THEAD +
          'example"><div style="padding: 2px;">Example</div></div>',
          onPostRender: function() {
            styleTable.changeExample(PREFIX_THEAD, styleData);
          }
        },
        {
          type    : 'colorbox',  // colorpicker plugin MUST be included for this to work
          name    : PREFIX_THEAD + 'backgroundColor',
          label   : 'Background color',
          onaction: createColorPickAction(),
          onChange: function(e) {
            styleTable.changeExample(PREFIX_THEAD, 'backgroundColor', this.value());
          }
        },
        {
          type    : 'colorbox',  // colorpicker plugin MUST be included for this to work
          name    : PREFIX_THEAD + 'color',
          label   : 'Text color',
          onaction: createColorPickAction(),
          onChange: function(e) {
            styleTable.changeExample(PREFIX_THEAD, 'color', this.value());
          }
        },
        {
          type    : 'listbox',
          name    : PREFIX_THEAD + 'textAlign',
          label   : 'Text align',
          values  : [
            {text: 'Default', value: ''},
            {text: 'Center', value: 'center'},
            {text: 'Left', value: 'left'},
            {text: 'Right', value: 'right'}
          ],
          onSelect: function(e) {
            styleTable.changeExample(PREFIX_THEAD, 'textAlign', this.value());
          }
        },
        {
          type   : 'textbox',
          name   : PREFIX_THEAD + 'height',
          label  : 'Height',
          tooltip: 'Only integer',
          onkeyup: function(e) {
            colorBorder.change(e.target, colorBorder.isInteger);
          }
        }
      ];

      return {
        title: 'style THEAD',
        type : 'form',
        items: items.concat(this.changeBorder(PREFIX_THEAD))
      };
    },
    tbody       : function(styleData) {
      var items = [
        {
          type        : 'container',
          label       : 'Example tbody',
          html        : '<div id="' + PREFIX_TBODY +
          'example" style="padding: 2px;"><div style="padding: 2px;">Example</div></div>',
          onPostRender: function() {
            styleTable.changeExample(PREFIX_TBODY, styleData);
          }
        },
        {
          type    : 'listbox',
          name    : PREFIX_TBODY + 'textAlign',
          label   : 'Text align',
          values  : [
            {text: 'Default', value: ''},
            {text: 'Center', value: 'center'},
            {text: 'Left', value: 'left'},
            {text: 'Right', value: 'right'}
          ],
          onSelect: function(e) {
            styleTable.changeExample(PREFIX_TBODY, 'textAlign', this.value());
          }
        }
      ];
      return {
        title: 'style TBODY',
        type : 'form',
        items: items.concat(this.changeBorder(PREFIX_TBODY))
      };
    }
  };

  function parseStyle(style, prefix, param, data) {
    var sides = ['Top', 'Right', 'Bottom', 'Left'],
        d = data.match(/([\d]*)px\s([\w]*?)\s([rgb]|\#.*)/),
        params = ['Width', 'Style', 'Color'];

    if (d === null) {
      switch (param.toLowerCase()) {
        case 'color':
          d = data.split('rgb').filter(function(val) {
            return val !== '';
          });
          if (d.length > 1) {
            d = d.map(function(val) {
              return 'rgb' + val;
            });
          } else {
            d = data.split('#').filter(function(val) {
              return val !== '';
            });
            if (d.length > 1) {
              d = d.map(function(val) {
                return '#' + val;
              });
            } else {
              d = [data];
            }
          }
          break;
        case 'width':
        case 'style':
          d = data.split(' ').map(function(value) {
            return value.replace('px', '');
          });
      }

      if (d.length > 1) {
        var len = d.length;
        sides.forEach(function(value, index) {
          if (len === 4) {
            style[prefix + 'border' + value + param] = styleTable.setValue(d[index]);
          } else if (len === 3) {
            if (value === 'Left') {
              style[prefix + 'border' + value + param] = styleTable.setValue(d[1]);
            } else {
              style[prefix + 'border' + value + param] = styleTable.setValue(d[index]);
            }
          } else if (len === 2) {
            if (value === 'Top' || value === 'Bottom') {
              style[prefix + 'border' + value + param] = styleTable.setValue(d[0]);
            } else {
              style[prefix + 'border' + value + param] = styleTable.setValue(d[1]);
            }
          }
        });
      } else {
        sides.forEach(function(value) {
          style[prefix + 'border' + value + param] = styleTable.setValue(data);
        });
      }
    } else {

      if (param === 'border') {
        sides.forEach(function(side) {
          params.forEach(function(value, index) {
            style[prefix + 'border' + this + value] = styleTable.setValue(d[index + 1]);
          }, side);
        });
      } else {
        params.forEach(function(value, index) {
          style[prefix + 'border' + param + value] = styleTable.setValue(d[index + 1]);
        });
      }
    }
  }

  function appendStylesToData(dom) {

    dom.thead = dom.table.querySelector('thead tr');
    dom.tbody = dom.table.querySelector('tbody tr');
    var styleTableStr = dom.table.getAttribute('style'),
        styleTheadStr = dom.thead.getAttribute('style'),
        styleTbodyStr = dom.tbody.getAttribute('style'),
        style = {};

    function getStyle(item) {
      var prefix = this;
      if (item !== '') {
        var singleStyle = item.split(':'),
            name = singleStyle[0].trim();
        if (name.search('border') !== -1 && name.search('border-collapse') === -1) {
          var n = name.split('-');
          if (n[2] !== undefined) {
            style[prefix + n[0] + n[1].ucFirst() +
            n[2].ucFirst()] = styleTable.setValue(singleStyle[1]);
          } else if (n[1] !== undefined) {
            parseStyle(style, prefix, n[1].ucFirst(), singleStyle[1].trim());
          } else {
            parseStyle(style, prefix, n[0], singleStyle[1].trim());
          }
        } else {
          style[prefix + name.convertStyle()] = styleTable.setValue(singleStyle[1]);
        }
      }
    }

    if (styleTableStr !== null) {
      styleTableStr.split(';').forEach(getStyle, PREFIX_TABLE);
    }

    if (styleTheadStr !== null) {
      styleTheadStr.split(';').forEach(getStyle, PREFIX_THEAD);
    }

    if (styleTbodyStr !== null) {
      styleTbodyStr.split(';').forEach(getStyle, PREFIX_TBODY);
    }

    return style;
  }

  editor.addMenuItem('mbh_table_property', {
    title       : 'MB Table properties',
    text        : 'MB Table properties',
    onPostRender: function() {
      var menu = this;
      editor.on('NodeChange', function(e) {
        var table = editor.dom.getParent(e.element, 'table');
        if (table === null || table.classList.value.search(/mbh_/) === -1) {
          menu.disabled(true);
        } else {
          menu.disabled(false);
        }
      });
    },
    onclick     : function(e) {
      var dom = {
        table: editor.dom.getParent(editor.selection.getStart(), 'table')
      };
      var style = appendStylesToData(dom);

      editor.windowManager.open({
        title   : 'Style table',
        data    : style,
        bodyType: 'tabpanel',
        body    : [
          form.general(style),
          form.thead(style),
          form.tbody(style)
        ],
        onsubmit: function(e) {
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
              newStyle[prefix][name] = data[rawKey];
            }
          }

          for (var key in newStyle) {
            styleTable.setProperty(dom[key], newStyle[key]);
          }
        }
      });
    }
  });

  editor.addMenuItem('mbh_table_add', {
    title  : 'table add',
    text   : 'MB Table add',
    onclick: function() {
      editor.windowManager.open({
        title   : 'Setting table',
        body    : [
          {
            type  : 'listbox',
            name  : 'source',
            label : 'Source Data',
            values: (function() {
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
            onkeyup: function(e) {
              colorBorder.change(e.target, colorBorder.isValidColumn);
            }
          }
        ],
        onsubmit: function(e) {
          if (colorBorder.isValidColumn(e.data['amount'])) {
            var menu = self.getMenu(e.data['source'], self.mbh_property.table[e.data['source']], false),
                items = [],
                sourceName = e.data['source'],
                needCounter = e.data['counter'];

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
              });
            }

            editor.windowManager.open({
              title   : 'Table for ' + menuText(sourceName.ucFirst()),
              body    : items,
              onsubmit: function(e) {
                var headers = [],
                    variables = [];
                for (var key in e.data) {
                  if (key.search('value') != -1) {
                    variables.push(e.data[key]);
                  } else if (key.search('name') != -1) {
                    headers.push(e.data[key]);
                  }
                }
                tinymce.activeEditor.execCommand('mceInsertContent', false,
                    (function() {
                      return mbhTable.insert(needCounter, sourceName, headers, variables);
                    })());
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
