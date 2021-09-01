"use strict";

var TopicPath = "/topics/main";

var waitingimage = TopicPath + "/images/icons/waiting-big.gif";

var lastgoodscookies = window.location.host + ".last";
//lastgoodscookies = lastgoodscookies.replace("www.", "");
lastgoodscookies = lastgoodscookies.replace(/\./g, '_');
