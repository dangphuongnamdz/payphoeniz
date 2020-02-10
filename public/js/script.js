$(document).ready(function() {
    //list server
    $('#listServer').DataTable({
        "paging": true,
        "ordering": true,
        "searching": false,
        "filter": true,
        "pageLength": 5,
        "info": false,
        "lengthChange": false,
    });

    // popup
    $(function() {
        //----- OPEN
        $('[pd-popup-open]').on('click', function(e) {
            var targeted_popup_class = jQuery(this).attr('pd-popup-open');
            var href = $(this).attr('href');
            $('#loadding_popup').show();
            $('#iframe').attr('src', href);
            $('[pd-popup="' + targeted_popup_class + '"]').fadeIn(100);
            e.preventDefault();
        });

        //---- CHECK LOAD IFRAME COMPLETE
        $('#iframe').load(function() {
            $('#loadding_popup').hide();
        });

        //----- CLOSE
        $('[pd-popup-close]').on('click', function(e) {
            var targeted_popup_class = jQuery(this).attr('pd-popup-close');
            $('[pd-popup="' + targeted_popup_class + '"]').fadeOut(200);
            e.preventDefault();
        });
    });


    //login
    $("#frmLoginDirect").submit(function(e) {
        e.preventDefault();
        var uname = $('#i_name').val().trim();
        var upass = $('#i_pass').val().trim();
        if (!uname) {
            $('#login-box-err').text("Vui lòng nhập tên đăng nhập.");
            $('#login-box-err').show();
        } else if (!upass) {
            $('#login-box-err').text("Vui lòng nhập mật khẩu.");
            $('#login-box-err').show();
        } else {
            $.post("passport/loginAjax", {
                    username: uname,
                    password: upass
                },
                function(data) {
                    if (data.response == true) {
                        document.getElementById("login-box").innerHTML =
                            "<p id='welcomeName'>Xin chào " + data.username +
                            "</p><a id='logout' href='https://id.100d.mobi/edit' pd-popup-open='popupNew'>Thông tin tài khoản</a>" +
                            "<a id='logout' href='https://id.100d.mobi/changepass' pd-popup-open='popupNew'>Đổi mật khẩu</a>" +
                            "<a id='logout' href='./payment/balance' pd-popup-open='popupNew'>Số dư tài khoản</a>" +
                            "<a id='logout' href='./passport/logout'>Logout</a>";
                        // console.log(data);
                    } else {
                        $('#login-box-err').text(data.error);
                        $('#login-box-err').show();
                        console.log('error');
                    }
                }, 'json');
        }

    });

    //tab news
    jQuery(function($) {
        Array.prototype.groupBy = function(prop) {
            return this.reduce(function(groups, item) {
                var val = item[prop];
                groups[val] = groups[val] || [];
                groups[val].push(item);
                return groups;
            }, {});
        }
        $(window).on('load', function() {
            $.post("tab.html", {},
                function(data) {
                    var arr = data.groupBy('id_category');
                    //console.log(arr);
                    var tabHtml = "";
                    var rowTabHtml = "";

                    for (var i in arr) {
                        if (arr[i][0].url == 'tin-tuc') {
                            tabHtml += "<li class='active'><a href='#" + arr[i][0].url + "' data-toggle='tab'>" + arr[i][0].tendanhmuc + "</a></li>";
                            rowTabHtml += "<div class='tab-pane active' id='" + arr[i][0].url + "'<ul class='rs-post-tab'>";
                        } else {
                            tabHtml += "<li><a href='#" + arr[i][0].url + "' data-toggle='tab'>" + arr[i][0].tendanhmuc + "</a></li>";
                            rowTabHtml += "<div class='tab-pane' id='" + arr[i][0].url + "'<ul class='rs-post-tab'>";
                        }
                        for (var j = 0; j < arr[i].length; j++) {
                            rowTabHtml += "<li><a href='" + arr[i][j].alias + "-" + arr[i][j].id + ".html'>" + arr[i][j].title + "</a><span class='rs-post-date'>" + arr[i][j].created_at + "</span></li>";
                        }
                        rowTabHtml += "</ul></div>";
                    }
                    document.getElementById("tabMenu").innerHTML = tabHtml;
                    document.getElementById("rowTabMenu").innerHTML = rowTabHtml;
                }, 'json');
        })
    });

});