/* ---- elcano Explorer v3.0 - alpha 0.3. ---- */

// global variables
var allowedAccess = false; // indica si el usuario esta autenticado
var allowedPass = '097100109105110'; // contraseña aceptada encriptada

var path = './'; // ruta actual del eplorador
var favorites = []; // almacena las rutas favoritas
var optMoreDespl = false; // estado del desplegable de más opciones
var optViewDespl = false; // estado del desplegable de las vistas

if (localStorage.getItem('elcano-settings') == null) {
    var settings = {'sideTree':true,'view':'Mosaic','optMoreDespl':false,'optViewDespl':false};
} else {
    var settings = JSON.parse(localStorage.getItem('elcano-settings'));
}

if (localStorage.getItem('elcano-favorites') == null) {
    localStorage.setItem('elcano-favorites','[]');
    favorites = JSON.parse(localStorage.getItem('elcano-favorites'));
} else {
    favorites = JSON.parse(localStorage.getItem('elcano-favorites'));
}

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


    // options functionalities
    $('#optLaunch').on('click', function() {
		window.open(path,'_blank');
	});

    $('#optDatabase').on('click', function() {
		window.open('http://localhost/phpmyadmin/','_blank');
	});

    $('#optFavorite').on('click', function() {
        if (favorites.length>0) {
            for (x in favorites) {
				if (favorites[x].path == path) {
					favorites.splice(x,1);
					localStorage.setItem('elcano-favorites',JSON.stringify(favorites));
				}
			}
			$('#optFavorite').hide();$('#optNotFavorite').show();
            showFavorites();
        }
    });

    $('#optNotFavorite').on('click', function() {
        var titFav = prompt('Título del favorito: ','');
        favorites.push({'path':path,'title':titFav});
        localStorage.setItem('elcano-favorites',JSON.stringify(favorites));
        console.log(favorites);

        $('#optFavorite').show();$('#optNotFavorite').hide();
        showFavorites();
    });

    $('#optView').on('click', function() {
        if (optViewDespl) {
            $('#optViewDespl').slideUp(200);
            $('#shadow').fadeOut(200);
            optViewDespl = false;
        } else {
            $('#optViewDespl').slideDown(200);
			$('#shadow').fadeIn(200);
			optViewDespl = true;
            $('#optMoreDespl').slideUp(200);
			optMoreDespl = false;
        }
    });

    $('#optMore').on('click', function() {
		if (optMoreDespl) {
			$('#optMoreDespl').slideUp(200);
			$('#shadow').fadeOut(200);
			optMoreDespl = false;
		} else {
			$('#optMoreDespl').slideDown(200);
			$('#shadow').fadeIn(200);
			optMoreDespl = true;
            $('#optViewDespl').slideUp(200);
            optViewDespl = false;
		}
	});

    $('#shadow').on('click', function() {
        $('#optViewDespl').slideUp(200);
        $('#shadow').fadeOut(200);
        optViewDespl = false;
        $('#optMoreDespl').slideUp(200);
        $('#shadow').fadeOut(200);
        optMoreDespl = false;
    })
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
    showFavorites();
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
    explodePath(path); // actualiza los directorios del nav

    var coincidencia = false;
	if (favorites.length>0) {
		for (x in favorites) {
			if (favorites[x].path == path) {
				coincidencia = true;
			}
		}
	}

	if (coincidencia) {
        console.log('coincide');
		$('#optFavorite').show();
		$('#optNotFavorite').hide();
	} else {
        console.log('no coincide');
		$('#optFavorite').hide();
		$('#optNotFavorite').show();
	}

    $.get( "listDirectory2.php", { ruta: path } )
		.done(function( data ) {
            console.log(data);
            var response = JSON.parse(data);

            $('#itemArea').html('');

            var files = 0;
            var directories = 0;

            if (response.dir.length == 0 && response.files.length == 0) {
                $('#itemArea').html('<p>Esta carpeta está vacia</p>');
            }
            for (i in response.dir) {
                setFolderItems(response.dir[i].fileName,response.dir[i].filePath, response.dir[i].fileType);
                directories++;
            }
            for (i in response.files) {
                setFolderItems(response.files[i].fileName,response.files[i].filePath,response.files[i].fileType,response.files[i].fileSize);
                files++;
            }

            $('#folderInfo p').text(directories+' carpetas y '+files+' archivos');
    });
}

function setFolderItems(name,path,type,size) {
    var html = '';
    console.log(type);
    if (type=='folder') {
        html += '<div class="item item'+settings.view+'" onclick="changePath(\'' + path + '/\')">';
    } else {
        html += '<div class="item item'+settings.view+'" onclick="readFich(\'' + path + '\')">';
    }
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
    console.log('changePath to: '+url);
    if (url.substring(0,2) == './' && url.indexOf('../') == -1) {
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

function readFich(url) {
	//console.log('reading ' + url);
	window.open(url,'_blank');
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
                $('nav').append('<div class="navItem" onclick="changePath(\'' + implode + '\')"><p>'+explode[i]+'</p></div>');
            } else {
                $('nav').append('<div class="navSeparator"><p>/</p></div><div class="navItem"><p>'+explode[i]+'</p></div>');
            }
		}
	}
}

function showFavorites() {
	if (favorites.length>0) {
		var favs = '';
		for (x in favorites) {
			favs += '<div class="favFolder" onclick="changePath(\'' + favorites[x].path + '\')"><p>' + favorites[x].title + '<small>' + favorites[x].path + '</small></p></div>';
		}
		$('#asideFavBody').html(favs);
	} else {
		$('#asideFavBody').html('<p style="text-align:center;color:#555">No hay favoritos</p>');
	}
}

function changeView(view) {
    if (view != settings.view && (view=='Mosaic' || view=='List' || view=='Icons')) {
        $('.item').removeClass('itemMosaic');
        $('.item').removeClass('itemList');
        $('.item').removeClass('itemIcons');

        if (view == 'Mosaic') {
            $('.item').addClass('itemMosaic');
            settings.view = 'Mosaic';
            localStorage.setItem('elcano-settings',JSON.stringify(settings));
        } else if (view == 'List') {
            $('.item').addClass('itemList');
            settings.view = 'List';
            localStorage.setItem('elcano-settings',JSON.stringify(settings));
        } else if (view == 'Icons') {
            $('.item').addClass('itemIcons');
            settings.view = 'Icons';
            localStorage.setItem('elcano-settings',JSON.stringify(settings));
        }
        $('#optViewDespl').slideUp(200);
        $('#shadow').fadeOut(200);
        optViewDespl = false;
    }
    console.log(settings);
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
