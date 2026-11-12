<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class PriceController extends Controller
{
    /** Tipo de cambio fijo de la práctica: 1 EUR = 1,08 USD. */
    public const TASA_EUR_USD = 1.08;

    public const MONEDAS = ['EUR', 'USD'];

    /**
     * Convierte una cantidad entre euros y dólares, redondeada a 2 decimales.
     *
     * @throws InvalidArgumentException si la cantidad es negativa o la moneda no es EUR/USD
     */
    public function convertir(float $cantidad, string $de, string $a): float
    {
        $de = strtoupper(trim($de));
        $a = strtoupper(trim($a));

        if ($cantidad < 0) {
            throw new InvalidArgumentException('La cantidad no puede ser negativa.');
        }
        foreach ([$de, $a] as $moneda) {
            if (! in_array($moneda, self::MONEDAS, true)) {
                throw new InvalidArgumentException("Moneda no soportada: {$moneda}. Usa EUR o USD.");
            }
        }

        $resultado = match (true) {
            $de === $a => $cantidad,
            $de === 'EUR' => $cantidad * self::TASA_EUR_USD,
            default => $cantidad / self::TASA_EUR_USD,
        };

        return round($resultado, 2);
    }

    /**
     * Aplica un porcentaje de descuento a un precio, redondeado a 2 decimales.
     *
     * @throws InvalidArgumentException si el precio es negativo o el porcentaje no está entre 0 y 100
     */
    public function calcularDescuento(float $precio, float $porcentaje): float
    {
        if ($precio < 0) {
            throw new InvalidArgumentException('El precio no puede ser negativo.');
        }
        if ($porcentaje < 0 || $porcentaje > 100) {
            throw new InvalidArgumentException('El descuento debe estar entre 0 y 100.');
        }

        return round($precio * (1 - $porcentaje / 100), 2);
    }

    /** GET /api/convertir?cantidad=100&de=EUR&a=USD */
    public function conversion(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'cantidad' => ['required', 'numeric', 'min:0'],
            'de' => ['required', 'string'],
            'a' => ['required', 'string'],
        ]);

        return $this->responder(fn () => [
            'cantidad' => (float) $datos['cantidad'],
            'de' => strtoupper($datos['de']),
            'a' => strtoupper($datos['a']),
            'resultado' => $this->convertir((float) $datos['cantidad'], $datos['de'], $datos['a']),
        ]);
    }

    /** GET /api/descuento?precio=80&porcentaje=25 */
    public function descuento(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'precio' => ['required', 'numeric', 'min:0'],
            'porcentaje' => ['required', 'numeric', 'between:0,100'],
        ]);

        return $this->responder(fn () => [
            'precio' => (float) $datos['precio'],
            'porcentaje' => (float) $datos['porcentaje'],
            'precio_final' => $this->calcularDescuento((float) $datos['precio'], (float) $datos['porcentaje']),
        ]);
    }

    private function responder(callable $calculo): JsonResponse
    {
        try {
            return response()->json($calculo());
        } catch (InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
