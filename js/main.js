/* ---- elcano Explorer v3.0 - alpha 0.3. ---- */

// global variables
var allowedAccess = false; // indica si el usuario esta autenticado
var allowedPass = '097100109105110'; // contraseña aceptada encriptada

var path = './'; // ruta actual del eplorador
var favorites = []; // almacena las rutas favoritas
var optMoreDespl = false; // estado del desplegable de más opciones
var optViewDespl = false; // estado del desplegable de las vistas

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
        html += '<div class="item item'+settings.view+'" onclick="changePath(\'' + path + '/\')">';
    } else {
        html += '<div class="item item'+settings.view+'" onclick="readFich(\'' + path + '\')">';
    }
    html += '<div class="itemLogo">';
    html += setItemIcon(type);
    html += '</div>';
    html += '<div class="itemText">';
    html += '<p split-lines>'+name+'</p>';
    html += '</div>';
    if (type!='folder') {
        html += '<div class="itemFiletype">';
        html += '<p>'+type+'</p>';
        html += '</div>';
        html += '<div class="itemFilesize">';
        html += '<p>'+size+'</p>';
        html += '</div>';
    }
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

function setItemIcon(type) {
    var image = 'default';
    if (type=='folder') {
        image = '<svg version="1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" enable-background="new 0 0 48 48"><path fill="#FFA000" d="M40,12H22l-4-4H8c-2.2,0-4,1.8-4,4v8h40v-4C44,13.8,42.2,12,40,12z"/><path fill="#FFCA28" d="M40,12H8c-2.2,0-4,1.8-4,4v20c0,2.2,1.8,4,4,4h32c2.2,0,4-1.8,4-4V16C44,13.8,42.2,12,40,12z"/></svg>';
    } else if (type=='zip' || type=='rar' || type=='7z') {
    } else if (type=='txt') {
    } else if (type=='pdf') {
        image = '';
    } else if (type=='html') {
    } else if (type=='css') {
    } else if (type=='sass' || type=='scss') {
    } else if (type=='js') {
    } else if (type=='php') {
    } else if (type=='py') {
    } else if (type=='c' || type=='cpp') {
    } else if (type=='doc' || type=='docx') {
    } else if (type=='xsl' || type=='xslx') {
    } else if (type=='ppt' || type=='pptx') {
    } else if (type=='accbd') {
    } else if (type=='jpg' || type=='png' || type=='gif' || type=='svg' || type=='bmp') {
    } else if (type=='ps') {
    } else if (type=='ai') {
    } else {
        image = 'default';
    }

    //return image;
    return '<svg version="1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" enable-background="new 0 0 48 48"><path fill="#FFA000" d="M40,12H22l-4-4H8c-2.2,0-4,1.8-4,4v8h40v-4C44,13.8,42.2,12,40,12z"/><path fill="#FFCA28" d="M40,12H8c-2.2,0-4,1.8-4,4v20c0,2.2,1.8,4,4,4h32c2.2,0,4-1.8,4-4V16C44,13.8,42.2,12,40,12z"/></svg>';
}

function showTree() {
    console.log('showTree');
    if (settings.tree) {
        $('aside').css('margin-left','-330px');
        settings.tree = false;
        localStorage.setItem('elcano-settings',JSON.stringify(settings));
    } else {
        $('aside').css('margin-left','0px');
        settings.tree = true;
        localStorage.setItem('elcano-settings',JSON.stringify(settings));
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
