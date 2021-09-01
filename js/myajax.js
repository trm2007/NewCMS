"use strict";

/**
 * Устанавливает содержимое innerHTML для div-a с id == DivId
 * 
 * @param {String} Text - текст для установки в div.innerHTML
 * @param {String} DivId - id объекта DIV в текущем DOM
 * 
 * @returns {undefined}
 */
function setTextTo(Text, DivId)
{
    if( Text === undefined || DivId === undefined || !DivId )
    {
        return false;
    }

    var myDiv = document.getElementById(DivId);
    if(!myDiv) { return false; }
    myDiv.innerHTML = Text;
    
    return true;
}

/**
 * Проверяет соответсвие кода ответа сервера на 200,
 * если отличается, то выведет окно с ошибкой
 * 
 * @param {Number} StatusCode - если передан статус и он не 200, то показываеся окно с ошибкой
 * @param {String} StatusText - может содержать текст сообщения при ошибке
 * 
 * @returns {Boolean} - true в случае кода ответа 200, false во всех других
 */
function checkAndAlertStatus(StatusCode, StatusText)
{
    if( "undefined" !== typeof StatusCode && StatusCode !== 200 )
    {
        alert( ("undefined" !== typeof StatusText && StatusText) ? StatusText : "Ошибка при запросе к серверу " );
        return false;
    }
    return true;
}

/**
 * Создает и возвращает объект XHR для запросов к серверу.
 * Если такие объекты не поддерживаются браузером, вернется null
 * 
 * @returns {ActiveXObject|XMLHttpRequest}
 */
function createRequest() 
{
    var request;
    // для современных браузеров
    if (window.XMLHttpRequest) { request = new XMLHttpRequest(); }
    // для старых Internet Explorer
    else if (window.ActiveXObject)
    {
        // пробуем для IE разных версий
        try {request = new ActiveXObject('Msxml2.XMLHTTP');} 
        catch (e1){
            try {request = new ActiveXObject('Microsoft.XMLHTTP');} 
            catch (e2){ return null; }
        }
    }
    return request;
}

/**
 * 
 * @param {String} locationRequest - URL запроса
 * @param {String} mtd - метод запроса, только POST или GET
 * @param {String} parameters - параметры запроса, 
 * если метод GET, то задается как в запросе через &, можно передать пустую строку или null.
 * Если метод POST, то будет передан в теле запроса, 
 * можно передать JSON или данные формы и загрузить файл
 * @param {String} func - функция-callback, 
 * которая будет вызвана в результате удачного асинхронного запроса,
 * в функцию будет передан аргумент Text - строка с ответом
 * @param {Object} context - если передан, 
 * то callback (func) будет вызван через call именнос этим контекстом
 * 
 * @returns {String|Boolean} - в случае асинхронного запроса всегда вернется true,
 * состояние запроса будет отслеживаться через обработчик onreadystatechange,
 * в случае синхронного запроса вернется строка с ответом, 
 * или false при возникновении ошибки 
 */
function sendRequest(locationRequest, mtd, parameters, func, context)
{
    if('undefined' === typeof context || undefined === context)
    {
        context = this;
    }
    // Создаем объект запроса
    var request = createRequest();
    if( !request )
    {
        alert("Браузер не поддерживает технологию AJAX");
        return false;
    }

    // указывает как будет производиться запрос , асинхронно или обычным (синхронным) способом
    var async = true;
    // если в аргументах не указана функция callback-а, 
    // то запрос всегда выполняется синхронно
    // и возвращается строка!
    if( func === undefined || !func ) { async = false; }

    // метод запроса на основе переданного, если не POST или post, то будет GET
    var method = "GET";
    if( undefined !== mtd && 
        mtd.toUpperCase() === "POST" ) { method = "POST"; }

    // Посылаем запрос методом method 
    // Указываем адрес,
    // если GET то с параметрами в адресной строке
    if( method === "GET" && parameters !== undefined && parameters.length ) 
    {
        locationRequest += '?'+parameters;
        parameters = null;
    }
    request.open( method , locationRequest, async );
    // Отправляем дополнительно header, если метод POST
    if(method === "POST" )
    {
        request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    }

    if(async)
    {
        // функция отслеживания состояния запроса при его изменении
        // и вызова функции-callback-а (func из аргументов!!!)
        // если все прошло удачно (status == 200)
        request.onreadystatechange = function ()
        { 
//            var StateString = [];
//            StateString[0] = "начальное состояние";
//            StateString[1] = "вызван open";
//            StateString[2] = "получены заголовки";
//            StateString[3] = "загружается тело";
//            StateString[4] = "запрос завершён";
//            console.log(locationRequest, StateString[this.readyState])

            if (this.readyState != 4) { return; }

            if (this.status == 200)
            {
                func.call(context, 
                    this.responseText ? this.responseText : '', 
                    this.status, 
                    this.statusText ? this.statusText : ''
                );
            }
            else // возможность вызвать другую функцию в случае ошибки
            {
                func.call(context, 
                    this.responseText ? this.responseText : '', 
                    this.status, 
                    this.statusText ? this.statusText : ''
                );
            }
        };
        // Отправляем запрос после установки обработчика асинхронного ответа 
        request.send(parameters);
        return true;
    }
    // отправляется простой синхронный запрос 
    // и выполнение останавливается в ожидании ответа запроса
    request.send(parameters);
    if( request.status != 200 )
    {
        //Если сервер вернул ошибку
        alert("Ошибка получения данных из "+locationRequest+":\n" + (request.status ? request.statusText : 'запрос не удался') );
        return false;
    }

    // Если всё хорошо возвращаем ответ в виде строки
    return request.responseText;
}
