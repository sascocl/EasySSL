<?php

/**
 *
 * Easy SSL
 * Copyright (C) 2011 Esteban De La Fuente Rubio (esteban[at]delaf.cl)
 *
 * Este programa es software libre: usted puede redistribuirlo y/o modificarlo
 * bajo los términos de la Licencia Pública General GNU publicada
 * por la Fundación para el Software Libre, ya sea la versión 3
 * de la Licencia, o (a su elección) cualquier versión posterior de la misma.
 *
 * Este programa se distribuye con la esperanza de que sea útil, pero
 * SIN GARANTÍA ALGUNA; ni siquiera la garantía implícita
 * MERCANTIL o de APTITUD PARA UN PROPÓSITO DETERMINADO.
 * Consulte los detalles de la Licencia Pública General GNU para obtener
 * una información más detallada.
 *
 * Debería haber recibido una copia de la Licencia Pública General GNU
 * junto a este programa.
 * En caso contrario, consulte <http://www.gnu.org/licenses/gpl.html>.
 *
 */

/**
 * @file index.php
 * Este archivo corresponde al frontend de la aplicación en CL
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
 * @version 2014-11-09
 */

// Nombre de la CA
define('CA_NAME', 'SASCO');
// Ubicación del directorio easyssl que contiene script y certificados
define('DIR', './easyssl');

// definir zona horaria
date_default_timezone_set('America/Santiago');

// crear sesion
session_start();
ob_start();

// Hash para nuevo certificado
if(!isset($_POST['generate'])) {
    $_SESSION['hash'] = md5(date('U')*rand());
}

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>EasySSL</title>
        <link rel="stylesheet" type="text/css" href="style.css" />
        <script type="text/javascript" src="js.js"></script>
    </head>
    <body>
        <div id="container">
            <a href="https://github.com/sascocl/easyssl"><img style="position: absolute; top: 0; right: 0; border: 0;" src="https://camo.githubusercontent.com/a6677b08c955af8400f44c6298f40e7d19cc5b2d/68747470733a2f2f73332e616d617a6f6e6177732e636f6d2f6769746875622f726962626f6e732f666f726b6d655f72696768745f677261795f3664366436642e706e67" alt="Fork me on GitHub" data-canonical-src="https://s3.amazonaws.com/github/ribbons/forkme_right_gray_6d6d6d.png"></a>
            <h1>Generación de certificados SSL</h1>
            <p>Este servicio provee certificados SSL para ser utilizados en sitios web, las características:</p>
            <ul>
                <li>Certificados son identificados (nombre común, <em>common name</em> o CN) por el dominio.</li>
                <li>Archivos .key, .csr y .crt serán entregados comprimidos y encriptados.</li>
                <li>Certificado firmado por una CA propia de este sistema (<a href="?CA_crt">certificado de la CA</a>).</li>
                <li>Si extravía su certificado puede volver a descargar el archivo original, deberá usar la clave ingresada la primera vez.</li>
            </ul>
            <p>Una vez descargado el archivo, para desencriptar y descomprimir utilizar los comandos:</p>
            <pre>$ gpg example.com-ssl.tar.gz.gpg</pre>
            <pre>$ tar xvzf example.com-ssl.tar.gz</pre>
<?php if(isset($_SESSION['status'])) { ?>
            <div class="<?php echo $_SESSION['status']['type']; ?>">
                <?php echo $_SESSION['status']['mesg']; ?>
            </div>
            <?php unset($_SESSION['status']); } ?>
            <h2>Solicitud de un nuevo certificado</h2>
            <p>En la raiz de su dominio crear un archivo de nombre <strong><?php echo $_SESSION['hash']; ?>.txt</strong> (vacío), de tal forma que si su dominio es example.com pueda acceder al archivo así: <strong>http://example.com/<?php echo $_SESSION['hash']; ?>.txt</strong>, esto se utilizará para verificar que usted es el dueño del dominio, una vez generado el certificado puede eliminar este archivo.</p>
            <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" onsubmit="return validarNuevo(this);">
                <label for="name">Nombre</label>
                <input type="text" name="REQUEST_NAME" value="Example S.A." id="name" />
                <label for="c">País</label>
                <input type="text" name="REQUEST_C" value="CL" id="c" />
                <label for="st">Región o provincia</label>
                <input type="text" name="REQUEST_ST" value="Metropolitana" id="st" />
                <label for="l">Ciudad</label>
                <input type="text" name="REQUEST_L" value="Santiago" id="l" />
                <label for="ou">Unidad organizacional</label>
                <input type="text" name="REQUEST_OU" value="Informatica" id="ou" />
                <label for="cn">Dominio</label>
                <input type="text" name="REQUEST_CN" value="example.com" id="cn" />
                <label for="emailAddress">Email</label>
                <input type="text" name="REQUEST_emailAddress" value="webmaster@example.com" id="emailAddress" />
                <label for="password1">Contraseña</label>
                <input type="password" name="REQUEST_PASSWORD1" id="password1" />
                <label for="password2">Repetir contraseña</label>
                <input type="password" name="REQUEST_PASSWORD2" id="password2" />
                <label>&nbsp;</label>
                <input type="submit" name="generate" value="Generar" />
            </form>
            <h2>Descargar certificado generado anteriormente</h2>
            <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" onsubmit="return validarDescarga(this);" >
                <label for="cn">Dominio</label>
                <input type="text" name="REQUEST_CN" value="example.com" id="cn" />
                <label>&nbsp;</label>
                <input type="submit" name="download" value="Descargar" />
            </form>
            <div id="footer">
                <hr />
                &copy; 2011 - <?=date('Y')?> EasySSL<br />
                Un proyecto de <a href="https://sasco.cl">SASCO SpA</a>
            </div>
        </div>
    </body>
