/* ---- elcano Explorer v3.0 - beta 2.4 ---- */

// global variables
var version = '3.2.4';
var allowedAccess = false; // indica si el usuario esta autenticado
var path = './'; // ruta actual del explorador
var favorites = []; // almacena las rutas favoritas
var optMoreDespl = false; // estado del desplegable de más opciones
var optViewDespl = false; // estado del desplegable de las vistas
var timeline = []; // almacena todas las rutas accedidas anteriormente
var posSelect = null; // posición del fichero o directorio seleccionado con las flechas de dirección
var defaultDirectoryIndex = ['index.php','index.asp','index.html'];
var ignoreFiles = [];
var currentPathLaunch = false; // almacena el fichero ejecutable prioritario en el directorio actual (modificado por la funcion setLaunchOptions)
var timelinePosition = 0; // es true cuando el directorio actual ha sido accedido volviendo atrás en el historial
var defaultSettings = {'version':version,'tree':true,'view':'Mosaic','darkMode':false,'showHidden':false,'showExtensions':true,'defaultView':'last','debug':true,'ignoreFiles':ignoreFiles,'systemIndex':true,'directoryIndex':defaultDirectoryIndex,'dbpath':'http://localhost/phpmyadmin/','firstLoad':false}; // configuracion por defecto de la aplicacion
var lang = getCookie('elcano-lang');

if (localStorage.getItem('elcano-settings') == null) {
    var settings = defaultSettings;
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
settings.firstLoad = true;

$(function() { // init
    if (sessionStorage.getItem('elcano-access') != null) {
        storedAuth = JSON.parse(sessionStorage.getItem('elcano-access'));
        let storedUser = storedAuth.user;
        let storedPass = storedAuth.pass;
        authorize(storedUser,storedPass);
    } else {
        disableExplorer();
    }

    availableLanguages();

    $('#signInForm').on('submit',function(e) { // evento al rellenar el formulario de login
        e.preventDefault();
        let user = $('#signInUser').val();
        let pass = $('#signInPass').val();
        authorize(user,sha1(pass));
    });

    $('#loginLangSelect').val(lang);

    $('#loginLangSelect').on('change',function() {
        console.log('entra');
        if (langs[$('#loginLangSelect').val()] != null) {
            changeLang($('#loginLangSelect').val());
        }
    });

    // aside tree loading circle
    $('#asideTreeBody').html('<div class="spinner"><div class="dot1"></div><div class="dot2"></div></div>');

    // options functionalities
    $('#optLaunch').on('click', function() {
        if (settings.systemIndex) {
            window.open(path,'_blank');
        } else {
            window.open(currentPathLaunch,'_blank');
        }
	});

    $('#optDatabase').on('click', function() {
		window.open(settings.dbpath,'_blank');
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
        addFavorite();
    });

    $('.optDropDownItem').removeClass('optViewDesplActive');
    if (settings.view == 'Mosaic') {
        $('#optViewDesplMosaic').addClass('optViewDesplActive');
    } else if (settings.view == 'List') {
        $('#optViewDesplList').addClass('optViewDesplActive');
    } else if (settings.view == 'Wall') {
        $('#optViewDesplWall').addClass('optViewDesplActive');
    }

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

    if (settings.tree) { // set startup state of the aside tree
        $('#optMoreDesplExplorer p').html(langs[lang].header.headMoreHideExpl+'<small>alt+x</small>');
    } else {
        $('aside').css('margin-left','-320px');
        $('#optMoreDesplExplorer p').html(langs[lang].header.headMoreShowExpl+'<small>alt+x</small>');
    }

    $('#shadow').on('click', function() {
        $('#optViewDespl').slideUp(200);
        $('#shadow').fadeOut(200);
        optViewDespl = false;
        $('#optMoreDespl').slideUp(200);
        $('#shadow').fadeOut(200);
        optMoreDespl = false;
    })

    $('#optMoreHistory').on('click',function(e) {
        if (e.target.id == 'optMorePrevPath' || e.target.parentNode.id == 'optMorePrevPath' || e.target.parentNode.parentNode.id == 'optMorePrevPath') {
            prevPath();
        } else if (e.target.id == 'optMoreNextPath' || e.target.parentNode.id == 'optMoreNextPath' || e.target.parentNode.parentNode.id == 'optMoreNextPath') {
            nextPath();
        } else if (e.target.id == 'optMoreHistory' || e.target.parentNode.id == 'optMoreHistory' || e.target.parentNode.parentNode.id == 'optMoreHistory') {
            showHistory(true);
        }
    });
});

function authorize(authUser,authPass) {
    // función que actúa tanto al rellenar el formulario de login como al cargar por primera vez la página si la contraseña está guardada en sessionStorage
    // la variable authPass contiene la contraseña a comprobar ya encriptada para enviar a PHP
    if (settings.debug){console.log('authorize')};

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
                    $('#signInError').html('<p>'+response.message+'</p>');
                }
            } else {
                disableExplorer();
            }
    });
}

function logout() { // cierra la sesión iniciada volviendo a mostrar el formulario de login
    if (settings.debug){console.log('logout')};
    sessionStorage.removeItem('elcano-access');
    disableExplorer();
    $('#optMoreDespl').slideUp(200);
    $('#shadow').fadeOut(200);
    optMoreDespl = false;
}

