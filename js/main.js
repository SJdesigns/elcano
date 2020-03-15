/* ---- elcano Explorer v3.0 - alpha 1.2. ---- */

// global variables
var allowedAccess = false; // indica si el usuario esta autenticado
var allowedPass = '097100109105110'; // contraseña aceptada encriptada

var path = './'; // ruta actual del eplorador
var favorites = []; // almacena las rutas favoritas
var optMoreDespl = false; // estado del desplegable de más opciones
var optViewDespl = false; // estado del desplegable de las vistas
var timeline = [];

if (localStorage.getItem('elcano-settings') == null) {
    var settings = {'tree':true,'view':'Mosaic','optMoreDespl':false,'optViewDespl':false};
    localStorage.setItem('elcano-settings',JSON.stringify(settings));
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
    if (sessionStorage.getItem('elcano-access') != null) {
        storedAuth = JSON.parse(sessionStorage.getItem('elcano-access'));
        let storedUser = storedAuth.user;
        let storedPass = storedAuth.pass;
        authorize(storedUser,storedPass);
    } else {
        disableExplorer();
    }

    $('#signInForm').on('submit',function(e) { // evento al rellenar el formulario de login
        e.preventDefault();
        let user = $('#signInUser').val();
        let pass = $('#signInPass').val();
        authorize(user,sha1(pass));
    });

    $('#asideTreeBody').html('<div class="spinner"><div class="dot1"></div><div class="dot2"></div></div>');

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

function authorize(authUser,authPass) {
    // función que actúa tanto al rellenar el formulario de login como al cargar por primera vez la página si la contraseña está guardada en sessionStorage
    // la variable authPass contiene la contraseña a comprobar ya encriptada para enviar a PHP
    console.log('authorize');

    var tokenCheck = Math.random();

    $.get( "auth.php", { user: authUser,pass: authPass,token: tokenCheck } )
		.done(function( data ) {
            //console.log(data);
            var response = JSON.parse(data);

            if (response.token == tokenCheck) {
                if (response.status == 200) {
                    accessDataJson = JSON.stringify({'user':authUser,'pass':authPass});
                    sessionStorage.setItem('elcano-access',accessDataJson);
                    enableExplorer();
                } else {
                    disableExplorer();
                }
            } else {
                disableExplorer();
            }
    });
}

function logout() { // cierra la sesión iniciada volviendo a mostrar el formulario de login
    sessionStorage.removeItem('elcano-access');
    disableExplorer();
    $('#optMoreDespl').slideUp(200);
    $('#shadow').fadeOut(200);
    optMoreDespl = false;
}

function enableExplorer() {
    console.log('explorer enabled');
    allowedAccess = true;
    changePath('./');
    showFavorites();
    loadTree();
    $('.screen').hide();
    $('#explorer').fadeIn(200);
}

function disableExplorer() {
    console.log('explorer disabled');
    allowedAccess = false;
    $('.screen').hide();
    $('#blocked').show();
    $('#signInUser,#signInPass').val('');
    $('#signInUser').focus();
}

function getFolder(path) { // recupera los ficheros y directorios existentes en la ruta que se le introduzca como parámetro
    console.log('getFolder(\''+path+'\')');
    explodePath(path);

    var coincidencia = false;
	if (favorites.length>0) {
		for (x in favorites) {
			if (favorites[x].path == path) {
				coincidencia = true;
			}
		}
	}

	if (coincidencia) {
		$('#optFavorite').show();
		$('#optNotFavorite').hide();
	} else {
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
                $('#itemArea').html('<div id="emptyFolder"><p>Esta carpeta está vacia</p></div>');
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
        if (name.substring(0,1) == '.') {
            html += '<div class="item item'+settings.view+' itemHidden" onclick="changePath(\'' + path + '/\')">';
        } else {
            html += '<div class="item item'+settings.view+'" onclick="changePath(\'' + path + '/\')">';
        }
    } else {
        html += '<div class="item item'+settings.view+'" onclick="readFich(\'' + path + '\')">';
    }
    html += '<div class="itemLogo">';
    html += setItemIcon(type,path);
    html += '</div>';
    html += '<div class="itemText">';
    html += '<p split-lines>'+name+'</p>';
    html += '</div>';
    html += '<div class="itemFiletype">';
    if (type!='folder') {
        html += '<p>'+type+'</p>';
    }
    html += '</div>';
    html += '<div class="itemFilesize">';
    if (type!='folder') {
        html += '<p>'+size+'</p>';
    }
    html += '</div>';
    html += '</div>';
    $('#itemArea').append(html);
}

function changePath(url) { // cambia la ruta actual
    console.log('changePath to: '+url);
    if (url.substring(0,2) == './' && url.indexOf('../') == -1) {
    	getFolder(url);
    	document.title = 'elcano ' + url;
        if (timeline.length>0) {
            if (timeline[timeline.length-1].path != url) {
                timeline.push({'path':url});
            }
        } else {
            timeline.push({'path':url});
        }
    } else {
        console.log('forbiden access to path '+url);
    }
}

function readFich(url) {
	//console.log('reading ' + url);
	window.open(url,'_blank');
}

function explodePath(url) { // actualiza los directorios del nav
	var explode = url.split('/');
	$('nav').html('');

	for (i=0;i<explode.length;i++) {
		if (explode[i]=='') {
			explode.splice(i,1);
		} else {
			if (explode[i]=='.') {
				explode[i] = 'paginas';
			}

            var implode = ''; // permite asignar la url a cada item del nav
            for (j=0;j<=i;j++) {
                if (explode[j] == 'paginas') {
                    implode += './';
                } else {
                    implode += explode[j] + '/';
                }
            }

            if (i != explode.length-1 && i != explode.length-2) {
                $('nav').append('<div class="navItem" onclick="changePath(\'' + implode + '\')"><p>'+explode[i]+'</p></div>');
                if (i != explode.length -1) {
                    $('nav').append('<div class="navSeparator"><p>/</p></div>');
                }
            } else if (explode[i] != '') {
                $('nav').append('<div class="navItem"><p>'+explode[i]+'</p></div>');
            }
		}
	}
}

function showFavorites() { // recarga la lista de favoritos en el aside
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

function changeView(view) { // permite cambiar el layout de elementos en el directorio
    if (view != settings.view && (view=='Mosaic' || view=='List' || view=='Wall')) {
        $('.item').removeClass('itemMosaic');
        $('.item').removeClass('itemList');
        $('.item').removeClass('itemWall');

        if (view == 'Mosaic') {
            $('.item').addClass('itemMosaic');
            settings.view = 'Mosaic';
            localStorage.setItem('elcano-settings',JSON.stringify(settings));
        } else if (view == 'List') {
            $('.item').addClass('itemList');
            settings.view = 'List';
            localStorage.setItem('elcano-settings',JSON.stringify(settings));
        } else if (view == 'Wall') {
            $('.item').addClass('itemWall');
            settings.view = 'Wall';
            localStorage.setItem('elcano-settings',JSON.stringify(settings));
        }
        $('#optViewDespl').slideUp(200);
        $('#shadow').fadeOut(200);
        optViewDespl = false;
    }
    console.log(settings);
}

function setItemIcon(type,path) {
    var standardSvgIcon = false; // indica si utiliza el icono svg estándar para un tipo concreto
    var paper='';var bend='';var text='';var size='36px';
    var image = 'default';
    if (type=='folder') { // folder
        image = '<svg version="1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" enable-background="new 0 0 48 48"><path fill="#FFA000" d="M40,12H22l-4-4H8c-2.2,0-4,1.8-4,4v8h40v-4C44,13.8,42.2,12,40,12z"/><path fill="#FFCA28" d="M40,12H8c-2.2,0-4,1.8-4,4v20c0,2.2,1.8,4,4,4h32c2.2,0,4-1.8,4-4V16C44,13.8,42.2,12,40,12z"/></svg>';
    } else if (type=='zip' || type=='rar' || type=='7z') {
        image ='<svg height="24" version="1.1" width="24" xmlns="http://www.w3.org/2000/svg" xmlns:cc="http://creativecommons.org/ns#" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"><g transform="translate(0 -1028.4)"><g transform="translate(-72,66)"><path d="m76 965.36c-1.105 0-2 0.9-2 2v8 2 1 6c0 1.11 0.895 2 2 2h2 12 2c1.105 0 2-0.89 2-2v-6-1-2-4-4c0-1.1-0.895-2-2-2h-2-2-2-8-2z" fill="#e67e22"/><path d="m76-64c-1.105 0-2 0.895-2 2v8 2 1 6c0 1.105 0.895 2 2 2h2 12 2c1.105 0 2-0.895 2-2v-6-1-2-4-4c0-1.105-0.895-2-2-2h-2-2-2-8-2z" fill="#f39c12" transform="translate(0 1028.4)"/><path d="m76-65c-1.105 0-2 0.895-2 2v8 2 1 6c0 1.105 0.895 2 2 2h2 12 2c1.105 0 2-0.895 2-2v-6-1-2-4-4c0-1.105-0.895-2-2-2h-2-2-2-8-2z" fill="#e67e22" transform="translate(0 1028.4)"/><path d="m76-66c-1.105 0-2 0.895-2 2v8 2 1 6c0 1.105 0.895 2 2 2h2 12 2c1.105 0 2-0.895 2-2v-6-1-2-4-4c0-1.105-0.895-2-2-2h-2-2-2-8-2z" fill="#f1c40f" transform="translate(0 1028.4)"/></g><path d="m17 1040.4c-1.105 0-2 0.9-2 2v4c0 1.1 0.895 2 2 2s2-0.9 2-2v-4c0-1.1-0.895-2-2-2zm0 1c0.552 0 1 0.4 1 1 0 0.5-0.448 1-1 1s-1-0.5-1-1c0-0.6 0.448-1 1-1zm0 3c0.552 0 1 0.4 1 1v1c0 0.5-0.448 1-1 1s-1-0.5-1-1v-1c0-0.6 0.448-1 1-1z" fill="#f39c12"/><g transform="translate(5)"><path d="m10 1028.4v10c0 1.1 0.895 2 2 2s2-0.9 2-2v-10h-4z" fill="#34495e"/><path d="m12 1028.4v1h1v-1h-1zm0 1h-1v1h1v-1zm0 1v1h1v-1h-1zm0 1h-1v1h1v-1zm0 1v1h1v-1h-1zm0 1h-1v1h1v-1zm0 1v1h1v-1h-1zm0 1h-1v1h1v-1zm0 1v1h1v-1h-1zm0 1h-1v1h1v-1zm0 1v1c0.552 0 1-0.5 1-1h-1z" fill="#95a5a6"/><path d="m11 1028.4v1h1v-1h-1zm0 2v1h1v-1h-1zm0 2v1h1v-1h-1zm0 2v1h1v-1h-1zm0 2v1h1v-1h-1zm0 2c0 0.5 0.448 1 1 1v-1h-1z" fill="#ecf0f1"/></g><path d="m17 1039.4c-1.105 0-2 0.9-2 2v4c0 1.1 0.895 2 2 2s2-0.9 2-2v-4c0-1.1-0.895-2-2-2zm0 1c0.552 0 1 0.4 1 1 0 0.5-0.448 1-1 1s-1-0.5-1-1c0-0.6 0.448-1 1-1zm0 3c0.552 0 1 0.4 1 1v1c0 0.5-0.448 1-1 1s-1-0.5-1-1v-1c0-0.6 0.448-1 1-1z" fill="#ecf0f1"/></g></svg>';
    } else if (type=='exe') {
    } else if (type=='bat') {
        standardSvgIcon = true;
        paper='#424242';bend='#6d6d6d';text='BAT';
    } else if (type=='txt') {
        standardSvgIcon = true;
        paper='#607d8b';bend='#8eacbb';text='TXT';
    } else if (type=='pdf') { // PDF
        standardSvgIcon = true;
        paper='#f00';bend='#ff5252';text='PDF';
    } else if (type=='html' || type=='htm') { // HTML
        standardSvgIcon = true;
        paper='#f44336';bend='#ff7961';text='HTML';
    } else if (type=='css') { // CSS
        standardSvgIcon = true;
        paper='#1e88e5';bend='#6ab7ff';text='CSS';
    } else if (type=='sass' || type=='scss') { // SASS
        standardSvgIcon = true;
        paper='#ec407a';bend='#ff77a9';text='SASS';
    } else if (type=='js') { // JS
        standardSvgIcon = true;
        paper='#fdd835';bend='#ffff6b';text='JS';size='42px';
    } else if (type=='php') { // PHP
        standardSvgIcon = true;
        paper='#6a1b9a';bend='#9c4dcc';text='PHP';
    } else if (type=='py') {
        standardSvgIcon = true;
        paper='#1e88e5';bend='#fdd835';text='PY';size='36px';
    } else if (type=='c' || type=='cpp' || type=='cs') {
        standardSvgIcon = true;
        paper='#2196f3';bend='#6ec6ff';text='C';
    } else if (type=='doc' || type=='docx' || type=='odt') {
        standardSvgIcon = true;
        paper='#1565c0';bend='#5e92f3';text='W';var size='50';
    } else if (type=='xls' || type=='xlsx' || type=='ods') {
        standardSvgIcon = true;
        paper='#2e7d32';bend='#60ad5e';text='X';var size='50';
    } else if (type=='ppt' || type=='pptx' || type=='odp') {
        standardSvgIcon = true;
        paper='#d84315';bend='#ff7543';text='P';var size='50';
    } else if (type=='accdb') {
        standardSvgIcon = true;
        paper='#c62828';bend='#ff5f52';text='A';var size='50';
    } else if (type=='jpg' || type=='jpeg' || type=='png' || type=='gif' || type=='svg' || type=='bmp' || type=='ico') {
        image = '<img src="'+path+'" />';
    } else if (type=='mp3' || type=='wav' || type=='ogg' || type=='wma' || type=='cda' || type=='mid' || type=='midi' || type=='ac3' || type=='ogm' || type=='aac' || type=='flac') { // audio types
        image = '<svg style="fill:#039be5" xmlns="http://www.w3.org/2000/svg" height="34" viewBox="0 0 24 24" width="34"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12 5v8.55c-.94-.54-2.1-.75-3.33-.32-1.34.48-2.37 1.67-2.61 3.07-.46 2.74 1.86 5.08 4.59 4.65 1.96-.31 3.35-2.11 3.35-4.1V7h2c1.1 0 2-.9 2-2s-.9-2-2-2h-2c-1.1 0-2 .9-2 2z"/></svg>';
    } else if (type=='mp4' || type=='avi' || type=='wmv' || type=='rpm' || type=='mov' || type=='dvd' || type=='div' || type=='divx' || type=='asf' || type=='wm') { // video types
        image ='<svg style="fill:#039be5" xmlns="http://www.w3.org/2000/svg" height="34" viewBox="0 0 24 24" width="34"><path d="M17 10.5V7c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h12c.55 0 1-.45 1-1v-3.5l2.29 2.29c.63.63 1.71.18 1.71-.71V8.91c0-.89-1.08-1.34-1.71-.71L17 10.5z"/></svg>';
    } else if (type=='psd') {
        standardSvgIcon = true;
        paper='#0d47a1';bend='#5472d3';text='PS';var size='45';
    } else if (type=='ai') {
        standardSvgIcon = true;
        paper='#ff6f00';bend='#ffa040';text='AI';var size='45';
    } else {
        standardSvgIcon = true;
        paper='#eceff1';bend='#e0e0e0';text='';
    }

    if (standardSvgIcon) {
        image = '<svg id="fileicon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 86 120"><title>FileIcon</title><g id="paper"><path d="M106,42v74a8,8,0,0,1-8,8H28a8,8,0,0,1-8-8V12a8,8,0,0,1,8-8H68L78,14V32H96Z" transform="translate(-20 -4)" style="fill:'+paper+'"/></g><text id="text" transform="translate(42 80.3)" style="text-anchor:middle;font-size:'+size+';fill:#fff;font-family:Calibri;cursor:default">'+text+'</text><polygon id="bend" points="86 38 48 38 48 0 86 38" style="fill:'+bend+'"/></svg>';
    }

    return image;
    //return '<svg version="1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" enable-background="new 0 0 48 48"><path fill="#FFA000" d="M40,12H22l-4-4H8c-2.2,0-4,1.8-4,4v8h40v-4C44,13.8,42.2,12,40,12z"/><path fill="#FFCA28" d="M40,12H8c-2.2,0-4,1.8-4,4v20c0,2.2,1.8,4,4,4h32c2.2,0,4-1.8,4-4V16C44,13.8,42.2,12,40,12z"/></svg>';
}

function showTree() { // muestra u oculta el árbol de directorios lateral
    console.log('showTree');
    if (settings.tree) {
        $('aside').css('margin-left','-320px');
        settings.tree = false;
        localStorage.setItem('elcano-settings',JSON.stringify(settings));
    } else {
        $('aside').css('margin-left','0px');
        settings.tree = true;
        localStorage.setItem('elcano-settings',JSON.stringify(settings));
    }
    $('#optMoreDespl').slideUp(200);
    $('#shadow').fadeOut(200);
    optMoreDespl = false;
}

function loadTree() { // carga los datos del árbol de directorios
    $.get( "directoryTree2.php", { ruta: path } )
		.done(function( data ) {
			$('#asideTreeBody').html(data);
			console.log("tree loaded");
	});
}

function showSettings() { // muestra u oculta la ventana de configuración
    console.log('showSettings');
}

function prevPath() {
    console.log('prevPath');
    if (timeline.length>1) {
        var url = timeline[timeline.length-2].path;
        timeline.splice(timeline.length-1,1);
        changePath(url);
    }
    $('#optMoreDespl').slideUp(200);
    $('#shadow').fadeOut(200);
    optMoreDespl = false;
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

function sha1 (str) {
  var hash
  try {
    var crypto = require('crypto')
    var sha1sum = crypto.createHash('sha1')
    sha1sum.update(str)
    hash = sha1sum.digest('hex')
  } catch (e) {
    hash = undefined
  }

  if (hash !== undefined) {
    return hash
  }

  var _rotLeft = function (n, s) {
    var t4 = (n << s) | (n >>> (32 - s))
    return t4
  }

  var _cvtHex = function (val) {
    var str = ''
    var i
    var v

    for (i = 7; i >= 0; i--) {
      v = (val >>> (i * 4)) & 0x0f
      str += v.toString(16)
    }
    return str
  }

  var blockstart
  var i, j
  var W = new Array(80)
  var H0 = 0x67452301
  var H1 = 0xEFCDAB89
  var H2 = 0x98BADCFE
  var H3 = 0x10325476
  var H4 = 0xC3D2E1F0
  var A, B, C, D, E
  var temp

  // utf8_encode
  str = unescape(encodeURIComponent(str))
  var strLen = str.length

  var wordArray = []
  for (i = 0; i < strLen - 3; i += 4) {
    j = str.charCodeAt(i) << 24 |
      str.charCodeAt(i + 1) << 16 |
      str.charCodeAt(i + 2) << 8 |
      str.charCodeAt(i + 3)
    wordArray.push(j)
  }

  switch (strLen % 4) {
    case 0:
      i = 0x080000000
      break
    case 1:
      i = str.charCodeAt(strLen - 1) << 24 | 0x0800000
      break
    case 2:
      i = str.charCodeAt(strLen - 2) << 24 | str.charCodeAt(strLen - 1) << 16 | 0x08000
      break
    case 3:
      i = str.charCodeAt(strLen - 3) << 24 |
        str.charCodeAt(strLen - 2) << 16 |
        str.charCodeAt(strLen - 1) <<
      8 | 0x80
      break
  }

  wordArray.push(i)

  while ((wordArray.length % 16) !== 14) {
    wordArray.push(0)
  }

  wordArray.push(strLen >>> 29)
  wordArray.push((strLen << 3) & 0x0ffffffff)

  for (blockstart = 0; blockstart < wordArray.length; blockstart += 16) {
    for (i = 0; i < 16; i++) {
      W[i] = wordArray[blockstart + i]
    }
    for (i = 16; i <= 79; i++) {
      W[i] = _rotLeft(W[i - 3] ^ W[i - 8] ^ W[i - 14] ^ W[i - 16], 1)
    }

    A = H0
    B = H1
    C = H2
    D = H3
    E = H4

    for (i = 0; i <= 19; i++) {
      temp = (_rotLeft(A, 5) + ((B & C) | (~B & D)) + E + W[i] + 0x5A827999) & 0x0ffffffff
      E = D
      D = C
      C = _rotLeft(B, 30)
      B = A
      A = temp
    }

    for (i = 20; i <= 39; i++) {
      temp = (_rotLeft(A, 5) + (B ^ C ^ D) + E + W[i] + 0x6ED9EBA1) & 0x0ffffffff
      E = D
      D = C
      C = _rotLeft(B, 30)
      B = A
      A = temp
    }

    for (i = 40; i <= 59; i++) {
      temp = (_rotLeft(A, 5) + ((B & C) | (B & D) | (C & D)) + E + W[i] + 0x8F1BBCDC) & 0x0ffffffff
      E = D
      D = C
      C = _rotLeft(B, 30)
      B = A
      A = temp
    }

    for (i = 60; i <= 79; i++) {
      temp = (_rotLeft(A, 5) + (B ^ C ^ D) + E + W[i] + 0xCA62C1D6) & 0x0ffffffff
      E = D
      D = C
      C = _rotLeft(B, 30)
      B = A
      A = temp
    }

    H0 = (H0 + A) & 0x0ffffffff
    H1 = (H1 + B) & 0x0ffffffff
    H2 = (H2 + C) & 0x0ffffffff
    H3 = (H3 + D) & 0x0ffffffff
    H4 = (H4 + E) & 0x0ffffffff
  }

  temp = _cvtHex(H0) + _cvtHex(H1) + _cvtHex(H2) + _cvtHex(H3) + _cvtHex(H4)
  return temp.toLowerCase()
}
