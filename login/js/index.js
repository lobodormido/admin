$('.message a').click(function(){
   $('form').animate({height: "toggle", opacity: "toggle"}, "slow");
});

$("#login").click(function(){
    var user = $("#user").val()
    var pass = $("#pass").val()

    $.ajax({url: "http://localhost:8888/proyectomuni/modernizacion/admin/data/public/login?user="+user+"&pass="+pass, 
    success: function(result){
        if (result['id']){
            window.location.href = "http://localhost:8888/proyectomuni/modernizacion/admin/"
        }
        else{
            alert(result['error'])
        }
    }});
});