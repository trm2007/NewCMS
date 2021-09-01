"use strict";

function BigImage(bigfile, idimage)
{
    var newimage = document.getElementById(idimage);
    newimage.style.visibility = 'visible';
}
function NoBigImage(idimage)
{
    var newimage = document.getElementById(idimage);
    newimage.style.visibility = 'hidden';
}


function closemenu(menudiv) {
  document.getElementById(menudiv).classList.toggle('visible_menu');
}
//вызывается при нажатии кнопки разворачивания подгруппы в дереве ( + )
function resize(resizer, ClassOpen, ClassClose)
{
    switchClass(resizer, ClassOpen, ClassClose);
//    resizer.classList.toggle(ClassOpen);
//    resizer.classList.toggle(ClassClose);
    var firstul = resizer.parentNode.querySelector('ul');

    if(!firstul) return false;
    
    var CompStyle = firstul.currentStyle || getComputedStyle(firstul);

    if( firstul.style.height !== '0' && firstul.style.height !== '0px' && CompStyle.height !== '0' && CompStyle.height !== '0px' )
    {
        firstul.style.height = '0';
    }
    else
    {
        firstul.style.height = 'auto';
    }
}


function addClass(el, className)
{
    if( className === undefined ) { return; }
    if( el.className.indexOf(className) === -1)
    {
        if( !el.className.length )
        {
            el.className = className;
        }
        else
        {
            el.className += " " + className;
        }
    }
}

function removeClass(el, className)
{
    if( className === undefined ) { return; }
    el.className = el.className.replace(" " + className, "");
    el.className = el.className.replace(className, "");
}

function haveClass(el, className)
{
    if( className === undefined ) { return false; }
    if( el.className.indexOf(className) === -1 ) { return false; }
    return true;
}

function switchClass(el, className1, className2)
{
    if( haveClass( el, className1 ) )
    {
        removeClass(el, className1);
        addClass(el, className2);
    }
    else
    {
        removeClass(el, className2);
        addClass(el, className1);
    }
}