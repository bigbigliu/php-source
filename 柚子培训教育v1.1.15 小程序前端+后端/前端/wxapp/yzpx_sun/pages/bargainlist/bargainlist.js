var _extends = Object.assign || function(t) {
    for (var a = 1; a < arguments.length; a++) {
        var e = arguments[a];
        for (var i in e) Object.prototype.hasOwnProperty.call(e, i) && (t[i] = e[i]);
    }
    return t;
}, _reload = require("../../resource/js/reload.js"), _api = require("../../resource/js/api.js");

function _defineProperty(t, a, e) {
    return a in t ? Object.defineProperty(t, a, {
        value: e,
        enumerable: !0,
        configurable: !0,
        writable: !0
    }) : t[a] = e, t;
}

var app = getApp();

Page(_extends({}, _reload.reload, {
    data: {
        nonePage: !1,
        list: {
            load: !1,
            over: !1,
            page: 1,
            length: 10,
            data: []
        }
    },
    onLoad: function(t) {
        var a = wx.getStorageSync("shopinfo");
        this.setData({
            sid: a.sid
        });
    },
    onloadData: function(t) {
        var a = this;
        t.detail.login && (this.setData({
            login: t.detail.login
        }), this.checkUrl().then(function(t) {
            return a.getListData();
        }).catch(function(t) {
            -1 === t.code ? a.tips(t.msg) : a.tips("false");
        }));
    },
    getListData: function() {
        var a = this;
        if (!this.data.list.over) {
            this.setData(_defineProperty({}, "list.load", !0));
            var e = {
                page: this.data.list.page,
                length: this.data.list.length,
                sid: this.data.sid
            };
            return (0, _api.BargainListData)(e).then(function(t) {
                a.dealList(t, e.page);
            });
        }
    },
    onReachBottom: function() {
        this.getListData();
    },
    onNavTab: function(t) {
        var a = t.currentTarget.dataset.idx, e = this.data.nav;
        for (var i in e) {
            e[i = i - 0].choose = i === a;
        }
        this.setData({
            nav: e,
            choose: a,
            list: {
                load: !1,
                over: !1,
                page: 1,
                length: 10,
                data: []
            }
        }), this.getListData();
    },
    onShareAppMessage: function(t) {
        return {
            title: "砍价列表",
            path: "/yzpx_sun/pages/bargainlist/bargainlist?sid=" + this.data.sid
        };
    }
}));