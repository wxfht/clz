define(['jquery.ui', '../../js/web/goods_selector.js'], function (ui, gSelector) {
    var modal = {
        sysinfo: null,
        id: 0,
        type: 1,
        navs: {},
        initnav: [],
        data: {},
        selected: 'page',
        childid: null,
        keyworderr: false
    };
    jQuery.base64 = (function ($) {
        var _PADCHAR = "=", _ALPHA = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/",
            _VERSION = "1.1";

        function _getbyte64(s, i) {
            var idx = _ALPHA.indexOf(s.charAt(i));
            if (idx === -1) {
                throw"Cannot decode base64"
            }
            return idx
        }

        function _decode_chars(y, x) {
            while (y.length > 0) {
                var ch = y[0];
                if (ch < 0x80) {
                    y.shift();
                    x.push(String.fromCharCode(ch))
                } else if ((ch & 0x80) == 0xc0) {
                    if (y.length < 2)break;
                    ch = y.shift();
                    var ch1 = y.shift();
                    x.push(String.fromCharCode(((ch & 0x1f) << 6) + (ch1 & 0x3f)))
                } else {
                    if (y.length < 3)break;
                    ch = y.shift();
                    var ch1 = y.shift();
                    var ch2 = y.shift();
                    x.push(String.fromCharCode(((ch & 0x0f) << 12) + ((ch1 & 0x3f) << 6) + (ch2 & 0x3f)))
                }
            }
        }

        function _decode(s) {
            var pads = 0, i, b10, imax = s.length, x = [], y = [];
            s = String(s);
            if (imax === 0) {
                return s
            }
            if (imax % 4 !== 0) {
                throw"Cannot decode base64"
            }
            if (s.charAt(imax - 1) === _PADCHAR) {
                pads = 1;
                if (s.charAt(imax - 2) === _PADCHAR) {
                    pads = 2
                }
                imax -= 4
            }
            for (i = 0; i < imax; i += 4) {
                var ch1 = _getbyte64(s, i);
                var ch2 = _getbyte64(s, i + 1);
                var ch3 = _getbyte64(s, i + 2);
                var ch4 = _getbyte64(s, i + 3);
                b10 = (_getbyte64(s, i) << 18) | (_getbyte64(s, i + 1) << 12) | (_getbyte64(s, i + 2) << 6) | _getbyte64(s, i + 3);
                y.push(b10 >> 16);
                y.push((b10 >> 8) & 0xff);
                y.push(b10 & 0xff);
                _decode_chars(y, x)
            }
            switch (pads) {
                case 1:
                    b10 = (_getbyte64(s, i) << 18) | (_getbyte64(s, i + 1) << 12) | (_getbyte64(s, i + 2) << 6);
                    y.push(b10 >> 16);
                    y.push((b10 >> 8) & 0xff);
                    break;
                case 2:
                    b10 = (_getbyte64(s, i) << 18) | (_getbyte64(s, i + 1) << 12);
                    y.push(b10 >> 16);
                    break
            }
            _decode_chars(y, x);
            if (y.length > 0)throw"Cannot decode base64";
            return x.join("")
        }

        function _get_chars(ch, y) {
            if (ch < 0x80)y.push(ch); else if (ch < 0x800) {
                y.push(0xc0 + ((ch >> 6) & 0x1f));
                y.push(0x80 + (ch & 0x3f))
            } else {
                y.push(0xe0 + ((ch >> 12) & 0xf));
                y.push(0x80 + ((ch >> 6) & 0x3f));
                y.push(0x80 + (ch & 0x3f))
            }
        }

        function _encode(s) {
            if (arguments.length !== 1) {
                throw"SyntaxError: exactly one argument required"
            }
            s = String(s);
            if (s.length === 0) {
                return s
            }
            var i, b10, y = [], x = [], len = s.length;
            i = 0;
            while (i < len) {
                _get_chars(s.charCodeAt(i), y);
                while (y.length >= 3) {
                    var ch1 = y.shift();
                    var ch2 = y.shift();
                    var ch3 = y.shift();
                    b10 = (ch1 << 16) | (ch2 << 8) | ch3;
                    x.push(_ALPHA.charAt(b10 >> 18));
                    x.push(_ALPHA.charAt((b10 >> 12) & 0x3F));
                    x.push(_ALPHA.charAt((b10 >> 6) & 0x3f));
                    x.push(_ALPHA.charAt(b10 & 0x3f))
                }
                i++
            }
            switch (y.length) {
                case 1:
                    var ch = y.shift();
                    b10 = ch << 16;
                    x.push(_ALPHA.charAt(b10 >> 18) + _ALPHA.charAt((b10 >> 12) & 0x3F) + _PADCHAR + _PADCHAR);
                    break;
                case 2:
                    var ch1 = y.shift();
                    var ch2 = y.shift();
                    b10 = (ch1 << 16) | (ch2 << 8);
                    x.push(_ALPHA.charAt(b10 >> 18) + _ALPHA.charAt((b10 >> 12) & 0x3F) + _ALPHA.charAt((b10 >> 6) & 0x3f) + _PADCHAR);
                    break
            }
            return x.join("")
        }

        return {decode: _decode, encode: _encode, VERSION: _VERSION}
    }(jQuery));
    modal.init = function (params) {
        window.tpl = params.tpl;
        modal.attachurl = params.attachurl;
        modal.type = params.type;
        modal.data = params.data;
        modal.id = params.id;
        modal.diymenu = params.diymenu;
        modal.diyadvs = params.diyadvs;
        modal.levels = params.levels;
        modal.merch = params.merch;
        modal.merchid = params.merchid;
        modal.plugins = params.plugins ? params.plugins : {};
        modal.shopset = params.shopset;
        if (modal.data) {
            modal.type = modal.data.page.type;
            modal.page = modal.data.page;
            modal.items = modal.data.items
        }
        ;modal.initTpl();
        modal.initPage();
        modal.initItems();
        modal.initNavs();
        modal.initSortable();
        modal.initGotop();
        $(".btn-save").unbind('click').click(function () {
            var status = $(this).data('status');
            var type = $(this).data('type');
            if (status) {
                tip.msgbox.err("?????????????????????????????????");
                return
            }
            if (type == 'preview') {
                modal.save(true)
            } else if (type == 'save') {
                modal.save()
            } else if (type = 'savetemp') {
                modal.initTemp();
                return
            }
        });
        $("#page").unbind('click').click(function () {
            if (modal.selected == 'page') {
                return
            }
            modal.selected = 'page';
            modal.initPage()
        });
        var preview_id = util.cookie.get('preview_id');
        if (preview_id) {
            setTimeout(function () {
                var previewUrl = biz.url("diypage/page/preview") + "&id=" + preview_id;
                window.open(previewUrl)
            }, 1000);
            util.cookie.set('preview_id', '')
        }
    };
    modal.initNavs = function () {
        modal.getNavs();
        var navgroup = {
            0: ['listmenu', 'richtext', 'title', 'line', 'blank', 'menu', 'menu2', 'picture', 'banner', 'picturew', 'pictures', 'icongroup', 'audio', 'coupon','tabbar', 'topmenu'],
            1: ['search', 'fixedsearch', 'notice', 'goods', 'merchgroup', 'diymod'],
            2: ['search', 'fixedsearch', 'notice', 'goods', 'merchgroup', 'seckillgroup', 'diymod'],
            3: ['member', 'bindmobile', 'logout', 'wxcard', 'verify'],
            4: ['memberc', 'commission_block', 'commission_sharecode', 'blockgroup'],
            5: ['detail_tab', 'detail_swipe', 'detail_info', 'detail_sale', 'detail_spec', 'detail_package', 'detail_shop', 'detail_store', 'detail_buyshow', 'detail_comment', 'detail_pullup', 'detail_navbar', 'detail_seckill', 'goods'],
            6: ['goods', 'search', 'merchgroup'],
            7: ['seckill_times', 'seckill_rooms', 'seckill_advs', 'seckill_list'],
            8: ['exchange_banner', 'exchange_input', 'exchange_rule'],
            9: ['tabbar']
        };
        var navpage = navgroup[modal.type],globalNavs = [];
        var navpageType = modal.type;
        if (navpage) {
            navpage = $.merge(navpage, navgroup[0])
        } else {
            navpage = navgroup[0]
        }
        $.each(navpage, function (index, val) {
            var params = modal.navs[val];
            if (params) {
                if(navpageType == 5 && val == 'tabbar'){
                    return
                }
                params.id = val;
                if(params.global) {
                    globalNavs.push(params);
                    return
                }
                modal.initnav.push(params)
            }
        });
        var temparr = [];
        for(var i=0;i<modal.initnav.length;i++) {
            if(modal.initnav[i].id!="merchgroup") {
                temparr.push(modal.initnav[i]);
            }
        }
        if (!modal.plugins.merch){
            modal.initnav = temparr;
        }
        var html = tpl("tpl_navs", {basic: modal.initnav, global: globalNavs,navpageType:navpageType});
        $("#navs").html(html).show();
        console.log()
        $("#navs nav").unbind('click').click(function () {
            var id = $(this).data('id');
            if (id === 'page') {
                $("#page").trigger("click");
                return
            }
            if (id == 'merchgroup' && !modal.plugins.merch) {
                tip.msgbox.err("?????????????????????????????????????????????");
                return
            }
            if ((id == 'seckillgroup' || id == 'detail_seckill') && !modal.plugins.seckill) {
                tip.msgbox.err("????????????????????????");
                return
            }
            var inArray = $.inArray(id, navpage);
            if (inArray < 0) {
                tip.msgbox.err("???????????????????????????????????????");
                return
            }
            var item = $.extend(true, {}, modal.navs[id]);
            delete item.name;
            if (!item) {
                tip.msgbox.err("?????????????????????");
                return
            }
            var itemTplShow = $("#tpl_show_" + id).length;
            var itemTplEdit = $("#tpl_edit_" + id).length;
            if (itemTplShow == 0 || itemTplEdit == 0) {
                tip.msgbox.err("???????????????????????????????????????????????????");
                return
            }
            if (id === 'diymod') {
                modal.initMod(item);
                return
            }
            var itemid = modal.getId("M", 0);
            if (item.data) {
                var itemData = $.extend(true, {}, item.data);
                var newData = {};
                var index = 0;
                $.each(itemData, function (id, data) {
                    var childid = modal.getId("C", index);
                    newData[childid] = data;
                    delete childid;
                    index++
                });
                item.data = newData
            }
            if (item.max && item.max > 0) {
                var itemNum = modal.getItemNum(id);
                if (itemNum > 0 && itemNum >= item.max) {
                    tip.msgbox.err("??????????????????????????? " + item.max + " ???");
                    return
                }
            }
            if(item.only && item.only == 'toptab'){
                var itemNum = modal.getItemOnlyNum(item.only);
                if (itemNum > 0) {
                    tip.msgbox.err("????????????????????????????????????????????????");
                    return;
                }
            }
            var append = true;
            if (modal.selected && modal.selected != 'page') {
                var thisitem = modal.items[modal.selected];
                if (thisitem.id == 'detail_navbar' || thisitem.id == 'detail_pullup' || id == 'detail_navbar' || id == 'detail_pullup') {
                    append = false
                }
            }
            if (item.istop) {
                var newItems = {};
                newItems[itemid] = item;
                $.each(modal.items, function (id, eachitem) {
                    newItems[id] = eachitem
                });
                modal.items = newItems
            } else if (modal.selected && modal.selected != 'page' && append) {
                var newItems = {};
                $.each(modal.items, function (id, eachitem) {
                    newItems[id] = eachitem;
                    if (id == modal.selected) {
                        newItems[itemid] = item
                    }
                });
                modal.items = newItems
            } else {
                if (modal.page.type == 5 && modal.items && id != 'detail_navbar') {
                    var navbar = null;
                    var pullup = null;
                    $.each(modal.items, function (newitemid, newitem) {
                        if (newitem.id == 'detail_navbar') {
                            navbar = {itemid: newitemid, item: newitem};
                            delete modal.items[newitemid]
                        } else if (newitem.id == 'detail_pullup') {
                            pullup = {itemid: newitemid, item: newitem};
                            delete modal.items[newitemid]
                        }
                    });
                    modal.items[itemid] = item;
                    if (pullup) {
                        modal.items[pullup.itemid] = pullup.item
                    }
                    if (navbar) {
                        modal.items[navbar.itemid] = navbar.item
                    }
                } else {
                    modal.items[itemid] = item
                }
            }
            modal.initItems();
            $(".drag[data-itemid='" + itemid + "']").trigger('mousedown').trigger('click');
            modal.selected = itemid
        })
    };
    modal.getId = function (S, N) {
        var date = +new Date();
        var id = S + (date + N);
        return id
    };
    modal.getNavs = function () {
        modal.navs = {
            notice: {
                name: '??????',
                params: {
                    'iconurl': '../addons/ewei_shopv2/plugin/diypage/static/images/default/hotdot.png',
                    'noticedata': '0',
                    'speed': '4',
                    'noticenum': '5'
                },
                style: {'background': '#ffffff', 'iconcolor': '#fd5454', 'color': '#666666', 'bordercolor': '#e2e2e2'},
                data: {
                    C0123456789101: {title: '??????????????????????????????????????????', linkurl: '',},
                    C0123456789102: {title: '??????????????????????????????????????????', linkurl: '',}
                }
            },
            banner: {
                name: '????????????',
                params: {},
                style: {
                    'dotstyle': 'round',
                    'dotalign': 'left',
                    'background': '#ffffff',
                    'leftright': '5',
                    'bottom': '5',
                    'opacity': '0.8'
                },
                data: {
                    C0123456789101: {
                        imgurl: '../addons/ewei_shopv2/plugin/diypage/static/images/default/banner-1.jpg',
                        linkurl: '',
                    },
                    C0123456789102: {
                        imgurl: '../addons/ewei_shopv2/plugin/diypage/static/images/default/banner-2.jpg',
                        linkurl: '',
                    }
                }
            },
            richtext: {name: '?????????', params: {content: ''}, style: {'background': '#ffffff', 'padding': '0'}},
            title: {
                name: '?????????',
                params: {'title': '', 'icon': ''},
                style: {
                    'background': '#ffffff',
                    'color': '#666666',
                    'textalign': 'left',
                    'fontsize': '12',
                    'paddingtop': '5',
                    'paddingleft': '5'
                }
            },
            search: {
                name: '?????????',
                params: {'placeholder': '??????????????????????????????'},
                style: {
                    'inputbackground': '#ffffff',
                    'background': '#f1f1f2',
                    'iconcolor': '#b4b4b4',
                    'color': '#999999',
                    'paddingtop': '10',
                    'paddingleft': '10',
                    'textalign': 'left',
                    'searchstyle': ''
                }
            },
            line: {
                name: '?????????',
                params: {},
                style: {
                    'height': '2',
                    'background': '#ffffff',
                    "border": "#000000",
                    'padding': '10',
                    'linestyle': 'solid'
                }
            },
            blank: {name: '????????????', params: {}, style: {height: '20', background: '#ffffff'}},
            menu: {
                name: '?????????',
                params: {},
                style: {
                    'navstyle': '',
                    'background': '#ffffff',
                    'rownum': '4',
                    'showtype': '0',
                    'pagenum': '8',
                    'showdot': '1',
                },
                data: {
                    C0123456789101: {
                        imgurl: '../addons/ewei_shopv2/plugin/diypage/static/images/default/icon-1.png',
                        linkurl: '',
                        text: '????????????1',
                        color: '#666666'
                    },
                    C0123456789102: {
                        imgurl: '../addons/ewei_shopv2/plugin/diypage/static/images/default/icon-2.png',
                        linkurl: '',
                        text: '????????????2',
                        color: '#666666'
                    },
                    C0123456789103: {
                        imgurl: '../addons/ewei_shopv2/plugin/diypage/static/images/default/icon-3.png',
                        linkurl: '',
                        text: '????????????3',
                        color: '#666666'
                    },
                    C0123456789104: {
                        imgurl: '../addons/ewei_shopv2/plugin/diypage/static/images/default/icon-4.png',
                        linkurl: '',
                        text: '????????????4',
                        color: '#666666'
                    }
                }
            },
            menu2: {
                name: '?????????2',
                params: {},
                style: {'margintop': '10', 'background': '#ffffff'},
                data: {
                    C0123456789101: {
                        text: '????????????',
                        iconclass: '',
                        textcolor: '#666666',
                        iconcolor: '#666666',
                        linkurl: ''
                    },
                    C0123456789102: {
                        text: '????????????',
                        iconclass: '',
                        textcolor: '#666666',
                        iconcolor: '#666666',
                        linkurl: ''
                    }
                }
            },
            picture: {
                name: '?????????',
                params: {},
                style: {'paddingtop': '0', 'paddingleft': '0'},
                data: {
                    C0123456789101: {
                        imgurl: '../addons/ewei_shopv2/plugin/diypage/static/images/default/banner-1.jpg',
                        linkurl: '',
                    },
                    C0123456789102: {
                        imgurl: '../addons/ewei_shopv2/plugin/diypage/static/images/default/banner-2.jpg',
                        linkurl: '',
                    }
                }
            },
            picturew: {
                name: '????????????',
                params: {row: '4', showtype: 0, pagenum: '2'},
                style: {paddingtop: '0', paddingleft: '0', showdot: 0, showbtn: 0},
                data: {
                    C0123456789101: {
                        imgurl: '../addons/ewei_shopv2/plugin/diypage/static/images/default/cube-1.jpg',
                        linkurl: '',
                    },
                    C0123456789102: {
                        imgurl: '../addons/ewei_shopv2/plugin/diypage/static/images/default/cube-2.jpg',
                        linkurl: '',
                    },
                    C0123456789103: {
                        imgurl: '../addons/ewei_shopv2/plugin/diypage/static/images/default/cube-3.jpg',
                        linkurl: '',
                    },
                    C0123456789104: {
                        imgurl: '../addons/ewei_shopv2/plugin/diypage/static/images/default/cube-4.jpg',
                        linkurl: '',
                    }
                }
            },
            pictures: {
                name: '????????????',
                params: {hidetext: 0, showtype: 0, rownum: 3, showbtn: 0},
                style: {
                    background: "#ffffff",
                    paddingtop: "3",
                    paddingleft: "5",
                    titlealign: 'left',
                    textalign: 'left',
                    titlecolor: '#ffffff',
                    textcolor: '#666666'
                },
                data: {
                    C0123456789101: {
                        imgurl: '../addons/ewei_shopv2/plugin/diypage/static/images/default/goods-1.jpg',
                        linkurl: '',
                        title: '??????????????????',
                        text: '??????????????????'
                    },
                    C0123456789102: {
                        imgurl: '../addons/ewei_shopv2/plugin/diypage/static/images/default/goods-2.jpg',
                        linkurl: '',
                        title: '??????????????????',
                        text: '??????????????????'
                    },
                    C0123456789103: {
                        imgurl: '../addons/ewei_shopv2/plugin/diypage/static/images/default/goods-4.jpg',
                        linkurl: '',
                        title: '??????????????????',
                        text: '??????????????????'
                    }
                }
            },
            goods: {
                name: '?????????',
                params: {
                    'goodstype': '0',
                    'showtitle': '1',
                    'showprice': '1',
                    'showtag': '0',
                    'goodsdata': '0',
                    'cateid': '',
                    'catename': '',
                    'groupid': '',
                    'groupname': '',
                    'goodssort': '0',
                    'goodsnum': '6',
                    'showicon': '1',
                    'iconposition': 'left top',
                    'productprice': '1',
                    'showproductprice': '0',
                    'showsales': '0',
                    'productpricetext': '??????',
                    'salestext': '??????',
                    'productpriceline': '0',
                    'saleout': '0',
                    'pagetype': modal.page.type,
                    'seecommission':0,
                    'cansee' : 0,
                    'seetitle': '',
                },
                style: {
                    'background': '#f3f3f3',
                    'liststyle': 'block',
                    'buystyle': 'buybtn-1',
                    'goodsicon': 'recommand',
                    'iconstyle': 'triangle',
                    'pricecolor': '#ff5555',
                    'productpricecolor': '#666666',
                    'iconpaddingtop': '0',
                    'iconpaddingleft': '0',
                    'buybtncolor': '#ff5555',
                    'iconzoom': '100',
                    'titlecolor': '#000000',
                    'tagbackground': '#fe5455',
                    'productpricecolor': '#999999',
                    'salescolor': '#999999'
                },
                data: {
                    C0123456789101: {
                        thumb: '../addons/ewei_shopv2/plugin/diypage/static/images/default/goods-1.jpg',
                        price: '20.00',
                        productprice: '99.00',
                        title: '?????????????????????',
                        sales: '0',
                        gid: '',
                        bargain: 0,
                        credit: 0,
                        ctype: 1
                    },
                    C0123456789102: {
                        thumb: '../addons/ewei_shopv2/plugin/diypage/static/images/default/goods-2.jpg',
                        price: '20.00',
                        productprice: '99.00',
                        title: '?????????????????????',
                        sales: '0',
                        gid: '',
                        bargain: 0,
                        credit: 0,
                        ctype: 1
                    },
                    C0123456789103: {
                        thumb: '../addons/ewei_shopv2/plugin/diypage/static/images/default/goods-3.jpg',
                        price: '20.00',
                        productprice: '99.00',
                        sales: '0',
                        title: '?????????????????????',
                        gid: '',
                        bargain: 0,
                        credit: 0,
                        ctype: 0
                    },
                    C0123456789104: {
                        thumb: '../addons/ewei_shopv2/plugin/diypage/static/images/default/goods-4.jpg',
                        price: '20.00',
                        productprice: '99.00',
                        sales: '0',
                        title: '?????????????????????',
                        gid: '',
                        bargain: 0,
                        credit: 0,
                        ctype: 0
                    }
                }
            },
            diymod: {name: '????????????', params: {'modid': '', 'modname': ''}, style: {}},
            listmenu: {
                name: '????????????',
                params: {},
                style: {
                    'margintop': '10',
                    'background': '#ffffff',
                    'iconcolor': '#999999',
                    'textcolor': '#000000',
                    'remarkcolor': '#888888'
                },
                data: {
                    C0123456789101: {text: '??????1', linkurl: '', iconclass: 'icon-home', remark: '??????', dotnum: ''},
                    C0123456789102: {text: '??????2', linkurl: '', iconclass: 'icon-home', remark: '??????', dotnum: ''},
                    C0123456789103: {text: '??????3', linkurl: '', iconclass: 'icon-home', remark: '??????', dotnum: ''}
                }
            },
            wxcard: {
                name: '???????????????',
                max: 1,
                type: 3,
                params: {iconclass: 'icon-same'},
                style: {
                    'margintop': '10',
                    'background': '#ffffff',
                    'iconcolor': '#999999',
                    'textcolor': '#333333',
                    'remarkcolor': '#888888',
                }
            },
            verify: {
                name: '???????????????',
                max: 1,
                type: 3,
                params: {title: '???????????????', remark: '', iconclass: 'icon-list', style: ''},
                style: {titlecolor: '#333333', remarkcolor: '#888888', titlebg: '#ffffff', background: '#ffffff'}
                },
            member: {
                name: '????????????',
                type: 3,
                max:1,
                params: {
                    style: 'default1',
                    levellink: '',
                    seticon: 'icon-settings',
                    setlink: '',
                    leftnav: '??????',
                    leftnavlink: '',
                    rightnav: '??????',
                    rightnavlink: ''
                },
                style: {'background': '#ff5555', 'textcolor': '#ffffff', 'textlight': '#ffffff', 'headstyle': ''},
                info: {avatar: '', nickname: '', levelname: '', textmoney: '', textcredit: '', money: '', credit: ''}
            },
            icongroup: {
                name: '?????????',
                params: {rownum: '4', border: '1', bordertop: '1', borderbottom: '1',},
                style: {
                    background: '#ffffff',
                    bordercolor: '#ffffff',
                    textcolor: '#000000',
                    iconcolor: '#666666',
                    dotcolor: '#ff0011'
                },
                data: {
                    C0123456789101: {iconclass: 'icon-daifukuan', text: '?????????', linkurl: '', dotnum: 0},
                    C0123456789102: {iconclass: 'icon-fahuo', text: '?????????', linkurl: '', dotnum: 0},
                    C0123456789103: {iconclass: 'icon-daishouhuo', text: '?????????', linkurl: '', dotnum: 0},
                    C0123456789104: {iconclass: 'icon-daituikuan', text: '?????????', linkurl: '', dotnum: 0}
                }
            },
            bindmobile: {
                name: '????????????',
                type: 3,
                max:1,
                params: {
                    linkurl: '',
                    title: '???????????????',
                    text: '?????????????????????????????????????????????????????????????????????????????????????????????',
                    iconclass: 'icon-mobile'
                },
                style: {
                    margintop: '10',
                    background: '#ffffff',
                    titlecolor: '#ff5555',
                    textcolor: '#999999',
                    iconcolor: '#999999'
                }
            },
            logout: {
                name: '????????????',
                type: 3,
                max:1,
                params: {bindurl: '', logouturl: ''},
                style: {subcolor: '#ffffff', maincolor: '#ff5555', margintop: '10'}
            },
            memberc: {
                name: '????????????',
                type: 4,
                max:1,
                params: {
                    style: 'default1',
                    seticon: 'icon-settings',
                    setlink: '',
                    leftnav: '??????1',
                    leftnavlink: '',
                    rightnav: '??????2',
                    rightnavlink: '',
                    centernav: '??????',
                    centernavlink: '',
                    hideup: 0
                },
                style: {background: '#fe5455', textcolor: '#ffffff', textlight: '#ffffff'}
            },
            commission_block: {
                name: "????????????",
                style: {background: '#ffffff', pricecolor: '#ff8000', textcolor: '#000000', btncolor: '#ff8000',},
                type: 4,
                max: 1
            },
            commission_sharecode: {
                name: "?????????",
                style: {background: '#ffffff', textcolor: '#000000', iconcolor: '#ff8000',},
                params: {iconclass: 'icon-link'},
                type: 4,
                max: 1
            },
            blockgroup: {
                name: '?????????',
                params: {rownum: 3, newstyle: 1},
                style: {background: '#ffffff', tipcolor: '#feb312'},
                data: {
                    C0123456789101: {
                        iconclass: 'icon-money',
                        iconcolor: '#feb312',
                        text: '????????????',
                        textcolor: '#666666',
                        linkurl: '',
                        tipnum: '',
                        tiptext: ''
                    },
                    C0123456789102: {
                        iconclass: 'icon-list',
                        iconcolor: '#50b6fe',
                        text: '????????????',
                        textcolor: '#666666',
                        linkurl: '',
                        tipnum: '50',
                        tiptext: '???'
                    },
                    C0123456789103: {
                        iconclass: 'icon-manageorder',
                        iconcolor: '#ff741d',
                        text: '????????????',
                        textcolor: '#666666',
                        linkurl: '',
                        tipnum: '10',
                        tiptext: '???'
                    },
                    C0123456789104: {
                        iconclass: 'icon-group',
                        iconcolor: '#ff741d',
                        text: '????????????',
                        textcolor: '#666666',
                        linkurl: '',
                        tipnum: '2',
                        tiptext: '???'
                    },
                    C0123456789105: {
                        iconclass: 'icon-qrcode',
                        iconcolor: '#feb312',
                        text: '???????????????',
                        textcolor: '#666666',
                        linkurl: '',
                        tipnum: '',
                        tiptext: ''
                    },
                    C0123456789106: {
                        iconclass: 'icon-shopfill',
                        iconcolor: '#50b6fe',
                        text: '????????????',
                        textcolor: '#666666',
                        linkurl: '',
                        tipnum: '',
                        tiptext: ''
                    },
                    C0123456789107: {
                        iconclass: 'icon-rank',
                        iconcolor: '#ff741d',
                        text: '????????????',
                        textcolor: '#666666',
                        linkurl: '',
                        tipnum: '',
                        tiptext: ''
                    }
                }
            },
            detail_tab: {
                name: "?????????",
                type: 5,
                max: 1,
                params: {goodstext: "??????", detailtext: "??????"},
                style: {background: "#f7f7f7", textcolor: "#666666", activecolor: "#ef4f4f"}
            },
            detail_swipe: {
                name: "?????????",
                type: 5,
                max: 1,
                params: {},
                style: {
                    dotstyle: 'rectangle',
                    dotalign: 'left',
                    background: '#ffffff',
                    leftright: '5',
                    bottom: '5',
                    opacity: '0.8'
                },
            },
            detail_info: {
                name: "????????????",
                type: 5,
                max: 1,
                params: {hideshare: '0', share: "??????", share_link: "", share_icon: "icon-share",},
                style: {
                    margintop: 0,
                    marginbottom: 0,
                    background: "#ffffff",
                    titlecolor: "#000000",
                    subtitlecolor: "#999999",
                    pricecolor: "#ff5555",
                    textcolor: "#cccccc",
                    timecolor: "#fff2e2",
                    timetextcolor: "#ef4f4f",
                }
            },
            detail_sale: {
                name: "????????????",
                type: 5,
                max: 1,
                params: {},
                style: {
                    margintop: 0,
                    marginbottom: 0,
                    background: "#ffffff",
                    textcolor: "#666666",
                    textcolorhigh: "#ef4f4f"
                },
                data: {
                    C0123456789100: {name: "????????????", type: "yushou"},
                    C0123456789101: {name: "????????????", type: "erci"},
                    C0123456789102: {name: "?????????", type: "huiyuan"},
                    C0123456789103: {name: "??????", type: "youhui"},
                    C0123456789104: {name: "??????", type: "jifen"},
                    C0123456789105: {name: "???????????????", type: "bupeisong"},
                    C0123456789106: {name: "????????????", type: "biaoqian"},
                    C0123456789107: {name: "???????????????", type: "coupon"},
                    C0123456789108: {name: "??????", type: "zengpin"},
                    C0123456789108: {name: "??????", type: "fullback"}
                }
            },
            detail_spec: {
                name: "????????????",
                type: 5,
                max: 1,
                params: {},
                style: {background: "#ffffff", textcolor: "#333333", margintop: 10, marginbottom: 0}
            },
            detail_shop: {
                name: "????????????",
                type: 5,
                max: 1,
                params: {
                    shoplogo: "../addons/ewei_shopv2/static/images/designer.jpg",
                    shopname: "",
                    shopdesc: "",
                    hidenum: 0,
                    leftnavtext: "????????????",
                    leftnavlink: "",
                    rightnavtext: "????????????",
                    rightnavlink: "",
                },
                style: {
                    margintop: 10,
                    marginbottom: 0,
                    background: "#ffffff",
                    goodsnumcolor: "#333333",
                    goodstextcolor: "#7c7c7c",
                    rightnavcolor: "#ff5555",
                    shopnamecolor: "#333333",
                    shopdesccolor: "#444444"
                }
            },
            detail_comment: {
                name: "????????????",
                type: 5,
                max: 1,
                params: {},
                style: {
                    margintop: 10,
                    marginbottom: 10,
                    background: "#ffffff",
                    maincolor: "#fd5454",
                    subcolor: "#000",
                    textcolor: "#333333",
                }
            },
            detail_buyshow: {
                name: "????????????",
                type: 5,
                max: 1,
                params: {},
                style: {background: "#ffffff", margintop: 10, marginbottom: 0}
            },
            detail_store: {
                name: "????????????",
                type: 5,
                max: 1,
                params: {},
                style: {
                    background: "#ffffff",
                    margintop: 10,
                    marginbottom: 0,
                    titlecolor: "#333333",
                    shopnamecolor: "#333333",
                    shopinfocolor: "#666666",
                    navtelcolor: "#008000",
                    navlocationcolor: "#ff9900",
                }
            },
            detail_package: {
                name: "????????????",
                type: 5,
                max: 1,
                params: {},
                style: {background: "#ffffff", margintop: 10, marginbottom: 0, textcolor: "#000000"}
            },
            detail_pullup: {
                name: "????????????",
                type: 5,
                max: 1,
                params: {},
                style: {margintop: 10, background: "#ffffff", textcolor: "#333333"}
            },
            detail_navbar: {
                name: "????????????",
                type: 5,
                max: 1,
                params: {
                    hidelike: 0,
                    hideshop: 0,
                    hidecart: 0,
                    hidecartbtn: 0,
                    textbuy: "????????????",
                    goodstext: "??????",
                    liketext: "??????",
                    likeiconclass: "icon-like",
                    likelink: "icon-like",
                    shoptext: "??????",
                    shopiconclass: "icon-shop",
                    carttext: "?????????",
                    carticonclass: "icon-cart"
                },
                style: {
                    background: "#ffffff",
                    textcolor: "#999999",
                    iconcolor: "#999999",
                    cartcolor: "#fe9402",
                    buycolor: "#fd5555",
                    dotcolor: "#ff0011"
                }
            },
            detail_seckill: {
                name: "?????????",
                type: 5,
                max: 1,
                params: {buybtntext: "????????????"},
                style: {
                    theme:'red'
                }
            },
            merchgroup: {
                name: "?????????",
                params: {
                    merchdata: '0',
                    merchnum: '6',
                    merchsort: '',
                    catename: '',
                    cateid: '',
                    groupname: '',
                    groupid: '',
                    openlocation: '0'
                },
                style: {
                    background: '#ffffff',
                    titlecolor: '#333333',
                    textcolor: '#666666',
                    rangecolor: '#ff5555',
                    locationcolor: '#ff5555',
                    margintop: '10'
                },
                data: {
                    C0123456789101: {name: "????????????A", desc: "???????????????A?????????", thumb: "", merchid: ""},
                    C0123456789102: {name: "????????????B", desc: "???????????????B?????????", thumb: "", merchid: ""},
                    C0123456789103: {name: "????????????C", desc: "???????????????C?????????", thumb: "", merchid: ""}
                }
            },
            audio: {
                name: "????????????",
                params: {
                    title: "?????????????????????",
                    subtitle: "?????????",
                    playerstyle: 0,
                    autoplay: 0,
                    loopplay: 0,
                    pausestop: 0,
                    headalign: "left",
                    headtype: "",
                    headurl: ""
                },
                style: {
                    background: "#f1f1f1",
                    bordercolor: "#ededed",
                    textcolor: "#333333",
                    subtitlecolor: "#666666",
                    timecolor: "#666666",
                    paddingtop: "20",
                    paddingleft: "20",
                    width: "80"
                }
            },
            seckillgroup: {
                max: 1,
                name: "?????????",
                params: {
                    iconurl: '../addons/ewei_shopv2/plugin/diypage/static/images/default/seckill.png',
                    hideborder: 0,
                    tag: ''
                },
                style: {
                    margintop: '10',
                    background: '#ffffff',
                    titlecolor: '#444444',
                    timecolor: '#444444',
                    timesigncolor: '#444444',
                    timebgcolor: '#ffffff',
                    timebordercolor: '#d9d9d9',
                    morecolor: '#888888',
                    marketpricecolor: '#ef4f4f',
                    productpricecolor: '#999999',
                }
            },
            seckill_times: {
                type: 7,
                max: 1,
                name: "???????????????",
                params: {},
                style: {}
            },
            seckill_rooms: {
                type: 7,
                name: "????????????",
                max: 1,
                params: {},
                style: {}
            },
            seckill_advs: {
                type: 7,
                max: 1,
                name: "????????????",
                params: {},
                style: {}
            },
            seckill_list: {
                type: 7,
                name: "????????????",
                max: 1,
                params: {},
                style: {}
            },
            coupon: {
                name: '????????????',
                params: {couponstyle: '3'},
                style: {background: '#ffffff', margintop: '10', marginleft: '5'},
                data: {
                    C0123456789101: {
                        name: "???????????????",
                        desc: "???100?????????",
                        price: "89.90",
                        couponid: "",
                        background: '#fd5454',
                        bordercolor: '#fd5454',
                        textcolor: '#ffffff',
                        couponcolor: '#55b5ff'
                    },
                    C0123456789102: {
                        name: "???????????????",
                        desc: "???100?????????",
                        price: "89.90",
                        couponid: "",
                        background: '#ff9140',
                        bordercolor: '#ff9140',
                        textcolor: '#ffffff',
                        couponcolor: '#ff5555'
                    },
                    C0123456789103: {
                        name: "???????????????",
                        desc: "???100?????????",
                        price: "89.90",
                        couponid: "",
                        background: '#54b5fd',
                        bordercolor: '#54b5fd',
                        textcolor: '#ffffff',
                        couponcolor: '#ff913f'
                    }
                }
            },
            fixedsearch: {
                name: '???????????????',
                istop: 1,
                max: 1,
                only:'toptab',
                params: {
                    leftnav: '1',
                    rightnav: '1',
                    rightnavclick: '0',
                    leftnavicon: 'icon-shop',
                    rightnavicon: 'icon-cart',
                    searchstyle: 'round',
                    placeholder: '???????????????????????????'
                },
                style: {
                    background: '#000000',
                    opacity: 0.8,
                    opacityinput: 0.8,
                    leftnavcolor: '#ffffff',
                    rightnavcolor: '#ffffff',
                    searchbackground: '#ffffff',
                    searchtextcolor: '#666666'
                }
            },
            exchange_banner: {
                name: '???????????????',
                max: 1,
                type: 8,
                params: {datatype: 0},
                style: {
                    'dotstyle': 'rectangle',
                    'dotalign': 'left',
                    'background': '#ffffff',
                    'leftright': '5',
                    'bottom': '5',
                    'opacity': '0.8'
                },
                data: {
                    C0123456789101: {
                        imgurl: '../addons/ewei_shopv2/plugin/diypage/static/images/default/banner-1.jpg',
                        linkurl: '',
                    },
                    C0123456789102: {
                        imgurl: '../addons/ewei_shopv2/plugin/diypage/static/images/default/banner-2.jpg',
                        linkurl: '',
                    }
                }
            },
            exchange_input: {
                name: '????????????',
                max: 1,
                type: 8,
                params: {
                    preview: '0',
                    title: '???????????????',
                    placeholder: '??????????????????',
                    btntext: '????????????',
                    backbtn: '???????????????????????????',
                    exbtntext: '??????',
                    exbtn2text: '?????????',
                    crediticon: '../addons/ewei_shopv2/plugin/diypage/static/images/default/icon_credit.png',
                    moneyicon: '../addons/ewei_shopv2/plugin/diypage/static/images/default/icon_money.png',
                    couponicon: '../addons/ewei_shopv2/plugin/diypage/static/images/default/icon_coupon.png',
                    redbagicon: '../addons/ewei_shopv2/plugin/diypage/static/images/default/icon_redbag.png',
                    goodsicon: '../addons/ewei_shopv2/plugin/diypage/static/images/default/icon_goods.png'
                },
                style: {
                    titlecolor: '#444444',
                    btncolor: '#ffffff',
                    btnbackground: '#ed5565',
                    inputcolor: '#666666',
                    inputbackground: '#ffffff',
                    inputborder: '#efefef',
                    codecolor: '#444444',
                    numcolor: '#999999',
                    exbtncolor: '#ffffff',
                    exbtnbackground: '#ed5565',
                    exbtn2color: '#ffffff',
                    exbtn2background: '#cccccc',
                    backbtncolor: '#444444',
                    backbtnborder: '#e7eaec',
                    backbtnbackground: '#f7f7f7',
                    goodstitle: '#444444',
                    goodsprice: '#aaaaaa'
                }
            },
            exchange_rule: {
                name: '????????????',
                max: 1,
                type: 8,
                params: {ruletitle: '????????????'},
                style: {ruletitlecolor: '#555555'}
            },
            tabbar: {
                name: '?????????',
                style: {
                    background: '#ffffff',
                    color: '#666666',
                    activebackground: '#ffffff',
                    activecolor: '#ef4f4f',
                    scrollnum: 5,
                    showtype: 1
                },
                params: {datatype: null},
                data: {
                    C0123456789101: {text: '??????', linkurl: ''},
                    C0123456789102: {text: '??????', linkurl: ''},
                    C0123456789103: {text: '??????', linkurl: ''},
                    C0123456789104: {text: '??????', linkurl: ''},
                    C0123456789105: {text: '??????', linkurl: ''}
                    }
            },
            topmenu:{
                name: '????????????',
                istop: 1,
                max: 1,
                only:'toptab',
                global:true,
                style: {
                    background: '#ffffff',
                    color: '#666666',
                    activebackground: '#ffffff',
                    activecolor: '#ef4f4f',
                    scrollnum: 5,
                    showtype: 1
                },
                params: {datatype: null},
                data: {
                    C0123456789101: {text: '??????', linkurl: '', active: 'active',},
                    C0123456789102: {text: '??????', linkurl: '',},
                    C0123456789103: {text: '??????', linkurl: '',},
                    C0123456789104: {text: '??????', linkurl: '',},
                    C0123456789105: {text: '??????', linkurl: '',}
                    }
                }
            }
    };
    modal.initItems = function (selected) {
        var phone = $("#phone");
        if (!modal.items) {
            modal.items = {};
            return
        }
        phone.empty();
        $.each(modal.items, function (itemid, item) {
            if (typeof(item.id) !== 'undefined') {
                var newItem = $.extend(true, {}, item);
                newItem.itemid = itemid;
                if (item.id == 'audio') {
                    newItem.shoplogo = modal.shopset ? modal.shopset.logo : ''
                }
                var html = tpl("tpl_show_" + item.id, newItem);
                $("#phone").append(html)
            }
        });
        var btnhtml = $("#edit-del").html();
        $("#phone .drag").append(btnhtml);
        $("#phone .drag .btn-edit-del .btn-del").unbind('click').click(function (e) {
            e.stopPropagation();
            var drag = $(this).closest(".drag");
            var itemid = drag.data('itemid');
            var nodelete = $(this).closest(".drag").hasClass("nodelete");
            if (nodelete) {
                tip.alert("?????????????????????");
                return
            }
            tip.confirm("???????????????", function () {
                var nearid = modal.getNear(itemid);
                delete modal.items[itemid];
                modal.initItems();
                if (nearid) {
                    $(document).find(".drag[data-itemid='" + nearid + "']").trigger('mousedown')
                } else {
                    $("#page").trigger('click')
                }
            })
        }); 
        if (selected) {
            modal.selectedItem(selected)
        }
    };
    modal.selectedItem = function (itemid) {
        if (!itemid) {
            return
        }
        modal.selected = itemid;
        if (itemid == 'page') {
            $("#page").trigger('click')
        } else {
            $(".drag[data-itemid='" + itemid + "']").addClass('selected')
        }
    };
    modal.initPage = function (initE) {
        if (typeof(initE) === 'undefined') {
            initE = true
        }
        if (!modal.page) {
            modal.page = {
                type: modal.type,
                title: '?????????????????????',
                name: '???????????????',
                desc: '',
                icon: '',
                keyword: '',
                background: '#f3f3f3',
                diymenu: '-1',
                diylayer: '0',
                diygotop: '0',
                followbar: '0',
                visit: '0',
                visitlevel: {member: null, commission: null},
                novisit: {title: null, link: null}
            };
            if (modal.type == 5) {
                modal.page.title = "????????????"
            }else if(modal.type==7){
                modal.page.seckill = {
                    style:'style1',
                    color:'red'
                }
            }else if (modal.type == 8) {
                modal.page.title = "????????????"
            }else if (modal.type == 99) {
                modal.page.type = 99;
                modal.page.title = '????????????';
                modal.page.name = '???????????????'
            }
        }else{
            if(modal.type==7 && !modal.page.seckill){
                modal.page.seckill = {
                    style: 'style1',
                    color: 'red'
                }
            }
        }
        if(!modal.page.visitlevel){
            modal.page.visitlevel = {member: null, commission: null}
        }
        if(!modal.page.novisit){
            modal.page.novisit = {};
        }
        $("#page").text(modal.page.title);
        $("#phone").css({'background-color': modal.page.background});
        $("#phone").find(".drag").removeClass("selected");
        if(modal.page.seckill){
            if(modal.page.seckill.style=='style1'){
                $("#phone").addClass(modal.page.seckill.style).removeClass('style2')
            }else{
                $("#phone").addClass(modal.page.seckill.style).removeClass('style1')
            }
            $("#phone").closest('.phone-body').removeClass().addClass('phone-body').addClass(modal.page.seckill.color)
        }
        if (initE) {
            modal.initEditor()
        }
    };
    modal.initSortable = function () {
        $("#phone").sortable({
            opacity: 0.8,
            placeholder: "highlight",
            items: '.drag:not(.fixed)',
            revert: 100,
            scroll: false,
            start: function (event, ui) {
                var height = ui.item.height();
                $(".highlight").css({"height": height + "px"});
                $(".highlight").html('<div><i class="fa fa-plus"></i> ????????????</div>');
                $(".highlight div").css({"line-height": height - 4 + "px"})
            },
            stop: function (event, ui) {
                modal.initEditor()
            },
            update: function (event, ui) {
                modal.sortItems()
            }
        });
        $("#phone").disableSelection();
        $(document).on('mousedown', "#phone .drag", function () {
            if ($(this).hasClass("selected")) {
                return
            }
            modal.selected = $(this).data('itemid');
            $("#phone").find(".drag").removeClass("selected");
            $(this).addClass("selected");
            modal.selected = $(this).data('itemid');
            modal.initEditor()
        })
    };
    modal.sortItems = function () {
        var newItems = {};
        $("#phone .drag").each(function () {
            var thisid = $(this).data('itemid');
            newItems[thisid] = modal.items[thisid]
        });
        modal.items = newItems
    };
    modal.initEditor = function (scroll) {
        if (typeof(scroll) === 'undefined') {
            scroll = true
        }
        var itemid = modal.selected;
        var top = 180;
        if (modal.selected != 'page') {
            var stop = $(".selected").position().top;
            top = stop ? stop : 0;
            if($('.wb-header').length>0){
                top += 100
            }else{
                top -= 30
            }
        }
        if (scroll) {
            $("#diy-editor").unbind('animate').animate({"margin-top": top - 130 + "px"});
            setTimeout(function () {
                $("body").unbind('animate').animate({scrollTop: top - 130 + "px"}, 1000)
            }, 1000)
        }
        if (modal.selected) {
            if (modal.selected == 'page') {
                if (modal.type == 99) {
                    var html = tpl("tpl_edit_page_mod", modal.page)
                } else {
                    var html = tpl("tpl_edit_page", modal)
                }
                $("#diy-editor .inner").html(html)
            } else {
                var item = $.extend(true, {}, modal.items[modal.selected]);
                item.itemid = modal.selected;
                item.merch = modal.merch;
                item.plugins = modal.plugins;
                var html = tpl("tpl_edit_" + item.id, item);
                $("#diy-editor .inner").html(html)
            }
            $("#diy-editor").attr("data-editid", modal.selected).show()
        }
        if(!html){
            $("#diy-editor").hide()
        }else{
            $("#diy-editor").show()
        }
        var sliderlength = $("#diy-editor .slider").length;
        if (sliderlength > 0) {
            $("#diy-editor .slider").each(function () {
                var decimal = $(this).data('decimal');
                var multiply = $(this).data('multiply');
                var defaultValue = $(this).data("value");
                if (decimal) {
                    defaultValue = defaultValue * decimal
                }
                $(this).slider({
                    slide: function (event, ui) {
                        var sliderValue = ui.value;
                        if (decimal) {
                            sliderValue = sliderValue / decimal
                        }
                        $(this).siblings(".input").val(sliderValue).trigger("propertychange");
                        $(this).siblings(".count").find("span").text(sliderValue)
                    }, value: defaultValue, min: $(this).data("min"), max: $(this).data("max")
                })
            })
        }
        var goodsSelector = $("#diy-editor .goods-selector").length;
        if (goodsSelector > 0) {
            var _this = $("#diy-editor .goods-selector");
            var gType = _this.data('goodstype') == 1 ? 'creditshop': '';
            var pType = _this.data('pagetype') == 2 ? 2: 0;
            var gUrl = modal.merch?  biz.url('goods/goods_selector', null, modal.merch): '';
            _this.unbind('click').click(function () {
                modal.childid = $(this).closest('.item').data('id');
                if (gType != 'creditshop'){
                    gType = pType;
                }
                gSelector.open('callbackGoods', gType, modal.merch, false, gUrl, false, false, pType)
            });
            }
        var categorySelector = $("#diy-editor .category-selector").length;
        if (categorySelector > 0) {
            var _this = $("#diy-editor .category-selector");
            var url = biz.url('goods/category/query', null, modal.merch);
            if (_this.data('goodstype') == 1) {
                url = biz.url('creditshop/category/query', null, modal.merch)
            }
            _this.attr({'id': 'category_selector', 'data-url': url, 'data-callback': 'callbackCategory'});
            _this.unbind('click').click(function () {
                biz.selector.select({name: 'category'})
            })
        }
        var groupSelector = $("#diy-editor .group-selector").length;
        if (groupSelector > 0) {
            var _this = $("#diy-editor .group-selector");
            _this.attr({
                'id': 'group_selector',
                'data-url': biz.url('goods/group/query', null, modal.merch),
                'data-callback': 'callbackGroup'
            });
            _this.unbind('click').click(function () {
                biz.selector.select({name: 'group'})
            })
        }
        var merchSelector = $("#diy-editor .merch-selector").length;
        if (merchSelector) {
            var _this = $("#diy-editor .merch-selector");
            var url = biz.url('merch/user/query', null, modal.merch);
            _this.attr({'id': 'merch_selector', 'data-url': url, 'data-callback': 'callbackMerch'});
            _this.unbind('click').click(function () {
                biz.selector.select({name: 'merch'});
                modal.childid = $(this).closest('.item').data('id')
            })
        }
        var merchCategorySelector = $("#diy-editor .merch-category-selector").length;
        if (merchCategorySelector) {
            var _this = $("#diy-editor .merch-category-selector");
            var url = biz.url('merch/category/query', null, modal.merch);
            _this.attr({'id': 'category_selector', 'data-url': url, 'data-callback': 'callbackMerchCategory'});
            _this.unbind('click').click(function () {
                biz.selector.select({name: 'category'})
            })
        }
        var merchGroupSelector = $("#diy-editor .merch-group-selector").length;
        if (merchGroupSelector) {
            var _this = $("#diy-editor .merch-group-selector");
            _this.attr({
                'id': 'group_selector',
                'data-url': biz.url('merch/group/query', null, modal.merch),
                'data-callback': 'callbackMerchGroup'
            });
            _this.unbind('click').click(function () {
                biz.selector.select({name: 'group'})
            })
        }
        var couponSelector = $("#diy-editor .coupon-selector").length;
        if (couponSelector) {
            var _this = $("#diy-editor .coupon-selector");
            _this.attr({
                'id': 'coupon_selector',
                'data-url': biz.url('sale/coupon/query', {diy: 1}, modal.merch),
                'data-callback': 'callbackCoupon'
            });
            _this.unbind('click').click(function () {
                biz.selector.select({name: 'coupon'});
                modal.childid = $(this).closest('.item').data('id')
            })
        }
        var audioPlayer = $("#diy-editor .audio-player").length;
        if (audioPlayer) {
            $("#diy-editor .audio-player").click(function () {
                var _this = $(this);
                var audio = _this.next('audio')[0];
                var src = _this.next('audio').attr('src');
                if (audio && src) {
                    if (audio.paused) {
                        audio.play();
                        _this.find('.fa').removeClass("fa-play").addClass("fa-stop");
                        var timer = setInterval(function () {
                            if (audio.currentTime >= audio.duration) {
                                audio.pause();
                                _this.find('.fa').removeClass("fa-stop").addClass("fa-play");
                                clearInterval(timer)
                            }
                        }, 1000)
                    } else {
                        audio.currentTime = 0;
                        audio.pause();
                        _this.find('.fa').removeClass("fa-stop").addClass("fa-play")
                    }
                } else {
                    tip.msgbox.err("?????????????????????")
                }
            })
        }
        var childitems = $("#diy-editor .form-items").length;
        if (childitems > 0) {
            modal.initSortableChild();
            $("#addChild").unbind('click').click(function () {
                var itemid = modal.selected;
                var type = modal.items[itemid].id;
                var temp = modal.navs[type].data;
                var max = $(this).closest(".form-items").data('max');
                if (max) {
                    var length = modal.length(modal.items[itemid].data);
                    if (length >= max) {
                        tip.msgbox.err("???????????? " + max + " ??????");
                        return
                    }
                }
                var newChild = {};
                var index = 0;
                $.each(temp, function (i, t) {
                    if (index == 0) {
                        newChild = t;
                        index++
                    }
                });
                if (newChild) {
                    var childName = modal.getId("M", 0);
                    if (typeof(modal.items[itemid].data) === 'undefined') {
                        modal.items[itemid].data = {}
                    }
                    newChild = $.extend(true, {}, newChild);
                    modal.items[itemid].data[childName] = newChild
                }
                modal.initItems(itemid);
                modal.initEditor(false)
            });
            $("#diy-editor .form-items .item .btn-del").unbind('click').click(function () {
                var childid = $(this).closest(".item").data('id');
                var itemid = modal.selected;
                var min = $(this).closest(".form-items").data("min");
                if (min) {
                    var length = modal.length(modal.items[itemid].data);
                    if (length <= min) {
                        tip.msgbox.err("???????????? " + min + " ??????");
                        return
                    }
                }
                tip.confirm("???????????????", function () {
                    delete modal.items[itemid].data[childid];
                    modal.initItems(itemid);
                    modal.initEditor(false)
                })
            })
        }
        var richtext = $("#diy-editor .form-richtext").length;
        if (richtext > 0) {
            var ueditoroption = {
                'autoClearinitialContent': false,
                'toolbars': [['fullscreen', 'source', 'preview', '|', 'bold', 'italic', 'underline', 'strikethrough', 'forecolor', 'backcolor', '|', 'justifyleft', 'justifycenter', 'justifyright', '|', 'insertorderedlist', 'insertunorderedlist', 'blockquote', 'emotion', 'removeformat', '|', 'rowspacingtop', 'rowspacingbottom', 'lineheight', 'indent', 'paragraph', 'fontsize', '|', 'inserttable', 'deletetable', 'insertparagraphbeforetable', 'insertrow', 'deleterow', 'insertcol', 'deletecol', 'mergecells', 'mergeright', 'mergedown', 'splittocells', 'splittorows', 'splittocols', '|', 'anchor', 'map', 'print', 'drafts', '|', 'link']],
                'elementPathEnabled': false,
                'initialFrameHeight': 300,
                'focus': false,
                'maximumWords': 9999999999999
            };
            var opts = {
                type: 'image',
                direct: false,
                multiple: true,
                tabs: {'upload': 'active', 'browser': '', 'crawler': ''},
                path: '',
                dest_dir: '',
                global: false,
                thumb: false,
                width: 0
            };
            if (modal.merch && modal.merchid){
                opts.dest_dir = 'merch/'+modal.merchid;
            }
            UE.registerUI('myinsertimage', function (editor, uiName) {
                editor.registerCommand(uiName, {
                    execCommand: function () {
                        require(['fileUploader'], function (uploader) {
                            uploader.show(function (imgs) {
                                if (imgs.length == 0) {
                                    return
                                } else if (imgs.length == 1) {
                                    editor.execCommand('insertimage', {
                                        'src': imgs[0]['url'],
                                        '_src': imgs[0]['url'],
                                        'width': '100%',
                                        'alt': imgs[0].filename
                                    })
                                } else {
                                    var imglist = [];
                                    for (i in imgs) {
                                        imglist.push({
                                            'src': imgs[i]['url'],
                                            '_src': imgs[i]['url'],
                                            'width': '100%',
                                            'alt': imgs[i].filename
                                        })
                                    }
                                    editor.execCommand('insertimage', imglist)
                                }
                            }, opts)
                        })
                    }
                });
                var btn = new UE.ui.Button({
                    name: '????????????',
                    title: '????????????',
                    cssRules: 'background-position: -726px -77px',
                    onclick: function () {
                        editor.execCommand(uiName)
                    }
                });
                editor.addListener('selectionchange', function () {
                    var state = editor.queryCommandState(uiName);
                    if (state == -1) {
                        btn.setDisabled(true);
                        btn.setChecked(false)
                    } else {
                        btn.setDisabled(false);
                        btn.setChecked(state)
                    }
                });
                return btn
            }, 48);
            UE.registerUI('myinsertvideo', function (editor, uiName) {
                editor.registerCommand(uiName, {
                    execCommand: function () {
                        require(['fileUploader'], function (uploader) {
                            uploader.show(function (video) {
                                if (!video) {
                                    return
                                } else {
                                    var videoType = video.isRemote ? 'iframe' : 'video';
                                    editor.execCommand('insertvideo', {
                                        'url': video.url,
                                        'width': 300,
                                        'height': 200
                                    }, videoType)
                                }
                            }, {fileSizeLimit: 5120000, type: 'video', allowUploadVideo: true, netWorkVideo: true})
                        })
                    }
                });
                var btn = new UE.ui.Button({
                    name: '????????????',
                    title: '????????????',
                    cssRules: 'background-position: -320px -20px',
                    onclick: function () {
                        editor.execCommand(uiName)
                    }
                });
                editor.addListener('selectionchange', function () {
                    var state = editor.queryCommandState(uiName);
                    if (state == -1) {
                        btn.setDisabled(true);
                        btn.setChecked(false)
                    } else {
                        btn.setDisabled(false);
                        btn.setChecked(state)
                    }
                });
                return btn
            }, 20);
            UE.registerUI('mylink', function (editor, uiName) {
                var btn = new UE.ui.Button({
                    name: 'selectUrl',
                    title: '????????????',
                    cssRules: 'background-position: -622px 80px;',
                    onclick: function () {
                        $("#" + this.id).attr({"data-toggle": "selectUrl", "data-callback": "selectUrlCallback"})
                    }
                });
                editor.addListener('selectionchange', function () {
                    var state = editor.queryCommandState(uiName);
                    if (state == -1) {
                        btn.setDisabled(true);
                        btn.setChecked(false)
                    } else {
                        btn.setDisabled(false);
                        btn.setChecked(state)
                    }
                });
                return btn
            });
            if (typeof(UE) != 'undefined') {
                UE.delEditor('rich')
            }
            var ue = UE.getEditor('rich', ueditoroption);
            ue.ready(function () {
                var thisitem = modal.items[itemid];
                var richContent = thisitem.params.content;
                richContent = $.base64.decode(richContent);
                ue.setContent(richContent);
                ue.addListener('contentChange', function () {
                    var newContent = ue.getContent();
                    newContent = $.base64.encode(newContent);
                    $("#richtext").html(newContent).trigger('change')
                })
            })
        }
        $("#diy-editor").find(".diy-bind").bind('input propertychange change', function () {
            var _this = $(this);
            var bind = _this.data("bind");
            var bindchild = _this.data('bind-child');
            var bindparent = _this.data('bind-parent');
            var initEditor = _this.data('bind-init');
            var value = '';
            var tag = this.tagName;
            if (!itemid) {
                modal.selectedItem('page ')
            }
            if (tag == 'INPUT') {
                var type = _this.attr('type');
                if (type == 'checkbox') {
                    value = [];
                    _this.closest('.form-group').find('input[type=checkbox]').each(function () {
                        var checked = this.checked;
                        var valname = $(this).val();
                        if (checked) {
                            value.push(valname)
                        }
                    })
                } else {
                    var placeholder = _this.data('placeholder');
                    value = _this.val();
                    value = value == '' ? placeholder : value
                }
            } else if (tag == 'SELECT') {
                value = _this.find('option:selected').val()
            } else if (tag == 'TEXTAREA') {
                value = _this.val()
            }
            value = $.trim(value);
            if (itemid == 'page') {
                if (bindchild) {
                    if (!modal.page[bindchild]) {
                        modal.page[bindchild] = {}
                    }
                    modal.page[bindchild][bind] = value
                } else {
                    modal.page[bind] = value
                }
                modal.initPage(false);
                if (bind == 'keyword') {
                    $.post(biz.url('diypage/page/keyword'), {id: modal.id, keyword: value}, function (r) {
                        if (r.status == 0) {
                            _this.closest('.form-group').addClass('has-error');
                            modal.keyworderr = true
                        } else {
                            _this.closest('.form-group').removeClass('has-error');
                            modal.keyworderr = false
                        }
                    }, 'json')
                }
            } else {
                if (bindchild) {
                    if (bindparent) {
                        modal.items[itemid][bindparent][bindchild][bind] = value
                    } else {
                        modal.items[itemid][bindchild][bind] = value
                    }
                } else {
                    modal.items[itemid][bind] = value
                }
                modal.initItems(itemid)
            }
            if (initEditor) {
                modal.initEditor(false)
            }
        })
    };
    modal.initSortableChild = function () {
        $("#diy-editor .inner").sortable({
            opacity: 0.8,
            placeholder: "highlight",
            items: '.item',
            revert: 100,
            scroll: false,
            cancel: '.goods-selector,input,select,.btn,btn-del',
            start: function (event, ui) {
                var height = ui.item.height();
                $(".highlight").css({"height": height + 22 + "px"});
                $(".highlight").html('<div><i class="fa fa-plus"></i> ????????????</div>');
                $(".highlight div").css({"line-height": height + 16 + "px"})
            },
            update: function (event, ui) {
                modal.sortChildItems()
            }
        })
    };
    modal.initMod = function (item) {
        $.ajax(biz.url('diypage/page/mod/query', null, modal.merch), {
            type: "get",
            dataType: "html",
            cache: false
        }).done(function (html) {
            modModal = $('<div class="modal fade" id="modModal"></div>');
            $(document.body).append(modal), modModal.modal('show');
            modModal.append2(html, function () {
                $(document).off("click", '#modModal nav').on("click", '#modModal nav', function () {
                    var modid = $(this).data('id');
                    var modname = $(this).data('name');
                    modModal.find(".close").click();
                    var itemid = modal.getId("M", 0);
                    item.params.modid = modid;
                    item.params.modname = modname;
                    if (modal.selected && modal.selected != 'page') {
                        var newItems = {};
                        $.each(modal.items, function (id, eachitem) {
                            newItems[id] = eachitem;
                            if (id == modal.selected) {
                                newItems[itemid] = item
                            }
                        });
                        modal.items = newItems
                    } else {
                        modal.items[itemid] = item
                    }
                    modal.initItems();
                    $(".drag[data-itemid='" + itemid + "']").trigger('mousedown').trigger('click');
                    modal.selected = itemid
                })
            })
        })
    };
    modal.initTemp = function () {
        var itemslength = 0;
        $.each(modal.items, function (index) {
            itemslength++;
            return false
        });
        if (!itemslength) {
            tip.msgbox.err("?????????????????????????????????????????????????????????");
            return
        }
        if (modal.type == 99) {
            tip.msgbox.err("??????????????????????????????????????????????????????");
            return
        }
        $("#saveTempModal").modal();
        $("#saveTemp", "#saveTempModal").unbind('click').click(function () {
            var tempname = $.trim($("#saveTempModal").find("#saveTempName").val());
            var tempcate = $.trim($("#saveTempModal").find("#saveTempCate option:selected").val());
            var temppreview = $.trim($("#saveTempModal").find("#saveTempPreview").val());
            var tempdata = {page: modal.page, items: modal.items};
            if (!tempname) {
                tip.msgbox.err("????????????????????????");
                $("#saveTempModal").find("#saveTempName").focus();
                return
            }
            $("#saveTempModal .close").trigger('click');
            if (modal.type == 1) {
                var posturl = biz.url("diypage/page/diy/savetemp", null, modal.merch)
            } else if (modal.type > 1 && modal.type < 6 && modal.type != 4) {
                var posturl = biz.url("diypage/page/sys/savetemp", null, modal.merch)
            } else if (modal.type > 4 && modal.type < 99 && modal.type != 5) {
                var posturl = biz.url("diypage/page/plu/savetemp", null, modal.merch)
            }
            $.post(posturl, {
                type: modal.type,
                cate: tempcate,
                name: tempname,
                preview: temppreview,
                data: tempdata
            }, function (ret) {
                if (ret.status == 0) {
                    tip.msgbox.err(ret.result.message)
                } else {
                    tip.msgbox.suc("??????????????????????????????")
                }
            }, 'json')
        })
    };
    modal.initTpl = function () {
        tpl.helper("imgsrc", function (src) {
            if (typeof src != 'string') {
                return ''
            }
            if (src.indexOf('http://') == 0 || src.indexOf('https://') == 0 || src.indexOf('/') == 0) {
                return src
            } else if (src.indexOf('images/') == 0 || src.indexOf('audios/') == 0) {
                return modal.attachurl + src
            }
        });
        tpl.helper("decode", function (content) {
            return $.base64.decode(content)
        });
        tpl.helper("count", function (data) {
            return modal.length(data)
        });
        tpl.helper("toArray", function (data) {
            var oldArray = $.makeArray(data);
            var newArray = [];
            $.each(data, function (itemid, item) {
                newArray.push(item)
            });
            return newArray
        });
        tpl.helper("strexists", function (str, tag) {
            if (!str || !tag) {
                return false
            }
            if (str.indexOf(tag) != -1) {
                return true
            }
            return false
        });
        tpl.helper("inArray", function (str, tag) {
            if (!str || !tag) {
                return false
            }
            if(typeof(str)=='string'){
                var arr = str.split(",");
                if($.inArray(tag, arr)>-1){
                    return true;
                }
            }
            return false
        });
        tpl.helper("define", function (str) {
            var str
        })
    };
    modal.initGotop = function () {
        $(window).bind('scroll resize', function () {
            var scrolltop = $(window).scrollTop();
            if (scrolltop > 300) {
                $("#gotop").show()
            } else {
                $("#gotop").hide()
            }
            $("#gotop").unbind('click').click(function () {
                $('body').animate({scrollTop: "0px"}, 1000)
            })
        })
    };
    modal.getNear = function (itemid) {
        var newarr = [];
        var index = 0;
        var prev = 0;
        var next = 0;
        $.each(modal.items, function (id, obj) {
            newarr[index] = id;
            if (id == itemid) {
                prev = index - 1;
                next = index + 1
            }
            index++
        });
        var pervid = newarr[prev];
        var nextid = newarr[next];
        if (nextid) {
            return nextid
        }
        if (pervid) {
            return pervid
        }
        return false
    };
    modal.getItemNum = function (id) {
        if (!id || !modal.items) {
            return -1
        }
        var itemNum = 0;
        $.each(modal.items, function (itemid, eachitem) {
            if (eachitem.id == id) {
                itemNum++
            }
        });
        return itemNum
    };
    modal.getItemOnlyNum = function (only) {
        if (!only || !modal.items) {
            return -1
        }
        var itemNum = 0;
        $.each(modal.items, function (itemid, eachitem) {
            if (eachitem.only == only) {
                itemNum++
            }
        });
        return itemNum
    };
    modal.sortChildItems = function () {
        var newChild = {};
        var itemid = modal.selected;
        $("#diy-editor .form-items .item").each(function () {
            var thisid = $(this).data('id');
            newChild[thisid] = modal.items[itemid].data[thisid]
        });
        modal.items[itemid].data = newChild;
        modal.initItems(itemid)
    };
    modal.length = function (json) {
        if (typeof(json) === 'undefined') {
            return 0
        }
        var jsonlen = 0;
        for (var item in json) {
            jsonlen++
        }
        return jsonlen
    };
    modal.callbackGoods = function (data) {
        console.log(data);
        if (!data) {
            tip.msgbox.err("?????????????????????????????????");
            return
        }
        if(!data.minprice){
            data.minprice = data.marketprice;
        }
        var itemid = modal.selected;
        var childid = modal.childid;
        modal.items[itemid].data[childid] = {
            'title': data.title,
            'thumb': data.thumb,
            'price': data.minprice,
            'gid': data.id,
            'bargain': data.bargain,
            'credit': data.credit,
            'seecommission':data.seecommission,
            'cansee' : data.cansee,
            'seetitle': data.seetitle,
        };
        modal.initItems(itemid);
        modal.initEditor(false);
        modal.childid = null
    };
    modal.callbackCategory = function (data) {
        if (!data) {
            tip.msgbox.err("?????????????????????????????????");
            return
        }
        var itemid = modal.selected;
        modal.items[itemid].params.catename = data.name;
        modal.items[itemid].params.cateid = data.id;
        modal.items[itemid].params.groupname = '';
        modal.items[itemid].params.groupid = '';
        modal.initItems(itemid);
        modal.initEditor(false)
    };
    modal.callbackGroup = function (data) {
        if (!data) {
            tip.msgbox.err("?????????????????????????????????");
            return
        }
        var itemid = modal.selected;
        modal.items[itemid].params.groupname = data.name;
        modal.items[itemid].params.groupid = data.id;
        modal.items[itemid].params.catename = '';
        modal.items[itemid].params.cateid = '';
        modal.initItems(itemid);
        modal.initEditor()
    };
    modal.callbackMerch = function (data) {
        if (!data) {
            tip.msgbox.err("?????????????????????????????????");
            return
        }
        var itemid = modal.selected;
        var childid = modal.childid;
        modal.items[itemid].data[childid] = {
            'name': data.merchname,
            'thumb': data.logo,
            'merchid': data.id,
            'desc': data.desc
        };
        modal.initItems(itemid);
        modal.initEditor(false);
        modal.childid = null
    };
    modal.callbackMerchCategory = function (data) {
        if (!data) {
            tip.msgbox.err("?????????????????????????????????");
            return
        }
        var itemid = modal.selected;
        modal.items[itemid].params.catename = data.catename;
        modal.items[itemid].params.cateid = data.id;
        modal.items[itemid].params.groupname = '';
        modal.items[itemid].params.groupid = '';
        modal.initItems(itemid);
        modal.initEditor(false)
    };
    modal.callbackMerchGroup = function (data) {
        if (!data) {
            tip.msgbox.err("?????????????????????????????????");
            return
        }
        var itemid = modal.selected;
        modal.items[itemid].params.groupname = data.groupname;
        modal.items[itemid].params.groupid = data.id;
        modal.items[itemid].params.catename = '';
        modal.items[itemid].params.cateid = '';
        modal.initItems(itemid);
        modal.initEditor()
    };
    modal.callbackCoupon = function (data) {
        if (!data) {
            tip.msgbox.err("?????????????????????????????????");
            return
        }
        var itemid = modal.selected;
        var childid = modal.childid;
        modal.items[itemid].data[childid].price = data.values;
        modal.items[itemid].data[childid].desc = data.uselimit;
        modal.items[itemid].data[childid].couponid = data.id;
        modal.items[itemid].data[childid].name = data.couponname;
        modal.initItems(itemid);
        modal.initEditor(false);
        modal.childid = null
    };
    modal.callbackData = function(data){
        if (!data) {
            tip.msgbox.err("?????????????????????????????????");
            return
        }
        var itemid = modal.selected;
        var childid = modal.childid;
       var item = modal.items[itemid];
       if(item.id == 'topmenu') {
           modal.items[itemid].params.datatype=data;
       }
        modal.initItems(itemid);
        modal.initEditor(false)
    };
    modal.save = function (preview) {
        if (typeof(preview) === 'undefined') {
            preview = false
        }
        if (preview && modal.type == 5) {
            tip.msgbox.err("???????????????????????????????????????????????????????????????");
            return
        }
        if (modal.keyworderr) {
            tip.msgbox.err("?????????????????????");
            $("#page").trigger('click');
            $("#diy-editor input[data-bind='keyword']").closest('.form-group').addClass('has-error');
            return
        }
        modal.data = {};
        modal.data = {page: modal.page, items: modal.items};
        if (!modal.page.title) {
            tip.msgbox.err("????????????????????????");
            $("#page").trigger("click");
            return
        }
        $(".btn-save").data('status', 1).text("?????????...");
        if (modal.type == 1) {
            if (modal.id > 0) {
                var posturl = biz.url("diypage/page/diy/edit", null, modal.merch)
            } else {
                var posturl = biz.url("diypage/page/diy/add", null, modal.merch)
            }
        } else if (modal.type > 1 && modal.type < 6 && modal.type != 4) {
            if (modal.id > 0) {
                var posturl = biz.url("diypage/page/sys/edit", null, modal.merch)
            } else {
                var posturl = biz.url("diypage/page/sys/add", null, modal.merch)
            }
        } else if (modal.type > 4 && modal.type < 99 && modal.type != 5) {
            if (modal.id > 0) {
                var posturl = biz.url("diypage/page/plu/edit", null, modal.merch)
            } else {
                var posturl = biz.url("diypage/page/plu/add", null, modal.merch)
            }
        } else if (modal.type == 99) {
            if (modal.id > 0) {
                var posturl = biz.url("diypage/page/mod/edit", null, modal.merch)
            } else {
                var posturl = biz.url("diypage/page/mod/add", null, modal.merch)
            }
        }
        $.post(posturl, {id: modal.id, data: modal.data}, function (ret) {
            if (ret.status == 0) {
                tip.msgbox.err(ret.result.message);
                $(".btn-save[data-type='save']").text("????????????").data("status", 0);
                $(".btn-save[data-type='preview']").text("???????????????").data("status", 0);
                $(".btn-save[data-type='savetemp']").text("???????????????").data("status", 0);
                return
            }
            var pageid = ret.result.id;
            if (pageid == modal.id) {
                $(".btn-save[data-type='save']").text("????????????").data("status", 0);
                $(".btn-save[data-type='preview']").text("???????????????").data("status", 0);
                $(".btn-save[data-type='savetemp']").text("???????????????").data("status", 0);
                if (preview) {
                    tip.msgbox.suc("?????????????????????????????????...");
                    setTimeout(function () {
                        var previewUrl = biz.url("diypage/page/preview", null, modal.merch) + "&id=" + pageid;
                        window.open(previewUrl)
                    }, 1000)
                } else {
                    tip.msgbox.suc("???????????????")
                }
            } else {
                if (preview) {
                    tip.msgbox.suc("?????????????????????????????????...");
                    util.cookie.set('preview_id', pageid)
                } else {
                    tip.msgbox.suc("???????????????")
                }
                location.href = ret.result.jump
            }
        }, 'json')
    };
    modal.length = function (json) {
        if (typeof(json) === 'undefined') {
            return 0
        }
        var jsonlen = 0;
        for (var item in json) {
            jsonlen++
        }
        return jsonlen
    };
    return modal
});