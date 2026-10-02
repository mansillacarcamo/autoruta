<?php

namespace App\Support;

use Illuminate\Support\Str;

// Ordena la descripción que escribe el vendedor (a veces todo seguido, sin mayúsculas ni tildes)
// en: destacados (etiquetas), párrafos limpios y lista de equipamiento. No modifica lo guardado:
// se aplica al mostrar el aviso, así que funciona también con los avisos antiguos.
class DescripcionProfesional
{
    // Frases que se muestran como etiqueta "✓ ..." en vez de texto.
    private const DESTACADOS = [
        '/(?<!\p{L})([uú]nic[oa]|1|un)\s+due[ñn][oa]s?\b/iu' => 'Único dueño',
        '/\bprecio\s+(conversable|negociable|a\s+conversar)\b|\b(conversable|negociable)\b/iu' => 'Precio conversable',
        '/\bpapeles\s+al\s+d[ií]a\b/iu' => 'Papeles al día',
        '/\bmantenciones?\s+(al\s+d[ií]a|en\s+(la\s+)?(concesionari[oa]|marca|agencia))\b/iu' => 'Mantenciones al día',
        '/\b(recibo|acepto|se\s+recibe)\s+(vehiculo|vehículo|auto|permuta)|\bpermuta\b/iu' => 'Acepta permuta',
        '/\bsin\s+choques?\b/iu' => 'Sin choques',
        '/\bfull\s+equipo\b/iu' => 'Full equipo',
        '/\b(con\s+)?garant[ií]a\b/iu' => 'Con garantía',
        '/\b(financiamiento|credito|crédito)\b/iu' => 'Acepta financiamiento',
    ];

    // Palabras típicas de equipamiento: una línea corta con alguna de estas se trata como ítem.
    private const PALABRAS_EQUIPO = 'climatizador|aire acondicionado|sunroof|techo|crucero|control crucero|llantas|espejos|asientos|radio|pantalla|camara|cámara|sensor|sensores|bluetooth|mandos al volante|alzavidrios|cierre centralizado|airbag|airbags|abs|android auto|carplay|neblineros|focos|led|xenon|gps|navegador|tapiz|cuero|portalon|portalón|barras|enganche|tiron|tirón|alarma|computador|keyless|start|isofix|vidrios|parabrisas|neumaticos|neumáticos|carroceria|carrocería';

    // Errores y términos frecuentes en los avisos.
    private const CORRECCIONES = [
        '/^e vende\b/iu' => 'Se vende',
        '/\bandroid\s*car\b(?!play)/iu' => 'Android Auto',
        '/\bapple\s*car\b(?!play)/iu' => 'Apple CarPlay',
        '/\bapple\s*carplay\b/iu' => 'Apple CarPlay',
        '/\bandroid\s*auto\b/iu' => 'Android Auto',
        '/\bgrabe(s)?\b/iu' => 'grave$1',
        '/\bunic([oa])\b/iu' => 'únic$1',
        '/\bvehiculo(s)?\b/iu' => 'vehículo$1',
        '/\bkilometro(s)?\b/iu' => 'kilómetro$1',
        '/\bautomatic([oa]s?)\b/iu' => 'automátic$1',
        '/\belectric([oa]s?)\b/iu' => 'eléctric$1',
        '/\bmecanic([oa]s?)\b/iu' => 'mecánic$1',
        '/\bestetic([oa]s?)\b/iu' => 'estétic$1',
        '/\bcamara(s)?\b/iu' => 'cámara$1',
        '/\bbateria(s)?\b/iu' => 'batería$1',
        '/\bneumatico(s)?\b/iu' => 'neumático$1',
        '/\btambien\b/iu' => 'también',
        '/\bdia(s)?\b/iu' => 'día$1',
        '/\b(unos|algunos|pequeños|pequenos)\s+detalle\b/iu' => '$1 detalles',
        '/\bportalon\b/iu' => 'portalón',
        '/\bcarroceria\b/iu' => 'carrocería',
        '/\btraccion\b/iu' => 'tracción',
        '/\bdiesel\b/iu' => 'diésel',
        '/\bcalefactable\b(?=.*\bespejos\b)|(?<=espejos abatibles )calefactable\b/iu' => 'calefactables',
        '/\b(abatibles calefactables) automático\b/iu' => '$1 automáticos',
        '/\s+,/u' => ',',
        '/\s{2,}/u' => ' ',
    ];

