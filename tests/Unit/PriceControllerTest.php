<?php

namespace Tests\Unit;

use App\Http\Controllers\PriceController;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PriceControllerTest extends TestCase
{
    private PriceController $precios;

    protected function setUp(): void
    {
        parent::setUp();
        $this->precios = new PriceController();
    }

    /* ---------------------------------------------------------------
     | Conversión de monedas
     * --------------------------------------------------------------- */

    #[Test]
    public function convierte_euros_a_dolares(): void
    {
        $this->assertSame(108.0, $this->precios->convertir(100, 'EUR', 'USD'));
    }

    #[Test]
    public function convierte_dolares_a_euros(): void
    {
        $this->assertSame(100.0, $this->precios->convertir(108, 'USD', 'EUR'));
    }

    #[Test]
    public function convertir_a_la_misma_moneda_no_cambia_la_cantidad(): void
    {
        $this->assertSame(49.99, $this->precios->convertir(49.99, 'EUR', 'EUR'));
        $this->assertSame(49.99, $this->precios->convertir(49.99, 'USD', 'USD'));
    }

    #[Test]
    public function el_resultado_se_redondea_a_dos_decimales(): void
    {
        // 10 / 1.08 = 9.259259... -> 9.26
        $this->assertSame(9.26, $this->precios->convertir(10, 'USD', 'EUR'));
    }

    #[Test]
    public function acepta_codigos_de_moneda_en_minusculas_y_con_espacios(): void
    {
        $this->assertSame(54.0, $this->precios->convertir(50, ' eur', 'usd '));
    }

    #[Test]
    public function convertir_cero_devuelve_cero(): void
    {
        $this->assertSame(0.0, $this->precios->convertir(0, 'EUR', 'USD'));
    }

    #[Test]
    public function ida_y_vuelta_recupera_la_cantidad_original(): void
    {
        $dolares = $this->precios->convertir(250, 'EUR', 'USD');

        $this->assertSame(250.0, $this->precios->convertir($dolares, 'USD', 'EUR'));
    }

    #[Test]
    public function rechaza_una_moneda_no_soportada(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Moneda no soportada: GBP');

        $this->precios->convertir(100, 'EUR', 'GBP');
    }

    #[Test]
    public function rechaza_una_cantidad_negativa(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->precios->convertir(-5, 'EUR', 'USD');
    }

    /* ---------------------------------------------------------------
     | Cálculo de descuentos
     * --------------------------------------------------------------- */

    #[Test]
    #[DataProvider('descuentos')]
    public function calcula_el_precio_con_descuento(float $precio, float $porcentaje, float $esperado): void
    {
        $this->assertSame($esperado, $this->precios->calcularDescuento($precio, $porcentaje));
    }

    /** @return array<string, array{float, float, float}> */
    public static function descuentos(): array
    {
        return [
            '25 % de 80 €' => [80.0, 25.0, 60.0],
            '10 % de 19,99 €' => [19.99, 10.0, 17.99],
            '50 % de 100 €' => [100.0, 50.0, 50.0],
            'descuento decimal 12,5 %' => [200.0, 12.5, 175.0],
            'sin descuento (0 %)' => [59.95, 0.0, 59.95],
            'gratis (100 %)' => [59.95, 100.0, 0.0],
            'precio cero' => [0.0, 30.0, 0.0],
        ];
    }

    #[Test]
    public function rechaza_un_porcentaje_mayor_que_100(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('El descuento debe estar entre 0 y 100.');

        $this->precios->calcularDescuento(100, 150);
    }

    #[Test]
    public function rechaza_un_porcentaje_negativo(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->precios->calcularDescuento(100, -10);
    }

    #[Test]
    public function rechaza_un_precio_negativo(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('El precio no puede ser negativo.');

        $this->precios->calcularDescuento(-20, 10);
    }

    /* ---------------------------------------------------------------
     | Ambas funciones combinadas
     * --------------------------------------------------------------- */

    #[Test]
    public function aplica_un_descuento_y_convierte_el_resultado_a_dolares(): void
    {
        $enEuros = $this->precios->calcularDescuento(150, 20);           // 120.00 €

        $this->assertSame(129.6, $this->precios->convertir($enEuros, 'EUR', 'USD')); // 129.60 $
    }
}
