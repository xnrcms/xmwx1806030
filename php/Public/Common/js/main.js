$(".popup_close").click(function(){
    $(".popup_1").hide();
});
$(".popup_btn").click(function(){
    $(".popup_1").hide();
});

// 到顶部
$(".gotop").click(function(){
	document.documentElement.scrollTop = document.body.scrollTop =0;
});
$(window).scroll( function() {
	if ($(window).scrollTop() > $(window).height()){
		$(".gotop").show();
	}else{
		$(".gotop").hide();
	}
});

// 弹窗
function popup_tips(text){
	$("body").append('<div class="popup_tips"><p>' + text + '</p></div>');
	setTimeout(" $('.popup_tips').hide();", 2000);
}

function popup_pic(text){
	$("body").append('<div class="popup_pic"><p>' + text + '</p></div>');
	setTimeout(" $('.popup_pic').hide();", 1500);
}

function popup_wait(text){
	$("body").append('<div class="popup_wait"><p>' + text + '</p></div>');
	setTimeout(" $('.popup_wait').hide();", 1500);
}

// 排序
$(".reorder .item").click(function(){
	var _this = $(this);
	if(_this.hasClass("active")){
		if(_this.hasClass("on")){
			_this.removeClass("on");
		}else{
			_this.addClass("on");
		}
	}else{
		_this.addClass("active").addClass("on").siblings().removeClass("active").removeClass("on");
	}
});
