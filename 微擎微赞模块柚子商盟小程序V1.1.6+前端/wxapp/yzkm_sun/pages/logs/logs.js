var util = require("../../resource/js/utils/util.js");

Page({
    data: {
        logs: []
    },
    onLoad: function() {
        this.setData({
            logs: (wx.getStorageSync("logs") || []).map(function(t) {
                return util.formatTime(new Date(t));
            })
        });
    }
});