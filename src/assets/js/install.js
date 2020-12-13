/*
 * @link https://github.com/EngineCore/module-installation
 * @copyright Copyright (c) 2020 E-Kevin
 * @license BSD 3-Clause License
 */

$(function () {
    var prevUrl = $('.list-group-item.active').prev().data("url");
    var nextUrl = $('.list-group-item.active').next().data("url");
    var activeUrl = $('.list-group-item.active').data("url");
    var data = {};

    if (prevUrl === undefined) {
        $("#prevButton").addClass("hidden");
    } else {
        $("#prevButton").click(function () {
            location.href = prevUrl;
        });
    }

    if (nextUrl === undefined) {
        $("#nextButton").text("完成");
        nextUrl = "/";
    }
    $("#nextButton").click(function () {
        if ($("#install-form").length !== 0) {
            data = $("#install-form").serializeArray();
        }
        $.post(activeUrl, data, function (data) {
            if (data.status === 1) {
                if (nextUrl) {
                    location.href = nextUrl;
                }
            } else {
                var info = '',
                    i = 1,
                    len = data.info.length;
                if (typeof data.info === 'object') {
                    for (var item in data.info) {
                        if (len >= 2) {
                            info += i + ') ' + data.info[item] + '</br>';
                            ++i;
                        } else {
                            info += data.info[item];
                        }
                    }
                } else {
                    info = data.info;
                }
                $("#msgBox").modal("show").find(".modal-body").html(info);
            }
        });
    });

});