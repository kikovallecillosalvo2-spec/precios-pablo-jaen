<?php

namespace Tests\Feature;

use Tests\TestCase;

class PriceEndpointsTest extends TestCase
{
    public function test_el_endpoint_de_conversion_devuelve_el_resultado(): void
    {
        $this->getJson('/api/convertir?cantidad=100&de=EUR&a=USD')
            ->assertOk()
            ->assertExactJson(['cantidad' => 100, 'de' => 'EUR', 'a' => 'USD', 'resultado' => 108]);
    }

    public function test_el_endpoint_de_descuento_devuelve_el_precio_final(): void
    {
        $this->getJson('/api/descuento?precio=80&porcentaje=25')
            ->assertOk()
            ->assertJson(['precio_final' => 60]);
    }

    public function test_una_moneda_no_soportada_devuelve_422(): void
    {
        $this->getJson('/api/convertir?cantidad=100&de=EUR&a=GBP')
            ->assertStatus(422)
            ->assertJson(['error' => 'Moneda no soportada: GBP. Usa EUR o USD.']);
    }

    public function test_un_descuento_fuera_de_rango_no_pasa_la_validacion(): void
    {
        $this->getJson('/api/descuento?precio=80&porcentaje=150')
            ->assertStatus(422)
            ->assertJsonValidationErrors('porcentaje');
    }

    public function test_la_pagina_web_muestra_los_formularios(): void
    {
        $this->get('/')->assertOk()->assertSee('Conversor de monedas')->assertSee('Calcular descuento');
    }

    public function test_la_pagina_web_muestra_la_conversion(): void
    {
        $this->get('/?cantidad=100&de=EUR&a=USD')->assertOk()->assertSee('108,00 USD');
    }

    public function test_la_pagina_web_muestra_el_precio_con_descuento(): void
    {
        $this->get('/?precio=80&porcentaje=25')->assertOk()->assertSee('Precio final: 60,00 €');
    }

    public function test_la_pagina_web_muestra_el_error_de_un_descuento_invalido(): void
    {
        $this->get('/?precio=80&porcentaje=150')->assertOk()->assertSee('El descuento debe estar entre 0 y 100.');
    }
}
