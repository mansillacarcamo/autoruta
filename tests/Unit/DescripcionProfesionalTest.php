<?php

namespace Tests\Unit;

use App\Support\DescripcionProfesional;
use PHPUnit\Framework\TestCase;

class DescripcionProfesionalTest extends TestCase
{
    public function test_ordena_un_aviso_escrito_todo_seguido(): void
    {
        $texto = "e vende Chevrolet Groove 1.5 Premier\nunico dueña\nse vende por no uso\ncon 27000 kilometro\nllame con boton 1 copia\n"
            . "-Llantas bicolor\n-Espejos abatibles calefactable automatico\n- Climatizador\n- mandos al volante\n- Crucero\n- asientos de cuero\n- sunroof\n"
            . "- Radio con android car y apple car con cable\ntiene unos detalle esteticos pero nada grabe\nPrecio conversable";

        $r = DescripcionProfesional::formatear($texto);

        $this->assertSame(['Único dueño', 'Precio conversable'], $r['destacados']);
        $this->assertSame([
            'Se vende Chevrolet Groove 1.5 Premier. Se vende por no uso.',
            'Tiene unos detalles estéticos, pero nada grave.',
        ], $r['parrafos']);
        $this->assertSame([
            'Llantas bicolor', 'Espejos abatibles calefactables automáticos', 'Climatizador', 'Mandos al volante', 'Crucero',
            'Asientos de cuero', 'Sunroof', 'Radio con Android Auto y Apple CarPlay con cable',
        ], $r['equipamiento']);
    }

    public function test_texto_en_mayusculas_y_vacio(): void
    {
        $r = DescripcionProfesional::formatear("VENDO AUTO EN EXCELENTE ESTADO\nPAPELES AL DIA");
        $this->assertSame(['Vendo auto en excelente estado.'], $r['parrafos']);
        $this->assertSame(['Papeles al día'], $r['destacados']);

        $this->assertSame(['destacados' => [], 'parrafos' => [], 'equipamiento' => []], DescripcionProfesional::formatear(''));
    }
}
