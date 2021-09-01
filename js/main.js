"use strict";

var GlobalBasket = new Basket();

var YLocation = new ylocation();

YLocation.start();


function isMobile() {
    if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
          return true; 
    }
    return false;
}


document.addEventListener('DOMContentLoaded', function() {
    GlobalBasket.getGoodsFromCookies();

    var xxx = document.getElementsByClassName('order_goods_form');
    for (var i = 0; i < xxx.length; i++) 
    {
        if (typeof xxx[i].countgoods !== "undefined")
        {
          if( (typeof GlobalBasket.getGoodsCount(xxx[i].idgoods.value) !== "undefined") 
                && (GlobalBasket.getGoodsCount(xxx[i].idgoods.value) > 0) )
          {
              xxx[i].countgoods.value = GlobalBasket.getGoodsCount(xxx[i].idgoods.value);
          }
        }
    }
}, false);

// onunload - при кешировании в системах iOS срабатывает только при перезагрузке страницы,
// но не при переходе на другую !!!
// используем onpagehide
//if ('onpagehide' in window)
//{
//    window.addEventListener('pagehide',function () {
//      GlobalBasket.putGoodsToCookies();
//    }, false);
//} 
//else
//{
//    window.addEventListener('unload',function () {
//      GlobalBasket.putGoodsToCookies();
//    }, false);
//}


window.onscroll = function () {
  var scrolled = window.pageYOffset || document.documentElement.scrollTop;
  var arrowdiv = document.getElementById('uparrowid');
  if (scrolled >= 500) arrowdiv.style.display = 'block';
  if (scrolled < 500) arrowdiv.style.display = 'none';
};
