
//Ejecutando funciones
document.getElementById("btn__iniciar-sesion").addEventListener("click", iniciarSesion);//Ejecutando el btn de register ejecuta la funcion de mover la caja login
document.getElementById("btn__registrarse").addEventListener("click", register);//Ejecutando el btn de register ejecuta la funcion de mover la caja registrar
window.addEventListener("resize", anchoPage); // ESTA FUNCION SE EJECUTA MEDIANTE ACHICAS O AGRANDAS LA PAGINA

//Declarando variables
//Declarando VARIABLE DEL FORMILARIO__LOGIN DEL HTML CON QUERYSELECTOR
var formulario_login = document.querySelector(".formulario__login");
//Declarando VARIABLE DEL FORMILARIO__REGISTER DEL HTML CON QUERYSELECTOR
var formulario_register = document.querySelector(".formulario__register");
//Declarando VARIABLE DEL FORMILARIO__LOGIN-REGISTER DEL HTML CON QUERYSELECTOR
var contenedor_login_register = document.querySelector(".contenedor__login-register");
// Y ASI CON LAS DEMAS
var caja_trasera_login = document.querySelector(".caja__trasera-login");
var caja_trasera_register = document.querySelector(".caja__trasera-register");

    //FUNCIONES
     // AGREGAMOS ESTILOS PARA OCULTAR CON JS PARA NO AGREGAR NI SACAR ESTILOS
// FUNCION PARA QUE CUANDO LA VENTANA TENGA INNERWIDTH MAYOR A 850 
function anchoPage(){

    if (window.innerWidth > 850){
        caja_trasera_register.style.display = "block";// QUE SE MUESTRE QUE SE MUESTRE LA CAJA DE FONDO TRANSPARENTE
        caja_trasera_login.style.display = "block";// QUE SE MUESTRE QUE SE MUESTRE LA CAJA DE FONDO TRANSPARENTE
    }else{
        caja_trasera_register.style.display = "block";// QUE SE MUESTRE LA CAJA DE FONDO TRANSPARENTE
        caja_trasera_register.style.opacity = "1";// 
        caja_trasera_login.style.display = "none";//
        formulario_login.style.display = "block";//
        contenedor_login_register.style.left = "0px";//
        formulario_register.style.display = "none"; //
    }
}

anchoPage();
 // AGREGAMOS ESTILOS PARA OCULTAR CON JS PARA NO AGREGAR NI SACAR ESTILOS
 // FUNCION DE MOVER INICIAR SESION
    function iniciarSesion(){
        if (window.innerWidth > 850){ // SI EL INNERWIDTH ES MAYOR A 850 EJECUTAR LO DE ABAJO 
            formulario_login.style.display = "block";
            contenedor_login_register.style.left = "10px";
            formulario_register.style.display = "none";
            caja_trasera_register.style.opacity = "1"; // ACA ES AL REVES DEL REGISTRARSE
            caja_trasera_login.style.opacity = "0"; //ACA ES AL REVES DEL REGISTRARSE
        }else{ //PARA HACERLO RESPONSIVE QUE SI ES MENOR A ESOS PIXELES SE QUEDE ACOMODADO
            formulario_login.style.display = "block";
            contenedor_login_register.style.left = "0px";
            formulario_register.style.display = "none";
            caja_trasera_register.style.display = "block"; 
            caja_trasera_login.style.display = "none";
        }
    }

//FUNCION DE REGISTRARSE
    function register(){
        if (window.innerWidth > 850){ // SI EL INNERWIDTH ES MAYOR A 850 EJECUTAR LO DE ABAJO 
            formulario_register.style.display = "block"; // LE PUSIMOS UN ESTILO CON UN DISPLAY BLOCK EN JS
            contenedor_login_register.style.left = "410px";// MUEVE EL BLOCKER REGISTER 410 PX PARA HACER EL EFECTO
            formulario_login.style.display = "none";// "NONE" PARA NO MOSTRAR EL LOGIN Y MOSTRAR EL REGISTER
            caja_trasera_register.style.opacity = "0";// "0" PARA TAPAR LA CAJA TRASERA
            caja_trasera_login.style.opacity = "1";//"1" PARA MOSTRAR LA CAJA DE EL LOGIN
        }else{ //PARA HACERLO RESPONSIVE QUE SI ES MENOR A ESOS PIXELES SE QUEDE ACOMODADO
            formulario_register.style.display = "block"; // PARA QUE SE VEA 
            contenedor_login_register.style.left = "0px"; //
            formulario_login.style.display = "none"; //
            caja_trasera_register.style.display = "none";//
            caja_trasera_login.style.display = "block"; // PARA QUE SE MUESTRE LA CAJA TRACERA DEL LOGIN
            caja_trasera_login.style.opacity = "1";//
        }
}