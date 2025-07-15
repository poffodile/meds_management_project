/*var city = "London";
var offset = "-10";
var d = new Date();
var utc = d.getTime() - (d.getTimezoneOffset() * 60000);
var nd = new Date(utc + (3600000*offset));*/
/*$(function() {

    setInterval( function() {
        var seconds = new Date().getSeconds();
        //var seconds = nd.getSeconds();
        var sdegree = seconds * 6;
        var srotate = "rotate(" + sdegree + "deg)";

        $("#sec").css({"-moz-transform" : srotate, "-webkit-transform" : srotate});
        
    }, 1000 );

    setInterval( function() {   
        var hours = new Date().getHours();
        // var hours = nd.getHours();
        // alert(hours);
        var hours = hours - 8;
        var mins = new Date().getMinutes();
        // var mins = nd.getMinutes();
        var hdegree = hours * 30 + (mins / 2);
        var hrotate = "rotate(" + hdegree + "deg)";

        $("#hour").css({"-moz-transform" : hrotate, "-webkit-transform" : hrotate});
        
    }, 1000 );

    setInterval( function() {
        var mins = new Date().getMinutes();
        // var mins = nd.getMinutes();
        var mins = mins - 30;
        var mdegree = mins * 6;
        var mrotate = "rotate(" + mdegree + "deg)";

        $("#min").css({"-moz-transform" : mrotate, "-webkit-transform" : mrotate});
        
    }, 1000 );

});*/

$(function() {

    setInterval( function() {
        var d = new Date();
        // var seconds = new Date().getSeconds();
        seconds = d.getUTCSeconds();
        var sdegree = seconds * 6;
        var srotate = "rotate(" + sdegree + "deg)";

        $("#sec").css({"-moz-transform" : srotate, "-webkit-transform" : srotate});
        
    }, 1000 );


    setInterval( function() {
        var d     = new Date();
        var hours = d.getUTCHours() + 1;
        // var hours = d.getUTCHours();
        var mins  = d.getUTCMinutes();
        // var hours = new Date().getHours();
        // var mins = new Date().getMinutes();

        var hdegree = hours * 30 + (mins / 2);
        var hrotate = "rotate(" + hdegree + "deg)";

        $("#hour").css({"-moz-transform" : hrotate, "-webkit-transform" : hrotate});
        
    }, 1000 );


    setInterval( function() {
        var d     = new Date();
         var mins  = d.getUTCMinutes();
        // var mins = new Date().getMinutes();
        var mdegree = mins * 6;
        var mrotate = "rotate(" + mdegree + "deg)";

        $("#min").css({"-moz-transform" : mrotate, "-webkit-transform" : mrotate});
        
    }, 1000 );

});