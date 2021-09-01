"use strict";
function Basket( BasketName ) //contactform)
{
this.Goods=[];
this.packstr="";
this.OrderString = "Корзина";
this.BasketDivId = "basketcounttext";
// this.ContactForm = contactform;
this.BasketControllerName = "/new-basket";
this.MaxAge = 30; // 30 дней, по умолчанию корзина хранится 1 месяц
if( BasketName === undefined )
{
    this.BasketName = window.location.hostname;
    this.BasketName = this.BasketName.replace("www.", "");
    this.BasketName = this.BasketName.replace(new RegExp("[.]", 'g'), "_");
    this.BasketName = this.BasketName+"_basket";
}
else
    this.BasketName = BasketName;

this.TotalCost = 0;

this.getGoodsFromCookies = function ()
{
    this.packstr = getCookie(this.BasketName);
    if(this.packstr !== undefined && this.packstr.length > 0)
    {
        var tmp;
        this.Goods=[];
        tmp = this.packstr.split("|");
        for(var i=0; i<tmp.length; i++)
        {
            this.Goods.push(tmp[i].split("-") );
        }
        document.getElementById(this.BasketDivId).innerHTML = "("+this.Goods.length+")";
    }
    else
    {
        this.Goods=[];
        document.getElementById(this.BasketDivId).innerHTML = "";
    }
};

this.putGoodsToCookies = function ()
{
    this.packstr="";
    if( this.Goods.length )
    {
        for(var i=0; i<this.Goods.length; i++)
        {
            if(i>0) this.packstr += "|";
            this.packstr += this.Goods[i][0]+"-"+this.Goods[i][1];
        }
        // MaxAge - в днях, Date.now() - в милисекундах !!!
        var Expires = new Date(Date.now() + 60*60*24*this.MaxAge*1000); 
        document.cookie = this.BasketName +"="+this.packstr+"; path=/; expires=" + Expires;
    }
    else
    {
        var date = new Date();
        date.setTime(date.getTime() - 1);

        document.cookie = this.BasketName+"=; path=/; expires="+date.toUTCString()+"; ";
    }
};

this.getGoodsNum = function (id_price)
{
    if( this.Goods.length )
    {
        for(var i=0; i<this.Goods.length; i++)
        {
            if(this.Goods[i][0] == id_price) return i;
        }
    }	
    return undefined;
};

this.getGoodsCount = function (id_price)
{
    if( this.Goods.length )
    {
        var i = this.getGoodsNum(id_price);
        if( i !== undefined)
            return this.Goods[i][1];
    }
    return 0;
};

this.setGoods = function (id_price, count)
{
    var k = this.getGoodsNum(id_price);
    if(k!==undefined) this.Goods[k][1] = count;
    else
    {
        k=this.Goods.length;
        this.Goods[k]=[];
        this.Goods[k][0] = id_price; 
        this.Goods[k][1] = count; 
    }
    this.putGoodsToCookies();
    document.getElementById(this.BasketDivId).innerHTML = "("+this.Goods.length+")";
};

this.removeGoods = function (id_price)
{
    if( this.Goods.length )
    {
        var i = this.getGoodsNum(id_price);
        if(i !== undefined)
        {
            this.Goods.splice(i, 1);
            this.putGoodsToCookies();
            if(this.Goods.length) document.getElementById(this.BasketDivId).innerHTML = "("+this.Goods.length+")";
            else document.getElementById(this.BasketDivId).innerHTML = "";
        }
    }
};

this.emptyBasket = function ()
{
    this.Goods = [];
    this.putGoodsToCookies();
    document.getElementById(this.BasketDivId).innerHTML = "";
};

}