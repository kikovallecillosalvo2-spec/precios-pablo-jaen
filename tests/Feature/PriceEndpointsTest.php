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
}
