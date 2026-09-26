<?php

return [
    'nombre_sitio' => 'AutoRuta',
    'max_fotos_vehiculo' => 8,
    'max_publicaciones_activas' => 3,
    'duracion_publicacion_dias' => 90,
    'precio_min_vehiculo' => 300000,
    'precio_max_vehiculo' => 500000000,
    'anio_min_vehiculo' => 1950,
    'max_banners_negocio' => 3,
    'visitas_inicio' => 1000,

    // Disco para fotos y banners. Si el disco por defecto es un bucket (Laravel Cloud Object
    // Storage) se usa ese; si es el disco local privado, se usa "public".
    'disco_archivos' => env('ARCHIVOS_DISCO') ?: (env('FILESYSTEM_DISK', 'local') === 'local' ? 'public' : env('FILESYSTEM_DISK')),
    'contacto_ubicacion' => 'Puerto Montt, Región de Los Lagos',

    'contacto_whatsapp' => '+56962148407',
    'contacto_telefono' => '+56 9 6214 8407',
    'contacto_email' => 'info@autoruta.cl',

    // Reemplazar por las URLs reales cuando existan las cuentas del sitio.
    'redes_sociales' => [
        'facebook' => 'https://facebook.com',
        'instagram' => 'https://instagram.com',
    ],
];
