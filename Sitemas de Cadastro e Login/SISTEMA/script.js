function Login(){
    const login = $('#login').val();
    const senha = $('#senha').val();

    $.ajax({
        url: "login.php",
        type: "POST",
        data: {login, senha},
        dataType: "html",

        success: function(resposta) {
            if(resposta==1){
                window.location.href = "../index.html";
            }else if(resposta == 0){
                document.querySelector('p').innerText = "ERRADO";
            }
        }, 
    })
}