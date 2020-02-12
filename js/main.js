/* ---- elcano Explorer v3.0 - alpha 0.3. ---- */


// global variables
var allowedAccess = false; // indica si el usuario esta autenticado
var allowedPass = '097100109105110';


$(function() { // init
    if (sessionStorage.getItem('elcano-access') === allowedPass) {
        enableExplorer();
    } else {
        disableExplorer();
    }

    $('#signInForm').on('submit',function(e) {
        console.log('submit: ' + $('#signInUser').val() + ' ' + $('#signInPass').val());
        e.preventDefault();
        authorize();
    });

    checkAccess();



});

function authorize() {
    console.log('authorize');

    let user = $('#signInUser').val();
    let pass = $('#signInPass').val();

    if (user == 'root' && pass == 'admin') {
        sessionStorage.setItem('elcano-access',passEncoder(pass));
        enableExplorer();
    }
}

function enableExplorer() {
    console.log('explorer enabled');
    allowedAccess = true;
    $('.screen').hide();
    $('#explorer').show();
}

function disableExplorer() {
    console.log('explorer disabled');
    allowedAccess = false;
    $('.screen').hide();
    $('#blocked').show();
    $('#signInUser,#signInPass').val('');
    $('#signInUser').focus();
}

function checkAccess() {
    var accessInterval = setInterval(function() {
        if (sessionStorage.getItem('elcano-access') != allowedPass && allowedAccess) {
            disableExplorer();
        }
    },5000);
}


/* ---- app utils ---- */


function passEncoder(pass) { // encode the sign in password
    let hash = '';
    for (i=0;i<pass.length;i++) {
        var passCode = ''+pass.charCodeAt(i);
        if (passCode.length == 1) {
            passCode = '00' + passCode;
        } else if (passCode.length == 2) {
            passCode = '0' + passCode;
        }
        hash += passCode;
    }

    return hash;
}

function getCookie(cname) {
    var name = cname + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');

    for(var i = 0; i <ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return false;
}
