/*!
 * jQuery ClassyKeyboard
 * http://www.class.pm/projects/jquery/classykeyboard
 *
 * Copyright 2011 - 2013, Class.PM www.class.pm
 * Written by Marius Stanciu - Sergiu <marius@picozu.net>
 * Licensed under the GPL Version 3 license.
 * Version 1.1.1
 *
 */
(function ($) {
    $.ClassyKeyboard = function (d, o) {
        var f = $.ClassyKeyboard.defaults;
        "object" === typeof d ? f = $.extend({}, f, d) : (f.keys = d, f.callback = o);
        f.keys = f.keys.replace("rightclick", "contextmenu");
        $.each("click,dblclick,hover,mousedown,mouseenter,mouseleave,tapone,taptwo,tapthree,mousemove,mouseout,mouseover,mouseup,contextmenu".split(","), function (d, h) {
            if (!/textarea|select/i.test($(f.selector).get(0).tagName) && !($(f.selector).is("[type=text]") || $(f.selector).prop("contenteditable") == "true") && f.selector !== $(document) && $(f.selector).get(0).tagName !== void 0 && $(f.selector).attr("tabindex") < 1) {
                $(f.selector).attr("tabindex", 1E3 + d);
                $(f.selector).css("outline", "none");
            }
            f.keys.indexOf(h.toLowerCase()) != -1 && $(f.selector).bind(h, f.callback);
        });
        $(f.selector).bind(f.event, f.keys, f.callback);
        $.ClassyKeyboard.active[f.keys] = f;
    };
    $.ClassyKeyboard.active = {};
    $.ClassyKeyboard.unbind = function (d) {
        var d = d.toLowerCase(), o = $.ClassyKeyboard.active[d];
        $(document).unbind(o.event, o.callback);
        delete $.ClassyKeyboard.active[d];
    };
    $.ClassyKeyboard.defaults = {
        selector: document,
        event: "keydown"
    };
    $.fn.ClassyKeyboard = function (d, o) {
        this.each(function (f, u) {
            $.ClassyKeyboard({
                keys: d,
                callback: o,
                selector: u
            })
        });
        return this
    };
    $.ClassyKeyboard.specialMap = {
        16: "shift",
        17: "ctrl",
        9: "tab",
        20: "caps",
        18: "alt",
        27: "esc",
        244: "meta",
        112: "f1",
        113: "f2",
        114: "f3",
        115: "f4",
        116: "f5",
        117: "f6",
        118: "f7",
        119: "f8",
        120: "f9",
        121: "f10",
        122: "f11",
        123: "f12",
        45: "insert",
        36: "home",
        35: "end",
        33: "pageup",
        34: "pagedown",
        19: "pause",
        145: "scroll",
        144: "num",
        37: "left",
        38: "up",
        39: "right",
        40: "down",
        111: "/",
        106: "*",
        109: "-",
        107: "+",
        110: ".",
        8: "backspace",
        32: "space",
        13: "enter"
    };
    $.ClassyKeyboard.shiftMap = {
        "`": "~",
        1: "!",
        2: "@",
        3: "#",
        4: "$",
        5: "%",
        6: "^",
        7: "&",
        8: "*",
        9: "(",
        "0": ")",
        "-": "_",
        "=": "+",
        ";": ": ",
        "'": '"',
        ",": "<",
        ".": ">",
        "/": "?",
        "\\": "|"
    };
    $.each(["keydown", "keyup", "keypress"], function () {
        $.event.special[this] = {
            add: function (d) {
                if ("string" === typeof d.data) {
                    var o = d.handler,
                    f = d.data.toLowerCase().split(" ");
                    d.handler = function (d) {
                        if (!(this !== d.target && (/textarea|select/i.test(d.target.nodeName) || "text" === d.target.type || "true" == $(d.target).prop("contenteditable")))) {
                            var h = "keypress" !== d.type && $.ClassyKeyboard.specialMap[d.which], r = String.fromCharCode(d.which).toLowerCase(), k = "", p = {};
                            d.altKey && "alt" !== h && (k += "alt+");
                            d.ctrlKey && "ctrl" !== h && (k += "ctrl+");
                            d.metaKey && !d.ctrlKey && "meta" !== h && (k += "meta+");
                            d.shiftKey && "shift" !== h && (k += "shift+");
                            h ? p[k + h] = !0 : (p[k + r] = !0, p[k + $.ClassyKeyboard.shiftMap[r]] = !0, "shift+" === k && (p[$.ClassyKeyboard.shiftMap[r]] = !0));
                            h = 0;
                            for (var l = f.length; h < l; h++) {
                                if (p[f[h]]) {
                                    return o.apply(this, arguments);
                                }
                            }
                        }
                    }
                }
            }
        }
    });
})(jQuery);