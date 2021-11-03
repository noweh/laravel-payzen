<?php

namespace Noweh\Payzen\Tests;

use Payzen;

class PayzenTest extends TestCase
{
    public function testAsciiTranscode(): void
    {
        $this->assertIsString(Payzen::ascii_transcode('an information12345', 'a', 255, true));
        $this->assertIsNumeric(Payzen::ascii_transcode('an information12345', 'n', 255, true));
        $this->assertMatchesRegularExpression(
            '/^[A-Za-z0-9 \-]+$/', Payzen::ascii_transcode('an information^123&ç', 'an', 255, true)
        );
    }

    public function testSignature(): void
    {
        $this->assertEquals(
            'ZbUfFXw5gO8nZL7nrMWd3OtvrFMehKe1PxsbNB9SRbk=',
            Payzen::set_trans_date(20211103100443)->set_signature()->get_signature()
        );
    }

    public function testParametersInsertion(): void
    {
        $newParameters = [
            'vads_payment_config' => 'MULTI',
            'vads_trans_date' => '20211103102516',
            'vads_ctx_mode' => 'PRODUCTION',
        ];

        $this->assertNotEmpty(
            array_filter(Payzen::get_params(), static function ($value, $key) use ($newParameters) {
                return (array_key_exists($key, $newParameters) && $newParameters[$key] !== $value);
            }, ARRAY_FILTER_USE_BOTH)
        );

        $this->assertEmpty(
            array_filter(Payzen::set_params($newParameters)->get_params(), static function ($value, $key) use ($newParameters) {
                return (array_key_exists($key, $newParameters) && $newParameters[$key] !== $value);
            }, ARRAY_FILTER_USE_BOTH)
        );
    }

    public function testAmount(): void
    {
        $instance = Payzen::
            add_product(
                [
                    'label' => 'Concert Coldplay 2016',
                    'amount' => 235.00,
                    'type' => 'ENTERTAINMENT',
                    'ref' => 'COLD016',
                    'qty' => 3
                ]
            )
            ->set_amount()
        ;
        $this->assertEquals(705, $instance->get_amount());
        $this->assertEquals(70500, $instance->get_amount(false));
    }

    public function testPaymentForm(): void
    {
        $blocks_html = Payzen::set_amount(300)
            ->set_trans_id(123456)
            ->set_order_info(Payzen::ascii_transcode('an information', 'an', 255, true))
            ->set_order_info2(Payzen::ascii_transcode('another information', 'an', 255, true))
            ->set_return_mode('POST')
            ->set_signature()
            ->get_form('<div><input id="spSubmit" type="submit" value="Pay" class="Button Button--black"></div>')
        ;

        $dom = new \DOMDocument();
        $dom->loadHTML($blocks_html);

        $this->assertTrue($dom->getElementById('spSubmit')->hasAttributes());
    }
}