    /** @return array{destacados: list<string>, parrafos: list<string>, equipamiento: list<string>} */
    public static function formatear(?string $texto, array $equipamientoExtra = []): array
    {
        $destacados = [];
        $parrafos = [];
        $equipamiento = [];
        $bloque = [];

        $cerrarBloque = function () use (&$bloque, &$parrafos) {
            if ($bloque) {
                $parrafos[] = implode(' ', $bloque);
                $bloque = [];
            }
        };

        foreach (preg_split('/\R/u', (string) $texto) as $linea) {
            $linea = trim($linea);
            if ($linea === '') {
                $cerrarBloque();
                continue;
            }

            $esItem = (bool) preg_match('/^\s*([-•*·✓✔►>]+|\d+[.)])\s*/u', $linea);
            $linea = trim(preg_replace('/^\s*([-•*·✓✔►>]+|\d+[.)])\s*/u', '', $linea));
            $linea = self::normalizarMayusculas($linea);
            $linea = self::corregir($linea);
            if ($linea === '') {
                continue;
            }

            // Instrucciones de contacto o kilometraje suelto: ya están en los botones y la ficha.
            if (self::esRuido($linea)) {
                continue;
            }

            // Frases destacadas: se vuelven etiqueta; si la línea era solo eso, no queda como texto.
            $resto = $linea;
            foreach (self::DESTACADOS as $patron => $etiqueta) {
                if (preg_match($patron, $resto)) {
                    $destacados[$etiqueta] = $etiqueta;
                    $resto = trim(preg_replace($patron, '', $resto), " \t,.;:-");
                }
            }
            if ($resto === '' || preg_match('/^(precio|el precio|y)$/iu', $resto)) {
                continue;
            }
            if ($resto !== $linea && mb_strlen($resto) < 4) {
                continue;
            }

            if ($esItem || self::pareceEquipamiento($linea)) {
                $cerrarBloque();
                $equipamiento[] = self::capitalizar(rtrim($linea, " .;,"));
                continue;
            }

            $bloque[] = self::oracion($linea);
        }
        $cerrarBloque();

        foreach ($equipamientoExtra as $item) {
            $item = self::capitalizar(trim((string) $item));
            if ($item !== '') {
                $equipamiento[] = $item;
            }
        }

        return [
            'destacados' => array_values($destacados),
            'parrafos' => $parrafos,
            'equipamiento' => array_values(array_unique($equipamiento)),
        ];
    }

    private static function corregir(string $linea): string
    {
        foreach (self::CORRECCIONES as $patron => $reemplazo) {
            $linea = preg_replace($patron, $reemplazo, $linea);
        }
        // "detalles estéticos pero nada grave" -> coma antes de "pero".
        $linea = preg_replace('/(\w)\s+pero\s+/u', '$1, pero ', $linea);

        return trim($linea);
    }

    // Texto escrito TODO EN MAYÚSCULAS se pasa a minúsculas (después se capitaliza la oración).
    private static function normalizarMayusculas(string $linea): string
    {
        $letras = preg_replace('/[^\p{L}]/u', '', $linea);
        if (mb_strlen($letras) >= 8 && $letras === mb_strtoupper($letras)) {
            return mb_strtolower($linea);
        }

        return $linea;
    }

    private static function esRuido(string $linea): bool
    {
        if (mb_strlen($linea) <= 60 && preg_match('/\b(llam[ea]n?|llamar|whats?app|wsp|wasap|contact[ea]n?|contactar|escrib[ae]n?|bot[oó]n)\b/iu', $linea)) {
            return true;
        }

        return (bool) preg_match('/^(con\s+|tiene\s+)?[\d.,]+\s*(km|kms|kilómetros?)\.?$/iu', $linea);
    }

    private static function pareceEquipamiento(string $linea): bool
    {
        return mb_strlen($linea) <= 60
            && ! preg_match('/[.!?]\s*\S/u', $linea)
            && preg_match('/\b(' . self::PALABRAS_EQUIPO . ')\b/iu', $linea);
    }

    private static function capitalizar(string $texto): string
    {
        return Str::ucfirst(trim($texto));
    }

    private static function oracion(string $linea): string
    {
        $linea = self::capitalizar($linea);

        return preg_match('/[.!?…:]$/u', $linea) ? $linea : $linea . '.';
    }
}
