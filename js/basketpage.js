"use strict";
function BasketPage(ContactForm, BasketDivName, CostDivName, ConfirmButtonId, CancelButtonId)
{
    this.BasketDivName = BasketDivName;
    this.CostDivName = CostDivName;
    this.ConfirmButton = document.getElementById(ConfirmButtonId);
    this.CancelButton = document.getElementById(CancelButtonId);
    this.ContactForm = ContactForm;

    this.confirmOrder = function()
    {
        if(this.ContactForm === undefined) { return false; }
//        GlobalBasket.putGoodsToCookies();

        document.getElementById(this.BasketDivName).innerHTML = '<img src="' + waitingimage + '">';
        this.showButtons(0);
        window.location.hash = this.BasketDivName;
    //    OffsetY = document.getElementById(this.BasketDivName).offsetTop;
    //    window.scrollTo({top: OffsetY});
        var parameters;
        var message = encodeURIComponent(this.ContactForm.message.value); //encodeURIComponent(this.ContactForm.message.value);
        var fio = encodeURIComponent(this.ContactForm.fio.value);
        var email = this.ContactForm.email.value;
        var phone = this.ContactForm.phone.value;
        parameters = "message="+message+"&fio="+fio+"&email="+email+"&phone="+phone;
        parameters += "&coding=UTF-8";

        sendRequest(
            GlobalBasket.BasketControllerName+"/confirm", 
            "POST", 
            parameters, 
            this.afterConfirm, 
            this );
    };

    this.afterConfirm = function(str, StatusCode, StatusText)
    {
        if( !checkAndAlertStatus(StatusCode, StatusText) ) { return; }
        setTextTo(str, this.BasketDivName);
        GlobalBasket.emptyBasket();
        this.ContactForm.message.value = "";
        this.showButtons();
    };

    this.loadBasket = function()
    {
        document.getElementById(this.BasketDivName).innerHTML = '<img src="' + waitingimage + '">';

        sendRequest(
            GlobalBasket.BasketControllerName+"/form", 
            "POST", 
            "", 
            this.afterLoad, 
            this );
    };

    this.afterLoad = function(str, StatusCode, StatusText)
    {
        if( !checkAndAlertStatus(StatusCode, StatusText) ) { return; }
        setTextTo(str, this.BasketDivName);
        this.showButtons();
        this.getCost();
    };

    this.emptyBasket = function()
    {
        GlobalBasket.emptyBasket();
//        GlobalBasket.putGoodsToCookies();
        this.loadBasket();
    };

    this.changeCounts = function(id_product, inputelem)
    {
        GlobalBasket.setGoods(id_product, inputelem.value ); // parentNode.countgoods.value);
//        GlobalBasket.putGoodsToCookies();
        this.getCost();
    };

    this.removeProduct = function(id_product, elem)
    {
        GlobalBasket.removeGoods(id_product);
//        GlobalBasket.putGoodsToCookies();
        elem.parentNode.parentNode.parentNode.removeChild(elem.parentNode.parentNode);
        this.getCost();
    };

    this.getCost = function ()
    {
        sendRequest(
            GlobalBasket.BasketControllerName+"/get-cost", 
            "POST", 
            "", 
            this.setBasketCost,
            this );	
    };

    this.setBasketCost = function(str, StatusCode, StatusText)
    {
        if( !checkAndAlertStatus(StatusCode, StatusText) ) { return; }
        GlobalBasket.TotalCost = Number(str);
        setTextTo(str, this.CostDivName);
    };

    this.showButtons = function(visibility)
    {
        if( visibility !== undefined )
        {
            if( !visibility )
            {
                this.ConfirmButton.style.visibility = "hidden";
                this.CancelButton.style.visibility = "hidden";
            }
            else
            {
                this.ConfirmButton.style.visibility = "visible";
                this.CancelButton.style.visibility = "visible";
            }
            return;
        }

        if(GlobalBasket.Goods.length) 
        {
            this.CancelButton.style.visibility = "visible";
        }
        else
        {
            this.CancelButton.style.visibility = "hidden";
        }
        if( !this.ContactForm.email.value.length )
        {
            this.ConfirmButton.style.visibility = "hidden";
            return;
        }
        if( this.ContactForm.message.value.length || 
            GlobalBasket.Goods.length )
        {
            this.ConfirmButton.style.visibility = "visible";
            return;
        }

        this.ConfirmButton.style.visibility = "hidden";
    };

}
