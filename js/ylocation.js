"use strict";

function ylocation(DivName)
{

if( undefined === DivName ) { DivName = "YLocation"; }
this.Country = null;
this.Province = null;
this.Area = null;
this.Locality = null;

this.CurrentLocation = null;

this.start = function()
{
    var context = this;
    ymaps.ready( function() {

        // определение местоположения пользователя
        ymaps.geolocation.get({
            // определение геолокации на основе ip пользователя
            provider: 'yandex',
            // автоматическое геокодирование результата
            autoReverseGeocode: true
        })
        .then(function (result) {
            // Выведем результат геокодирования в div с id == YLocation
            var Arr = result.geoObjects.get(0)
                .properties.get('metaDataProperty')
                .GeocoderMetaData.Address.Components;
            Arr.forEach( function(Item){
                if( Item.kind === "country" ) { context.Country = Item.name; }
                if( Item.kind === "province" ) { context.Province = Item.name; }
                if( Item.kind === "area" ) { context.Area = Item.name; }
                if( Item.kind === "locality" ) { context.Locality = Item.name; }
            });
            if(context.Locality) { context.CurrentLocation = context.Locality; }
            else if(context.Area) { context.CurrentLocation = context.Area; }
            else if(context.Province) { context.CurrentLocation = context.Province; }
            else if(context.Country) { context.CurrentLocation = context.Country; }
            else { return false; }

            var Div = document.getElementById(DivName);
            if( !Div ) { return false; }

            Div.innerHTML = context.CurrentLocation;
            return true;
        });
    });
};

}