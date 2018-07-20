//提示函数
function webTip(txt,fn,time){
  var webTip=document.getElementById("webTip");
  if(webTip){
    return false;
  }
  var div=document.createElement("div");
  var body=document.getElementsByTagName("body")[0];
  var t;
  if(time){
    t=time;
  }else{
    t=1000;
  }
  div.style.cssText="position:fixed;left:50%;top:100px;padding:0.4rem 0.6rem;background:rgba(0,0,0,0.7);-webkit-transform:translate(-50%,0);font-size:15px;color:#fff;text-align:center;border-radius:0.08rem;z-index:999;-webkit-transition:all 0.3s;";
  div.innerHTML=txt;
  div.id="webTip";
  body.appendChild(div);
  setTimeout(function(){
    body.removeChild(div);
    if(fn) fn();
  },t)
}

//浏览器类型判断
var inBrowser = typeof window !== 'undefined';            
var UA = inBrowser && window.navigator.userAgent.toLowerCase();
var isIE = UA && /msie|trident/.test(UA); //是否ie
var isIE9 = UA && UA.indexOf('msie 9.0') > 0; //是否id9
var isEdge = UA && UA.indexOf('edge/') > 0;   //是否win10内置的浏览器Microsoft Edge
var isAndroid = UA && UA.indexOf('android') > 0;  //是否安卓浏览器
var isIOS = UA && /iphone|ipad|ipod|ios/.test(UA);  //是否IOS浏览器

//判断是否微信浏览器
function is_weixin(){  
  var ua = navigator.userAgent.toLowerCase();  
  if(ua.match(/MicroMessenger/i)=="micromessenger") {  
      return true;  
  } else {  
      return false;  
  }  
} 
function getQueryString(name) { 
  var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
  var url=window.location.search;
  var r = url.substr(1).match(reg); 
  if (r != null) return decodeURI(r[2]); return null; 
}

function lessTime(num,dom){
  if(dom.getAttribute('checked')){
    return false;
  }
  let N = num;
  less()
  let timer = setInterval(function(){
    less()
  },1000) 
  function less(){
    if(N>1){
      N--;
      dom.innerHTML = '等待'+N+'s';
      dom.setAttribute('checked',true);
    }else{
      dom.innerHTML = '发送验证码';
      dom.removeAttribute('checked');
      clearInterval(timer)
    }
  }
}