function enableExplorer() {
    if (settings.debug){console.log('explorer enabled')};
    allowedAccess = true;
    changePath('./');
    showFavorites();
    loadTree();
    setSettings();
    $('.screen').hide();
    $('#explorer').fadeIn(200);
}

function disableExplorer() {
    if (settings.debug){console.log('explorer disabled')};
    allowedAccess = false;
    $('.screen').hide();
    $('#blocked').show();
    $('#signInUser,#signInPass').val('');
    $('#signInError').html('');
    $('#signInUser').focus();
}

function getFolder(url) { // recupera los ficheros y directorios existentes en la ruta que se le introduzca como parámetro
    if (allowedAccess) {
        if (settings.debug){console.log('getFolder(\''+url+'\')')};

        var coincidencia = false;
    	if (favorites.length>0) {
    		for (x in favorites) {
    			if (favorites[x].path == url) {
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

        $.post( "listDirectory.php", { ruta: url, listDir: true } )
    		.done(function( data ) {
                if (settings.debug){console.log(data)};
                var response = JSON.parse(data);
                if (settings.debug){console.log(response)};

                if (response.error != null) {
                    if (settings.debug){console.log('Se ha producido un error al acceder al directorio')};
                    errorReporting(response);
                } else {
                    path = url; // cambiamos la ruta general al confirmar la existencia del directorio
                    explodePath(url);
                    $('#itemArea').html('');

                    var files = 0;
                    var directories = 0;

                    if (response.dir.length == 0 && response.files.length == 0) {
                        $('#itemArea').html('<div id="emptyFolder"><p>'+langs[lang].section.noResults+'</p></div>');
                    }
                    for (i in response.dir) {
                        if (settings.ignoreFiles.indexOf(response.dir[i].fileName) == -1) {
                            if (settings.showHidden) { // muestra los ficheros y directorios ocultos
                                setFolderItems(response.dir[i].fileName,response.dir[i].filePath, response.dir[i].fileType);
                                directories++;
                            } else { // solo muestra los directorios y ficheros que son visibles
                                if (response.dir[i].fileName.substring(0,1) != '.') {
                                    setFolderItems(response.dir[i].fileName,response.dir[i].filePath, response.dir[i].fileType);
                                    directories++;
                                }
                            }
                        }
                    }
                    for (i in response.files) {
                        if (!ignoreThisFile(path, response.files[i].fileName, filename)) {
                            if (settings.ignoreFiles.indexOf(response.files[i].fileName) == -1) {
                                if (settings.showExtensions) {
                                    setFolderItems(response.files[i].fileName,response.files[i].filePath,response.files[i].fileType,response.files[i].fileSize,true);
                                } else {
                                    setFolderItems(response.files[i].fileName,response.files[i].filePath,response.files[i].fileType,response.files[i].fileSize,false);
                                }
                                files++;
                            }
                        }
                    }

                    setLaunchOptions(response);

                    $('#folderInfo p').text(directories+' '+langs[lang].section.sectionFolder+', '+files+' '+langs[lang].section.sectionFiles);
                }
                settings.firstLoad = false;
        });
    }
}

function setFolderItems(name,path,type,size,extensions=true) {
    if (!extensions) {
        var extensionStart = name.lastIndexOf('.');
        var nameNoExt = name.substring(0,extensionStart);
    }

    if (settings.firstLoad) {
        if (settings.defaultView=='mosaic') { // detect default view
            settings.view = 'Mosaic';
        } else if (settings.defaultView=='list') {
            settings.view = 'List';
        } else if (settings.defaultView=='wall') {
            settings.view = 'Wall';
        }
    }

    var html = '';
    if (settings.debug){console.log(type)};
    if (type=='folder') {
        if (name.substring(0,1) == '.') {
            html += '<div class="item item'+settings.view+' itemDir itemHidden" onclick="changePath(\'' + path + '/\')">';
        } else {
            html += '<div class="item item'+settings.view+' itemDir" onclick="changePath(\'' + path + '/\')">';
        }
    } else {
        html += '<div class="item item'+settings.view+' itemFich" onclick="readFich(\'' + path + '\')">';
    }
    html += '<div class="itemLogo">';
    html += setItemIcon(type,path);
    html += '</div>';
    html += '<div class="itemText">';
    if (extensions) {
        html += '<p split-lines>'+name+'</p>';
    } else {
        html += '<p split-lines>'+nameNoExt+'</p>';
    }
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

function changePath(url,tlPos = null) { // cambia la ruta actual. tlPos indica que se llama desde la funcion navigateHistory
    if (settings.debug){console.log('changePath to: '+url+' | tlPos = '+tlPos)};
    if (url.substring(0,2) == './' && url.indexOf('../') == -1) {
    	getFolder(url);
    	document.title = 'elcano ' + url;
        if (tlPos == null) {
            if (timeline.length>0) {
                if (timeline[timeline.length-1].path != url) {
                    timeline.push({'path':url});
                }
            } else {
                timeline.push({'path':url});
            }
            reloadTimeline();
        } else {
            reloadTimeline(tlPos);
        }
    } else {
        if (settings.debug){console.log('forbiden access to path '+url)};
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
				//explode[i] = '<div class="navHomeItem"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="black" width="24px" height="24px"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M10 19v-5h4v5c0 .55.45 1 1 1h3c.55 0 1-.45 1-1v-7h1.7c.46 0 .68-.57.33-.87L12.67 3.6c-.38-.34-.96-.34-1.34 0l-8.36 7.53c-.34.3-.13.87.33.87H5v7c0 .55.45 1 1 1h3c.55 0 1-.45 1-1z"/></svg><i>Home</i></div>';
                explode[i] = 'homePage';
			}

            var implode = ''; // permite asignar la url a cada item del nav
            for (j=0;j<=i;j++) {
                //if (explode[j] == '<div class="navHomeItem"><svg class="navHomeItem" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="black" width="24px" height="24px"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M10 19v-5h4v5c0 .55.45 1 1 1h3c.55 0 1-.45 1-1v-7h1.7c.46 0 .68-.57.33-.87L12.67 3.6c-.38-.34-.96-.34-1.34 0l-8.36 7.53c-.34.3-.13.87.33.87H5v7c0 .55.45 1 1 1h3c.55 0 1-.45 1-1z"/></svg><i>home</i></div>') {

                if (explode[j] == 'homePage') {
                    implode += './';
                } else {
                    implode += explode[j] + '/';
                }
            }

            if (i != explode.length-1 && i != explode.length-2) {
                if (explode[i] == 'homePage') {
                    $('nav').append('<div class="navItem" onclick="changePath(\'' + implode + '\')"><p><div class="navHomeItem"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="black" width="24px" height="24px"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M10 19v-5h4v5c0 .55.45 1 1 1h3c.55 0 1-.45 1-1v-7h1.7c.46 0 .68-.57.33-.87L12.67 3.6c-.38-.34-.96-.34-1.34 0l-8.36 7.53c-.34.3-.13.87.33.87H5v7c0 .55.45 1 1 1h3c.55 0 1-.45 1-1z"/></svg></div></p></div>');
                } else {
                    $('nav').append('<div class="navItem" onclick="changePath(\'' + implode + '\')"><p>'+explode[i]+'</p></div>');
                }
                if (i != explode.length -1) {
                    $('nav').append('<div class="navSeparator"><p>/</p></div>');
                }
            } else if (explode[i] != '') {
                if (explode[i] == 'homePage') {
                    $('nav').append('<div class="navItem"><p><div class="navHomeItem"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="black" width="24px" height="24px"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M10 19v-5h4v5c0 .55.45 1 1 1h3c.55 0 1-.45 1-1v-7h1.7c.46 0 .68-.57.33-.87L12.67 3.6c-.38-.34-.96-.34-1.34 0l-8.36 7.53c-.34.3-.13.87.33.87H5v7c0 .55.45 1 1 1h3c.55 0 1-.45 1-1z"/></svg><i>Home</i></div></p></div>');
                } else {
                    $('nav').append('<div class="navItem"><p>'+explode[i]+'</p></div>');
                }
            }
		}
	}
}

function showFavorites() { // recarga la lista de favoritos en el aside
    var favCount = 0;
	if (favorites.length>0) {
		var favs = '';
		for (x in favorites) {
            var filePath = location.pathname;
            if (favorites[x].root == filePath) {
    			favs += '<div class="favFolder" onclick="changePath(\'' + favorites[x].path + '\')"><p>' + favorites[x].title + '<small>' + favorites[x].path + '</small></p></div>';
                favCount++;
            }
		}
        $('#favCount').text(favCount);
		$('#asideFavBody').html(favs);
	} else {
        $('#favCount').text('');
		$('#asideFavBody').html('<p style="text-align:center;color:#555">No hay favoritos</p>');
	}
}

function addFavorite(current=null) {
    console.log('addFavorite');
    var titFav = prompt('Título del favorito: ','');
    if (titFav!=null && titFav!='') {
        if (settings.debug){console.log(path)};
        var filePath = location.pathname;
        if (current==null) {
            favorites.push({'root':filePath,'path':path,'title':titFav});
        } else {
            favorites.push({'root':filePath,'path':current,'title':titFav});
        }
        localStorage.setItem('elcano-favorites',JSON.stringify(favorites));
        if (settings.debug){console.log(favorites)};
        if (current==null) { // cambia el icono de favorito de la barra superior solo si es en el directorio actual
            $('#optFavorite').show();$('#optNotFavorite').hide();
        }
        showFavorites();
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
            $('.optDropDownItem').removeClass('optViewDesplActive');
            $('#optViewDesplMosaic').addClass('optViewDesplActive');
        } else if (view == 'List') {
            $('.item').addClass('itemList');
            settings.view = 'List';
            localStorage.setItem('elcano-settings',JSON.stringify(settings));
            $('.optDropDownItem').removeClass('optViewDesplActive');
            $('#optViewDesplList').addClass('optViewDesplActive');
        } else if (view == 'Wall') {
            $('.item').addClass('itemWall');
            settings.view = 'Wall';
            localStorage.setItem('elcano-settings',JSON.stringify(settings));
            $('.optDropDownItem').removeClass('optViewDesplActive');
            $('#optViewDesplWall').addClass('optViewDesplActive');
        }
        $('#optViewDespl').slideUp(200);
        $('#shadow').fadeOut(200);
        optViewDespl = false;
    }
}

function setItemIcon(type,path) {
    var standardSvgIcon = false; // indica si utiliza el icono svg estándar para un tipo concreto
    var paper='';var bend='';var text='';var size='36px';
    var image = 'default';
    if (type=='folder') { // folder
        image = '<svg class="fileicon" version="1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" enable-background="new 0 0 48 48"><path fill="#FFA000" d="M40,12H22l-4-4H8c-2.2,0-4,1.8-4,4v8h40v-4C44,13.8,42.2,12,40,12z"/><path fill="#FFCA28" d="M40,12H8c-2.2,0-4,1.8-4,4v20c0,2.2,1.8,4,4,4h32c2.2,0,4-1.8,4-4V16C44,13.8,42.2,12,40,12z"/></svg>';
    } else if (type=='zip' || type=='rar' || type=='7z') {
        image = '<svg class="fileicon" id="Capa_1" data-name="Capa 1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 384 384"><title>rar</title><image width="512" height="512" transform="scale(0.75)" xlink:href="data:image/png;base64,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"/></svg>';
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
    } else if (type=='asp') {
        standardSvgIcon = true;
        paper='#6cbeff';bend='#a1d9fe';text='ASP';
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
        image = '<svg class="fileicon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 86 120"><title>'+text+'</title><g id="paper"><path d="M106,42v74a8,8,0,0,1-8,8H28a8,8,0,0,1-8-8V12a8,8,0,0,1,8-8H68L78,14V32H96Z" transform="translate(-20 -4)" style="fill:'+paper+'"/></g><text id="text" transform="translate(42 80.3)" style="text-anchor:middle;font-size:'+size+';fill:#fff;font-family:Calibri;cursor:default">'+text+'</text><polygon id="bend" points="86 38 48 38 48 0 86 38" style="fill:'+bend+'"/></svg>';
    }

    return image;
    //return '<svg version="1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" enable-background="new 0 0 48 48"><path fill="#FFA000" d="M40,12H22l-4-4H8c-2.2,0-4,1.8-4,4v8h40v-4C44,13.8,42.2,12,40,12z"/><path fill="#FFCA28" d="M40,12H8c-2.2,0-4,1.8-4,4v20c0,2.2,1.8,4,4,4h32c2.2,0,4-1.8,4-4V16C44,13.8,42.2,12,40,12z"/></svg>';
}

function showTree() { // muestra u oculta el árbol de directorios lateral
    if (settings.debug){console.log('showTree')};
    if (settings.tree) {
        $('aside').css('margin-left','-320px');
        settings.tree = false;
        $('#optMoreDesplExplorer p').html(langs[lang].header.headMoreShowExpl+'<small>alt+x</small>');
        localStorage.setItem('elcano-settings',JSON.stringify(settings));
    } else {
        $('aside').css('margin-left','0px');
        settings.tree = true;
        $('#optMoreDesplExplorer p').html(langs[lang].header.headMoreHideExpl+'<small>alt+x</small>');
        localStorage.setItem('elcano-settings',JSON.stringify(settings));
    }
    $('#optMoreDespl').slideUp(200);
    $('#shadow').fadeOut(200);
    optMoreDespl = false;
}

function loadTree() { // carga los datos del árbol de directorios
    if (allowedAccess) {
        $.post( "directoryTree.php", { ruta: path, dirTree: true } )
    		.done(function( data ) {
    			$('#asideTreeBody').html(data);
    			if (settings.debug){console.log("tree loaded")};
    	});
    }
}

function ignoreThisFile(path,file,filename) {
    if (path == './') { // ruta base
        if (filename == file) { // son el mismo archivo
            return true;
        }
    }
    return false;
}

function showSettings(op) { // muestra u oculta la ventana de configuración
    if (settings.debug){console.log('showSettings')};
    if (op) {
        $('#dialogBack').css('display','flex');
        $('.dialog').hide();
        $('#settings').fadeIn(200);
    } else {
        $('#settings').fadeOut(200);
        $('#dialogBack').css('display','none');
    }

    $('#optMoreDespl').slideUp(200);
    $('#shadow').fadeOut(200);
    optMoreDespl = false;
}

function prevPath() {
    if (settings.debug){console.log('prevPath')};

    if (timeline.length>1) {
        if (timeline[timelinePosition-1] != undefined) {
            timelinePosition -= 1;
            var url = timeline[timelinePosition].path;
            changePath(url,timelinePosition);
        }
    }

    $('#optMoreDespl').slideUp(200);
    $('#shadow').fadeOut(200);
    optMoreDespl = false;
}

function nextPath() {
    if (settings.debug){console.log('nextPath')};

    if (timelinePosition < timeline.length-1) {
        if (timeline[timelinePosition+1] != undefined) {
            timelinePosition += 1;
            var url = timeline[timelinePosition].path;
            changePath(url,timelinePosition);
        }

        $('#optMoreDespl').slideUp(200);
        $('#shadow').fadeOut(200);
        optMoreDespl = false;
    }
}

function showHistory(op) {
    if (settings.debug){console.log('showHistory')};
    if (op) {
        $('#dialogBack').css('display','flex');
        $('.dialog').hide();
        $('#history').fadeIn(200);
    } else {
        $('#history').fadeOut(200);
        $('#dialogBack').css('display','none');
    }

    $('#optMoreDespl').slideUp(200);
    $('#shadow').fadeOut(200);
    optMoreDespl = false;
}

function reloadTimeline(pos = null) {
    if (pos==null) {
        timelinePosition = timeline.length-1;
    } else {
        timelinePosition = pos;
    }

    if (timelinePosition == timeline.length-1) { // detectamos si la opcion nextPath debe estar disponible
        $('#optMoreNextPath').addClass('optMoreDisabled');
    } else {
        $('#optMoreNextPath').removeClass('optMoreDisabled');
    }

    var html = '';
    for (i in timeline) {
        var folderName = timeline[i].path.split('/');
        if (folderName.length==2) {
            folderName2 = langs[lang].history.historyHome;
        } else {
            folderName2 = folderName[folderName.length-2];
        }
        if (timelinePosition == i) {
            html += '<div class="historyItem historyActive">';
        } else {
            html += '<div class="historyItem">';
        }
        html += '<p>'+folderName2+'</p>';
        html += '<p>'+timeline[i].path+'</p>';
        if (timelinePosition > i) {
            html += '<svg onclick="navigateHistory('+i+')" xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M12.5 8c-2.65 0-5.05.99-6.9 2.6L3.71 8.71C3.08 8.08 2 8.52 2 9.41V15c0 .55.45 1 1 1h5.59c.89 0 1.34-1.08.71-1.71l-1.91-1.91c1.39-1.16 3.16-1.88 5.12-1.88 3.16 0 5.89 1.84 7.19 4.5.27.56.91.84 1.5.64.71-.23 1.07-1.04.75-1.72C20.23 10.42 16.65 8 12.5 8z"/></svg>';
        } else if (timelinePosition < i) {
            html += '<svg onclick="navigateHistory('+i+')" xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 0 24 24" width="24"><path d="M0 0h24v24H0z" fill="none"/><path d="M18.4 10.6C16.55 8.99 14.15 8 11.5 8c-4.65 0-8.58 3.03-9.96 7.22L3.9 16c1.05-3.19 4.05-5.5 7.6-5.5 1.95 0 3.73.72 5.12 1.88L13 16h9V7l-3.6 3.6z"/></svg>';
        } else {
            html += '';
        }

        html += '</div>';
    }
    $('#historyPaths').html(html);
}

function navigateHistory(pos) {
    if (settings.debug){console.log('navigateHistory to: '+pos)};
    timelinePosition = pos;
    changePath(timeline[pos].path,pos);
}

function errorReporting(info) { // Muestra los mensajes de error por pantalla
    if (settings.debug){console.log('errorReporting')};

    var errorItemId = Math.floor(Math.random()*10000);

    var errorMsg = '<div class="errorItem" id="errorItem'+errorItemId+'">';
    errorMsg += '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="black" width="24px" height="24px"><path d="M0 0h24v24H0V0z" fill="none"/><path d="M11 15h2v2h-2zm0-8h2v6h-2zm.99-5C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8z"/></svg>';
    errorMsg += '<p>'+info.error.message+'</p>';
    errorMsg += '</div>';

    $('#errorReporting').append(errorMsg);

    setTimeout(function() {
        $('#errorItem'+errorItemId).remove();
    },5000);
}

function setLaunchOptions(data) { // indica el fichero ejecutable prioritario del directorio
    if (settings.debug){console.log(settings.directoryIndex)};
    var priority = settings.directoryIndex;
    var priorityFound = false;
    var launchPath = '';

    for (i in priority) {
        for (j in data.files) {
            if (!priorityFound) {
                if (data.files[j].fileName==priority[i]) {
                    launchPath = data.files[j].filePath;
                    priorityFound = true;
                }
            }
        }
    }

    if (priorityFound) {
        $('#optLaunch').show();
        currentPathLaunch = launchPath;
    } else {
        $('#optLaunch').hide();
        currentPathLaunch = false;
    }
}

function setSettings() {
    if (settings.debug){console.log('setSettings')};

    if (settings.version != version) {
        upgradeSettings();
    }

    // dark mode
    if (settings.darkMode) {
        $('body').removeClass('lightMode');
        $('body').addClass('darkMode');
        $('#darkModeCheckbox').prop('checked', true);
    } else {
        $('body').removeClass('darkMode');
        $('body').addClass('lightMode');
        $('#darkModeCheckbox').prop('checked', false);
    }

    $('#darkModeCheckbox').on('change',function() {
        if (settings.darkMode) {
            console.log('set light mode');
            $('body').removeClass('darkMode');
            $('body').addClass('lightMode');
            settings.darkMode = false;
        } else {
            console.log('set dark mode');
            $('body').removeClass('lightMode');
            $('body').addClass('darkMode');
            settings.darkMode = true;
        }
        localStorage.setItem('elcano-settings',JSON.stringify(settings));
    });

    // show hidden files
    if (settings.showHidden) {
        $('#showHiddenCheckbox').prop('checked', true);
    } else {
        $('#showHiddenCheckbox').prop('checked', false);
    }

    $('#showHiddenCheckbox').on('change',function() {
        if (settings.showHidden) {
            settings.showHidden = false;
        } else {
            settings.showHidden = true;
        }
        changePath(path);
        localStorage.setItem('elcano-settings',JSON.stringify(settings));
    });

    // show extensions
    if (settings.showExtensions) {
        $('#showExtensionCheckbox').prop('checked',true);
    } else {
        $('#showExtensionCheckbox').prop('checked',false);
    }

    $('#showExtensionCheckbox').on('change',function() {
        if (settings.showExtensions) {
            settings.showExtensions = false;
        } else {
            settings.showExtensions = true;
        }
        console.log(settings.showExtensions);
        changePath(path);
        localStorage.setItem('elcano-settings',JSON.stringify(settings));
    });

    // set default view
    if (settings.defaultView=='mosaic') {
        $('#defaultViewMosaic').prop('checked',true);
    } else if (settings.defaultView=='list') {
        $('#defaultViewList').prop('checked',true);
    } else if (settings.defaultView=='wall') {
        $('#defaultViewWall').prop('checked',true);
    } else if (settings.defaultView=='last') {
        $('#defaultViewLast').prop('checked',true);
    }

    $('.defaultView').on('change', function() {
        if ($(this).attr('id') == 'defaultViewMosaic') {
            settings.defaultView = 'mosaic';
        } else if ($(this).attr('id') == 'defaultViewList') {
            settings.defaultView = 'list';
        } else if ($(this).attr('id') == 'defaultViewWall') {
            settings.defaultView = 'wall';
        } else {
            settings.defaultView = 'last';
        }
        localStorage.setItem('elcano-settings',JSON.stringify(settings));
    });

    // ignore files
    $('#ignoreFilesInput').val((settings.ignoreFiles).join(','));

    $('#ignoreFilesInput').on('change',function() {
        console.log('ignoreFiles change');
        settings.ignoreFiles = ($('#ignoreFilesInput').val()).split(',');
        localStorage.setItem('elcano-settings',JSON.stringify(settings));
        getFolder(path);
        console.log(settings.ignoreFiles);
    });

    // index priority
    if (settings.systemIndex) {
        $('#systemIndexPriority').attr('checked',true);
        $('#indexPriorityInput').attr('disabled',true);
    }

    $('#indexPriorityInput').val((settings.directoryIndex).join(','));

    $('#systemIndexPriority').on('change',function() {
        if (settings.systemIndex) {
            settings.systemIndex = false;
            $('#systemIndexPriority').attr('checked',false);
            $('#indexPriorityInput').attr('disabled',false);
        } else {
            settings.systemIndex = true;
            $('#systemIndexPriority').attr('checked',true);
            $('#indexPriorityInput').attr('disabled',true);
        }
        localStorage.setItem('elcano-settings',JSON.stringify(settings));
    });

    $('#indexPriorityInput').on('change',function() {
        console.log('indexPriority change');
        settings.directoryIndex = ($('#indexPriorityInput').val()).split(',');
        localStorage.setItem('elcano-settings',JSON.stringify(settings));
        getFolder(path);
        console.log(settings.directoryIndex);
    });

    // database
    $('#databasePathInput').val(settings.dbpath);

    $('#databasePathInput').on('change',function() {
        settings.dbpath = $('#databasePathInput').val();
        localStorage.setItem('elcano-settings',JSON.stringify(settings));
    });

    // lang
    if (langs[lang] != null) {
        $('#settingsSelectLang').val(lang);
    }

    $('#settingsSelectLang').on('change',function() {
        console.log('entra');
        if (langs[$('#settingsSelectLang').val()] != null) {
            changeLang($('#settingsSelectLang').val());
        }
    });

    // reset
    $('#resetSettingsButton').on('click',function() {
        console.log('reset settings');
        settings = defaultSettings;
        localStorage.setItem('elcano-settings',JSON.stringify(settings));
        setSettings();
        changePath(path);
    });

    $('#settingsVersion').text('beta '+version);
}

function upgradeSettings() { // actualiza los datos del localStorage si el usuario tiene una versión diferente
    console.log('upgradeSettings');

    var newSettings = {};

    if (settings.version != version) {
        newSettings.version = version;


        if (settings.tree==true) { newSettings.tree = true; }
        else if (settings.tree==false) { newSettings.tree = false; }
        else { newSettings.tree = true; }

        if (settings.view == 'Mosaic' || settings.view == 'List' || settings.view == 'Wall') {
            newSettings.view = settings.view;
        } else {
            newSettings.view = 'Mosaic';
        }

        if (settings.darkMode == true) { newSettings.darkMode = true; }
        else if (settings.darkMode == false) { newSettings.darkMode = false; }
        else { newSettings.darkMode = false; }

        if (settings.showHidden == true) { newSettings.showHidden = true; }
        else if (settings.showHidden == false) { newSettings.showHidden = false; }
        else { newSettings.showHidden = false; }

        if (settings.showExtensions == true) { newSettings.showExtensions = true; }
        else if (settings.showExtensions == false) { newSettings.showExtensions = false; }
        else { newSettings.showExtensions = false; }

        if (settings.defaultView=='mosaic' || settings.defaultView=='list' || settings.defaultView=='wall' || settings.defaultView=='last') {
            newSettings.defaultView = settings.defaultView;
        } else {
            newSettings.defaultView = 'last';
        }

        if (settings.debug==true || settings.debug==false) { newSettings.debug = settings.debug; } else { newSettings.debug = false; }

        if (Array.isArray(settings.ignoreFiles)) {
            newSettings.ignoreFiles = settings.ignoreFiles;
        } else {
            newSettings.ignoreFiles = ignoreFiles;
        }

        if (settings.systemIndex=='true' || settings.systemIndex=='false') {
            newSettings.systemIndex = settings.systemIndex;
        } else {
            newSettings.systemIndex = true;
        }

        if (Array.isArray(settings.directoryIndex)) {
            newSettings.directoryIndex = settings.directoryIndex;
        } else {
            newSettings.directoryIndex = defaultDirectoryIndex;
        }

        if (!settings.dbpath) {
            newSettings.dbpath = '';
        } else {
            newSettings.dbpath = defaultSettings.dbpath;
        }

        if (settings.firstLoad == true || settings.firstLoad == false) {
            newSettings.firstLoad = settings.firstLoad;
        } else {
            newSettings.firstLoad = 'false';
        }

        localStorage.setItem('elcano-settings',JSON.stringify(newSettings));
        settings = newSettings;
    }
}

function availableLanguages() {
    if (settings.debug){console.log('availableLanguages')};

    var html = '';
    for (i in langs) {
        html += '<option value="'+i+'">'+langs[i].langName+'</option>';
    }
    $('#settingsSelectLang').html(html);
    $('#settingsSelectLang').val(lang);

    $('#loginLangSelect').html(html);
    $('#loginLangSelect').val(lang);
}

function changeLang(l) {
    if (settings.debug){console.log('changeLang')};

    setCookie('elcano-lang',l,3650);
    window.location.reload();
}

/* ---- app utils ---- */

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

function setCookie(name,value,days) {
    var expires = "";
    if (days) {
        var date = new Date();
        date.setTime(date.getTime() + (days*24*60*60*1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "")  + expires + "; path=/";
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
            str.charCodeAt(strLen - 1) << 8 | 0x80
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

/* ----- comandos de teclado ----- */

document.onkeydown = function() {
	if (window.event.keyCode == 86) { if (event.altKey) { changeView('List') } } // alt+v
	if (window.event.keyCode == 88) { if (event.altKey) { showTree() } } // alt+x
	if (window.event.keyCode == 72) { if (event.altKey) { showHistory(true) } } // alt+h
    if (window.event.keyCode == 83) { if (event.altKey) { showSettings(true) } } // alt+s
    if (window.event.keyCode == 37) { if (event.altKey) { event.preventDefault(); prevPath() } } // alt+flecha izquierda
    if (window.event.keyCode == 39) { if (event.altKey) { event.preventDefault(); nextPath() } } // alt+flecha derecha

	if (window.event.keycode == 8) {
		if (settings.debug){console.log('backspace')};
	}

	// navegación por el directorio con las flechas de dirección

    // ATENCIÓN: NO MODIFICAR estos datos sin cambiar las media queries correspondientes
    // Cada variable indica la anchura máxima para cada cantidad de columnas
    cols1=750; cols2=1050; cols3=1300; cols4=1750; cols5=2100;

    if ($('#settings').css('display') == 'block') {
        if ($(':focus').attr('id') == undefined) {
            if (window.event.keyCode == 88) {
                showSettings(false);
            }
        }
    } else if ($('#history').css('display') == 'block') {
        if (window.event.keyCode == 88) {
            showHistory(false);
        }
    } else {
    	if (window.event.keyCode == 37) { // flecha izquierda
    		$('.item').removeClass('itemActive');
    		if (posSelect<=0) {
    			posSelect=0;
    		} else if (posSelect>=$('.item').length-1) {
    			posSelect=$('.item').length-2;
    		} else {
    			posSelect--;
    		}
    		$($('.item')[posSelect]).addClass('itemActive');
    		if (settings.debug){console.log(posSelect)};
    	}
    	if (window.event.keyCode == 39) { // flecha derecha
    		$('.item').removeClass('itemActive');
    		if (posSelect==null) {
    			posSelect=0;
    		} else if (posSelect<=0) {
    			posSelect=1;
    		} else if (posSelect>=$('.item').length-1) {
    			posSelect=$('.item').length-1;
    		} else {
    			posSelect++;
    		}
    		$($('.item')[posSelect]).addClass('itemActive');
    		if (settings.debug){console.log(posSelect)};
    	}
        if (window.event.keyCode == 38) { // fecha arriba
            $('.item').removeClass('itemActive');
            if (posSelect==null) {
                posSelect=0;
            } else if (posSelect<=0) {
                posSelect=0;
            } else {
                if (window.innerWidth < cols1) { // 1 columna
                    if (posSelect-1>=0) { posSelect = posSelect-1; }
                } else if (window.innerWidth < cols2) { // 2 columnas
                    if (posSelect-2>=0) { posSelect = posSelect-2; }
                } else if (window.innerWidth < cols3) { // 3 columnas
                    if (posSelect-3>=0) { posSelect = posSelect-3; }
                } else if (window.innerWidth < cols4) { // 4 columnas
                    if (posSelect-4>=0) { posSelect = posSelect-4; }
                } else if (window.innerWidth < cols5) { // 5 columnas
                    if (posSelect-5>=0) { posSelect = posSelect-5; }
                } else if (window.innerWidth >= cols5) { // 6 columnas
                    if (posSelect-6>=0) { posSelect = posSelect-6; }
                }
            }
            $($('.item')[posSelect]).addClass('itemActive');
    		if (settings.debug){console.log(posSelect)};
        }
        if (window.event.keyCode == 40) { // flecha abajo
            $('.item').removeClass('itemActive');
            if (posSelect==null) {
                posSelect=0;
            } else if (posSelect>=$('.item').length-1) {
                posSelect=$('.item').length-1;
            } else {
                if (window.innerWidth < cols1) { // 1 columna
                    if (posSelect+1<=$('.item').length-1) { posSelect = posSelect+1; }
                } else if (window.innerWidth < cols2) { // 2 columnas
                    if (posSelect+2<=$('.item').length-1) { posSelect = posSelect+2; } else if (posSelect+1<=$('.item').length-1) { posSelect = $('.item').length-1; }
                } else if (window.innerWidth < cols3) { // 3 columnas
                    if (posSelect+3<=$('.item').length-1) { posSelect = posSelect+3; } else if (posSelect+1<=$('.item').length-1) { posSelect = $('.item').length-1; }
                } else if (window.innerWidth < cols4) { // 4 columnas
                    if (posSelect+4<=$('.item').length-1) { posSelect = posSelect+4; } else if (posSelect+1<=$('.item').length-1) { posSelect = $('.item').length-1; }
                } else if (window.innerWidth < cols5) { // 5 columnas
                    if (posSelect+5<=$('.item').length-1) { posSelect = posSelect+5; } else if (posSelect+1<=$('.item').length-1) { posSelect = $('.item').length-1; }
                } else if (window.innerWidth >= cols5) { // 6 columnas
                    if (posSelect+6<=$('.item').length-1) { posSelect = posSelect+6; } else if (posSelect+1<=$('.item').length-1) { posSelect = $('.item').length-1; }
                }
            }
            $($('.item')[posSelect]).addClass('itemActive');
    		if (settings.debug){console.log(posSelect)};
        }
    	if (window.event.keyCode == 13) { // intro
    		if (posSelect!=null) {
    			$($('.item')[posSelect]).click();
    		}
    	}
    }
	$('section').on('click',function() { // deseleccionar los items
		posSelect=null;
		$('.item').removeClass('itemActive');
	});
}

/* -------------------- context menu -------------------- */

$(function() {
	//EVITAMOS que se muestre el MENU CONTEXTUAL del sistema operativo al hacer CLICK con el BOTON DERECHO del RATON
	$(document).bind("contextmenu", function(e){
        var target = $(e.target);

        //console.log(target.parents('.item').length); // booleano que indica si existe un elemento padre con una clase determinada
        //console.log(target.closest('.item').attr('onclick')); // obtener el primer elemento padre con una clase determinada
        //console.log(target.closest('.item').attr('onclick').substring(target.closest('.item').attr('onclick').indexOf('(')+1, target.closest('.item').attr('onclick').indexOf(')')));
        //console.log(target.closest('.item').attr('onclick').indexOf('('));
        //console.log(target.closest('.item').attr('onclick').indexOf(')'));

        if (target.parents('.item').length) { // detecta si elemento sobre el que se hace click es hijo de .item
			$('#context>ul').html('');
            if (target.parents('.itemDir').length) {
                $('#context>ul').append('<li id="contextExplore" onclick="changePath('+target.closest('.item').attr('onclick')+')">'+langs[lang].context.contextExplore+'</li>'); // CLOSEST: obtener el primer elemento padre con una clase determinada
    			$('#context>ul').append('<li id="contextAddFav" onclick="addFavorite('+target.closest('.item').attr('onclick').substring(target.closest('.item').attr('onclick').indexOf('(')+1, target.closest('.item').attr('onclick').indexOf(')'))+')">'+langs[lang].context.contextFavorites+'</li>');
            } else {
                $('#context>ul').append('<li id="contextOpen" onclick="readFich('+target.closest('.item').attr('onclick')+')">'+langs[lang].context.contextOpen+'</li>');
            }

			menu.css({'display':'block', 'left':e.pageX, 'top':e.pageY});
	   		return false;
		}
	});

	//variables de control
	var menuId = "context";
	var menu = $("#"+menuId);

	//Control sobre las opciones del menu contextual
	menu.click(function(e){
	    //si la opcion esta desactivado, no pasa nada
	    if(e.target.className == "disabled"){
	        return false;
	    }
	    //si esta activada, gestionamos cada una y sus acciones
	    else{
	        switch(e.target.id){
	            case "contextExplore":
	                break;
	            case "contextAddFav":
	                break;
	            case "contextOpen":
	                break;
	        }
	        menu.css("display", "none");
	    }
	});

	//controlamos ocultado de menu cuando esta activo
	//click boton principal raton
	$(document).click(function(e){
	    if(e.button == 0 && e.target.parentNode.parentNode.id != menuId){
	        menu.css("display", "none");
	    }
	});
	//pulsacion tecla escape
	$(document).keydown(function(e){
	    if(e.keyCode == 27){
	        menu.css("display", "none");
	    }
	});
});
