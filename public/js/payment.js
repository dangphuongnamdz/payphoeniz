var formatter = new Intl.NumberFormat('vi-VI', {
    style: 'currency',
    currency: 'VND',
    minimumFractionDigits: 0,
});
$(document).ready(function(e) {
    if (is_pay == 1) {
        $('input[name=in_serie]').val('');
        $('input[name=in_pin]').val('');
        $('select[name=amount_pay]').val('');
        $("#accGetRole").show();
        $("#loaddingAgent").hide();
        $.post("payment/getrole.html", {
            server_id: $("#server_list").val(),
            id_user: $('#id_user').val(),
            agent: $('#agent').val(),
            is_amount: is_amount,
			gold_id: gold_id,
			role:role_id,
        }, function(result) {
            $("#loaddingAgent").hide();
            document.getElementById('server_list').removeAttribute("disabled");
            $('#appentHtml').empty().append("<div class='clearfix'></div>" + result['result']);			
            //
            if (result['status'] == true) {
				if(agent == 'm002' || agent == 'm003'){
					$('#showcoingold').empty().append(result['resultCoinGold']);
					$('#showcoingift').empty().append(result['resultCoinGift']);
					if(result['active']=='gift'){
						$('#showcoingold').css('height','0px');
						$('#showcoingold').css('display','none');
						$('#coingift').css('background-color','#0e90d2');
						$('#coingift').css('color','#fff');
						$('#showcoingift').css('height','1005');
						$('#showcoingift').css('display','block');
					}else{
						$('#showcoingift').css('height','0px');
						$('#showcoingift').css('display','none');
						$('#coingold').css('background-color','#0e90d2');
						$('#coingold').css('color','#fff');
						$('#showcoingold').css('height','100%');
						$('#showcoingold').css('display','block');
					}
				}else{
					var col_wrapper = document.getElementById("accRang").getElementsByTagName("p");
					var len = col_wrapper.length;

					for (var i = 0; i < len; i++) {
						if (col_wrapper[i].className.toLowerCase() == "tab") {
							col_wrapper[i].parentNode.removeChild(col_wrapper[i]);
						}
					}
					$('.resultCoin').empty().append(result['resultCoin']);
				}
                $("#accRang").show();
            } else {
                $("#accRang").hide();
            }
        }, 'json');
    }
    $("#server_list").change(function() {
        $("#loaddingAgent").show();
        document.getElementById('server_list').setAttribute("disabled", "disabled");
        $("#accRang").hide();
        $.post("payment/getrole.html", {
            server_id: $(this).val(),
            id_user: $('#id_user').val(),
            agent: $('#agent').val(),
			is_amount: is_amount,
			gold_id: gold_id,
			role:role_id,
        }, function(result) {
            $("#loaddingAgent").hide();
            document.getElementById('server_list').removeAttribute("disabled");
            $('#appentHtml').empty().append("<div class='clearfix'></div>" + result['result']);
            //$('.resultCoin').empty().append(result['resultCoin']);
            if (result['status'] == true) {
				if(agent == 'm002' || agent == 'm003'){
					$('#showcoingold').empty().append(result['resultCoinGold']);
					$('#showcoingift').empty().append(result['resultCoinGift']);
					if(result['active']=='gift'){
						$('#showcoingold').css('height','0px');
						$('#showcoingold').css('display','none');
						$('#coingift').css('background-color','#0e90d2');
						$('#coingift').css('color','#fff');
						$('#showcoingift').css('height','1005');
						$('#showcoingift').css('display','block');
					}else{
						$('#showcoingift').css('height','0px');
						$('#showcoingift').css('display','none');
						$('#coingold').css('background-color','#0e90d2');
						$('#coingold').css('color','#fff');
						$('#showcoingold').css('height','100%');
						$('#showcoingold').css('display','block');
					}
				}else{
					var col_wrapper = document.getElementById("accRang").getElementsByTagName("p");
					var len = col_wrapper.length;

					for (var i = 0; i < len; i++) {
						if (col_wrapper[i].className.toLowerCase() == "tab") {
							col_wrapper[i].parentNode.removeChild(col_wrapper[i]);
						}
					}
					$('.resultCoin').empty().append(result['resultCoin']);
				}
                $("#accRang").show();
            } else {
                $("#accRang").hide();
            }
        }, 'json');
    });
	$(document).on('click', '#coingold', function(e) {
		$('#showcoingift').css('height','0px');
		$('#showcoingift').css('display','none');
		$('#coingold').css('background-color','#0e90d2');
		$('#coingold').css('color','#fff');
		$('#showcoingold').css('height','100%');
		$('#showcoingold').css('display','block');
		$('#coingift').css('background-color','#fff');
		$('#coingift').css('color','#777');
	});
	$(document).on('click', '#coingift', function(e) {
		$('#showcoingold').css('height','0px');
		$('#showcoingold').css('display','none');
		$('#coingift').css('background-color','#0e90d2');
		$('#coingift').css('color','#fff');
		$('#showcoingift').css('height','100%');
		$('#showcoingift').css('display','block');
		$('#coingold').css('background-color','#fff');
		$('#coingold').css('color','#777');
	});
    $(document).on('click', '.img-check', function(e) {
        $('.img-check').not(this).removeClass('check').siblings('input').prop("checked", false);
        $(this).addClass('check').siblings('input').prop('checked', true);
        if (parseInt($(this).siblings('input').val()) > parseInt($("input[name=balance]").val())) {
            $("#myModal p").html("Không đủ số dư trong tài khoản, hãy chọn mệnh giá thấp hơn hoặc nạp thêm tiền vào ví để tiếp tục giao dịch");
            $(".close").text('Chấp nhận');
            document.getElementById('myModal').style.display = "block";
            $("#myModal #submitbutton").hide();
            document.getElementsByClassName("close")[0].onclick = function() {
                document.getElementById('myModal').style.display = "none";
                $("#accType").focus();
                $('html, body').animate({ scrollTop: $("#accType").offset().top }, 'slow');
            }
            $("#accType").show();
        } else {
            document.getElementById('myModal').style.display = "block";
            document.getElementsByClassName("close")[0].onclick = function() {
                document.getElementById('myModal').style.display = "none";
            }
            
            $(".close").text('Hủy bỏ'); 
            
			var agent = $('#agent').val();
			/*lay thong tin goi*/
			var gold_id = 0;
			if(agent == 'h001'){
				gold_id = $(this).siblings('input').attr("data-product");
			}else{
				gold_id = $(this).siblings('input').val();;
			}
			var html = "THÔNG TIN NẠP TIỀN VÀO GAME  <i class='fa fa-star' aria-hidden='true'></i>";
			$.post("payment/getinfogold.html", {
				gold_id: gold_id,
				agent: agent,
			}, function(result) {
				if(result['status']==true){
					$("#myModal #submitbutton").show();
					$("input[name=submit]").val('Thanh toán');
					var server = $('select[name=server_list] option:selected').text();
					var balance = $("input[name=balance]").val();
					var unit = 'KC';
					var note = '';
					if(agent=='h001'){
						if(gold_id=='28581'){
							$("#theThang").val('28581');
							note = "<br><b style='color:red'>Lưu ý: thiếu hiệp cần chắc chắn rằng gói quà này có thể tiếp tục mua. Nếu trong game gói quà này vẫn còn hạn nhận quà hoặc mới nhận quà lần cuối trong ngày hôm nay, thì khi tiếp tục mua lần nữa sẽ không cộng dồn số ngày nhận thưởng.</b>";
						}
						unit = 'NB';
					}else if(agent == 'm002'){
						unit = 'NB';
						if(isNaN(result['gold'])){							
							unit = '';
							if(gold_id=='209'){
								note = "<br><b style='color:red'>Lưu ý: Đây là Quà chỉ nên mua 1 lần duy nhất. </b>";
								note = note + "<br><b style='color:red'>Bạn cần chắc rằng Quà đang chọn có hiển thị giao diện trong game và có thể mua. </b>";
								note = note + "<br><b style='color:red'>Nếu đã mua 1 lần, thì khi mua tiếp các lần sau sẽ tự động quy đổi thành 800 NB </b>";
							}else if(gold_id=='210'){
								note = "<br><b style='color:red'>Lưu ý: Đây là Quà chỉ nên mua 1 lần duy nhất. </b>";
								note = note + "<br><b style='color:red'>Bạn cần chắc rằng Quà đang chọn có hiển thị giao diện trong game và có thể mua. </b>";
								note = note + "<br><b style='color:red'>Nếu đã mua 1 lần, thì khi mua tiếp các lần sau sẽ tự động quy đổi thành 80 NB </b>";
							}else if(gold_id=='211'){
								note = "<br><b style='color:red'>Lưu ý: Đây là Quà chỉ nên mua 1 lần duy nhất. </b>";
								note = note + "<br><b style='color:red'>Bạn cần chắc rằng Quà đang chọn có hiển thị giao diện trong game và có thể mua. </b>";
								note = note + "<br><b style='color:red'>Nếu đã mua 1 lần, thì khi mua tiếp các lần sau sẽ tự động quy đổi thành 200 NB </b>";
							}else{
								note = "<br><b style='color:red'>Lưu ý: Bạn cần chắc chắn rằng gói quà đang chọn có hiển thị giao diện trong game và có thể mua. Nếu không phải trong thời gian hoạt động hoặc đã mua rồi hoặc hết số lần mua, thì khi mua tiếp sẽ mất tiền mà không nhận được vật phẩm.</br>HTGH xin phép từ chối hỗ trợ các trường hợp mua quá số lượng cho phép và không trong thời gian diễn ra hoạt động.</b>";
							}
						}else{
							note = "<br><b style='color:red'>Lưu ý: Nạp gói lần đầu tiên nhận thêm 100% NB, các lần tiếp theo nhận thêm 10% NB.</b>";
						}
						
					}else if(agent=='m003'){
						unit = 'LN';
					}
					html = html +  "<br>Server: " + server + "<br>Gói chọn: " + result['gold'] + unit;
					html = html + "<br>Số dư hiện tại: " + formatter.format(balance);
					html = html + "<br>Số tiền cần thanh toán: " + formatter.format(result['amount']);
					html = html + "<br>Số dư còn lại sau thanh toán: " + formatter.format(parseInt(balance) - parseInt(result['amount']));
					html = html + note;
				}else{
					html = html + '<br>Không tìm thấy thông tin gói';
				}
				$("#myModal p").html(html);
			}, 'json');
			/**/   
        }
    });
	
	$(document.body).delegate('#server_role', 'change', function() {
		var roleName = this.options[this.selectedIndex].text;
		document.getElementById("role_name").value = roleName;
	});
    $("#napgold").validate({
        rules: {
            in_serie: {
                required: true,
                minlength: 6,
                maxlength: 24,
            },
            in_pin: {
                required: true,
                minlength: 6,
                maxlength: 24,
            },
            amount_pay: {
                required: true
            },
        },
        messages: {
            in_serie: {
                required: "Vui lòng nhập serie",
                minlength: "Vui lòng nhập nhiều hơn 6 ký tự",
                maxlength: "Vui lòng nhập ít hơn 24 ký tự"
            },
            in_pin: {
                required: "Vui lòng nhập mã pin",
                minlength: "Vui lòng nhập nhiều hơn 6 ký tự",
                maxlength: "Vui lòng nhập ít hơn 24 ký tự"
            },
            amount_pay: {
                required: "Vui lòng nhập số tiền"
            }
        },
    });
    $(document).on("keypress", $('input[name=in_serie]'), function(e) {
        $('select[name=amount_pay]').val('');
    });
    $(document).on("keypress", $('input[name=in_pin]'), function(e) {
        $('select[name=amount_pay]').val('');
    });
    $("select[name=amount_pay]").on('change', function() {
        $('input[name=in_serie]').val('');
        $('input[name=in_pin]').val('');
    });
    $(document).on('click', '#btnXacnhan', function(e) {
        if (!$('#napgold').valid()) {
            alert('Không được để trống dữ liệu');
        } else {
            if ($(this).attr("data-id") == 'the-atm') {
                $('input[name=in_serie]').val('');
                $('input[name=in_pin]').val('');
                document.getElementById('myModal').style.display = "block"; 
                document.getElementsByClassName("close")[0].onclick = function() {
                    document.getElementById('myModal').style.display = "none";
                }
                $("#myModal #submitbutton").show();
                $(".close").text('Hủy bỏ');
                $("input[name=submit]").val('Nạp ngay');
                var html = "THÔNG TIN NẠP TIỀN VÀO VÍ  <i class='fa fa-star' aria-hidden='true'></i>" +
                    "<br>" + $(".message-item .message-head .user-detail h5").html() +
                    "<br>Tên Game: " + $("#napgold h2").html() +
                    "<br>Số tiên nạp vào: <b>" + formatter.format($('select[name=amount_pay]').val()) + "</b>" +
                    "<br>Phương thức thanh toán: " + $("#napgold .collapse-group .in").attr("data-id");
                $("#myModal p").html(html);
            } else {
                $('select[name=amount_pay]').val('');
                document.getElementById('myModal').style.display = "block";
                document.getElementsByClassName("close")[0].onclick = function() {
                    document.getElementById('myModal').style.display = "none";
                }
                $("#myModal #submitbutton").show();
                $(".close").text('Hủy bỏ');
                $("input[name=submit]").val('Nạp ngay');
                var html = "THÔNG TIN NẠP TIỀN VÀO VÍ  <i class='fa fa-star' aria-hidden='true'></i>" +
                    "<br>" + $(".message-item .message-head .user-detail h5").html() +
                    "<br>Tên Game: " + $("#napgold h2").html() +
                    "<br>Phương thức thanh toán: Thẻ " + $('select[name=in_type] option:selected').text();
                $("#myModal p").html(html);
            }
        }
    });
    $(window).on('beforeunload', function() {
        $(".loading").show();
    });
});