</html>

<?php

// funcion para descargar
function download($uri, $name = '')
{
    $gestor = fopen($uri, 'rb');
    $file['size'] = filesize($uri);
    $file['data'] = fread($gestor, $file['size']);
    $file['type'] = mime_content_type($uri);
    $file['name'] = !empty($name) ? $name : basename($uri);
    fclose($gestor);
    // limpiar buffer salida
    ob_clean();
    // envio cabeceras
    header('Cache-control: private');
    header('Content-Disposition: attachment; filename='.$file['name']);
    header('Content-type: '.$file['type']);
    header('Content-length: '.$file['size']);
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    // mostrar file
    print $file['data'];
}

// funcion (alpha) para limpiar parametros recibidos antes de pasarlos a la terminal
// TODO: usar expresion regular para quitar caracteres extraños
function clean($txt)
{
    $txt = str_replace('"', '', $txt);
    $txt = str_replace('\\', '', $txt);
    $txt = str_replace('#', '', $txt);
    $txt = str_replace(';', '', $txt);
    $txt = str_replace('&', '', $txt);
    $txt = str_replace('|', '', $txt);
    $txt = str_replace('>', '', $txt);
    return $txt;
}

// descargar certificado de la CA
if(isset($_GET['CA_crt']))
{
    if (!file_exists(DIR.'/CA/ca.crt')) {
        $_SESSION['status']['type'] = 'error';
        $_SESSION['status']['mesg'] = 'Certificado de la CA no existe';
        $uri_parts = explode('?', $_SERVER['REQUEST_URI'], 2);
        ob_clean();
        header('location: '.$uri_parts[0]);
        exit;
    }
    download(DIR.'/CA/ca.crt', CA_NAME.'.crt');
}

// Si se solicita un certificado nuevo
else if(isset($_POST['generate']))
{
    // Verificar hash en el domino que se solicita, si el archivo no existe se aborta
    if(!fopen('http://'.$_POST['REQUEST_CN'].'/'.$_SESSION['hash'].'.txt', 'r')) {
        $_SESSION['status']['type'] = 'error';
        $_SESSION['status']['mesg'] = 'El archivo .txt no fue encontrado en la raíz del dominio '.$_POST['REQUEST_CN'].'<br />Notar que ahora el nombre del archivo ha cambiado<br />(cambia cada vez que se ingresa a esta página)';
        header('location: '.$_SERVER['REQUEST_URI']);
        exit;
    }
    // Generar certificado
    $cmd = DIR.'/easyssl "'.clean($_POST['REQUEST_NAME']).'" "'.clean($_POST['REQUEST_C']).'" "'.clean($_POST['REQUEST_ST']).'" "'.clean($_POST['REQUEST_L']).'" "'.clean($_POST['REQUEST_OU']).'" "'.clean($_POST['REQUEST_CN']).'" "'.clean($_POST['REQUEST_emailAddress']).'" "'.clean($_POST['REQUEST_PASSWORD1']).'"';
    system($cmd);
    // Verificar que el certificado haya sido generado
    if(!file_exists(DIR.'/CA/newcerts/'.$_POST['REQUEST_CN'].'-ssl.tar.gz.gpg')) {
        $_SESSION['status']['type'] = 'error';
        $_SESSION['status']['mesg'] = 'Certificado para el dominio '.$_POST['REQUEST_CN'].' no fue generado<br />Reporte este problema al proveedor del servicio';
        header('location: '.$_SERVER['REQUEST_URI']);
        exit;
    }
    // Descargar fichero comprimido y encriptado
    download(DIR.'/CA/newcerts/'.$_POST['REQUEST_CN'].'-ssl.tar.gz.gpg');
}

// Si se solicita descargar un certificado creado antes
else if(isset($_POST['download']))
{
    // Verificar que el archivo solicitado exista, si no existe no se puede descargar nada
    if(!file_exists(DIR.'/CA/newcerts/'.$_POST['REQUEST_CN'].'-ssl.tar.gz.gpg')) {
        $_SESSION['status']['type'] = 'error';
        $_SESSION['status']['mesg'] = 'Certificado para el dominio '.$_POST['REQUEST_CN'].' no existe<br />Debe solicitar uno nuevo mediante "Solicitud de un nuevo certificado"';
        header('location: '.$_SERVER['REQUEST_URI']);
        exit;
    }
    // Descargar fichero con clave y certificado
    download(DIR.'/CA/newcerts/'.$_POST['REQUEST_CN'].'-ssl.tar.gz.gpg');
}
