/* ---- elcano Explorer v3.0 - alpha 0.3. ---- */


// global variables
var allowedAccess = false; // indica si el usuario esta autenticado
var allowedPass = '097100109105110'; // contraseña aceptada encriptada

if (localStorage.getItem('elcano-settings') == null) {
    var settings = {'aside':true,'view':'Mosaic'};
} else {
    var settings = JSON.parse(localStorage.getItem('elcano-settings'));
}

var path = './';


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
    changePath('./');
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

function getFolder(path) {
    // introducir funcion descomponer ruta en el nav

    $.get( "listDirectory2.php", { ruta: path } )
		.done(function( data ) {
            console.log(data);
            var response = JSON.parse(data);

            explodePath(path); // actualiza los directorios del nav
            $('#itemArea').html('');

            for (i in response.dir) {
                setFolderItems(response.dir[i].fileName,response.dir[i].filePath);
            }
            for (i in response.files) {
                setFolderItems(response.files[i].fileName,response.files[i].filePath,response.files[i].fileType,response.files[i].fileSize);
            }
    });
}

function setFolderItems(name,path,type,size) {
    var html = '';
    html += '<div class="item item'+settings.view+'">';
    html += '<div class="itemLogo">';
    html += '<img src="img/typePDF.png" />';
    html += '</div>';
    html += '<div class="itemText">';
    html += '<p split-lines>'+name+'</p>';
    html += '</div>';
    html += '</div>';
    $('#itemArea').append(html);
}

function changePath(url) { // cambia la ruta actual

    if (url.substring(0,2) == './' && url.indexOf('../') == -1) {
    	//console.log('list --- ' + url);
    	getFolder(url);
    	document.title = 'elcano ' + url;
    	/*if (historyPaths.length>=10) {
    		historyPaths.splice(0,1);
    	}
    	historyPaths.push(url);*/
    	path = url;
    } else {
        console.log('forbiden access to path '+url);
    }
}

function explodePath(url) {
	var explode = url.split('/');
	$('nav').html('');

	for (i=0;i<explode.length;i++) {
		if (explode[i]=='') {
			explode.splice(i,1);
		} else {
			if (explode[i]=='.') {
				explode[i] = 'paginas';
			}
			var implode = '';
			for (j=0;j<=i;j++) {
				if (explode[j] == 'paginas') {
					implode += './';
				} else {
					implode += explode[j] + '/';
				}

			}
            if ($('nav').html() == '') {
                $('nav').append('<div class="navItem"><p>'+explode[i]+'</p></div>');
            } else {
                $('nav').append('<div class="navSeparator"><p>/</p></div><div class="navItem"><p>'+explode[i]+'</p></div>');
            }
			//$('#pathFolders').append('<div class="pathFolder" onclick="changePath(\'' + implode + '\')">' + explode[i] + '</div>');
		}
	}
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
