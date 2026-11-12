# Calculadora de precios · `PriceController` con tests unitarios (Laravel 13)

Controlador que convierte importes entre **euros y dólares** y calcula el **precio con descuento**. Su lógica está cubierta por varios tests unitarios de PHPUnit.

## `App\Http\Controllers\PriceController`

| Método | Qué hace | Ejemplo |
|---|---|---|
| `convertir(float $cantidad, string $de, string $a): float` | Convierte entre `EUR` y `USD` con una tasa fija de **1 EUR = 1,08 USD**. Redondea a 2 decimales y acepta los códigos en mayúsculas o minúsculas. | `convertir(100, 'EUR', 'USD')` → `108.0` |
| `calcularDescuento(float $precio, float $porcentaje): float` | Aplica un descuento del 0 al 100 % y redondea a 2 decimales. | `calcularDescuento(80, 25)` → `60.0` |

Las entradas no válidas (cantidad o precio negativos, moneda distinta de EUR/USD, porcentaje fuera de 0–100) lanzan `InvalidArgumentException`.

El controlador también expone ambas funciones como endpoints JSON:

| Ruta | Respuesta |
|---|---|
| `GET /api/convertir?cantidad=100&de=EUR&a=USD` | `{"cantidad":100,"de":"EUR","a":"USD","resultado":108}` |
| `GET /api/descuento?precio=80&porcentaje=25` | `{"precio":80,"porcentaje":25,"precio_final":60}` |

## Tests

`tests/Unit/PriceControllerTest.php` contiene **20 tests unitarios** (14 métodos; el data provider se ejecuta 7 veces):

- **Conversión:** EUR→USD, USD→EUR, misma moneda, redondeo a 2 decimales, códigos en minúsculas o con espacios, cantidad cero, ida y vuelta, moneda no soportada y cantidad negativa.
- **Descuento:** un *data provider* con 7 casos (25 %, 10 %, 50 %, 12,5 %, 0 %, 100 % y precio 0), más porcentaje mayor que 100, porcentaje negativo y precio negativo.
- **Combinado:** aplicar un descuento y convertir el resultado a dólares.

`tests/Feature/PriceEndpointsTest.php` añade 4 tests de los endpoints HTTP.

```bash
php artisan test --filter=PriceControllerTest   # solo los tests unitarios del controlador
php artisan test                                # todos
```

Resultado:

```
Price Controller (Tests\Unit\PriceController)
 ✔ Convierte euros a dolares
 ✔ Convierte dolares a euros
 ✔ Convertir a la misma moneda no cambia la cantidad
 ✔ El resultado se redondea a dos decimales
 ✔ Acepta codigos de moneda en minusculas y con espacios
 ✔ Convertir cero devuelve cero
 ✔ Ida y vuelta recupera la cantidad original
 ✔ Rechaza una moneda no soportada
 ✔ Rechaza una cantidad negativa
 ✔ Calcula el precio con descuento with data set "25 % de 80 €"
 ✔ Calcula el precio con descuento with data set "10 % de 19,99 €"
 ✔ Calcula el precio con descuento with data set "50 % de 100 €"
 ✔ Calcula el precio con descuento with data set "descuento decimal 12,5 %"
 ✔ Calcula el precio con descuento with data set "sin descuento (0 %)"
 ✔ Calcula el precio con descuento with data set "gratis (100 %)"
 ✔ Calcula el precio con descuento with data set "precio cero"
 ✔ Rechaza un porcentaje mayor que 100
 ✔ Rechaza un porcentaje negativo
 ✔ Rechaza un precio negativo
 ✔ Aplica un descuento y convierte el resultado a dolares

OK (26 tests, 35 assertions)
```

## Puesta en marcha

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan serve        # http://127.0.0.1:8000
```
